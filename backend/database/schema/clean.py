import os, re
d = r'c:\web\genycom\backend\database\schema'
for f in os.listdir(d):
    if f.startswith('00') and f[2] in '234567' and f.endswith('.sql'):
        p = os.path.join(d, f)
        
        with open(p, 'r', encoding='utf-8') as file:
            content = file.read()
            
        content = re.sub(r'(?i)^\s*`?tenant_id`?\s+BIGINT.*?,\r?\n?', '', content, flags=re.MULTILINE)
        content = re.sub(r'(?i)^\s*FOREIGN KEY\s*\(`?tenant_id`?\).*?,\r?\n?', '', content, flags=re.MULTILINE)
        content = re.sub(r'(?i)^\s*FOREIGN KEY\s*\(`?tenant_id`?\).*?\r?\n?', '', content, flags=re.MULTILINE)
        
        content = content.replace(", `tenant_id`", "")
        content = content.replace("`tenant_id`, ", "")
        content = content.replace("(`tenant_id`)", "")
        
        content = re.sub(r'(?i)^\s*(?:UNIQUE KEY|KEY|INDEX)\s+`?\w+`?\s*\(\s*\),?\r?\n?', '', content, flags=re.MULTILINE)
        
        with open(p, 'w', encoding='utf-8') as file:
            file.write(content)
        print('Fixed ' + f)
