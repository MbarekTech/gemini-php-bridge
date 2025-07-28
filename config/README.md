# Gemini AI Integration - Configuration

## Setup Instructions

1. Copy `config.example.php` to `config.php`
2. Update the API key with your actual Google API key
3. Adjust other settings as needed
4. Ensure the `temp_files` and `logs` directories are writable

## Security Notes

- Never commit `config.php` to version control
- Use environment variables in production
- Enable SSL verification for production use
- Implement proper rate limiting
