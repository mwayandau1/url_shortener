<?php

require_once __DIR__ . '/../src/URLShortener.php';

class URLShortenerTest
{
    private URLShortener $shortener;

    public function __construct()
    {
        $this->shortener = new URLShortener();
    }



  

    public function testGenerateShortCode(): void
    {
        $code1 = $this->shortener->generateShortCode();
        $code2 = $this->shortener->generateShortCode();
        
        $this->assertEquals(4, strlen($code1));
        $this->assertEquals(4, strlen($code2));
        $this->assertNotEquals($code1, $code2);
        
        echo "✓ Generate short code tests passed\n";
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

        $this->testGenerateShortCode();
        echo "All URLShortener tests passed!\n\n";
    }
}