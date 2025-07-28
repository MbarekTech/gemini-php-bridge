<?php
/**
 * PowerShell Client Proxy
 * Serves the PowerShell script with proper headers to bypass security restrictions
 * @version 2.0
 */

// Set headers for PowerShell script download
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: inline; filename="gemini-bridge.ps1"');
header('Cache-Control: no-cache, must-revalidate');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN'); // Less restrictive than DENY

/**
 * Get the PowerShell script with dynamic URL replacement
 */
function getClientScript(): string {
    $scriptPath = __DIR__ . '/client/gemini-bridge.ps1';
    
    if (!file_exists($scriptPath)) {
        http_response_code(404);
        return "# PowerShell client not found at: $scriptPath\n# Please ensure client/gemini-bridge.ps1 exists";
    }
    
    $script = file_get_contents($scriptPath);
    if ($script === false) {
        http_response_code(500);
        return "# Error reading PowerShell client script\n# Check file permissions";
    }
    
    // Auto-detect current server configuration
    $domain = $_SERVER['HTTP_HOST'] ?? 'yourdomain.com';
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $currentDir = dirname($_SERVER['REQUEST_URI']);
    $baseUrl = $protocol . '://' . $domain . $currentDir;
    
    // Replace placeholder URLs with actual server URLs
    $replacements = [
        'https://yourdomain.com/gemini-api.php' => $baseUrl . '/gemini-api.php',
        'https://yourdomain.com' => $baseUrl
    ];
    
    foreach ($replacements as $placeholder => $actual) {
        $script = str_replace($placeholder, $actual, $script);
    }
    
    return $script;
}

// Output the script
echo getClientScript();
?>
