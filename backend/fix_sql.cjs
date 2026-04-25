const fs = require('fs');
const path = require('path');

const dir = path.join(__dirname, 'database', 'schema');
const files = fs.readdirSync(dir).filter(f => /^00[2-7].*\.sql$/.test(f)).sort();

let totalFixes = 0;

files.forEach(file => {
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');
    const before = content;
    
    // 1. Remove lines with FOREIGN KEY ... REFERENCES `users`(...)
    //    Handle both cases: line ending with comma, or line being last before )
    content = content.replace(/,?\s*\n\s*FOREIGN KEY\s*\([^)]+\)\s*REFERENCES\s*`users`\s*\([^)]+\)[^,\n]*/g, '');
    
    // 2. Remove lines with REFERENCES `tenants`(...) ON DELETE CASCADE (standalone orphans)
    content = content.replace(/,?\s*\n\s*REFERENCES\s*`tenants`\s*\([^)]+\)[^\n]*/g, '');
    
    // 3. Fix trailing commas before ) ENGINE  (e.g. "KEY ...,\n) ENGINE" -> "KEY ...\n) ENGINE")
    content = content.replace(/,(\s*\n\s*\)\s*ENGINE)/g, '$1');
    
    if (content !== before) {
        fs.writeFileSync(filePath, content, 'utf8');
        totalFixes++;
        console.log('Fixed:', file);
    } else {
        console.log('OK:', file);
    }
});

// Verification pass
console.log('\n--- Verification ---');
let errors = 0;

files.forEach(file => {
    const filePath = path.join(dir, file);
    const content = fs.readFileSync(filePath, 'utf8');
    
    // Check for remaining REFERENCES `users`
    const userRefs = (content.match(/REFERENCES\s*`users`/g) || []).length;
    if (userRefs > 0) {
        console.log(`ERROR: ${file} still has ${userRefs} REFERENCES users`);
        errors++;
    }
    
    // Check for remaining REFERENCES `tenants`  
    const tenantRefs = (content.match(/REFERENCES\s*`tenants`/g) || []).length;
    if (tenantRefs > 0) {
        console.log(`ERROR: ${file} still has ${tenantRefs} REFERENCES tenants`);
        errors++;
    }
    
    // Check for trailing commas before ) ENGINE
    const trailingCommas = (content.match(/,\s*\)\s*ENGINE/g) || []).length;
    if (trailingCommas > 0) {
        console.log(`ERROR: ${file} still has ${trailingCommas} trailing comma(s)`);
        errors++;
    }
});

if (errors === 0) {
    console.log(`All clean! ${totalFixes} file(s) fixed.`);
} else {
    console.log(`${errors} error(s) found!`);
}
