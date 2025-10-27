<?php

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/URLShortener.php';
require_once __DIR__ . '/../src/URLService.php';
require_once __DIR__ . '/../src/EnvLoader.php';

class URLServiceTest
{
    private ?URLService $urlService = null;
    private ?Database $db = null;

    public function __construct()
    {
        EnvLoader::load(__DIR__ . '/../.env');
        
        $config = [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'database' => $_ENV['DB_NAME'] ?? 'url_shortener',
            'username' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASS'] ?? '',
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        ];

        try {
            $this->db = new Database($config);
            $this->setupTestDatabase();
            $shortener = new URLShortener();
            $this->urlService = new URLService($this->db, $shortener);
        } catch (Exception $e) {
            echo "Warning: Database connection failed. Skipping URLService tests.\n";
            echo "Error: " . $e->getMessage() . "\n\n";
            return;
        }
    }

    private function setupTestDatabase(): void
    {
        $this->db->query("CREATE DATABASE IF NOT EXISTS url_shortener_test");
        $this->db->query("USE url_shortener_test");
        $this->db->query("DROP TABLE IF EXISTS urls");
        $this->db->query("
            CREATE TABLE urls (
                id INT AUTO_INCREMENT PRIMARY KEY,
                original_url TEXT NOT NULL,
                short_code VARCHAR(10) UNIQUE,
                access_count INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_accessed TIMESTAMP NULL,
                INDEX idx_short_code (short_code)
            )
        ");
    }

    public function testEncodeValidURL(): void
    {
        if (!$this->db) return;

        $url = 'https://example.com/test';
        $result = $this->urlService->encodeURL($url);
        
        $this->assertArrayHasKey('short_url', $result);
        $this->assertArrayHasKey('original_url', $result);
        $this->assertEquals($url, $result['original_url']);
        $this->assertStringContains('/', $result['short_url']);
        
        echo "✓ Encode valid URL test passed\n";
    }

    public function testEncodeInvalidURL(): void
    {
        if (!$this->db) return;

        try {
            $this->urlService->encodeURL('invalid-url');
            throw new Exception('Should have thrown InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            echo "✓ Encode invalid URL test passed\n";
        }
    }

    public function testDecodeShortenedURL(): void
    {
        if (!$this->db) return;

        $originalUrl = 'https://example.com/decode-test';
        $encoded = $this->urlService->encodeURL($originalUrl);
        $decoded = $this->urlService->decodeURL($encoded['short_url']);
        
        $this->assertEquals($originalUrl, $decoded['original_url']);
        $this->assertEquals($encoded['short_url'], $decoded['short_url']);
        
        echo "✓ Decode shortened URL test passed\n";
    }

    public function testDecodeNonExistentURL(): void
    {
        if (!$this->db) return;

        try {
            $baseUrl = $_ENV['BASE_URL'] ?? 'http://localhost/url-shortener';
            $this->urlService->decodeURL($baseUrl . '/nonexistent');
            throw new Exception('Should have thrown Exception');
        } catch (Exception $e) {
            if ($e->getMessage() === 'Should have thrown Exception') {
                throw $e;
            }
            echo "✓ Decode non-existent URL test passed\n";
        }
    }

    public function testDuplicateURLHandling(): void
    {
        if (!$this->db) return;

        $url = 'https://example.com/duplicate-test';
        $result1 = $this->urlService->encodeURL($url);
        $result2 = $this->urlService->encodeURL($url);
        
        $this->assertEquals($result1['short_url'], $result2['short_url']);
        
        echo "✓ Duplicate URL handling test passed\n";
    }

    private function assertArrayHasKey(string $key, array $array): void
    {
        if (!array_key_exists($key, $array)) {
            throw new Exception("Array does not have key: {$key}");
        }
    }

    private function assertEquals($expected, $actual): void
    {
        if ($expected !== $actual) {
            throw new Exception("Expected {$expected}, got {$actual}");
        }
    }

    private function assertStringContains(string $needle, string $haystack): void
    {
        if (strpos($haystack, $needle) === false) {
            throw new Exception("String '{$haystack}' does not contain '{$needle}'");
        }
    }

    public function runAllTests(): void
    {
        if (!$this->db || !$this->urlService) {
            echo "Skipping URLService tests due to database connection issues.\n\n";
            return;
        }

        echo "Running URLService tests...\n";
        $this->testEncodeValidURL();
        $this->testEncodeInvalidURL();
        $this->testDecodeShortenedURL();
        $this->testDecodeNonExistentURL();
        $this->testDuplicateURLHandling();
        echo "All URLService tests passed!\n\n";
    }
}