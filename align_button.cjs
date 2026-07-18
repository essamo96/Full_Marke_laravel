const fs = require('fs');
const path = require('path');

const adminDir = 'c:\\\\laragon\\\\www\\\\full-mark-academy\\\\resources\\\\views\\\\admin';

function processFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;

    // We want to replace the d-flex align-items-end on the button wrapper and insert the hidden label
    const regex = /<div class="col-lg-2 col-md-2 col-sm-12 mb-3 d-flex align-items-end">\s*<button type="button" class="btn btn-light-danger h-40px fs-7 fw-bold reset-filters-btn w-100">\s*<i class="bi bi-eraser fs-3"><\/i>\s*@lang\('app\.clear'\)\s*<\/button>\s*<\/div>/i;

    const replacement = `<div class="col-lg-2 col-md-2 col-sm-12 mb-3">
                                <label class="form-label d-none d-md-block text-white" style="user-select: none;">&nbsp;</label>
                                <button type="button" class="btn btn-light-danger h-40px fs-7 fw-bold reset-filters-btn w-100">
                                    <i class="bi bi-eraser fs-3"></i> @lang('app.clear')
                                </button>
                            </div>`;

    if (regex.test(content)) {
        content = content.replace(regex, replacement);
    } else {
        // Fallback replacement if regex spacing is slightly different
        // Let's just do a simpler replace
        let startMatch = content.indexOf('<div class="col-lg-2 col-md-2 col-sm-12 mb-3 d-flex align-items-end">');
        if (startMatch !== -1) {
            content = content.replace('<div class="col-lg-2 col-md-2 col-sm-12 mb-3 d-flex align-items-end">', 
                '<div class="col-lg-2 col-md-2 col-sm-12 mb-3">\n                                <label class="form-label d-none d-md-block text-white" style="user-select: none;">&nbsp;</label>');
        }
    }

    if (content !== original) {
        fs.writeFileSync(filePath, content);
        console.log('Fixed button alignment in: ' + filePath);
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
