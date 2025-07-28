# Simple Setup Guide

## Quick Deployment

### 1. Update API Key
Edit `gemini-api.php` (line 13):
```php
define('GOOGLE_API_KEY', 'your-actual-google-api-key-here');
```

### 2. Upload Files
Upload all files to your web server maintaining directory structure.

### 3. Set Permissions
```bash
chmod 755 temp_files logs output
chmod 644 *.php
```

### 4. Test
```bash
curl -X POST https://yourdomain.com/gemini-api.php -d "test=true"
```

## Usage

**PowerShell one-liner:**
```powershell
irm 'https://yourdomain.com/simple-client.php?text=Your question' | iex
```

**API call:**
```bash
curl -X POST "https://yourdomain.com/gemini-api.php" -d "Your question"
```

```powershell
# Test
irm https://yourdomain.com/client/gemini-client.ps1 | iex -Test

# Process text
irm https://yourdomain.com/client/gemini-client.ps1 | iex -InputText "Create a Python web scraper"

# Process file
irm https://yourdomain.com/client/gemini-client.ps1 | iex -InputFile "myfile.txt"
```

## 🔐 **Security Note**
- The API key is embedded in the server-side scripts
- Users never see or need to handle the API key
- All API calls are proxied through your server
- Consider implementing rate limiting for production use

## 📁 **Required Files**
```
/
├── api.php                    # Main API (with embedded key)
├── index.php                  # Legacy API (with embedded key)  
├── client/
│   └── gemini-client.ps1     # Client script (with embedded config)
├── temp_files/               # Writable directory
├── logs/                     # Writable directory
├── output/                   # Optional: for local development
└── .htaccess                 # Optional: security rules
```

That's it! No complex configuration files or setup scripts needed.
