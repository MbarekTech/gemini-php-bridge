<?php
// Set your API key (replace with your actual key)
$API_KEY = "AIzaSyAJWYKKkaXOmQpaJWbPYmXu6NavEFdKJJY";

// Get the raw input (text file content)
$inputText = file_get_contents('php://input');
if (empty($inputText)) {
    http_response_code(400);
    echo json_encode(array("error" => "No input text detected"));
    exit;
}

// Append the instruction to the input text
$instruction = "\n\ndont write lines ```filetype , and Every file you create must start with FILE_START:filename.extension and end with FILE_END:filename.extension.";
$inputText .= $instruction;

// Construct JSON payload for the external API
$payload = array(
    "contents" => array(
        array(
            "role" => "user",
            "parts" => array(
                array("text" => $inputText)
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

// API endpoint URL
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=" . $API_KEY;

// Initialize cURL session
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

// Execute cURL session
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for errors
if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(array("error" => "cURL Error: " . curl_error($ch)));
    curl_close($ch);
    exit;
}

curl_close($ch);

// Send the response to the client
if ($httpCode == 200) {
    echo $response;
} else {
    http_response_code($httpCode);
    echo json_encode(array("error" => "API Response Error", "httpCode" => $httpCode, "response" => $response));
}
?>