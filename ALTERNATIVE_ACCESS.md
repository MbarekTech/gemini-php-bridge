# Alternative Remote Execution Methods

Due to security layers on some hosting providers, here are alternative ways to use the Gemini PHP Bridge:

## Method 1: Simple Client (Recommended) ✅ WORKING
```powershell
# Test connection
irm 'https://yourdomain.com/a/simple-client.php?test=1' | iex

# Process text directly
irm 'https://yourdomain.com/a/simple-client.php?text=Write a Python hello world' | iex

# Create a simple function for easy use
function AI { param([string]$prompt); irm "https://yourdomain.com/a/simple-client.php?text=$prompt" | iex }
AI "Explain quantum computing"
```

## Method 2: Direct GitHub Raw ✅ WORKING
```powershell
# Download from GitHub (bypasses hosting security) - requires scriptblock
$script = irm https://raw.githubusercontent.com/MbarekTech/gemini-php-bridge/main/client/gemini-bridge.ps1
$sb = [scriptblock]::Create($script)
& $sb -ApiUrl 'https://yourdomain.com/gemini-api.php' -Test
& $sb -ApiUrl 'https://yourdomain.com/gemini-api.php' -InputText "Your prompt here"
```

## Method 3: Proxy Endpoint ⚠️ REQUIRES SCRIPTBLOCK
```powershell
# Use the proxy endpoint (requires scriptblock due to complexity)
$script = irm https://yourdomain.com/get-client.php
$sb = [scriptblock]::Create($script)
& $sb -Test
& $sb -InputText "Your prompt here"
```

## Method 4: Direct API Function ✅ WORKING
```powershell
# Create a simple function (most reliable)
function Invoke-GeminiAI {
    param([string]$Text)
    $response = Invoke-WebRequest -Uri "https://yourdomain.com/gemini-api.php" -Method POST -Body $Text -ContentType "text/plain"
    $json = $response.Content | ConvertFrom-Json
    return $json.candidates[0].content.parts[0].text
}

# Usage
Invoke-GeminiAI "Write a simple Python function"
```

## Testing ✅ ALL METHODS TESTED AND WORKING
- **Method 1**: ✅ Perfect for one-liners - `irm 'yourdomain.com/simple-client.php?text=hello' | iex`
- **Method 2**: ✅ GitHub raw works with scriptblock approach  
- **Method 3**: ⚠️ Proxy works but requires scriptblock
- **Method 4**: ✅ Direct API function most reliable for scripting

**Recommended**: Use Method 1 for interactive use, Method 4 for scripts.
