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
require_once __DIR__ . '/../src/UrlHelper.php';


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


    $endpoint = UrlHelper::extractPathSegment($requestUri);


    if (preg_match('/^[a-zA-Z0-9]+$/', $endpoint) && strlen($endpoint) <= 10 && $method === 'GET' && $endpoint !== 'encode' && $endpoint !== 'decode') {
        try {
            $result = $db->fetchOne(
                'SELECT original_url FROM urls WHERE short_code = ?',
                [$endpoint]
            );
            
            if ($result) {
                $db->query(
                    'UPDATE urls SET access_count = access_count + 1, last_accessed = ? WHERE short_code = ?',
                    [date('Y-m-d H:i:s'), $endpoint]
                );
                
                header('Location: ' . $result['original_url']);
                exit;
            } else {
                http_response_code(404);
                echo '<h1>404 - Short URL not found</h1>';
                exit;
            }
        } catch (Exception $e) {
            http_response_code(404);
            echo '<h1>404 - Short URL not found</h1>';
            exit;
        }
    }

    switch ($endpoint) {
        case 'encode':
            header('Content-Type: application/json');
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
            header('Content-Type: application/json');
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
            header('Content-Type: application/json');
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
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}