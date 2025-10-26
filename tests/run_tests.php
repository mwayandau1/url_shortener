<?php

require_once __DIR__ . '/URLShortenerTest.php';
require_once __DIR__ . '/URLServiceTest.php';

echo "=== URL Shortener Test Suite ===\n\n";

$shortenerTest = new URLShortenerTest();
$shortenerTest->runAllTests();

$serviceTest = new URLServiceTest();
$serviceTest->runAllTests();

echo "=== All Tests Completed ===\n";