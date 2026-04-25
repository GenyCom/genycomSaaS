const fs = require('fs');
const path = require('path');

function walk(dir) {
    let results = [];
    const list = fs.readdirSync(dir);
    list.forEach(function(file) {
        file = path.join(dir, file);
        const stat = fs.statSync(file);
        if (stat && stat.isDirectory()) {
            results = results.concat(walk(file));
        } else if (file.endsWith('.vue')) {
            results.push(file);
        }
    });
    return results;
}

const vueFiles = walk(path.join(__dirname, 'src', 'views'));

vueFiles.forEach(file => {
    let content = fs.readFileSync(file, 'utf8');
    
    // Regex to match "catch {" followed by anything, until "}\n})"
    // Notice the structure in Vue components:
    // } catch {
    //   ... demo data
    // }
    // })
    const catchRegex1 = /\} catch \s*\{\s*([\s\S]*?)\s*\}\n\}\)/g;
    const catchRegex2 = /\} catch \s*\(\s*e?r?r?(or)?\s*\)\s*\{\s*([\s\S]*?)\s*\}\n\}\)/g;

    let modified = false;

    if(catchRegex1.test(content) || catchRegex2.test(content)) {
        content = content.replace(/\} catch[^{]*\{\s*[\s\S]*?\s*\}\n\}\)/g, (match) => {
            // Check if it looks like demo data assignment
            if(match.includes('.value = [') || match.includes('.value = {') || match.match(/\[\s*\{\s*id:/) || match.includes('loadDemoData()')) {
                return `} catch (error) {\n    console.error('Erreur:', error)\n  }\n})`;
            }
            return match; // return unchanged if it's a real error handler
        });
        modified = true;
    }

    if (modified) {
        fs.writeFileSync(file, content, 'utf8');
        console.log(`Updated ${file}`);
    }
});
