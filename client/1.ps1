# Specify the URL to your PHP script (absolute path)
$phpApiUrl = "http://aska.website/a/index.php"  # Full URL that works regardless of where the script is run

Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Starting script"

# Get the current directory where the script is running from
$currentDir = Get-Location
Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Current Directory: $currentDir"


# Search for files in current directory
$textFiles = Get-ChildItem -Path $currentDir -Filter "*.txt"
Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Found Text Files: $($textFiles | ForEach-Object {$_.Name})"
# $pdfFiles = Get-ChildItem -Path $currentDir -Filter "*.pdf"  Removed PDF

# Initialize variables for file paths
$textFilePath = ""
$pdfFilePath = ""

# Check number of text files found
if ($textFiles.Count -gt 1) {
    Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Error: Multiple text files found, please use only one txt file"
    return;
} elseif($textFiles.Count -eq 1) {
    $textFilePath = $textFiles[0].FullName
    Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Text File Path: $textFilePath"
}

# Check number of pdf files found Removed PDF
# if ($pdfFiles.Count -gt 1) {
#         Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Error: Multiple pdf files found, please use only one pdf file"
#     return;
# } elseif($pdfFiles.Count -eq 1) {
#     $pdfFilePath = $pdfFiles[0].FullName
#     Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - PDF File Path: $pdfFilePath"
# }

# Check if both a text file and a pdf file are present Removed PDF
# if ($textFiles.Count -gt 1 -and $pdfFiles.Count -gt 1){
#     Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Error: Only one text and one pdf file allowed"
#     return;
# }

Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Constructing multipart/form-data body"

#Construct the multipart body
# Construct the multipart/form-data body
$boundary = [guid]::NewGuid().ToString()
$LF = "`r`n"
$postBody = @()

#Add text file if present
if($textFilePath) {
    Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Adding text file to multipart/form-data"
    $preamble = "--$boundary$LF"
    $fileBody = $preamble
    $fileBody += "Content-Disposition: form-data; name=`"textFile`"; filename=`"$([System.IO.Path]::GetFileName($textFilePath))`"$LF"
    $fileBody += "Content-Type: application/octet-stream$LF$LF"
    
    Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Reading content of text file"
    $fileContent = Get-Content -Path $textFilePath -Encoding Byte
    
    Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Adding preamble to body"
    $postBody += [System.Text.Encoding]::ASCII.GetBytes($fileBody)
    Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Adding file content to body"
    $postBody += $fileContent
    Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Adding new line to body"
    $postBody += [System.Text.Encoding]::ASCII.GetBytes("$LF")
    Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Text file added to multipart/form-data"
}
#Removed PDF logic



# Add the closing boundary
Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Adding closing boundary to multipart/form-data"
$postBody += [System.Text.Encoding]::ASCII.GetBytes("--$boundary--")
Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Closing boundary added to multipart/form-data"

Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Making HTTP POST request"
# Make the HTTP POST request using Invoke-WebRequest
try {
    $response = Invoke-WebRequest -Uri $phpApiUrl -Method Post -Body $postBody -ContentType "multipart/form-data; boundary=`"$boundary`""
    Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - HTTP POST request successful"
    
    # Output response text
     $responseContent = $response.Content
     Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Response received: $($responseContent)"
     # Attempt to convert the response to JSON
    try {
        $responseJson = $responseContent | ConvertFrom-Json
        if ($responseJson.candidates -and $responseJson.candidates[0].content -and $responseJson.candidates[0].content.parts) {
            $responseText = $responseJson.candidates[0].content.parts[0].text
             Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Response:"
            Write-Host $responseText
        } else {
              Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Error: Could not extract text from response"
             Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Response Content: $responseContent"
        }
    } catch {
        # If it can't be converted to JSON, it may be an error object, just echo to terminal
        Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Error: JSON Parsing Error"
        Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Response: $($responseContent)"
    }
    
} catch {
    Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Error during API request:"
    Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - $($_.Exception.Message)"
    if ($_.Exception.Response.Content) {
         Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Response Content: $($_.Exception.Response.Content)"
    }
    # Log the response content
    $logMessage = "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - API Error Response: $($_.Exception.Response.Content)"
    Add-Content -Path "powershell_error.log" -Value $logMessage
}
Write-Host "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss') - Script Finished"