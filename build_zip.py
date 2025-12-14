import os
import zipfile
import shutil

def create_plugin_zip(source_dir, output_filename):
    # Name of the root folder inside the zip
    plugin_slug = "easy-liveblogs"
    
    # List of items to exclude
    excludes = {
        'node_modules', 
        '.git', 
        '.github', 
        '.vscode', 
        '.idea',
        '.gitignore',
        '.gitattributes',
        'package-lock.json',
        'build_zip.py',
        output_filename # Don't zip the zip itself if it exists
    }

    # Create the zip file
    with zipfile.ZipFile(output_filename, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for root, dirs, files in os.walk(source_dir):
            # Modify dirs in-place to skip excluded directories
            dirs[:] = [d for d in dirs if d not in excludes]
            
            for file in files:
                if file in excludes:
                    continue
                
                file_path = os.path.join(root, file)
                # Create a relative path for the archive, enabling the standard folder structure "plugin-folder/files"
                rel_path = os.path.relpath(file_path, source_dir)
                archive_path = os.path.join(plugin_slug, rel_path)
                
                try:
                    zipf.write(file_path, archive_path)
                except Exception as e:
                    print(f"Skipping {file_path}: {e}")

    print(f"\nSuccess! Created {output_filename}")

if __name__ == "__main__":
    # Current directory assumed to be the project root
    project_root = os.getcwd()
    output_zip = "easy-liveblogs.zip"
    
    if os.path.exists(output_zip):
        os.remove(output_zip)
        
    create_plugin_zip(project_root, output_zip)
