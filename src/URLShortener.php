<?php

/**
 * URL Shortener class for encoding and decoding URLs using base62 encoding
 */
class URLShortener
{
    private const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const BASE = 62;


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