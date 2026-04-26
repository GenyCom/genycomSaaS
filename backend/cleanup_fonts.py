import os
import re

directory = r'c:\web\genycom\frontend\src\views'

# Pattern to find the font-family declaration in the specific context
pattern = re.compile(r"font-family:\s*'Inter',\s*(?:sans-serif|system-ui,\s*sans-serif|[^;]+);")

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    new_content = pattern.sub('', content)
    
    # Also cleanup any trailing space/semicolon if necessary, 
    # but the regex above should match the whole property including semicolon.
    
    if content != new_content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        return True
    return False

count = 0
for root, dirs, files in os.walk(directory):
    for file in files:
        if file.endswith('.vue'):
            if process_file(os.path.join(root, file)):
                count += 1

print(f"Cleaned up font-family in {count} files.")
