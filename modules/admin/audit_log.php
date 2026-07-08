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

$logs = $pdo->query("
    SELECT audit_logs.*, users.full_name
    FROM audit_logs
    LEFT JOIN users ON audit_logs.user_id = users.user_id
    ORDER BY audit_logs.created_at DESC
    LIMIT 200
")->fetchAll(PDO::FETCH_ASSOC);

require 'audit_log_view.php';