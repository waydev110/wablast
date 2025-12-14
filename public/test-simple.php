<?php
/**
 * Simple Node.js Connection Test (Shared Hosting Compatible)
 * No shell commands - pure HTTP test only
 */

require __DIR__.'/../vendor/autoload.php';

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();

$waUrlServer = $_ENV['WA_URL_SERVER'] ?? getenv('WA_URL_SERVER') ?: 'http://localhost';
$portNode = $_ENV['PORT_NODE'] ?? getenv('PORT_NODE') ?: '';

// Remove trailing slash
$waUrlServer = rtrim($waUrlServer, '/');

// Build URL
if (!empty($portNode) && (strpos($waUrlServer, 'localhost') !== false || strpos($waUrlServer, '127.0.0.1') !== false)) {
    $nodeUrl = $waUrlServer . ':' . $portNode;
} else {
    $nodeUrl = $waUrlServer;
}

$isLocalhost = (strpos($waUrlServer, 'localhost') !== false || strpos($waUrlServer, '127.0.0.1') !== false);
$setupType = $isLocalhost ? 'Internal Localhost' : 'External/Subdomain';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Node.js Connection Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        .config-box {
            background: #f5f5f5;
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .config-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            font-size: 14px;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .value {
            color: #333;
            font-weight: 600;
        }
        .test-box {
            background: #fafafa;
            border-left: 4px solid #ccc;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .test-box.success {
            background: #e8f5e9;
            border-left-color: #4caf50;
        }
        .test-box.error {
            background: #ffebee;
            border-left-color: #f44336;
        }
        .test-box.info {
            background: #e3f2fd;
            border-left-color: #2196f3;
        }
        .test-title {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 8px;
        }
        .test-result {
            font-size: 14px;
            line-height: 1.6;
        }
        .icon {
            font-size: 24px;
            margin-right: 10px;
        }
        .summary {
            margin-top: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            text-align: center;
        }
        .summary h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .summary p {
            font-size: 16px;
            line-height: 1.6;
        }
        code {
            background: #333;
            color: #0f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 13px;
        }
        .btn {
            display: inline-block;
            margin-top: 15px;
            padding: 12px 24px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Node.js Connection Test</h1>
        
        <div class="config-box">
            <div class="config-row">
                <span class="label">WA_URL_SERVER:</span>
                <span class="value"><?php echo htmlspecialchars($waUrlServer); ?></span>
            </div>
            <div class="config-row">
                <span class="label">PORT_NODE:</span>
                <span class="value"><?php echo $portNode ?: '(not used - subdomain mode)'; ?></span>
            </div>
            <div class="config-row">
                <span class="label">Full URL:</span>
                <span class="value"><?php echo htmlspecialchars($nodeUrl); ?></span>
            </div>
            <div class="config-row">
                <span class="label">Setup Type:</span>
                <span class="value"><?php echo $setupType; ?></span>
            </div>
        </div>

        <?php
        // Test 1: Base Connection
        echo '<div class="test-box info">';
        echo '<div class="test-title"><span class="icon">🔌</span>Test 1: Base Connection</div>';
        echo '<div class="test-result">Testing: ' . htmlspecialchars($nodeUrl) . '</div>';
        echo '</div>';

        $ch = curl_init($nodeUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            echo '<div class="test-box error">';
            echo '<div class="test-title">❌ Connection FAILED</div>';
            echo '<div class="test-result">';
            echo 'Error: ' . htmlspecialchars($error) . '<br><br>';
            echo '<strong>Troubleshooting:</strong><br>';
            echo '1. Check Node.js app status in cPanel<br>';
            echo '2. Ensure app is running (green status)<br>';
            echo '3. Verify URL: ' . htmlspecialchars($nodeUrl) . '<br>';
            echo '4. Check cPanel Node.js logs for errors';
            echo '</div>';
            echo '</div>';
            $baseSuccess = false;
        } else {
            echo '<div class="test-box success">';
            echo '<div class="test-title">✅ Connection SUCCESS</div>';
            echo '<div class="test-result">';
            echo 'HTTP Status: ' . $httpCode . '<br>';
            echo 'Node.js server is reachable!';
            echo '</div>';
            echo '</div>';
            $baseSuccess = true;
        }

        // Test 2: API Endpoint
        if ($baseSuccess) {
            echo '<div class="test-box info">';
            echo '<div class="test-title"><span class="icon">🧪</span>Test 2: API Endpoint</div>';
            echo '<div class="test-result">Testing POST to /start-connection</div>';
            echo '</div>';

            $testDevice = 'test' . time();
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
                echo '<div class="test-box error">';
                echo '<div class="test-title">❌ API Test FAILED</div>';
                echo '<div class="test-result">Error: ' . htmlspecialchars($apiError) . '</div>';
                echo '</div>';
                $apiSuccess = false;
            } else {
                echo '<div class="test-box success">';
                echo '<div class="test-title">✅ API Test SUCCESS</div>';
                echo '<div class="test-result">';
                echo 'HTTP Status: ' . $apiHttpCode . '<br>';
                echo 'Response: ' . htmlspecialchars(substr($apiResponse, 0, 200)) . '<br>';
                echo 'API endpoint is working correctly!';
                echo '</div>';
                echo '</div>';
                $apiSuccess = true;
            }
        } else {
            $apiSuccess = false;
        }

        // Summary
        $allPassed = $baseSuccess && $apiSuccess;

        if ($allPassed) {
            echo '<div class="summary">';
            echo '<h2>🎉 All Tests PASSED!</h2>';
            echo '<p>Node.js server is running and Laravel can communicate with it successfully.</p>';
            echo '<p style="margin-top: 15px;">You can now:</p>';
            echo '<p>1. Login to dashboard<br>2. Add a device<br>3. Scan QR code<br>4. Connect WhatsApp</p>';
            echo '<a href="/" class="btn">Go to Dashboard →</a>';
            echo '</div>';
        } else {
            echo '<div class="summary" style="background: linear-gradient(135deg, #f44336 0%, #e91e63 100%);">';
            echo '<h2>⚠️ Connection Issues Detected</h2>';
            echo '<p>Some tests failed. Please check:</p>';
            echo '<p style="margin-top: 15px;">';
            echo '1. cPanel → Setup Node.js App → Check status<br>';
            echo '2. Ensure app is "Running" (green)<br>';
            echo '3. Check environment variables are set<br>';
            echo '4. View logs in cPanel Node.js interface';
            echo '</p>';
            echo '</div>';
        }
        ?>

        <div style="margin-top: 20px; padding: 15px; background: #fff3e0; border-radius: 6px; font-size: 13px;">
            <strong>⚠️ Security Note:</strong> Delete this file after testing!<br>
            <code>rm public/test-simple.php</code>
        </div>
    </div>
</body>
</html>
