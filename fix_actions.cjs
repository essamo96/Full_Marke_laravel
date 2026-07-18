const fs = require('fs');
const path = require('path');

const adminDir = 'c:\\\\laragon\\\\www\\\\full-mark-academy\\\\resources\\\\views\\\\admin';

function processActionsFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;

    // Check if it already has a top-level d-flex div
    if (content.match(/<div[^>]*class=["'][^"']*d-flex[^"']*["']/i)) {
        // Find that div and replace its justify-content class
        content = content.replace(/(<div[^>]*class=["'][^"']*d-flex[^"']*)justify-content-(?:end|center|start)([^"']*["'])/i, "$1justify-content-start gap-2$2");
        // if it didn't have justify-content, add it
        if (!content.includes('justify-content-start')) {
            content = content.replace(/(<div[^>]*class=["'][^"']*d-flex)([^"']*["'])/i, "$1 justify-content-start gap-2$2");
        }
    } else {
        // Wrap the entire content in a d-flex container
        content = `<div class="d-flex justify-content-start gap-2 flex-shrink-0">\n${content}\n</div>`;
    }

    if (content !== original) {
        fs.writeFileSync(filePath, content);
        console.log('Fixed actions: ' + filePath);
    }
}

function traverse(dir) {
    const files = fs.readdirSync(dir);
    for (const file of files) {
        const fullPath = path.join(dir, file);
        const stat = fs.statSync(fullPath);
        if (stat.isDirectory()) {
            traverse(fullPath);
        } else if (file === 'actions.blade.php' && fullPath.includes('parts')) {
            try {
                processActionsFile(fullPath);
            } catch(e) {
                console.log('Error processing ' + fullPath + ': ' + e);
            }
        }
    }
}

traverse(adminDir);
console.log("Done");
