BCST — Static Firebase Port
===========================

This repository is a static client-side port of the original PHP-based BCST enrollment app. It uses Firebase Authentication and Firestore for user accounts and enrollment data.

Quick setup
-----------

1. Create a Firebase project at https://console.firebase.google.com/
2. Enable Email/Password sign-in in Authentication → Sign-in methods.
3. Create a Firestore database (start in test mode while developing).
4. Copy your Firebase SDK config into `assets/js/firebase-config.js`.

Local preview
-------------

Serve the folder with a static server (recommended). Example:

PowerShell:
```powershell
npx http-server -c-1 .
```

Security rules
--------------
See `firestore.rules` for recommended rules. Update them in the Firebase console's Rules tab and adjust as needed.

Admin setup
-----------
Create an admin user by creating an account and then setting the document in `users/{uid}` to include `role: "admin"`. You can use `tools/create_admin.html` to create an admin account.

Next steps
----------
- Review and tighten Firestore rules before going to production.
- Add input validation and file uploads (if needed).
- Remove `tools/create_admin.html` after creating the first admin.
