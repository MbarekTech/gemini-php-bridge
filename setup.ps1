# Gemini AI Integration - Setup Script
# Run this script to set up the project after cloning or remote execution
# Usage: irm https://yourdomain.com/setup.ps1 | iex -ApiKey "YOUR_API_KEY"

param(
    [string]$ApiKey = "",
    [string]$BaseUrl = "",
    [string]$InstallPath = "gemini-ai-integration",
    [switch]$Help = $false,
    [switch]$RemoteMode = $false
)

if ($Help) {
    Write-Host @"
Gemini AI Integration Setup Script

REMOTE USAGE:
  irm https://yourdomain.com/setup.ps1 | iex -ApiKey "YOUR_API_KEY"
  irm https://yourdomain.com/setup.ps1 | iex -ApiKey "YOUR_API_KEY" -BaseUrl "https://yourdomain.com"

LOCAL USAGE:
  .\setup.ps1 -ApiKey "YOUR_API_KEY"

Parameters:
  -ApiKey         Your Google Gemini AI API key (required)
  -BaseUrl        Base URL for hosted version (auto-detected if remote)
  -InstallPath    Local installation directory (default: gemini-ai-integration)
  -RemoteMode     Force remote mode detection
  -Help           Show this help message

Examples:
  # Remote installation
  irm https://example.com/setup.ps1 | iex -ApiKey "AIzaSyAJWYKKka..."
  
  # Local installation
  .\setup.ps1 -ApiKey "AIzaSyAJWYKKka..."

This script will:
1. Detect if running locally or remotely
2. Download necessary files (if remote)
3. Create directory structure
4. Configure with your API key
5. Test the setup
"@
    return
}

Write-Host "🚀 Setting up Gemini AI Integration..." -ForegroundColor Green

# Check if API key is provided
if ([string]::IsNullOrEmpty($ApiKey)) {
    Write-Host "❌ Error: API key is required" -ForegroundColor Red
    Write-Host "Usage: irm https://yourdomain.com/setup.ps1 | iex -ApiKey <your-api-key>" -ForegroundColor Yellow
    Write-Host "   or: .\setup.ps1 -ApiKey <your-api-key>" -ForegroundColor Yellow
    return
}

# Detect if running remotely (invoked via irm | iex)
$isRemote = $RemoteMode -or ($MyInvocation.MyCommand.Path -eq $null) -or ($MyInvocation.InvocationName -like "*iex*")

if ($isRemote) {
    Write-Host "🌐 Remote installation detected" -ForegroundColor Cyan
    
    # Auto-detect base URL if not provided
    if ([string]::IsNullOrEmpty($BaseUrl)) {
        # Try to extract from the current execution context
        $BaseUrl = "https://yourdomain.com"  # Default - should be updated for your domain
        Write-Host "⚠️  Using default BaseUrl: $BaseUrl" -ForegroundColor Yellow
        Write-Host "   You can specify: -BaseUrl 'https://your-actual-domain.com'" -ForegroundColor Yellow
    }
    
    # Create local installation directory
    if (!(Test-Path $InstallPath)) {
        New-Item -ItemType Directory -Path $InstallPath -Force | Out-Null
        Write-Host "✅ Created installation directory: $InstallPath" -ForegroundColor Green
    }
    
    Set-Location $InstallPath
    
    # Download necessary files
    $filesToDownload = @(
        @{Url = "$BaseUrl/config/config.example.php"; Path = "config/config.example.php"},
        @{Url = "$BaseUrl/client/gemini-client.ps1"; Path = "client/gemini-client.ps1"},
        @{Url = "$BaseUrl/api.php"; Path = "api-reference.txt"}  # For reference only
    )
    
    foreach ($file in $filesToDownload) {
        try {
            $dir = Split-Path $file.Path -Parent
            if ($dir -and !(Test-Path $dir)) {
                New-Item -ItemType Directory -Path $dir -Force | Out-Null
            }
            
            Invoke-RestMethod -Uri $file.Url -OutFile $file.Path -ErrorAction Stop
            Write-Host "✅ Downloaded: $($file.Path)" -ForegroundColor Green
        } catch {
            Write-Host "⚠️  Could not download $($file.Url): $($_.Exception.Message)" -ForegroundColor Yellow
        }
    }
    
    $apiUrl = "$BaseUrl/api.php"
} else {
    Write-Host "💻 Local installation detected" -ForegroundColor Cyan
    $apiUrl = "http://localhost:8000/api.php"
}

