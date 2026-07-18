const fs = require('fs');
const path = require('path');

const adminDir = 'c:\\\\laragon\\\\www\\\\full-mark-academy\\\\resources\\\\views\\\\admin';

function processFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;

    // Check if card-title row exists
    const cardTitleRegex = /<div class="card-title w-100 mb-0 row">([\s\S]*?)(<\/div>\s*<\/div>\s*<div class="card-body|<\/div>\s*<\/div>\s*<\/div>)/i;
    const match = content.match(cardTitleRegex);

    if (match) {
        let titleInner = match[1];
        
        // If it doesn't already have the reset button
        if (!titleInner.includes('reset-filters-btn')) {
            const resetHtml = `
                            <div class="col-lg-12 d-flex justify-content-end mt-3">
                                <button type="button" class="btn btn-light-danger btn-sm reset-filters-btn">
                                    <i class="bi bi-eraser fs-3"></i> {{ \\App\\Helpers\\translate('reset_filters') ?? 'تصفية الفلاتر' }}
                                </button>
                            </div>`;
            
            // Append resetHtml before the closing of the row div
            const newTitleInner = titleInner + resetHtml;
            content = content.replace(match[1], newTitleInner);
        }
    }

    if (content !== original) {
        fs.writeFileSync(filePath, content);
        console.log('Added reset button: ' + filePath);
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
