<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
if ($_SESSION['role'] !== 'administrator') {
    die('Access denied. This page is for administrators only.');
}
require '../../config/database.php';

$error = '';
$success = '';

// Reset a user's password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $target_user_id = (int) $_POST['user_id'];
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($new_password) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirmation do not match.';
    } else {
        $stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
        $stmt->execute([$target_user_id]);
        $target_user = $stmt->fetch(PDO::FETCH_ASSOC);

        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $upd = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $upd->execute([$new_hash, $target_user_id]);

        $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'UPDATE', 'users', ?, ?)");
        $log->execute([$_SESSION['user_id'], $target_user_id, "Reset password for user: " . ($target_user['username'] ?? 'unknown')]);

        $success = 'Password reset successfully for ' . htmlspecialchars($target_user['username'] ?? 'user') . '.';
    }
}

// Toggle active/inactive
if (isset($_GET['toggle'])) {
    $target_user_id = (int) $_GET['toggle'];
    $stmt = $pdo->prepare("SELECT username, is_active FROM users WHERE user_id = ?");
    $stmt->execute([$target_user_id]);
    $target_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($target_user) {
        $new_status = $target_user['is_active'] ? 0 : 1;
        $upd = $pdo->prepare("UPDATE users SET is_active = ? WHERE user_id = ?");
        $upd->execute([$new_status, $target_user_id]);

        $action_word = $new_status ? 'Reactivated' : 'Deactivated';
        $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'UPDATE', 'users', ?, ?)");
        $log->execute([$_SESSION['user_id'], $target_user_id, "$action_word user: " . $target_user['username']]);
    }

    header('Location: user_management.php');
    exit;
}

// Add new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $new_username = trim($_POST['username'] ?? '');
    $new_full_name = trim($_POST['full_name'] ?? '');
    $new_role = $_POST['role'] ?? '';
    $new_password = $_POST['new_user_password'] ?? '';

    if ($new_username === '' || $new_full_name === '' || $new_role === '' || strlen($new_password) < 8) {
        $error = 'Please fill in all fields; password must be at least 8 characters.';
    } else {
        $check = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $check->execute([$new_username]);
        if ($check->fetch()) {
            $error = 'That username is already taken.';
        } else {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO users (username, password_hash, full_name, role, is_active) VALUES (?, ?, ?, ?, 1)");
            $ins->execute([$new_username, $hash, $new_full_name, $new_role]);

            $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'INSERT', 'users', ?, ?)");
            $log->execute([$_SESSION['user_id'], $pdo->lastInsertId(), "Added new user: $new_username ($new_role)"]);

            $success = 'User added successfully.';
        }
    }
}

$users = $pdo->query("SELECT user_id, username, full_name, role, is_active FROM users ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);

require 'user_management_view.php';