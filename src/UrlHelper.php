<?php

/**
 * URL helper for auto-detecting base URLs and handling different server setups
 */
class UrlHelper
{
    /**
     * Auto-detect the base URL from the current request
     * 
     * @return string The detected base URL
     */
    public static function getBaseUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        
        $currentPath = dirname($requestUri);
        if ($currentPath === '/' || $currentPath === '\\') {
            $currentPath = '';
        }
        
        return $protocol . '://' . $host . $currentPath;
    }

    /**
     * Extract the path segment after the base path
     * 
     * @param string $requestUri The full request URI
     * @return string The extracted path segment
     */
    public static function extractPathSegment(string $requestUri): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = dirname($scriptName);
        
        $path = parse_url($requestUri, PHP_URL_PATH);
        
        if ($basePath !== '/' && $basePath !== '\\' && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        $pathParts = explode('/', trim($path, '/'));
        return $pathParts[0] ?? '';
    }
}