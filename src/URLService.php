<?php

class URLService
{
    private Database $db;
    private URLShortener $shortener;

    public function __construct(Database $db, URLShortener $shortener)
    {
        $this->db = $db;
        $this->shortener = $shortener;
    }

    public function encodeURL(string $originalUrl): array
    {
        if (!$this->isValidUrl($originalUrl)) {
            throw new InvalidArgumentException('Invalid URL provided');
        }

        // Check if URL already exists
        $existing = $this->db->fetchOne(
            'SELECT id, short_code FROM urls WHERE original_url = ?',
            [$originalUrl]
        );

        if ($existing) {
            return [
                'short_url' => $this->buildShortUrl($existing['short_code']),
                'original_url' => $originalUrl
            ];
        }

        // Insert new URL
        $id = $this->db->insert('urls', [
            'original_url' => $originalUrl,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if (!$id) {
            throw new Exception('Failed to insert URL into database');
        }

        // Generate short code
        $shortCode = $this->shortener->encode($id);
        
        // Update with short code
        $result = $this->db->query(
            'UPDATE urls SET short_code = ? WHERE id = ?',
            [$shortCode, $id]
        );

        if (!$result) {
            throw new Exception('Failed to update short code');
        }

        return [
            'short_url' => $this->buildShortUrl($shortCode),
            'original_url' => $originalUrl
        ];
    }

    public function decodeURL(string $shortUrl): array
    {
        $shortCode = $this->extractShortCode($shortUrl);
        
        if (empty($shortCode)) {
            throw new InvalidArgumentException('Invalid short URL format');
        }

        $result = $this->db->fetchOne(
            'SELECT original_url FROM urls WHERE short_code = ?',
            [$shortCode]
        );

        if (!$result) {
            throw new Exception('Short URL not found');
        }

        // Update access count
        $this->db->query(
            'UPDATE urls SET access_count = access_count + 1, last_accessed = ? WHERE short_code = ?',
            [date('Y-m-d H:i:s'), $shortCode]
        );

        return [
            'original_url' => $result['original_url'],
            'short_url' => $shortUrl
        ];
    }

    private function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    private function buildShortUrl(string $shortCode): string
    {
        $baseUrl = $_ENV['BASE_URL'] ?? 'http://shrt.est';
        return rtrim($baseUrl, '/') . '/' . $shortCode;
    }

    private function extractShortCode(string $shortUrl): string
    {
        $parts = parse_url($shortUrl);
        return ltrim($parts['path'] ?? '', '/');
    }
}