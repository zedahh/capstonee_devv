<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require '../../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_name'])) {
        $new_name = trim($_POST['full_name'] ?? '');
        if ($new_name === '') {
            $error = 'Name cannot be empty.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ? WHERE user_id = ?");
            $stmt->execute([$new_name, $_SESSION['user_id']]);
            $_SESSION['full_name'] = $new_name;

            $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'UPDATE', 'users', ?, 'Updated own display name')");
            $log->execute([$_SESSION['user_id'], $_SESSION['user_id']]);

            $success = 'Name updated successfully.';
        }
    } elseif (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($current_password, $user['password_hash'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new_password) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New password and confirmation do not match.';
        } else {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
            $stmt->execute([$new_hash, $_SESSION['user_id']]);

            $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'UPDATE', 'users', ?, 'Changed own password')");
            $log->execute([$_SESSION['user_id'], $_SESSION['user_id']]);

            $success = 'Password updated successfully.';
        }
    }
}

require 'my_account_view.php';