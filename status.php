<?php
/**
 * System Health Check Endpoint
 * Usage: curl https://yourdomain.com/status.php
 * @version 2.1
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Security check - only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Load configuration if available
$config = null;
$config_status = 'missing';
if (file_exists('config/config.php')) {
    try {
        $config = require_once 'config/config.php';
        $config_status = 'loaded';
    } catch (Exception $e) {
        $config_status = 'error: ' . $e->getMessage();
    }
}

// Check directory permissions
$directories = ['temp_files', 'logs', 'output', 'config'];
$dir_status = [];
foreach ($directories as $dir) {
    $dir_status[$dir] = [
        'exists' => is_dir($dir),
        'writable' => is_writable($dir)
    ];
}

// Check PHP requirements
$php_status = [
    'version' => PHP_VERSION,
    'curl_enabled' => extension_loaded('curl'),
    'upload_enabled' => (bool)ini_get('file_uploads'),
    'max_upload_size' => ini_get('upload_max_filesize'),
    'max_post_size' => ini_get('post_max_size'),
    'memory_limit' => ini_get('memory_limit')
];

// System status
$status = [
    'system' => [
        'name' => 'Gemini AI Integration',
        'version' => '2.0',
        'status' => 'online',
        'timestamp' => date('c'),
        'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown'
    ],
    'configuration' => [
        'status' => $config_status,
        'api_key_set' => $config && !empty($config['api']['key']) && $config['api']['key'] !== 'YOUR_GOOGLE_API_KEY_HERE'
    ],
    'directories' => $dir_status,
    'php' => $php_status,
    'endpoints' => [
        'api' => 'api.php',
        'setup' => 'setup.ps1',
        'client' => 'client/gemini-client.ps1',
        'status' => 'status.php'
    ]
];

// Add warnings
$warnings = [];
if (!$php_status['curl_enabled']) {
    $warnings[] = 'cURL extension is not enabled';
}
if (!$php_status['upload_enabled']) {
    $warnings[] = 'File uploads are disabled';
}
if (!$status['configuration']['api_key_set']) {
    $warnings[] = 'API key not configured';
}
foreach ($dir_status as $dir => $info) {
    if (!$info['exists']) {
        $warnings[] = "Directory '$dir' does not exist";
    } elseif (!$info['writable']) {
        $warnings[] = "Directory '$dir' is not writable";
    }
}

if (!empty($warnings)) {
    $status['warnings'] = $warnings;
}

// Overall health check
$status['health'] = empty($warnings) && $status['configuration']['api_key_set'] ? 'healthy' : 'needs_attention';

echo json_encode($status, JSON_PRETTY_PRINT);
?>
