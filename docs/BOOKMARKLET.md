# Browser Bookmarklet

Create a browser bookmark to process selected text with AI.

## Bookmarklet Code
```javascript
javascript:(function(){
    var text = window.getSelection().toString().trim() || prompt('Enter text for AI:');
    if(text) {
        var cmd = `irm 'https://yourdomain.com/simple-client.php?text=${encodeURIComponent(text)}' | iex`;
        if(navigator.clipboard) {
            navigator.clipboard.writeText(cmd).then(() => {
                alert('PowerShell command copied to clipboard!');
            });
        } else {
            prompt('Copy this command:', cmd);
        }
    }
})();
```

## Setup
1. Copy the JavaScript code above
2. Create a new bookmark in your browser
3. Replace "yourdomain.com" with your domain
4. Paste the code as the URL
5. Name it "AI Process"

## Usage
1. Select text on any webpage
2. Click the bookmark
3. The PowerShell command is copied to clipboard
4. Paste and run in PowerShell terminal
2. **Create a new bookmark** in your browser
3. **Set the name** to "AI Process Text" or similar
4. **Set the URL** to the JavaScript code you copied
5. **Update `yourdomain.com`** with your actual domain

### **How to Use:**

1. **Select text** on any webpage, or
2. **Click the bookmark** (it will prompt for text input)
3. **Copy and run** the generated PowerShell command
4. **Get AI-processed results** instantly!

### **Example Usage:**

**On a Wikipedia article:**
1. Select interesting text about a topic
2. Click your "AI Process Text" bookmark
3. Run the PowerShell command
4. Get AI analysis, summary, or enhancement of that text

**For coding:**
1. Select code from Stack Overflow or documentation
2. Click the bookmark
3. Get AI explanations, improvements, or translations

### **Advanced Bookmarklet (Direct Execution):**

For advanced users who want immediate execution without clipboard:

```javascript
javascript:(function(){
    var text = window.getSelection().toString().trim() || prompt('Enter text:');
    if(text) {
        var newWindow = window.open('', '_blank', 'width=800,height=600');
        newWindow.document.write(`
            <html><head><title>AI Processing...</title></head>
            <body style="font-family:monospace;padding:20px;">
                <h3>🤖 Processing with AI...</h3>
                <p><strong>Input:</strong> ${text.substring(0,200)}${text.length > 200 ? '...' : ''}</p>
                <p><strong>PowerShell Command:</strong></p>
                <textarea style="width:100%;height:100px;font-family:monospace;">irm https://yourdomain.com/client/gemini-client.ps1 | iex -InputText "${text.replace(/"/g, '\\"').replace(/\n/g, '\\n')}"</textarea>
                <p><em>Copy the command above and run it in PowerShell to get AI results.</em></p>
            </body></html>
        `);
    }
})();
```

### **Mobile-Friendly Version:**

For mobile browsers, create a simpler version:

```javascript
javascript:prompt('Copy and run in PowerShell:', 'irm https://yourdomain.com/client/gemini-client.ps1 | iex -InputText "' + (window.getSelection().toString() || prompt('Enter text:') || '').replace(/"/g, '\\"') + '"');
```

### **WordPress/CMS Integration:**

You can also embed this functionality directly in websites:

```html
<button onclick="
    var text = window.getSelection().toString() || prompt('Enter text:');
    if(text) {
        var cmd = 'irm https://yourdomain.com/client/gemini-client.ps1 | iex -InputText \"' + text.replace(/\"/g, '\\\"') + '\"';
        navigator.clipboard.writeText(cmd).then(() => alert('Command copied to clipboard!'));
    }
">🤖 Process with AI</button>
```

This makes your Gemini AI integration accessible from any webpage with a single click!
