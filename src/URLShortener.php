<?php

/**
 * URL Shortener class for encoding and decoding URLs using base62 encoding
 */
class URLShortener
{
    private const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const BASE = 62;

    /**
     * Encode an integer ID to a short string using base62
     * 
     * @param int $id The ID to encode
     * @return string The encoded short string
     */
    public function encode(int $id): string
    {
        if ($id === 0) {
            return self::ALPHABET[0];
        }

        $shortUrl = '';
        while ($id > 0) {
            $shortUrl = self::ALPHABET[$id % self::BASE] . $shortUrl;
            $id = intval($id / self::BASE);
        }

        return $shortUrl;
    }

    /**
     * Decode a short string back to its original integer ID
     * 
     * @param string $shortUrl The short string to decode
     * @return int The decoded ID
     * @throws InvalidArgumentException If invalid character found
     */
    public function decode(string $shortUrl): int
    {
        $id = 0;
        $length = strlen($shortUrl);

        for ($i = 0; $i < $length; $i++) {
            $char = $shortUrl[$i];
            $position = strpos(self::ALPHABET, $char);
            
            if ($position === false) {
                throw new InvalidArgumentException("Invalid character in short URL: {$char}");
            }
            
            $id = $id * self::BASE + $position;
        }

        return $id;
    }

    /**
     * Generate a random short code
     * 
     * @param int $length Length of the short code
     * @return string Random short code
     */
    public function generateShortCode(int $length = 4): string
    {
        $shortCode = '';
        for ($i = 0; $i < $length; $i++) {
            $shortCode .= self::ALPHABET[random_int(0, self::BASE - 1)];
        }
        return $shortCode;
    }
}