<?php
// Set your API key (replace with your actual key)
$API_KEY = "AIzaSyAJWYKKkaXOmQpaJWbPYmXu6NavEFdKJJY";

// Log the request for debugging
$log_file = 'php_debug.log';
$log_message = date('Y-m-d H:i:s') . " - Script Start\n";
file_put_contents($log_file, $log_message, FILE_APPEND);

$log_message = date('Y-m-d H:i:s') . " - Request Received:\n";
$log_message .= "POST Data: " . json_encode($_POST) . "\n";
file_put_contents($log_file, $log_message, FILE_APPEND);

// Get the file name from the URL
$requestedFile = isset($_SERVER['REQUEST_URI']) ? basename($_SERVER['REQUEST_URI']) : '';

$log_message = date('Y-m-d H:i:s') . " - Requested File: " . $requestedFile . "\n";
file_put_contents($log_file, $log_message, FILE_APPEND);

// Check if the filename matches api_handler.php and if so, treat as normal.
if ($requestedFile == "api_handler.php" || $requestedFile == "") {
    $log_message = date('Y-m-d H:i:s') . " - Handling API Request\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);

    // Initialize input text for JSON payload
    $inputText = "";

    // Handle image file content if present
    if (isset($_FILES['imageFile'])) {
        $log_message = date('Y-m-d H:i:s') . " - imageFile upload attempt detected\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);

        if ($_FILES['imageFile']['error'] == UPLOAD_ERR_OK) {
            $log_message = date('Y-m-d H:i:s') . " - imageFile upload successful\n";
            file_put_contents($log_file, $log_message, FILE_APPEND);
            $uploadedImageFile = $_FILES['imageFile']['tmp_name'];
            $imageData = file_get_contents($uploadedImageFile);
            $inputText = base64_encode($imageData);
        } else {
            $log_message = date('Y-m-d H:i:s') . " - imageFile upload failed with error: " . json_encode($_FILES['imageFile']['error']) . "\n";
            file_put_contents($log_file, $log_message, FILE_APPEND);
        }
    }

    // Handle raw input if present:
    if (empty($inputText)) {
        $rawInput = file_get_contents('php://input');
        if (!empty($rawInput)) {
            $inputText = $rawInput;
            $log_message = date('Y-m-d H:i:s') . " - Raw input detected: " . $inputText . "\n";
            file_put_contents($log_file, $log_message, FILE_APPEND);
        }
    }

    $log_message = date('Y-m-d H:i:s') . " - Input Text (Before Decoding): " . $inputText . "\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);


    if (empty($inputText)) {
        $log_message = date('Y-m-d H:i:s') . " - Error: No input text detected or uploaded file\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);
        http_response_code(400);
        echo json_encode(array("error" => "No input text detected or uploaded file"));
        exit;
    }

      // Add a prompt to instruct the API to generate only the file content without explanations or comments
    $prompt = "Generate only the file content without any explanations or comments. Here is the input:\n\n" . $inputText;


    // Construct JSON payload
    $payload = array(
        "contents" => array(
            array(
                "role" => "user",
                "parts" => array(
                    array("text" => $prompt)
                )
            )
        ),
        "generationConfig" => array(
            "temperature" => 1,
            "topK" => 40,
            "topP" => 0.95,
            "maxOutputTokens" => 8192,
            "responseMimeType" => "text/plain"
        )
    );

    $jsonPayload = json_encode($payload);

    // Log the json payload
    $log_message = date('Y-m-d H:i:s') . " - JSON Payload: " . $jsonPayload . "\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);

    // API endpoint URL
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=" . $API_KEY;

    $log_message = date('Y-m-d H:i:s') . " - API URL: " . $url . "\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);

    // Initialize cURL session
    $ch = curl_init($url);

    $log_message = date('Y-m-d H:i:s') . " - cURL initialized\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $log_message = date('Y-m-d H:i:s') . " - cURL options set\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);

    // **Add SSL Verification**
     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $log_message = date('Y-m-d H:i:s') . " - SSL verification disabled\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);

    // Initialize httpCode here
    $httpCode = 0;

    $log_message = date('Y-m-d H:i:s') . " - Before API Call \n";
    file_put_contents($log_file, $log_message, FILE_APPEND);

    // Execute cURL session
    $response = curl_exec($ch);

    $log_message = date('Y-m-d H:i:s') . " - After API Call \n";
    file_put_contents($log_file, $log_message, FILE_APPEND);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $log_message = date('Y-m-d H:i:s') . " - API call finished, http code: " . $httpCode . "\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);

    // Check for errors
    if (curl_errno($ch)) {
        $log_message = date('Y-m-d H:i:s') . " - cURL Error: " . curl_error($ch) . "\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);
        http_response_code(500); // Internal Server Error
        echo json_encode(array("error" => "cURL Error: " . curl_error($ch)));
        curl_close($ch);
        exit;
    }
    curl_close($ch);

    // Send the response to the PowerShell script
    if ($httpCode == 200) {
        $log_message = date('Y-m-d H:i:s') . " - API Success\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);

        echo $response; // Send the raw json if successful
    } else {
        $log_message = date('Y-m-d H:i:s') . " - API Error, HTTP Code: " . $httpCode . ", Response: " . $response . "\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);
        http_response_code($httpCode);
        echo json_encode(array("error" => "API Response Error", "httpCode" => $httpCode, "response" => $response)); //Send errors if needed
    }
} else { // If a specific file is requested
    $log_message = date('Y-m-d H:i:s') . " - File Request Received: " . $requestedFile . "\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
    $filePath = "./" . $requestedFile;

    if (file_exists($filePath)) {
        $log_message = date('Y-m-d H:i:s') . " - File Exists: " . $filePath . "\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);

        // Check if file is a .txt
        if (pathinfo($filePath, PATHINFO_EXTENSION) === "txt") {
            $log_message = date('Y-m-d H:i:s') . " - Serving TXT File\n";
            file_put_contents($log_file, $log_message, FILE_APPEND);
            header('Content-type: text/plain');
            readfile($filePath);
        } else if (pathinfo($filePath, PATHINFO_EXTENSION) === "pdf") {
            $log_message = date('Y-m-d H:i:s') . " - Serving PDF File\n";
            file_put_contents($log_file, $log_message, FILE_APPEND);
            header('Content-type: application/pdf');
            readfile($filePath);
        } else {
            $log_message = date('Y-m-d H:i:s') . " - File type not supported: " . $filePath . "\n";
            file_put_contents($log_file, $log_message, FILE_APPEND);
            http_response_code(400);
            echo json_encode(array("error" => "File type not supported"));
        }
    } else {
        $log_message = date('Y-m-d H:i:s') . " - File Not Found: " . $filePath . "\n";
        file_put_contents($log_file, $log_message, FILE_APPEND);
        http_response_code(404);
        echo json_encode(array("error" => "File not found"));
    }
}
?>