<?php

/**
 * URL Service class for managing URL shortening operations with database persistence
 */
class URLService
{
    private Database $db;
    private URLShortener $shortener;

    /**
     * Constructor
     * 
     * @param Database $db Database instance
     * @param URLShortener $shortener URL shortener instance
     */
    public function __construct(Database $db, URLShortener $shortener)
    {
        $this->db = $db;
        $this->shortener = $shortener;
    }

    /**
     * Encode a URL and store it in the database
     * 
     * @param string $originalUrl The original URL to shorten
     * @return array Array containing short_url and original_url
     * @throws InvalidArgumentException If URL is invalid
     * @throws Exception If database operation fails
     */
    public function encodeURL(string $originalUrl): array
    {
        if (!$this->isValidUrl($originalUrl)) {
            throw new InvalidArgumentException('Invalid URL provided');
        }

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

        // Generate unique short code
        do {
            $shortCode = $this->shortener->generateShortCode();
            $exists = $this->db->fetchOne(
                'SELECT id FROM urls WHERE short_code = ?',
                [$shortCode]
            );
        } while ($exists);

        // Insert new URL with short code
        $id = $this->db->insert('urls', [
            'original_url' => $originalUrl,
            'short_code' => $shortCode,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if (!$id) {
            throw new Exception('Failed to insert URL into database');
        }

        return [
            'short_url' => $this->buildShortUrl($shortCode),
            'original_url' => $originalUrl
        ];
    }

    /**
     * Decode a short URL and retrieve the original URL
     * 
     * @param string $shortUrl The short URL to decode
     * @return array Array containing original_url and short_url
     * @throws InvalidArgumentException If short URL format is invalid
     * @throws Exception If short URL not found
     */
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

        $this->db->query(
            'UPDATE urls SET access_count = access_count + 1, last_accessed = ? WHERE short_code = ?',
            [date('Y-m-d H:i:s'), $shortCode]
        );

        return [
            'original_url' => $result['original_url'],
            'short_url' => $shortUrl
        ];
    }

    /**
     * Validate if a URL is properly formatted
     * 
     * @param string $url URL to validate
     * @return bool True if valid, false otherwise
     */
    private function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Build complete short URL from short code
     * 
     * @param string $shortCode The short code
     * @return string Complete short URL
     */
    private function buildShortUrl(string $shortCode): string
    {
        $baseUrl = $_ENV['BASE_URL'] ?? 'http://shrt.est';
        return rtrim($baseUrl, '/') . '/' . $shortCode;
    }

    /**
     * Extract short code from complete short URL
     * 
     * @param string $shortUrl Complete short URL
     * @return string The extracted short code
     */
    private function extractShortCode(string $shortUrl): string
    {

        if (filter_var($shortUrl, FILTER_VALIDATE_URL)) {
            $parts = parse_url($shortUrl);
            $path = ltrim($parts['path'] ?? '', '/');

            $basePath = 'url-shortener/public';
            if (strpos($path, $basePath) === 0) {
                $path = substr($path, strlen($basePath));
            }
            return ltrim($path, '/');
        }

        return $shortUrl;
    }
}