# Create required directories
$directories = @("temp_files", "logs", "output", "config")
foreach ($dir in $directories) {
    if (!(Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
        Write-Host "✅ Created directory: $dir" -ForegroundColor Green
    } else {
        Write-Host "📁 Directory already exists: $dir" -ForegroundColor Yellow
    }
}

# Copy configuration template if config.php doesn't exist
if (!(Test-Path "config/config.php")) {
    if (Test-Path "config/config.example.php") {
        Copy-Item "config/config.example.php" "config/config.php"
        Write-Host "✅ Created config/config.php from template" -ForegroundColor Green
    } else {
        Write-Host "❌ Error: config/config.example.php not found" -ForegroundColor Red
        return
    }
} else {
    Write-Host "📄 Config file already exists: config/config.php" -ForegroundColor Yellow
}

# Update API key in configuration
try {
    $configContent = Get-Content "config/config.php" -Raw
    $updatedContent = $configContent -replace "'key' => 'YOUR_GOOGLE_API_KEY_HERE'", "'key' => '$ApiKey'"
    Set-Content "config/config.php" -Value $updatedContent -Encoding UTF8
    Write-Host "✅ Updated API key in configuration" -ForegroundColor Green
} catch {
    Write-Host "❌ Error updating configuration: $($_.Exception.Message)" -ForegroundColor Red
    return
}

# Set directory permissions (Windows)
try {
    icacls "temp_files" /grant "Everyone:(OI)(CI)F" /q 2>$null
    icacls "logs" /grant "Everyone:(OI)(CI)F" /q 2>$null
    icacls "output" /grant "Everyone:(OI)(CI)F" /q 2>$null
    Write-Host "✅ Set directory permissions" -ForegroundColor Green
} catch {
    Write-Host "⚠️  Could not set directory permissions (this may be normal)" -ForegroundColor Yellow
}

# Test the setup
Write-Host "`n🧪 Testing setup..." -ForegroundColor Cyan

try {
    # Test PHP syntax
    $phpTest = php -l "api.php" 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ PHP syntax check passed" -ForegroundColor Green
    } else {
        Write-Host "❌ PHP syntax error: $phpTest" -ForegroundColor Red
    }
    
    # Test configuration loading
    $configTest = php -r "try { `$config = require 'config/config.php'; echo 'Config loaded successfully'; } catch (Exception `$e) { echo 'Config error: ' . `$e->getMessage(); exit(1); }"
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✅ Configuration loading test passed" -ForegroundColor Green
    } else {
        Write-Host "❌ Configuration error: $configTest" -ForegroundColor Red
    }
} catch {
    Write-Host "⚠️  Could not run PHP tests (PHP may not be in PATH)" -ForegroundColor Yellow
}

Write-Host "`n🎉 Setup completed!" -ForegroundColor Green

if ($isRemote) {
    Write-Host @"

🌐 REMOTE SETUP COMPLETE

Your Gemini AI Integration is now ready!

Quick Usage:
1. Test the API:
   irm $BaseUrl/client/gemini-client.ps1 | iex -ApiUrl "$apiUrl" -Test

2. Process a file:
   irm $BaseUrl/client/gemini-client.ps1 | iex -ApiUrl "$apiUrl" -InputFile "your-file.txt"

3. Direct API access:
   curl -X POST $apiUrl -F "textFile=@your-file.txt"

Local files created in: $InstallPath
API endpoint: $apiUrl
"@ -ForegroundColor Cyan
} else {
    Write-Host @"

💻 LOCAL SETUP COMPLETE

Next steps:
1. Start a PHP development server:
   php -S localhost:8000

2. Test the API:
   .\client\gemini-client.ps1 -Test -ApiUrl "$apiUrl"

3. Process a file:
   .\client\gemini-client.ps1 -InputFile "your-file.txt" -ApiUrl "$apiUrl"

For more information, see README.md
"@ -ForegroundColor Cyan
}
