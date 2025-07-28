<?php
/**
 * Gemini AI API Integration
 * Main endpoint for processing text and file uploads with Google's Gemini AI
 * 
 * @version 2.1
 * @author Gemini PHP Bridge Team
 * @license MIT
 */

// ========================================
// CONFIGURATION CONSTANTS
// ========================================
define('GOOGLE_API_KEY', 'your-google-api-key-here');  // Update with your actual API key
define('BASE_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent');
define('MAX_FILE_SIZE', '10M');
define('MAX_INPUT_LENGTH', 100000);
define('RATE_LIMIT_PER_MINUTE', 60);

// Configuration array
$config = [
    'api' => [
        'key' => GOOGLE_API_KEY,
        'endpoint' => BASE_API_URL,
        'model' => 'gemini-2.0-flash-exp'
    ],
    'generation' => [
        'temperature' => 1,
        'topK' => 40,
        'topP' => 0.95,
        'maxOutputTokens' => 8192,
        'responseMimeType' => 'text/plain'
    ],
    'upload' => [
        'temp_dir' => 'temp_files',
        'max_file_size' => MAX_FILE_SIZE,
        'allowed_extensions' => ['txt', 'pdf', 'docx', 'md'],
        'cleanup_after_hours' => 24
    ],
    'logging' => [
        'enabled' => true,
        'level' => 'DEBUG',
        'file' => 'logs/gemini_api.log',
        'max_size' => '50M'
    ],
    'security' => [
        'ssl_verify' => true,
        'rate_limit_requests_per_minute' => RATE_LIMIT_PER_MINUTE,
        'max_input_length' => MAX_INPUT_LENGTH
    ]
];

// Initialize logging
class Logger {
    private string $logFile;
    private bool $enabled;
    
    public function __construct(array $config) {
        $this->logFile = $config['logging']['file'];
        $this->enabled = $config['logging']['enabled'];
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    public function log(string $level, string $message): void {
        if (!$this->enabled) return;
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    public function debug(string $message): void { $this->log('DEBUG', $message); }
    public function info(string $message): void { $this->log('INFO', $message); }
    public function warning(string $message): void { $this->log('WARNING', $message); }
    public function error(string $message): void { $this->log('ERROR', $message); }
}

$logger = new Logger($config);
$logger->info("Request received: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);

/**
 * API Response Helper
 */
function sendResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Error Handler
 */
function handleError(string $message, int $statusCode = 400): void {
    global $logger;
    $logger->error($message);
    sendResponse(['error' => $message], $statusCode);
}

// Handle test requests
if (isset($_POST['test']) && $_POST['test'] === 'true') {
    $logger->info("Test request received");
    sendResponse(['status' => 'OK', 'message' => 'API is working']);
}

// Initialize input processing
$inputText = '';

// Handle file uploads
if (isset($_FILES['textFile']) && $_FILES['textFile']['error'] === UPLOAD_ERR_OK) {
    $logger->debug("Text file upload detected");
    $inputText = file_get_contents($_FILES['textFile']['tmp_name']);
    $logger->info("Text file processed, length: " . strlen($inputText));
}

// Handle PDF uploads
if (isset($_FILES['pdfFile']) && $_FILES['pdfFile']['error'] === UPLOAD_ERR_OK) {
    $logger->debug("PDF file upload detected");
    
    $originalFileName = $_FILES['pdfFile']['name'];
    $fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
    
    if ($fileExtension !== 'pdf') {
        handleError("Invalid file type. Only PDF files are accepted.");
    }
    
    // Create temp directory if it doesn't exist
    $tempDir = $config['upload']['temp_dir'];
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    }
    
    // Generate unique filename
    $uniqueFileName = uniqid() . '.pdf';
    $tempFilePath = $tempDir . '/' . $uniqueFileName;
    
    if (!move_uploaded_file($_FILES['pdfFile']['tmp_name'], $tempFilePath)) {
        handleError("Failed to save uploaded file", 500);
    }
    
    $fileUrl = './' . $tempFilePath;
    $inputText .= " Uploaded PDF: " . $fileUrl;
    $logger->info("PDF file saved: " . $fileUrl);
}

// Handle raw input
if (empty($inputText)) {
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $inputText = trim($rawInput);
        $logger->debug("Raw input received, length: " . strlen($inputText));
    }
}

// Validate input
if (empty($inputText)) {
    handleError("No input text or file provided");
}

// Security check - limit input length
if (strlen($inputText) > $config['security']['max_input_length']) {
    handleError("Input text too long. Maximum length: " . $config['security']['max_input_length']);
}

$logger->info("Processing input, length: " . strlen($inputText));

// Prepare API payload
$payload = [
    'contents' => [
        [
            'role' => 'user',
            'parts' => [
                ['text' => $inputText]
            ]
        ]
    ],
    'generationConfig' => $config['generation']
];

$jsonPayload = json_encode($payload);
$apiUrl = $config['api']['endpoint'] . '?key=' . $config['api']['key'];

$logger->debug("Calling Gemini API");

// Initialize cURL
$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $jsonPayload,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_SSL_VERIFYPEER => $config['security']['ssl_verify'],
    CURLOPT_TIMEOUT => 30
]);

// Execute API call
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Handle cURL errors
if (curl_errno($ch)) {
    $error = curl_error($ch);
    curl_close($ch);
    handleError("API connection failed: " . $error, 500);
}

curl_close($ch);

// Process response
if ($httpCode === 200) {
    $logger->info("API call successful");
    
    // Return raw response for client processing
    header('Content-Type: application/json');
    echo $response;
} else {
    $logger->error("API call failed with HTTP code: " . $httpCode);
    handleError("API request failed", $httpCode);
}
?>
