Firebase vendor ZIP — README

Contents:
- fetch-firebase-vendor.ps1   (PowerShell downloader)
- fetch-firebase-vendor.sh    (Bash downloader)

Purpose:
These scripts attempt to download Firebase SDK files (firebase-app.js, firebase-auth.js, firebase-firestore.js)
into assets/js/vendor/. They try multiple CDN mirrors and versions and validate downloaded files.

How to use (PowerShell):
1. Open PowerShell and change directory to your project root (example):
   Set-Location C:\xampp\htdocs\BCSTR
2. Run the script:
   .\assets\tools\fetch-firebase-vendor.ps1

How to use (bash):
1. Open a bash shell (Git Bash, WSL, macOS, Linux) and change to project root:
   cd /c/xampp/htdocs/BCSTR
2. Make script executable and run:
   chmod +x assets/tools/fetch-firebase-vendor.sh
   ./assets/tools/fetch-firebase-vendor.sh

If automatic downloads fail:
- Open the CDN URLs listed in the scripts in your browser and save each file manually to assets/js/vendor/.
  Examples:
    https://cdn.jsdelivr.net/npm/firebase@9.23.0/firebase-app.js
    https://cdn.jsdelivr.net/npm/firebase@9.23.0/firebase-auth.js
    https://cdn.jsdelivr.net/npm/firebase@9.23.0/firebase-firestore.js

After vendor files are present:
- Reload your app (Ctrl+F5) and check DevTools Console for "Loaded Firebase SDK from local vendor" and
  "Firebase initialized for project: bcstr-6c048".

Contact me with any errors and paste the console output; I will help further.
