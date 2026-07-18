const fs = require('fs');
const path = require('path');

const adminDir = 'c:\\\\laragon\\\\www\\\\full-mark-academy\\\\resources\\\\views\\\\admin';

function processFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;

    // 1. Remove text-end and text-center from <th>
    content = content.replace(/<th([^>]*)class=["']([^"']*)["']([^>]*)>/gi, (match, before, classes, after) => {
        let newClasses = classes.replace(/\btext-end\b/g, '').replace(/\btext-center\b/g, '').trim();
        if (newClasses === '') {
            return `<th${before}${after}>`;
        }
        return `<th${before}class="${newClasses}"${after}>`;
    });

    // Also ensure <thead><tr> has text-start instead of text-center
    content = content.replace(/<tr([^>]*)class=["']([^"']*)["']([^>]*)>/gi, (match, before, classes, after) => {
        // Only if it's inside thead (we can heuristically check if it has fw-bold)
        if (classes.includes('fw-bold') || classes.includes('fw-semibold')) {
            let newClasses = classes.replace(/\btext-center\b/g, '').replace(/\btext-end\b/g, '').trim();
            if (!newClasses.includes('text-start')) {
                newClasses += ' text-start';
            }
            return `<tr${before}class="${newClasses}"${after}>`;
        }
        return match;
    });

    // 2. Update JavaScript columns array to use text-start
    const columnsRegex = /var\s+columns\s*=\s*\[([\s\S]*?)\];/;
    const match = content.match(columnsRegex);
    if (match) {
        let columnsBlock = match[1];
        
        // Let's replace className: 'text-center' or 'text-end' with 'text-start'
        columnsBlock = columnsBlock.replace(/className\s*:\s*['"]text-center['"]/g, "className: 'text-start'");
        columnsBlock = columnsBlock.replace(/className\s*:\s*['"]text-end['"]/g, "className: 'text-start'");

        // To add className: 'text-start' to those without any className
        // A column definition is an object { ... }
        // We can parse the block into individual objects or use regex
        
        // This regex tries to find each object { ... } in the columns array
        columnsBlock = columnsBlock.replace(/\{([^{}]*)\}/g, (objMatch, inner) => {
            if (!inner.includes('className')) {
                return `{${inner}, className: 'text-start' }`;
            }
            return objMatch;
        });

        content = content.replace(match[1], columnsBlock);
    }

    if (content !== original) {
        fs.writeFileSync(filePath, content);
        console.log('Aligned: ' + filePath);
    }
}

function traverse(dir) {
    const files = fs.readdirSync(dir);
    for (const file of files) {
        const fullPath = path.join(dir, file);
        const stat = fs.statSync(fullPath);
        if (stat.isDirectory()) {
            traverse(fullPath);
        } else if (file === 'view.blade.php' || file === 'index.blade.php') {
            try {
                processFile(fullPath);
            } catch(e) {
                console.log('Error processing ' + fullPath + ': ' + e);
            }
        }
    }
}

traverse(adminDir);
console.log("Done");
