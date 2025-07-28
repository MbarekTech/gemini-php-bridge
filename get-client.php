<?php
/**
 * PowerShell Client Proxy
 * Serves the PowerShell script with proper headers to bypass security restrictions
 */

// Set headers for PowerShell script download
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: inline; filename="gemini-bridge.ps1"');
header('Cache-Control: no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');

// Disable any security redirects for this endpoint
header('X-Frame-Options: SAMEORIGIN'); // Less restrictive than DENY

// Read and output the PowerShell script
$scriptPath = __DIR__ . '/client/gemini-bridge.ps1';

if (file_exists($scriptPath)) {
    $script = file_get_contents($scriptPath);
    
    // Update the default API URL in the script to point to this server
    $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'yourdomain.com';
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $baseUrl = $protocol . '://' . $domain . dirname($_SERVER['REQUEST_URI']);
    
    // Replace the placeholder URL with the actual URL
    $script = str_replace(
        'https://yourdomain.com/gemini-api.php',
        $baseUrl . '/gemini-api.php',
        $script
    );
    
    echo $script;
} else {
    http_response_code(404);
    echo "# PowerShell client not found";
}
?>
