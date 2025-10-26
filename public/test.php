<!DOCTYPE html>
<html>
<head>
    <title>URL Shortener - Test Suite</title>
    <style>
        body { font-family: monospace; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 5px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>URL Shortener Test Suite</h1>
        <pre><?php
ob_start();
require_once __DIR__ . '/../tests/run_tests.php';
$output = ob_get_clean();
echo htmlspecialchars($output);
        ?></pre>
        <hr>
        <p><a href="../">← Back to API</a> | <a href="javascript:location.reload()">🔄 Run Tests Again</a></p>
    </div>
</body>
</html>