<?php

/**
 * Fix Laravel Storage & Cache via Web
 * Access this file via browser: https://wablast.inilaku.com/fix-storage.php
 * DELETE this file after use for security!
 */

header('Content-Type: text/plain');
echo "====================================\n";
echo "Fixing Laravel Storage & Cache\n";
echo "====================================\n\n";

// Define directories to create
$directories = [
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'storage/app/public',
    'bootstrap/cache',
    'credentials',
];

echo "Creating directories...\n";
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0775, true)) {
            echo "✓ Created: $dir\n";
        } else {
            echo "✗ Failed to create: $dir\n";
        }
    } else {
        echo "- Already exists: $dir\n";
    }
}

echo "\nSetting permissions...\n";

// Set permissions
$permissions = [
    'storage' => 0755,
    'storage/framework' => 0775,
    'storage/framework/cache' => 0775,
    'storage/framework/sessions' => 0775,
    'storage/framework/views' => 0775,
    'storage/logs' => 0775,
    'bootstrap/cache' => 0755,
    'credentials' => 0775,
];

foreach ($permissions as $path => $perm) {
    if (is_dir($path)) {
        if (chmod($path, $perm)) {
            echo "✓ Set permission $perm for: $path\n";
        } else {
            echo "✗ Failed to set permission for: $path\n";
        }
    }
}

echo "\nClearing caches...\n";

// Clear Laravel caches
$commands = [
    'cache:clear' => 'Cache',
    'config:clear' => 'Config',
    'view:clear' => 'Views',
    'route:clear' => 'Routes',
];

foreach ($commands as $cmd => $name) {
    exec("php artisan $cmd 2>&1", $output, $return);
    if ($return === 0) {
        echo "✓ Cleared: $name\n";
    } else {
        echo "- Skipped: $name (or was empty)\n";
    }
}

echo "\n====================================\n";
echo "Fix Complete!\n";
echo "====================================\n\n";

echo "Next steps:\n";
echo "1. Run: php artisan config:cache\n";
echo "2. Run: php artisan route:cache\n";
echo "3. Run: php artisan view:cache\n";
echo "4. Or visit: /clear-cache (via browser)\n\n";

echo "IMPORTANT: Delete this file (fix-storage.php) after use!\n\n";

echo "Test your website now: https://wablast.inilaku.com\n";
