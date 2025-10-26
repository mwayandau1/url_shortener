<?php

class URLShortener
{
    private const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const BASE = 62;

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

    public function generateHash(string $url): string
    {
        return substr(md5($url . microtime(true)), 0, 8);
    }
}