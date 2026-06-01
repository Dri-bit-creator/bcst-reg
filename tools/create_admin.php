<?php

/**
 * One-time setup: creates the default admin user if it does not exist.
 * Run: php tools/create_admin.php
 * Delete or restrict access to this file after use in production.
 */

require_once __DIR__ . '/../config/database.php';

$adminName = 'BCST Administrator';
$adminEmail = 'admin@bcst.edu.ph';
$adminPassword = 'bcstadmin';
$adminRole = 'admin';

$check = $dbhandle->prepare('SELECT id, role FROM users WHERE email = ? LIMIT 1');
$check->bind_param('s', $adminEmail);
$check->execute();
$check->bind_result($existingId, $existingRole);
$found = $check->fetch();
$check->close();

if ($found) {
    $hashed = password_hash($adminPassword, PASSWORD_DEFAULT);
    $update = $dbhandle->prepare('UPDATE users SET name = ?, password = ?, role = ? WHERE id = ?');
    $update->bind_param('sssi', $adminName, $hashed, $adminRole, $existingId);
    $update->execute();
    $update->close();

    echo "Admin account already existed (id {$existingId}). Password and role reset to admin.\n";
    echo "Email: {$adminEmail}\n";
    echo "Password: {$adminPassword}\n";
    exit(0);
}

$hashed = password_hash($adminPassword, PASSWORD_DEFAULT);
$insert = $dbhandle->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
$insert->bind_param('ssss', $adminName, $adminEmail, $hashed, $adminRole);

if ($insert->execute()) {
    echo "Admin account created successfully (id {$insert->insert_id}).\n";
    echo "Email: {$adminEmail}\n";
    echo "Password: {$adminPassword}\n";
} else {
    echo 'Failed to create admin: ' . $insert->error . "\n";
    exit(1);
}

$insert->close();
