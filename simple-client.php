<?php
/**
 * Simple Text Client - Bypass for security restrictions
 * Returns a simplified PowerShell client as plain text
 */

header('Content-Type: text/plain; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache');

// Get the current domain and protocol
$domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'yourdomain.com';
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$currentDir = dirname($_SERVER['REQUEST_URI']);
$apiUrl = $protocol . '://' . $domain . $currentDir . '/gemini-api.php';

// Check if parameters are passed via URL
$text = isset($_GET['text']) ? $_GET['text'] : '';
$test = isset($_GET['test']) ? true : false;

// Simple PowerShell client that works with iex
echo <<<POWERSHELL
# Gemini PHP Bridge - Simple Client (iex compatible)
# Auto-detect parameters from URL or use defaults

\$ApiUrl = "$apiUrl"
\$Text = "$text"
\$Test = [bool]"$test"

# Check command line arguments from \$args
for (\$i = 0; \$i -lt \$args.Count; \$i++) {
    if (\$args[\$i] -eq "-Text" -and (\$i + 1) -lt \$args.Count) {
        \$Text = \$args[\$i + 1]
    }
    if (\$args[\$i] -eq "-Test") {
        \$Test = \$true
    }
}

if (\$Test) {
    Write-Host "Testing connection to \$ApiUrl..." -ForegroundColor Yellow
    try {
        \$response = Invoke-WebRequest -Uri \$ApiUrl -Method POST -Body "test=true"
        \$result = \$response.Content | ConvertFrom-Json
        Write-Host "✅ Success: \$(\$result.message)" -ForegroundColor Green
    } catch {
        Write-Host "❌ Error: \$(\$_.Exception.Message)" -ForegroundColor Red
    }
    return
}

if ([string]::IsNullOrEmpty(\$Text)) {
    Write-Host "🚀 Gemini PHP Bridge - Ready!" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Usage examples:" -ForegroundColor Yellow
    Write-Host "  irm $protocol://$domain$currentDir/simple-client.php?test=1 | iex" -ForegroundColor Green
    Write-Host "  irm '$protocol://$domain$currentDir/simple-client.php?text=hello' | iex" -ForegroundColor Green
    Write-Host ""
    Write-Host "Or use the function method:" -ForegroundColor Yellow
    Write-Host '  function AI { param([string]$prompt); irm "$protocol://$domain$currentDir/simple-client.php?text=$prompt" | iex }' -ForegroundColor Green
    Write-Host '  AI "Write a Python hello world"' -ForegroundColor Green
    return
}

Write-Host "🤖 Processing: \$Text" -ForegroundColor Cyan
try {
    \$response = Invoke-WebRequest -Uri \$ApiUrl -Method POST -Body \$Text -ContentType "text/plain"
    \$json = \$response.Content | ConvertFrom-Json
    \$aiResponse = \$json.candidates[0].content.parts[0].text
    Write-Host ""
    Write-Host "--- AI Response ---" -ForegroundColor Green
    Write-Host \$aiResponse
    Write-Host "--- End Response ---" -ForegroundColor Green
} catch {
    Write-Host "❌ Error: \$(\$_.Exception.Message)" -ForegroundColor Red
}
POWERSHELL;
?>
