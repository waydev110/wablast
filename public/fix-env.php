<?php
/**
 * Fix .env Configuration
 * Updates WA_URL_SERVER to use localhost for shared hosting
 * 
 * SECURITY: Delete this file after use!
 */

$envPath = __DIR__ . '/../.env';
$success = false;
$message = '';
$currentValue = '';
$newValue = 'http://localhost';
$customUrl = $_POST['custom_url'] ?? '';

// Check if .env exists
if (!file_exists($envPath)) {
    $message = '❌ Error: .env file not found!';
} else {
    // Read current .env
    $envContent = file_get_contents($envPath);
    
    // Extract current WA_URL_SERVER
    if (preg_match('/^WA_URL_SERVER=(.*)$/m', $envContent, $matches)) {
        $currentValue = trim($matches[1]);
    } else {
        $currentValue = '(not set)';
    }
    
    // If form submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
        // Determine URL to use
        $urlToUse = !empty($customUrl) ? trim($customUrl) : 'http://localhost';
        
        // Validate URL
        if (!filter_var($urlToUse, FILTER_VALIDATE_URL)) {
            $message = '❌ Error: Invalid URL format!';
        } else {
            // Backup .env
            $backupPath = $envPath . '.backup.' . date('Y-m-d_H-i-s');
            copy($envPath, $backupPath);
            
            // Update WA_URL_SERVER
            if (preg_match('/^WA_URL_SERVER=.*$/m', $envContent)) {
                $newContent = preg_replace('/^WA_URL_SERVER=.*$/m', 'WA_URL_SERVER=' . $urlToUse, $envContent);
            } else {
                $newContent = $envContent . "\nWA_URL_SERVER=" . $urlToUse . "\n";
            }
        
            // PORT_NODE only for localhost
            if (strpos($urlToUse, 'localhost') !== false || strpos($urlToUse, '127.0.0.1') !== false) {
                if (!preg_match('/^PORT_NODE=.*$/m', $newContent)) {
                    $newContent .= "PORT_NODE=3100\n";
                }
            }
            
            // Save
            if (file_put_contents($envPath, $newContent)) {
                $success = true;
                $message = '✅ Success! .env updated to: ' . htmlspecialchars($urlToUse);
            
                // Try to clear Laravel cache
                exec('cd ' . dirname($envPath) . ' && php artisan config:clear 2>&1', $output, $returnCode);
                if ($returnCode === 0) {
                    $message .= '<br>✅ Laravel cache cleared';
                }
                
                $currentValue = $urlToUse;
            } else {
                $message = '❌ Error: Could not write to .env file. Check permissions.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix .env Configuration</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .status-box {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .status-box.info {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            color: #1565c0;
        }
        .status-box.success {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            color: #2e7d32;
        }
        .status-box.error {
            background: #ffebee;
            border-left: 4px solid #f44336;
            color: #c62828;
        }
        .status-box.warning {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            color: #e65100;
        }
        .config-display {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .config-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 8px 0;
        }
        .config-label {
            font-weight: bold;
            color: #555;
        }
        .config-value {
            color: #333;
            background: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 13px;
        }
        .arrow {
            font-size: 24px;
            color: #4caf50;
            margin: 20px 0;
            text-align: center;
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        .next-steps {
            margin-top: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .next-steps h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .next-steps ol {
            margin-left: 20px;
            color: #555;
        }
        .next-steps li {
            margin: 10px 0;
            line-height: 1.6;
        }
        .next-steps a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .next-steps a:hover {
            text-decoration: underline;
        }
        .security-warning {
            margin-top: 20px;
            padding: 15px;
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            border-radius: 6px;
            font-size: 13px;
            color: #e65100;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix .env Configuration</h1>
        <p class="subtitle">Update WA_URL_SERVER for shared hosting compatibility</p>
        
        <?php if ($message): ?>
            <div class="status-box <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
            <div class="status-box warning">
                ⚠️ <strong>Issue:</strong> Your .env is configured for external URL<br>
                Shared hosting blocks external access to port 3100
            </div>
            
            <div class="config-display">
                <div class="config-row">
                    <span class="config-label">Current:</span>
                    <span class="config-value"><?php echo htmlspecialchars($currentValue); ?></span>
                </div>
            </div>
            
            <div class="status-box info">
                💡 <strong>Choose your Node.js setup:</strong><br>
                • <strong>Option A:</strong> Separate subdomain (e.g., node.wablast.inilaku.com)<br>
                • <strong>Option B:</strong> Internal localhost (for manual PM2 setup)
            </div>
            
            <form method="POST">
                <div style="margin: 20px 0;">
                    <label style="display: block; font-weight: 600; margin-bottom: 10px; color: #333;">
                        Node.js Server URL:
                    </label>
                    <input type="text" 
                           name="custom_url" 
                           placeholder="https://node.wablast.inilaku.com or http://localhost"
                           style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 14px; font-family: 'Courier New', monospace;"
                           value="<?php echo htmlspecialchars($customUrl); ?>">
                    <small style="color: #666; display: block; margin-top: 8px;">
                        Examples:<br>
                        • <code>https://node.wablast.inilaku.com</code> (subdomain setup)<br>
                        • <code>https://api.wablast.inilaku.com</code> (API subdomain)<br>
                        • <code>http://localhost</code> (manual PM2 on same server)
                    </small>
                </div>
                
                <button type="submit" name="confirm" value="1">
                    ✅ Update .env Configuration
                </button>
            </form>
        <?php else: ?>
            <div class="next-steps">
                <h3>✅ Configuration Updated!</h3>
                <ol>
                    <li>Clear Laravel cache: <a href="/clear-cache" target="_blank">Visit /clear-cache</a></li>
                    <li>Test connection: <a href="/test-nodejs.php" target="_blank">Visit /test-nodejs.php</a></li>
                    <li>Restart Node.js: <code>pm2 restart mpwa-whatsapp</code></li>
                    <li>Test dashboard: <a href="/" target="_blank">Login and scan QR code</a></li>
                </ol>
            </div>
            
            <div class="security-warning">
                <strong>⚠️ Security:</strong> Delete this file after use!<br>
                <code>rm public/fix-env.php</code>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
