# Alternative Access Methods

Alternative ways to access the Gemini PHP Bridge when standard methods are blocked.

## URL Parameter Method
Direct execution with URL parameters (bypasses PowerShell parameter restrictions):
```powershell
irm 'https://yourdomain.com/simple-client.php?text=Your question' | iex
irm 'https://yourdomain.com/simple-client.php?test=1' | iex

# Create a function
function AI { param([string]$prompt); irm "https://yourdomain.com/simple-client.php?text=$prompt" | iex }
```

## GitHub Raw Method
Download directly from GitHub repository:
```powershell
$script = irm https://raw.githubusercontent.com/MbarekTech/gemini-php-bridge/main/client/gemini-bridge.ps1
$sb = [scriptblock]::Create($script)
& $sb -ApiUrl 'https://yourdomain.com/gemini-api.php' -Test
```

## Direct API Function
Most reliable method for automation:
```powershell
function Invoke-GeminiAI {
    param([string]$Text)
    $response = Invoke-WebRequest -Uri "https://yourdomain.com/gemini-api.php" -Method POST -Body $Text -ContentType "text/plain"
    return ($response.Content | ConvertFrom-Json).candidates[0].content.parts[0].text
}
```

## Client Proxy
Use the get-client.php endpoint:
```powershell
$script = irm https://yourdomain.com/get-client.php
$sb = [scriptblock]::Create($script)
& $sb -InputText "Your question"
```
- **Method 1**: ✅ Perfect for one-liners - `irm 'yourdomain.com/simple-client.php?text=hello' | iex`
- **Method 2**: ✅ GitHub raw works with scriptblock approach  
- **Method 3**: ⚠️ Proxy works but requires scriptblock
- **Method 4**: ✅ Direct API function most reliable for scripting

**Recommended**: Use Method 1 for interactive use, Method 4 for scripts.
