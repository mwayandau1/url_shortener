<?php

require_once __DIR__ . '/../src/URLShortener.php';

class URLShortenerTest
{
    private URLShortener $shortener;

    public function __construct()
    {
        $this->shortener = new URLShortener();
    }

    public function testEncode(): void
    {
        $this->assertEquals('1', $this->shortener->encode(1));
        $this->assertEquals('a', $this->shortener->encode(10));
        $this->assertEquals('Z', $this->shortener->encode(61));
        $this->assertEquals('10', $this->shortener->encode(62));
        $this->assertEquals('0', $this->shortener->encode(0));
        
        echo "✓ Encode tests passed\n";
    }

    public function testDecode(): void
    {
        $this->assertEquals(1, $this->shortener->decode('1'));
        $this->assertEquals(10, $this->shortener->decode('a'));
        $this->assertEquals(61, $this->shortener->decode('Z'));
        $this->assertEquals(62, $this->shortener->decode('10'));
        $this->assertEquals(0, $this->shortener->decode('0'));
        
        echo "✓ Decode tests passed\n";
    }

    public function testEncodeDecodeConsistency(): void
    {
        $testIds = [1, 10, 100, 1000, 10000, 999999];
        
        foreach ($testIds as $id) {
            $encoded = $this->shortener->encode($id);
            $decoded = $this->shortener->decode($encoded);
            $this->assertEquals($id, $decoded);
        }
        
        echo "✓ Encode/Decode consistency tests passed\n";
    }

    public function testInvalidCharacterThrowsException(): void
    {
        try {
            $this->shortener->decode('invalid@char');
            throw new Exception('Should have thrown InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            echo "✓ Invalid character exception test passed\n";
        }
    }

    public function testGenerateHash(): void
    {
        $url = 'https://example.com';
        $hash1 = $this->shortener->generateHash($url);
        usleep(1000); // Ensure different timestamp
        $hash2 = $this->shortener->generateHash($url);
        
        $this->assertEquals(8, strlen($hash1));
        $this->assertNotEquals($hash1, $hash2); // Should be different due to time component
        
        echo "✓ Generate hash tests passed\n";
    }

    private function assertEquals($expected, $actual): void
    {
        if ($expected !== $actual) {
            throw new Exception("Expected {$expected}, got {$actual}");
        }
    }

    private function assertNotEquals($expected, $actual): void
    {
        if ($expected === $actual) {
            throw new Exception("Expected values to be different, but both were {$expected}");
        }
    }

    public function runAllTests(): void
    {
        echo "Running URLShortener tests...\n";
        $this->testEncode();
        $this->testDecode();
        $this->testEncodeDecodeConsistency();
        $this->testInvalidCharacterThrowsException();
        $this->testGenerateHash();
        echo "All URLShortener tests passed!\n\n";
    }
}