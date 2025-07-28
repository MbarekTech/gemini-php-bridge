# Web Hosting Deployment Guide

## 🌐 Hosting the Gemini AI Integration

This guide explains how to deploy and use the Gemini AI Integration on a web server for remote execution.

## 📁 Required Files for Web Hosting

### Core Files (Required)
```
/
├── api.php                    # Main API endpoint
├── index.php                  # Legacy endpoint (optional)
├── setup.ps1                  # Remote setup script
├── config/
│   ├── config.example.php     # Configuration template
│   └── config.php            # Your actual config (create from template)
├── client/
│   └── gemini-client.ps1     # PowerShell client
├── temp_files/               # Writable directory for uploads
├── logs/                     # Writable directory for logs
└── .htaccess                 # Optional: Security rules
```

### Optional Files
- `README.md` - Documentation
- `LICENSE` - License file
- `unused/` - Not needed for web hosting

## 🔧 Server Configuration

### PHP Requirements
- PHP 7.4 or higher
- cURL extension enabled
- File upload enabled (`file_uploads = On`)
- Adequate memory limit (`memory_limit = 256M`)
- Execution time limit (`max_execution_time = 300`)

### Directory Permissions
```bash
chmod 755 temp_files logs config
chmod 644 *.php *.ps1
chmod 600 config/config.php  # Secure the config file
```

### Apache .htaccess (Optional Security)
```apache
# Deny access to sensitive files
<Files "config.php">
    Require all denied
</Files>

<Files "*.log">
    Require all denied
</Files>

# Allow PowerShell scripts to be downloaded
<Files "*.ps1">
    Header set Content-Type "text/plain; charset=utf-8"
    Header set Content-Disposition "inline"
</Files>
```

## 🚀 Remote Usage Patterns

### 1. One-Time Setup
```powershell
# Download and run setup script
irm https://yourdomain.com/setup.ps1 | iex -ApiKey "YOUR_API_KEY"
```

### 2. Direct Client Execution
```powershell
# Test connection
irm https://yourdomain.com/client/gemini-client.ps1 | iex -ApiUrl "https://yourdomain.com/api.php" -Test

# Process text directly
irm https://yourdomain.com/client/gemini-client.ps1 | iex -ApiUrl "https://yourdomain.com/api.php" -InputText "Generate a simple HTML page"

# Process a local file
irm https://yourdomain.com/client/gemini-client.ps1 | iex -ApiUrl "https://yourdomain.com/api.php" -InputFile "myfile.txt"
```

### 3. Shortened URLs (Recommended)
Use a URL shortener for easier execution:

```powershell
# Instead of the long URL, use:
irm bit.ly/gemini-setup | iex -ApiKey "YOUR_KEY"
irm bit.ly/gemini-client | iex -ApiUrl "https://yourdomain.com/api.php" -Test
```

### 4. Bookmarklet for Web Browsers
Create a browser bookmark with this JavaScript:
```javascript
javascript:(function(){
    var text = window.getSelection().toString() || prompt('Enter text to process:');
    if(text) {
        var ps1 = 'irm https://yourdomain.com/client/gemini-client.ps1 | iex -ApiUrl "https://yourdomain.com/api.php" -InputText "' + text.replace(/"/g, '\\"') + '"';
        navigator.clipboard.writeText(ps1).then(function() {
            alert('PowerShell command copied to clipboard!');
        });
    }
})();
```

## 📱 Cross-Platform Support

### Windows PowerShell / PowerShell Core
```powershell
irm https://yourdomain.com/client/gemini-client.ps1 | iex -ApiUrl "https://yourdomain.com/api.php" -InputText "Hello"
```

### Linux/macOS with PowerShell Core
```bash
pwsh -c "irm https://yourdomain.com/client/gemini-client.ps1 | iex -ApiUrl 'https://yourdomain.com/api.php' -InputText 'Hello'"
```

### cURL (Universal)
```bash
# Direct API call
curl -X POST https://yourdomain.com/api.php \
  -F "textFile=@myfile.txt"

# With text content
curl -X POST https://yourdomain.com/api.php \
  -H "Content-Type: text/plain" \
  -d "Generate a simple HTML page"
```

### Python (Alternative Client)
```python
import requests

# Test connection
response = requests.post('https://yourdomain.com/api.php', data={'test': 'true'})
print(response.json())

# Send text
response = requests.post('https://yourdomain.com/api.php', data='Your text here')
print(response.json())
```

## 🔐 Security Considerations for Web Hosting

### Essential Security Measures
1. **HTTPS Only**: Always use SSL/TLS encryption
2. **API Key Protection**: Never expose your API key in client-side code
3. **Rate Limiting**: Implement server-side rate limiting
4. **Input Validation**: Validate all uploads and inputs
5. **File Cleanup**: Regularly clean temporary files
6. **Access Logs**: Monitor for abuse

### Recommended Server Headers
```apache
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

## 📊 Usage Analytics

### Simple Usage Tracking
Add to your `api.php`:
```php
// Log usage (add to existing logging)
$usage_log = [
    'timestamp' => date('c'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'input_length' => strlen($inputText)
];
file_put_contents('logs/usage.json', json_encode($usage_log) . "\n", FILE_APPEND | LOCK_EX);
```

## 🎯 Integration Examples

### GitHub Actions
```yaml
name: Process with Gemini AI
on: [push]
jobs:
  process:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Process files
        shell: pwsh
        run: |
          irm https://yourdomain.com/client/gemini-client.ps1 | iex -ApiUrl "https://yourdomain.com/api.php" -InputFile "README.md"
```

### WordPress Integration
```php
// WordPress shortcode
function gemini_ai_shortcode($atts) {
    $atts = shortcode_atts(['text' => ''], $atts);
    if (empty($atts['text'])) return '';
    
    $response = wp_remote_post('https://yourdomain.com/api.php', [
        'body' => $atts['text']
    ]);
    
    if (is_wp_error($response)) return 'Error processing request';
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No response';
}
add_shortcode('gemini_ai', 'gemini_ai_shortcode');
```

## 📈 Performance Optimization

### Caching Responses
```php
// Add to api.php for response caching
$cache_key = md5($inputText);
$cache_file = "cache/{$cache_key}.json";

if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 3600) {
    // Return cached response
    echo file_get_contents($cache_file);
    exit;
}

// After API call, cache the response
file_put_contents($cache_file, $response);
```

### CDN Integration
- Host static files (PS1 scripts, setup) on a CDN
- Use CDN URLs in your documentation
- Implement proper cache headers

## 🛠️ Troubleshooting

### Common Issues
1. **PowerShell Execution Policy**: `Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser`
2. **CORS Issues**: Add appropriate CORS headers to API responses
3. **File Upload Limits**: Check PHP upload limits and server configuration
4. **SSL Certificate Issues**: Ensure valid SSL certificates

### Debug Mode
Add `?debug=1` to API calls for verbose output during testing.

---

This deployment guide enables flexible, remote usage of your Gemini AI Integration while maintaining security and performance.
