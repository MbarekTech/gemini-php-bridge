# Web Deployment Guide

## 📁 **Clean Web Structure**

After reorganization, your web directory should contain only these essential files:

### **Root Directory (upload to web server)**
```
gemini-php-bridge/
├── gemini-api.php          # Main API endpoint
├── api.php                 # Compatibility redirect
├── index.php              # Legacy endpoint
├── status.php             # Health check
├── .htaccess              # Security rules
├── LICENSE                # MIT license
├── README.md              # Main documentation
└── client/
    └── gemini-bridge.ps1   # PowerShell client
```

### **Required Directories**
```bash
# Create these directories on your web server
mkdir temp_files logs output
chmod 755 temp_files logs output
```

### **Optional Directories**
```
├── config/                # Configuration templates
├── docs/                  # Documentation files
└── unused/               # Development/backup files
```

## 🚀 **Quick Deployment**

### **1. Upload Core Files**
Upload these files to your web server root:
- `gemini-api.php` (main API)
- `api.php` (redirect for compatibility)
- `index.php` (legacy support)
- `status.php` (health check)
- `.htaccess` (security)
- `client/gemini-bridge.ps1` (PowerShell client)

### **2. Configure API Keys**
Edit two files with your Google API key:

**gemini-api.php** (line 11):
```php
$GOOGLE_API_KEY = "your-actual-api-key-here";
```

**client/gemini-bridge.ps1** (line 16):
```powershell
$GOOGLE_API_KEY = "your-actual-api-key-here"
```

**client/gemini-bridge.ps1** (line 15):
```powershell
$DEFAULT_API_URL = "https://yourdomain.com/gemini-api.php"
```

### **3. Test Deployment**
```bash
# Test main API
curl -X POST https://yourdomain.com/gemini-api.php -d "test=true"

# Test compatibility redirect
curl -X POST https://yourdomain.com/api.php -d "test=true"

# Test health check
curl https://yourdomain.com/status.php

# Test PowerShell client
irm https://yourdomain.com/client/gemini-bridge.ps1 | iex -Test
```

## 🔧 **File Purpose Guide**

### **Essential Web Files**
- **gemini-api.php**: Main API endpoint with all functionality
- **api.php**: Redirect for backward compatibility
- **client/gemini-bridge.ps1**: PowerShell client for remote execution
- **.htaccess**: Security rules and access control

### **Optional Files**
- **index.php**: Legacy endpoint (can be removed if not needed)
- **status.php**: Health monitoring (recommended for production)
- **config/**: Template files for complex setups
- **docs/**: Documentation (not needed on web server)

### **Moved to unused/**
- Old client files (1, 2, 3, 1.ps1)
- Development scripts (setup.ps1, deploy.sh)
- Documentation drafts and assessments
- Temporary files

## 🛡️ **Security Notes**

1. **API Key Protection**: Keys are embedded in server-side files only
2. **File Upload Security**: Validated and cleaned automatically
3. **Access Control**: .htaccess restricts access to sensitive directories
4. **HTTPS Required**: Use SSL in production
5. **Rate Limiting**: Consider implementing for production use

## 📊 **Monitoring**

Use `status.php` to monitor your deployment:
```bash
curl https://yourdomain.com/status.php
```

Returns JSON with server status, API availability, and system health.

## 🔄 **Updates**

To update your deployment:
1. Replace `gemini-api.php` and `client/gemini-bridge.ps1`
2. Keep your API key configuration
3. Test with `status.php`
4. Verify client functionality

---

Your Gemini PHP Bridge is now professionally organized and web-ready! 🎉
