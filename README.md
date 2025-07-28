# Gemini PHP Bridge

A PHP web service for Google's Gemini AI API with PowerShell client support.

[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

## Features

- REST API for Gemini AI integration
- PowerShell client for remote execution  
- File upload support (TXT, PDF, DOCX, MD)
- Cross-platform compatibility
- Rate limiting and security controls

## Quick Start

**PowerShell (One-liner):**
```powershell
irm 'https://yourdomain.com/simple-client.php?text=Your question here' | iex
```

**API (Direct):**
```bash
curl -X POST "https://yourdomain.com/gemini-api.php" -d "Your question here"
```

**Setup:**
1. Add your Google API key to `gemini-api.php` (line 13)
2. Upload files to web server
3. Set permissions: `chmod 755 temp_files logs output`

## Usage

**PowerShell:**
```powershell
# Simple function
function AI { param([string]$prompt); irm "https://yourdomain.com/simple-client.php?text=$prompt" | iex }
AI "Write a Python function"

# Direct API call
function Invoke-GeminiAI { 
    param([string]$Text)
    $response = Invoke-WebRequest -Uri "https://yourdomain.com/gemini-api.php" -Method POST -Body $Text -ContentType "text/plain"
    ($response.Content | ConvertFrom-Json).candidates[0].content.parts[0].text
}
```

**cURL:**
```bash
# Text query
curl -X POST "https://yourdomain.com/gemini-api.php" -d "Your question"

# File upload
curl -X POST "https://yourdomain.com/gemini-api.php" -F "textFile=@file.txt"
```

**Python:**
```python
import requests

def ask_ai(question):
    response = requests.post('https://yourdomain.com/gemini-api.php', data=question)
    return response.json()['candidates'][0]['content']['parts'][0]['text']
```

## Configuration

Edit `gemini-api.php`:
```php
$config = [
    'generation' => [
        'temperature' => 1,        // Creativity (0-2)
        'maxOutputTokens' => 8192  // Response length
    ],
    'security' => [
        'rate_limit_requests_per_minute' => 60,  // Rate limiting
        'max_input_length' => 100000,            // Input size limit
    ]
];
```

**Limits:**
- Rate limit: 60 requests/minute per IP
- File size: 10MB max
- Input text: 100,000 characters max
- Supported files: .txt, .pdf, .docx, .md

## Troubleshooting

**PowerShell execution policy:**
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

**File uploads:**
- Check PHP limits in php.ini: `upload_max_filesize`, `post_max_size`
- Verify permissions: 755 for directories, 644 for files
- Ensure file extension is allowed: .txt, .pdf, .docx, .md

**API issues:**
- Verify Google API key is valid
- Check logs in `logs/gemini_api.log`
- Test with: `curl -X POST url -d "test=true"`

## Requirements

- PHP 7.4+ with cURL and JSON extensions
- Google Cloud API key with Gemini API access
- Web server with file upload support

## Development

**Local testing:**
```bash
php -S localhost:8000
curl -X POST http://localhost:8000/gemini-api.php -d "test=true"
```

## License

MIT License - see [LICENSE](LICENSE) file.

## Documentation

- [Alternative Access Methods](docs/ALTERNATIVE_ACCESS.md)
- [Web Hosting Guide](docs/WEB_HOSTING.md)
- [Deployment Guide](docs/DEPLOYMENT.md)
