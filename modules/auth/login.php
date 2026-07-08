<?php
session_start();
require '../../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        // Check if this username is currently locked out
        $lockStmt = $pdo->prepare("SELECT * FROM login_attempts WHERE username = ?");
        $lockStmt->execute([$username]);
        $attempt = $lockStmt->fetch(PDO::FETCH_ASSOC);

        if ($attempt && $attempt['locked_until'] && strtotime($attempt['locked_until']) > time()) {
            $minutes_left = ceil((strtotime($attempt['locked_until']) - time()) / 60);
            $error = "Too many failed attempts. Please try again in $minutes_left minute(s).";
        } else {
            $stmt = $pdo->prepare("SELECT user_id, username, password_hash, full_name, role, is_active FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['is_active'] && password_verify($password, $user['password_hash'])) {
                // Success - clear any failed attempt record
                $pdo->prepare("DELETE FROM login_attempts WHERE username = ?")->execute([$username]);

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                header('Location: ../dashboard/dashboard.php');
                exit;
            } else {
                // Failed - increment or create the attempt record
                if ($attempt) {
                    $new_count = $attempt['failed_count'] + 1;
                    $locked_until = $new_count >= 5 ? date('Y-m-d H:i:s', strtotime('+5 minutes')) : null;
                    $upd = $pdo->prepare("UPDATE login_attempts SET failed_count = ?, locked_until = ? WHERE username = ?");
                    $upd->execute([$new_count, $locked_until, $username]);
                } else {
                    $ins = $pdo->prepare("INSERT INTO login_attempts (username, failed_count) VALUES (?, 1)");
                    $ins->execute([$username]);
                }
                $error = 'Invalid username or password.';
            }
        }
    }
}

require 'login_view.php';