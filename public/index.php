<?php

/**
 * URL Shortener API Entry Point
 * 
 * This file serves as the main API endpoint for the URL shortener service.
 * It handles HTTP requests for encoding and decoding URLs.
 * 
 * Endpoints:
 * - POST /encode: Shortens a given URL
 * - POST /decode: Retrieves original URL from short URL
 * - GET /: Returns API information
 * 
 * @author mosesayandau
 * @version 1.0
 */

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/URLShortener.php';
require_once __DIR__ . '/../src/URLService.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $config = require __DIR__ . '/../config/database.php';
    $db = new Database($config);
    $shortener = new URLShortener();
    $urlService = new URLService($db, $shortener);

    $requestUri = $_SERVER['REQUEST_URI'];
    $path = parse_url($requestUri, PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];

    $pathParts = explode('/', trim($path, '/'));
    $endpoint = end($pathParts);

    switch ($endpoint) {
        case 'encode':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed', 405);
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['url'])) {
                throw new InvalidArgumentException('URL parameter is required');
            }

            $result = $urlService->encodeURL($input['url']);
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            break;

        case 'decode':
            if ($method !== 'POST') {
                throw new Exception('Method not allowed', 405);
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['short_url'])) {
                throw new InvalidArgumentException('short_url parameter is required');
            }

            $result = $urlService->decodeURL($input['short_url']);
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            break;

        default:
            echo json_encode([
                'success' => true,
                'message' => 'URL Shortener API',
                'endpoints' => [
                    'POST /encode' => 'Encode a URL',
                    'POST /decode' => 'Decode a short URL'
                ]
            ]);
    }

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}