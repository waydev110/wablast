<?php
/**
 * Test Node.js Connection
 * Run via browser: https://wablast.inilaku.com/test-nodejs.php
 * Or command line: php public/test-nodejs.php
 */

require __DIR__.'/../vendor/autoload.php';

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();

$waUrlServer = $_ENV['WA_URL_SERVER'] ?? getenv('WA_URL_SERVER') ?: 'http://localhost';
$portNode = $_ENV['PORT_NODE'] ?? getenv('PORT_NODE') ?: '';

// Remove trailing slash from URL
$waUrlServer = rtrim($waUrlServer, '/');

// Build URL: add port only for localhost setup
if (!empty($portNode) && (strpos($waUrlServer, 'localhost') !== false || strpos($waUrlServer, '127.0.0.1') !== false)) {
    $nodeUrl = $waUrlServer . ':' . $portNode;
} else {
    $nodeUrl = $waUrlServer; // Subdomain/external URL doesn't need port
}

echo "====================================\n";
echo "Testing Node.js Connection\n";
echo "====================================\n\n";

echo "Configuration:\n";
echo "  WA_URL_SERVER: $waUrlServer\n";
echo "  PORT_NODE: " . ($portNode ?: '(not used - subdomain mode)') . "\n";
echo "  Full URL: $nodeUrl\n\n";

// Detect setup type
$isLocalhost = (strpos($waUrlServer, 'localhost') !== false || strpos($waUrlServer, '127.0.0.1') !== false);
$setupType = $isLocalhost ? 'Internal Localhost (PM2)' : 'External/Subdomain';
echo "  Setup Type: $setupType\n\n";

// Test 1: Check if Node.js process is running
echo "Test 1: Check Node.js Process\n";
echo "Running: ps aux | grep node\n";
exec("ps aux | grep -v grep | grep node", $output, $returnCode);
if (count($output) > 0) {
    echo "✓ Node.js process found:\n";
    foreach ($output as $line) {
        echo "  $line\n";
    }
} else {
    echo "✗ Node.js process NOT running!\n";
    echo "  Solution: pm2 start server.js --name mpwa-whatsapp\n";
}
echo "\n";

// Test 2: Check if port 3100 is listening
echo "Test 2: Check Port 3100\n";
echo "Running: netstat -tuln | grep 3100\n";
exec("netstat -tuln 2>/dev/null | grep 3100", $output2, $returnCode2);
if (count($output2) > 0) {
    echo "✓ Port 3100 is listening:\n";
    foreach ($output2 as $line) {
        echo "  $line\n";
    }
} else {
    echo "✗ Port 3100 NOT listening!\n";
    echo "  Node.js server may not be running\n";
}
echo "\n";

// Test 3: Try to connect via HTTP
echo "Test 3: HTTP Connection Test\n";
echo "Attempting to connect to: $nodeUrl\n";

$ch = curl_init($nodeUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 5,
    CURLOPT_CONNECTTIMEOUT => 3,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($error) {
    echo "✗ Connection FAILED!\n";
    echo "  Error: $error\n\n";
    
    echo "Troubleshooting:\n";
    if (strpos($error, 'Could not connect') !== false) {
        echo "  1. Node.js server not running → pm2 start server.js\n";
        echo "  2. Wrong URL → Check WA_URL_SERVER in .env\n";
        echo "  3. Firewall blocking → Use http://localhost instead\n";
    }
} else {
    echo "✓ Connection SUCCESS!\n";
    echo "  HTTP Status: $httpCode\n";
    echo "  Response: " . substr($response, 0, 100) . "...\n";
}
echo "\n";

// Test 4: Try POST request (actual API test)
echo "Test 4: API Endpoint Test\n";
echo "Testing POST to /start-connection\n";

$testDevice = 'test123456789';
$ch = curl_init($nodeUrl . '/start-connection');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query(['device' => $testDevice]),
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
]);

$apiResponse = curl_exec($ch);
$apiError = curl_error($ch);
$apiHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($apiError) {
    echo "✗ API Test FAILED!\n";
    echo "  Error: $apiError\n";
} else {
    echo "✓ API Test SUCCESS!\n";
    echo "  HTTP Status: $apiHttpCode\n";
    echo "  Response: $apiResponse\n";
}
echo "\n";

// Summary
echo "====================================\n";
echo "Summary\n";
echo "====================================\n";

$allPassed = !$error && !$apiError && count($output) > 0;

if ($allPassed) {
    echo "✓ All tests PASSED!\n";
    echo "Node.js server is running and accessible.\n\n";
    echo "You can now:\n";
    echo "  1. Login to dashboard: https://wablast.inilaku.com\n";
    echo "  2. Add a device\n";
    echo "  3. Scan QR code\n";
} else {
    echo "✗ Some tests FAILED!\n\n";
    
    // Specific fix for external URL
    if (strpos($waUrlServer, 'localhost') === false && strpos($waUrlServer, '127.0.0.1') === false) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "🔧 FIX REQUIRED: Change to localhost\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        echo "Your .env is using: $waUrlServer\n";
        echo "This won't work on shared hosting!\n\n";
        echo "SOLUTION:\n";
        echo "  1. Edit .env file (via cPanel File Manager or SSH)\n";
        echo "  2. Find line: WA_URL_SERVER=$waUrlServer\n";
        echo "  3. Change to: WA_URL_SERVER=http://localhost\n";
        echo "  4. Save file\n";
        echo "  5. Visit: https://wablast.inilaku.com/clear-cache\n";
        echo "  6. Re-test: https://wablast.inilaku.com/test-nodejs.php\n\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    }
    
    echo "Quick Fixes:\n";
    echo "  1. Run: pm2 start server.js --name mpwa-whatsapp\n";
    echo "  2. Update .env: WA_URL_SERVER=http://localhost\n";
    echo "  3. Clear cache: php artisan config:clear && php artisan config:cache\n";
    echo "     Or visit: https://wablast.inilaku.com/clear-cache\n";
    echo "  4. Check PM2 status: pm2 status\n";
    echo "  5. View PM2 logs: pm2 logs mpwa-whatsapp\n";
}
echo "\n";

// Output as HTML if accessed via browser
if (php_sapi_name() !== 'cli') {
    echo "<style>body{font-family:monospace;white-space:pre;}</style>";
}
