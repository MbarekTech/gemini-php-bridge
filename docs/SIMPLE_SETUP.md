# Simple Setup Guide for Web Hosting

## 🚀 Quick Deployment

### 1. **Update API Key**
Edit these files with your Google API key:

**In `api.php` (line 11):**
```php
$GOOGLE_API_KEY = "YOUR_ACTUAL_GOOGLE_API_KEY_HERE";
```

**In `client/gemini-client.ps1` (line 14):**
```powershell
$GOOGLE_API_KEY = "YOUR_ACTUAL_GOOGLE_API_KEY_HERE"
```

**In `client/gemini-client.ps1` (line 13):**
```powershell
$DEFAULT_API_URL = "https://yourdomain.com/api.php"  # Update with your actual domain
```

### 2. **Upload to Web Server**
Upload all files to your web server maintaining the directory structure.

### 3. **Set Permissions**
```bash
chmod 755 temp_files logs output
chmod 644 *.php *.ps1
```

### 4. **Test**
```bash
curl -X POST https://yourdomain.com/api.php -d "test=true"
```

## ✅ **Ready to Use!**

Users can now execute with simple one-liners:

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
