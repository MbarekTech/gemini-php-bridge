# PowerShell Client for Gemini AI Integration
# Version 2.0 - Remote execution with embedded API configuration
# Usage: irm https://yourdomain.com/client/gemini-client.ps1 | iex -InputText "Your prompt here"

param(
    [string]$ApiUrl = "",
    [string]$InputFile = "",
    [string]$OutputDir = "output",
    [string]$InputText = "",
    [switch]$Test = $false,
    [switch]$Verbose = $false
)

# ========================================
# CONFIGURATION - UPDATE THESE VALUES
# ========================================
$DEFAULT_API_URL = "https://yourdomain.com/api.php"  # Update with your domain
$GOOGLE_API_KEY = "AIzaSyAJWYKKkaXOmQpaJWbPYmXu6NavEFdKJJY"  # Update with your actual API key

# Use provided API URL or default
if ([string]::IsNullOrEmpty($ApiUrl)) {
    $ApiUrl = $DEFAULT_API_URL
}

# Configuration
$LogFile = "logs/client_$(Get-Date -Format 'yyyyMMdd').log"

# Detect if running remotely
$isRemote = ($MyInvocation.MyCommand.Path -eq $null) -or ($MyInvocation.InvocationName -like "*iex*")

# Ensure output and log directories exist (only for local execution)
if (!$isRemote) {
    if (!(Test-Path "logs")) { New-Item -ItemType Directory -Path "logs" | Out-Null }
    if (!(Test-Path $OutputDir)) { New-Item -ItemType Directory -Path $OutputDir | Out-Null }
} else {
    # For remote execution, use current directory
    $OutputDir = "."
    $LogFile = "client_$(Get-Date -Format 'yyyyMMdd').log"
}

# Logging function
function Write-Log {
    param([string]$Message, [string]$Level = "INFO")
    $Timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $LogMessage = "[$Timestamp] [$Level] $Message"
    
    if ($Verbose -or $Level -eq "ERROR") {
        Write-Host $LogMessage -ForegroundColor $(
            switch ($Level) {
                "ERROR" { "Red" }
                "WARNING" { "Yellow" }
                "SUCCESS" { "Green" }
                default { "White" }
            }
        )
    }
    
    Add-Content -Path $LogFile -Value $LogMessage
}

Write-Log "Client started with parameters: ApiUrl=$ApiUrl, InputFile=$InputFile, Test=$Test"

# Test connection if requested
if ($Test) {
    Write-Log "Testing API connection..."
    try {
        $testBody = @{ test = "true" }
        $response = Invoke-RestMethod -Uri $ApiUrl -Method Post -Body $testBody
        Write-Log "Test successful: $($response.message)" "SUCCESS"
        return
    } catch {
        Write-Log "Test failed: $($_.Exception.Message)" "ERROR"
        return
    }
}

# Find input file if not specified and no direct text
if ([string]::IsNullOrEmpty($InputFile) -and [string]::IsNullOrEmpty($InputText)) {
    $textFiles = Get-ChildItem -Filter "*.txt" | Where-Object { $_.Name -ne $LogFile }
    
    if ($textFiles.Count -eq 0) {
        Write-Log "No .txt files found in current directory" "ERROR"
        Write-Host "💡 Tip: You can also use -InputText parameter for direct text input" -ForegroundColor Yellow
        Write-Host "Example: irm https://domain.com/client/gemini-client.ps1 | iex -ApiUrl 'https://domain.com/api.php' -InputText 'Create a simple HTML page'" -ForegroundColor Yellow
        return
    } elseif ($textFiles.Count -gt 1) {
        Write-Log "Multiple .txt files found. Please specify which one to use:" "WARNING"
        $textFiles | ForEach-Object { Write-Host "  - $($_.Name)" }
        return
    }
    
    $InputFile = $textFiles[0].FullName
}

# Handle direct text input
if (![string]::IsNullOrEmpty($InputText)) {
    Write-Log "Processing direct text input, length: $($InputText.Length)"
    
    # Create temporary file for text input
    $tempFile = [System.IO.Path]::GetTempFileName()
    Set-Content -Path $tempFile -Value $InputText -Encoding UTF8
    $InputFile = $tempFile
    $cleanupTempFile = $true
} else {
    $cleanupTempFile = $false
}

# Validate input file
if (-not (Test-Path $InputFile)) {
    Write-Log "Input file not found: $InputFile" "ERROR"
    return
}

Write-Log "Processing file: $InputFile"

# Prepare multipart form data
$boundary = [System.Guid]::NewGuid().ToString()
$LF = "`r`n"

# Read file content
$fileContent = Get-Content -Path $InputFile -Encoding Byte
$fileName = [System.IO.Path]::GetFileName($InputFile)

# Build multipart body
$bodyParts = @()
$bodyParts += "--$boundary$LF"
$bodyParts += "Content-Disposition: form-data; name=`"textFile`"; filename=`"$fileName`"$LF"
$bodyParts += "Content-Type: text/plain$LF$LF"

# Convert to bytes
$bodyBytes = [System.Text.Encoding]::UTF8.GetBytes($bodyParts -join "")
$bodyBytes += $fileContent
$bodyBytes += [System.Text.Encoding]::UTF8.GetBytes("$LF--$boundary--")

Write-Log "Sending request to API..."

try {
    $response = Invoke-RestMethod -Uri $ApiUrl -Method Post -Body $bodyBytes -ContentType "multipart/form-data; boundary=`"$boundary`""
    
    Write-Log "API request successful" "SUCCESS"
    
    # Parse and display response
    if ($response.candidates -and $response.candidates[0].content -and $response.candidates[0].content.parts) {
        $responseText = $response.candidates[0].content.parts[0].text
        Write-Log "Response received, length: $($responseText.Length)"
        
        # Save response to file
        $outputFile = "$OutputDir/response_$(Get-Date -Format 'yyyyMMdd_HHmmss').txt"
        Set-Content -Path $outputFile -Value $responseText -Encoding UTF8
        Write-Log "Response saved to: $outputFile" "SUCCESS"
        
        # Display response
        Write-Host "`n--- API Response ---" -ForegroundColor Green
        Write-Host $responseText
        Write-Host "`n--- End Response ---" -ForegroundColor Green
    } else {
        Write-Log "Unexpected response format" "WARNING"
        Write-Host "Raw Response:" -ForegroundColor Yellow
        Write-Host ($response | ConvertTo-Json -Depth 10)
    }
    
} catch {
    Write-Log "API request failed: $($_.Exception.Message)" "ERROR"
    
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode
        Write-Log "HTTP Status Code: $statusCode" "ERROR"
        
        try {
            $errorResponse = $_.Exception.Response.GetResponseStream()
            $reader = New-Object System.IO.StreamReader($errorResponse)
            $errorContent = $reader.ReadToEnd()
            Write-Log "Error details: $errorContent" "ERROR"
        } catch {
            Write-Log "Could not read error details" "WARNING"
        }
    }
}

# Cleanup temporary file if created
if ($cleanupTempFile -and (Test-Path $tempFile)) {
    Remove-Item $tempFile -Force
    Write-Log "Cleaned up temporary file"
}

Write-Log "Client finished"
