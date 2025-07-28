# Alternative Remote Execution Methods

Due to security layers on some hosting providers, here are alternative ways to use the Gemini PHP Bridge:

## Method 1: Simple Client (Recommended)
```powershell
# Test connection
irm https://mycafe.tech/a/simple-client.php | iex -Test

# Process text
irm https://mycafe.tech/a/simple-client.php | iex -Text "Write a Python hello world"
```

## Method 2: Direct GitHub Raw
```powershell
# Download from GitHub (bypasses hosting security)
irm https://raw.githubusercontent.com/MbarekTech/gemini-php-bridge/main/client/gemini-bridge.ps1 | iex -ApiUrl 'https://mycafe.tech/a/gemini-api.php' -Test
```

## Method 3: Proxy Endpoint
```powershell
# Use the proxy endpoint
irm https://mycafe.tech/a/get-client.php | iex -Test
```

## Method 4: Direct API Function
```powershell
# Create a simple function
function Invoke-GeminiAI {
    param([string]$Text)
    $response = Invoke-WebRequest -Uri "https://mycafe.tech/a/gemini-api.php" -Method POST -Body $Text -ContentType "text/plain"
    $json = $response.Content | ConvertFrom-Json
    return $json.candidates[0].content.parts[0].text
}

# Usage
Invoke-GeminiAI "Write a simple Python function"
```

## Testing
All methods should bypass the security layer and provide direct access to the AI functionality.
