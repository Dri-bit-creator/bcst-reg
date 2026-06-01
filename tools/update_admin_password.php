<?php

require_once __DIR__ . '/../config/database.php';

$newPassword = 'bcstadmin';
$hashed = password_hash($newPassword, PASSWORD_DEFAULT);

$stmt = $dbhandle->prepare("UPDATE users SET password = ? WHERE email = ? LIMIT 1");
if (!$stmt) {
    die('Prepare failed: ' . $dbhandle->error . PHP_EOL);
}

$adminEmail = 'admin@bcst.edu.ph';
$stmt->bind_param('ss', $hashed, $adminEmail);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Admin password updated successfully." . PHP_EOL;
} else {
    echo "No rows updated. Check that the admin user exists in the users table." . PHP_EOL;
}

$stmt->close();
