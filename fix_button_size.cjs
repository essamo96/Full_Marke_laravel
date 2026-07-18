const fs = require('fs');
const path = require('path');

const adminDir = 'c:\\\\laragon\\\\www\\\\full-mark-academy\\\\resources\\\\views\\\\admin';

function processFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;

    // 1. Fix the translation
    content = content.replace(/\{\{\s*\\App\\Helpers\\translate\('clear'\)\s*\?\?\s*'تصفية'\s*\}\}/g, "@lang('app.clear')");

    // 2. Fix the button size and alignment classes
    // Currently it's: btn btn-light-danger btn-sm reset-filters-btn w-100
    // Change to: btn btn-light-danger h-40px fs-7 fw-bold reset-filters-btn w-100
    content = content.replace(/btn btn-light-danger btn-sm reset-filters-btn w-100/g, "btn btn-light-danger h-40px fs-7 fw-bold reset-filters-btn w-100");

    // Just in case it was written slightly differently
    content = content.replace(/btn-sm reset-filters-btn/g, "h-40px fs-7 fw-bold reset-filters-btn");

    if (content !== original) {
        fs.writeFileSync(filePath, content);
        console.log('Fixed button size and translation in: ' + filePath);
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
