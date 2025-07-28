# Gemini PHP Bridge

A professional PHP web service that connects to Google's Gemini AI API. Upload text files or send content directly to get AI-generated responses. Built for easy web deployment and one-line remote execution.

[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

## What it does

- Process text files and documents with Google's Gemini AI
- Run PowerShell commands remotely without installation
- Handle file uploads (text, PDF, markdown)
- Provide REST API endpoints for integration
- Work across Windows, Linux, and macOS

## Getting started

### For web hosting (recommended)

1. **Edit the API key** in these files:
   - `gemini-api.php` (line 11): `$GOOGLE_API_KEY = "your-key-here";`
   - `client/gemini-bridge.ps1` (line 16): `$GOOGLE_API_KEY = "your-key-here";`
   - `client/gemini-bridge.ps1` (line 15): Update domain URL

2. **Upload files** to your web server

3. **Set folder permissions**:
   ```bash
   chmod 755 temp_files logs output
   ```

4. **Test it works**:
   ```bash
   curl -X POST https://yourdomain.com/gemini-api.php -d "test=true"
   ```

That's it. Users can now run commands like:

```powershell
# Test connection
irm https://yourdomain.com/client/gemini-bridge.ps1 | iex -Test

# Process text
irm https://yourdomain.com/client/gemini-bridge.ps1 | iex -InputText "Create a Python web scraper"

# Process file
irm https://yourdomain.com/client/gemini-bridge.ps1 | iex -InputFile "myfile.txt"
```

### For local development

```bash
git clone https://github.com/MbarekTech/gemini-php-bridge.git
cd gemini-php-bridge
# Edit config files with your API key
php -S localhost:8000
```

## How to use it

### PowerShell (Windows/Linux/macOS)

```powershell
# Quick test
irm https://yourdomain.com/client/gemini-bridge.ps1 | iex -Test

# Process some text
irm https://yourdomain.com/client/gemini-bridge.ps1 | iex -InputText "Explain quantum computing"

# Process your clipboard
irm https://yourdomain.com/client/gemini-bridge.ps1 | iex -InputText "$(Get-Clipboard)"

# Process a local file
irm https://yourdomain.com/client/gemini-bridge.ps1 | iex -InputFile "README.md"
```

### Direct API calls

```bash
# Test
curl -X POST https://yourdomain.com/gemini-api.php -d "test=true"

# Send text
curl -X POST https://yourdomain.com/gemini-api.php \
  -H "Content-Type: text/plain" \
  -d "Write a JavaScript function to sort an array"

# Upload file
curl -X POST https://yourdomain.com/gemini-api.php \
  -F "textFile=@document.txt"
```

### Browser bookmark

Create a bookmark with this JavaScript to process selected text:

```javascript
javascript:(function(){
    var text = window.getSelection().toString() || prompt('Enter text:');
    if(text) {
        var cmd = 'irm https://yourdomain.com/client/gemini-bridge.ps1 | iex -InputText "' + text + '"';
        navigator.clipboard.writeText(cmd);
        alert('Command copied! Paste in PowerShell.');
    }
})();
```

## File structure

```
gemini-php-bridge/
├── gemini-api.php          # Main API endpoint
├── api.php                 # API redirect (for compatibility)
├── index.php              # Legacy endpoint  
├── status.php             # Health check endpoint
├── client/
│   └── gemini-bridge.ps1   # PowerShell client
├── temp_files/            # File uploads (create manually)
├── logs/                  # Request logs (create manually)
├── output/                # Generated outputs (create manually)
├── config/                # Config templates (optional)
├── docs/                  # Documentation
└── .htaccess             # Web server security
```

## Configuration

The API key is embedded directly in the script files for simplicity. For development, you can also use the config files in the `config/` folder.

**Main settings** (in gemini-api.php):
- Google API key
- Model parameters (temperature, max tokens)
- File upload limits
- Logging preferences

**Client settings** (in gemini-bridge.ps1):
- Default API URL
- Output directory
- Logging preferences

## API reference

### POST /gemini-api.php

**Parameters:**
- `textFile`: Upload a text file
- `pdfFile`: Upload a PDF
- `test=true`: Test connection
- Raw POST body: Direct text input

**Response:**
```json
{
  "candidates": [
    {
      "content": {
        "parts": [
          {
            "text": "AI response here"
          }
        ]
      }
    }
  ]
}
```

**Error response:**
```json
{
  "error": "Error message",
  "httpCode": 400
}
```

## Security notes

- API key is stored server-side, users never see it
- File uploads are validated and cleaned up
- Consider rate limiting for production use
- Use HTTPS in production
- Monitor the logs for abuse

## Troubleshooting

**PowerShell execution policy error:**
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

**File upload issues:**
- Check PHP upload limits in php.ini
- Verify folder permissions (755 for directories)
- Check available disk space

**API connection problems:**
- Verify your Google API key is valid
- Check firewall/proxy settings
- Test with curl first

## Development

**Requirements:**
- PHP 7.4+
- cURL extension
- Google Cloud API key

**Local testing:**
```bash
php -S localhost:8000
curl -X POST http://localhost:8000/gemini-api.php -d "test=true"
```

**Adding features:**
- Edit `gemini-api.php` for server-side changes
- Edit `client/gemini-bridge.ps1` for client features
- Use the `config/` templates for complex setups

## Contributing

1. Fork the repo
2. Make your changes
3. Test locally
4. Submit a pull request

## License

MIT License - see [LICENSE](LICENSE) file.

## More info

- [Simple setup guide](docs/SIMPLE_SETUP.md) - Quick deployment
- [Web hosting guide](docs/WEB_HOSTING.md) - Detailed deployment
- [Bookmarklet guide](docs/BOOKMARKLET.md) - Browser integration
- [Google AI docs](https://ai.google.dev/docs) - API reference

---

Built for developers who want AI integration without the complexity.
