const fs = require('fs');
const path = require('path');

const adminDir = 'c:\\\\laragon\\\\www\\\\full-mark-academy\\\\resources\\\\views\\\\admin';

function fixRowHtml(html) {
    // Remove the old reset button block
    html = html.replace(/<div class="col-lg-12 d-flex justify-content-end mt-3">[\s\S]*?reset-filters-btn[\s\S]*?<\/button>\s*<\/div>/i, '');
    html = html.replace(/<div class="col-lg-2 col-md-2 col-sm-12 mb-3 d-flex align-items-end">[\s\S]*?reset-filters-btn[\s\S]*?<\/button>\s*<\/div>/i, '');
    
    // Change all col-lg-6 to col-lg-5
    html = html.replace(/col-lg-6/g, 'col-lg-5');
    html = html.replace(/col-md-6/g, 'col-md-5');

    // Make sure we have enough closing divs. The previous injection might have messed up the div closures.
    // The original structure was:
    // <div class="card-title w-100 mb-0 row">
    //    <div class="col-lg-6...">...</div>
    //    <div class="col-lg-6...">...</div>
    // </div>
    // But previously I replaced it blindly.
    // Let's just append the button before the final </div> of the row.
    return html;
}

function processFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;

    const regex = /(<div class="card-title w-100 mb-0 row">)([\s\S]*?)(<\/div>\s*<\/div>\s*<div class="card-body|<\/div>\s*<\/div>\s*<\/div>)/i;
    
    // A better approach is to use a simple string manipulation for the card-title row
    const startIdx = content.indexOf('<div class="card-title w-100 mb-0 row">');
    if (startIdx !== -1) {
        // find the end of this row by finding the card-body
        const bodyIdx = content.indexOf('<div class="card-body', startIdx);
        if (bodyIdx !== -1) {
            // Find the </div></div> right before card-body
            const endRowIdx = content.lastIndexOf('</div>', bodyIdx - 1);
            const endRowIdx2 = content.lastIndexOf('</div>', endRowIdx - 1);
            
            let rowHtml = content.substring(startIdx, endRowIdx2); // This includes everything inside the row
            
            rowHtml = fixRowHtml(rowHtml);

            // Add the new reset button
            const newResetHtml = `
                            <div class="col-lg-2 col-md-2 col-sm-12 mb-3 d-flex align-items-end">
                                <button type="button" class="btn btn-light-danger btn-sm reset-filters-btn w-100">
                                    <i class="bi bi-eraser fs-3"></i> {{ \\App\\Helpers\\translate('clear') ?? 'تصفية' }}
                                </button>
                            </div>\n`;

            const finalRow = rowHtml + newResetHtml;
            content = content.substring(0, startIdx) + finalRow + content.substring(endRowIdx2);
        }
    }

    if (content !== original) {
        fs.writeFileSync(filePath, content);
        console.log('Fixed filters UI in: ' + filePath);
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
