<?php
/**
 * Simple Text Client - Bypass for security restrictions
 * Returns a simplified PowerShell client as plain text
 */

header('Content-Type: text/plain; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache');

// Get the current domain and protocol
$domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'mycafe.tech';
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$currentDir = dirname($_SERVER['REQUEST_URI']);
$apiUrl = $protocol . '://' . $domain . $currentDir . '/gemini-api.php';

// Simple PowerShell client
echo <<<'POWERSHELL'
# Gemini PHP Bridge - Simple Client
param(
    [string]$Text = "",
    [switch]$Test = $false
)

$ApiUrl = "##API_URL##"

if ($Test) {
    Write-Host "Testing connection to $ApiUrl..."
    try {
        $response = Invoke-WebRequest -Uri $ApiUrl -Method POST -Body "test=true"
        $result = $response.Content | ConvertFrom-Json
        Write-Host "✅ Success: $($result.message)" -ForegroundColor Green
    } catch {
        Write-Host "❌ Error: $($_.Exception.Message)" -ForegroundColor Red
    }
    return
}

if ([string]::IsNullOrEmpty($Text)) {
    Write-Host "Usage examples:" -ForegroundColor Yellow
    Write-Host "  irm ##DOMAIN##/simple-client.php | iex -Text 'Your prompt here'"
    Write-Host "  irm ##DOMAIN##/simple-client.php | iex -Test"
    return
}

Write-Host "Processing: $Text"
try {
    $response = Invoke-WebRequest -Uri $ApiUrl -Method POST -Body $Text -ContentType "text/plain"
    $json = $response.Content | ConvertFrom-Json
    $aiResponse = $json.candidates[0].content.parts[0].text
    Write-Host "`n--- AI Response ---" -ForegroundColor Green
    Write-Host $aiResponse
    Write-Host "--- End Response ---" -ForegroundColor Green
} catch {
    Write-Host "❌ Error: $($_.Exception.Message)" -ForegroundColor Red
}
POWERSHELL;

// Replace placeholders
$output = str_replace('##API_URL##', $apiUrl, ob_get_contents());
$output = str_replace('##DOMAIN##', $protocol . '://' . $domain . $currentDir, $output);
ob_clean();
echo $output;
?>
