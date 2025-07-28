<?php
/**
 * Configuration file for Gemini AI API Integration
 * Copy this file to config.php and update with your actual values
 */

return [
    // Google Gemini AI API Configuration
    'api' => [
        'key' => 'YOUR_GOOGLE_API_KEY_HERE',
        'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent',
        'model' => 'gemini-2.0-flash-exp'
    ],
    
    // Generation Configuration
    'generation' => [
        'temperature' => 1,
        'topK' => 40,
        'topP' => 0.95,
        'maxOutputTokens' => 8192,
        'responseMimeType' => 'text/plain'
    ],
    
    // File Upload Configuration
    'upload' => [
        'temp_dir' => 'temp_files',
        'max_file_size' => '10M',
        'allowed_extensions' => ['txt', 'pdf', 'docx', 'md'],
        'cleanup_after_hours' => 24
    ],
    
    // Logging Configuration
    'logging' => [
        'enabled' => true,
        'level' => 'DEBUG', // DEBUG, INFO, WARNING, ERROR
        'file' => 'logs/gemini_api.log',
        'max_size' => '50M'
    ],
    
    // Security Configuration
    'security' => [
        'ssl_verify' => true,
        'rate_limit_requests_per_minute' => 60,
        'max_input_length' => 100000
    ],
    
    // Application Configuration
    'app' => [
        'name' => 'Gemini AI Integration',
        'version' => '2.0',
        'debug' => false,
        'timezone' => 'UTC'
    ],
    
    // Client Configuration
    'client' => [
        'default_output_dir' => 'output',
        'response_format' => 'json', // json, text, both
        'auto_save_responses' => true
    ]
];
