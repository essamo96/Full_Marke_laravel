const fs = require('fs');
const path = require('path');

const adminDir = 'c:\\\\laragon\\\\www\\\\full-mark-academy\\\\resources\\\\views\\\\admin';

function processFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;

    // 1. Table class
    content = content.replace(/<table\s+(?:[^>]*?\s+)?id=[\"']([^\"']+)[\"']\s+(?:[^>]*?\s+)?class=[\"']([^\"']+)[\"'][^>]*>/i, (match, id, cls) => {
        return `<table id="${id}" class="table table-striped table-row-bordered gy-5 gs-7">`;
    });
    content = content.replace(/<table\s+(?:[^>]*?\s+)?class=[\"']([^\"']+)[\"']\s+(?:[^>]*?\s+)?id=[\"']([^\"']+)[\"'][^>]*>/i, (match, cls, id) => {
        return `<table id="${id}" class="table table-striped table-row-bordered gy-5 gs-7">`;
    });
    if (!content.match(/<table[^>]+id=/)) {
        content = content.replace(/<table\s+(?:[^>]*?\s+)?class=[\"']([^\"']+)[\"'][^>]*>/i, `<table class="table table-striped table-row-bordered gy-5 gs-7">`);
    }

    // 2. Table Header TR class
    content = content.replace(/<thead>\s*<tr[^>]*>/i, `<thead>\n                                <tr class="fw-semibold fs-6 text-gray-800 fw-bold text-start">`);

    // 3. Status filter injection
    const hasStatusColumn = /<th[^>]*>.*?status.*?<\/th>/i.test(content) || /<th[^>]*>.*?الحالة.*?<\/th>/i.test(content) || /data:\s*['"]status['"]/i.test(content);
    const hasStatusFilterHtml = /<select[^>]+id=[\"']status[\"']/i.test(content);
    
    if (hasStatusColumn && !hasStatusFilterHtml) {
        // Find general search div and insert after it
        const cardTitleMatch = content.match(/<div class="card-title w-100 mb-0 row">([\s\S]*?)<\/div>\s*<\/div>\s*<div class="card-body/i);
        if (cardTitleMatch) {
            let titleInner = cardTitleMatch[1];
            // If it doesn't already have status
            if (!titleInner.includes('id="status"')) {
                // Adjust col-lg sizes for generalSearch to make room
                titleInner = titleInner.replace(/col-lg-\d+/g, 'col-lg-6');
                titleInner = titleInner.replace(/col-md-\d+/g, 'col-md-6');
                
                const statusHtml = `
                            <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
                                <label for="status" class="form-label"> {{ \\App\\Helpers\\translate('status') ?? 'الحالة' }}</label>
                                <select id="status" name="status" class="form-select form-select-solid" data-control="select2" data-hide-search="true" data-placeholder="الكل">
                                    <option value="">الكل</option>
                                    <option value="1">مفعل</option>
                                    <option value="0">معطل</option>
                                </select>
                            </div>`;
                const newTitleInner = titleInner + statusHtml;
                content = content.replace(cardTitleMatch[1], newTitleInner);
            }
        }
    }
    
    // Add status to filterFields if it exists in HTML but not in JS
    if (/<select[^>]+id=[\"']status[\"']/i.test(content)) {
        const filterFieldsMatch = content.match(/var filterFields = \[([\s\S]*?)\];/);
        if (filterFieldsMatch) {
            let fields = filterFieldsMatch[1];
            if (!fields.includes("'#status'")) {
                fields = fields.trim();
                if (fields.length > 0 && !fields.endsWith(',')) {
                    fields += ',';
                }
                fields += "\n            '#status'";
                content = content.replace(filterFieldsMatch[1], "\n" + fields + "\n        ");
            }
        }
    }

    // 4. Update general search UI
    // Ensure general search has the correct icon and class
    content = content.replace(/<input type="text" id="generalSearch"[^>]*placeholder="([^"]+)"[^>]*>/, (match, placeholder) => {
        if (!match.includes('form-control-solid')) {
            return `<input type="text" id="generalSearch" class="form-control form-control-solid ps-13 generalSearch" placeholder="${placeholder}" />`;
        }
        return match;
    });

    if (content !== original) {
        fs.writeFileSync(filePath, content);
        console.log('Updated: ' + filePath);
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
