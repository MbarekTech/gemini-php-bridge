#!/bin/bash
# Quick deployment script for web hosting
# Usage: curl -sSL https://yourdomain.com/deploy.sh | bash

echo "🚀 Deploying Gemini AI Integration..."

# Create directory structure
mkdir -p config client temp_files logs output unused

# Set permissions
chmod 755 temp_files logs output config
chmod 644 *.php *.ps1 2>/dev/null || true

# Copy configuration template if it doesn't exist
if [ ! -f "config/config.php" ] && [ -f "config/config.example.php" ]; then
    cp "config/config.example.php" "config/config.php"
    echo "✅ Created config/config.php from template"
    echo "⚠️  Remember to update your API key in config/config.php"
fi

# Create basic .htaccess if it doesn't exist
if [ ! -f ".htaccess" ]; then
    cat > .htaccess << 'EOF'
# Basic security for Gemini AI Integration
<Files "config.php">
    Require all denied
</Files>
<Files "*.log">
    Require all denied
</Files>
<Files "*.ps1">
    Header set Content-Type "text/plain; charset=utf-8"
</Files>
Options -Indexes
EOF
    echo "✅ Created basic .htaccess file"
fi

echo "🎉 Deployment complete!"
echo ""
echo "Next steps:"
echo "1. Edit config/config.php with your Google API key"
echo "2. Test: curl -X POST $(pwd)/api.php -d 'test=true'"
echo "3. Remote usage: irm https://yourdomain.com/setup.ps1 | iex"
