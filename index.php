<?php
/**
 * Gemini PHP Bridge - Main Entry Point
 * Routes requests to appropriate endpoints
 * @version 2.1
 */

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// Check if this is an API request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
    (isset($_POST['test']) || !empty(file_get_contents('php://input')) || isset($_FILES['textFile']) || isset($_FILES['pdfFile']))) {
    // Route to API
    require_once 'gemini-api.php';
    exit;
}

// Check if requesting web interface
if (isset($_GET['interface']) || isset($_GET['web'])) {
    require_once 'web-interface.php';
    exit;
}

// Default: Show project information
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gemini PHP Bridge</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; line-height: 1.6; }
        .endpoint { background: #f8f9fa; padding: 20px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #007bff; }
        .code { background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 6px; font-family: 'Consolas', 'Monaco', monospace; font-size: 14px; overflow-x: auto; }
        h1 { color: #2d3748; margin-bottom: 10px; }
        h2 { color: #4a5568; margin-top: 30px; }
        h3 { color: #007bff; margin-bottom: 10px; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .status { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .status.ok { background: #d4edda; color: #155724; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; color: #718096; font-size: 14px; }
    </style>
</head>
<body>
    <h1>🤖 Gemini PHP Bridge</h1>
    <p>A PHP web service for Google's Gemini AI API with PowerShell client support.</p>
    
    <h2>Available Endpoints</h2>
    
    <div class="endpoint">
        <h3>📡 Main API</h3>
        <p><strong>URL:</strong> <code>/gemini-api.php</code></p>
        <p>POST requests with text or file uploads</p>
        <div class="code">curl -X POST "/gemini-api.php" -d "Your question here"</div>
    </div>
    
    <div class="endpoint">
        <h3>💻 PowerShell Client</h3>
        <p><strong>URL:</strong> <code>/simple-client.php</code></p>
        <p>One-line remote execution</p>
        <div class="code">irm '/simple-client.php?text=Hello AI' | iex</div>
    </div>
    
    <div class="endpoint">
        <h3>🌐 Web Interface</h3>
        <p><strong>URL:</strong> <a href="?interface=1">Web Interface</a></p>
        <p>Browser-based file upload interface</p>
    </div>
    
    <div class="endpoint">
        <h3>📊 System Status</h3>
        <p><strong>URL:</strong> <a href="/status.php">Status Check</a></p>
        <p>Health check and system information</p>
    </div>
    
    <h2>Quick Start</h2>
    <div class="code">
# PowerShell one-liner<br>
irm 'https://yourdomain.com/simple-client.php?text=Your question' | iex<br><br>
# API call<br>
curl -X POST "https://yourdomain.com/gemini-api.php" -d "Your question"
    </div>
    
    <h2>Documentation</h2>
    <p>📖 <a href="https://github.com/MbarekTech/gemini-php-bridge">GitHub Repository</a></p>
    <p>🚀 <a href="https://github.com/MbarekTech/gemini-php-bridge#quick-start">Quick Start Guide</a></p>
    
    <div class="footer">
        <p><strong>Gemini PHP Bridge v2.1</strong> - Built for developers who need AI integration.</p>
    </div>
</body>
</html>
