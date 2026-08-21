<?php
// ============================================================
// Pet Care System - Admin Seeder
// University Web Application Development Project
// Member 1: Core Auth
//
// PURPOSE: Creates the demo administrator account with a
//          securely hashed password.
//
// COURSEWORK / DEMO CREDENTIALS ONLY - DO NOT USE IN PRODUCTION
//   Username : admin
//   Password : admin123
//
// Run once via browser or CLI:
//   php -S localhost:8000
//   Then visit: http://localhost:8000/database/seed_admin.php
// ============================================================

// Resolve the path regardless of how / from where the script is called
require_once __DIR__ . '/../config/db.php';

// Demo credentials (coursework only - never use plain text in real systems)
$username      = 'admin';
$plain_password = 'admin123';

// Hash the password using PHP's secure default algorithm (bcrypt)
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

// Check whether the admin already exists to avoid duplicates
$stmt = $conn->prepare('SELECT id FROM admins WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Admin already exists ΓÇö update the password hash in case it changed
    $stmt->close();
    $update = $conn->prepare('UPDATE admins SET password = ? WHERE username = ?');
    $update->bind_param('ss', $hashed_password, $username);
    if ($update->execute()) {
        echo '<p style="color:green;">Γ£ö Admin account already exists. Password hash updated successfully.</p>';
    } else {
        echo '<p style="color:red;">Γ£ÿ Failed to update admin: ' . htmlspecialchars($conn->error) . '</p>';
    }
    $update->close();
} else {
    // Insert a new admin record
    $stmt->close();
    $insert = $conn->prepare('INSERT INTO admins (username, password) VALUES (?, ?)');
    $insert->bind_param('ss', $username, $hashed_password);
    if ($insert->execute()) {
        echo '<p style="color:green;">Γ£ö Admin account created successfully.</p>';
    } else {
        echo '<p style="color:red;">Γ£ÿ Failed to create admin: ' . htmlspecialchars($conn->error) . '</p>';
    }
    $insert->close();
}

echo '<p><strong>Username:</strong> ' . htmlspecialchars($username) . '</p>';
echo '<p><em>Password is stored as a bcrypt hash ΓÇö never in plain text.</em></p>';

$conn->close();
