<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';

$maternal_record_id = (int) ($_GET['id'] ?? 0);
$error = '';
$success = '';

// Get the maternal record + resident info
$stmt = $pdo->prepare("
    SELECT maternal_records.*, residents.first_name, residents.last_name, residents.purok
    FROM maternal_records
    JOIN residents ON maternal_records.resident_id = residents.resident_id
    WHERE maternal_record_id = ?
");
$stmt->execute([$maternal_record_id]);
$record = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$record) {
    die('Maternal record not found.');
}

// Handle new checkup entry
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checkup_date = $_POST['checkup_date'] ?? '';
    $findings = trim($_POST['findings'] ?? '');
    $next_checkup_date = $_POST['next_checkup_date'] ?: null;

    if ($checkup_date === '') {
        $error = 'Checkup date is required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO prenatal_checkups 
            (maternal_record_id, checkup_date, findings, next_checkup_date, recorded_by)
            VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$maternal_record_id, $checkup_date, $findings, $next_checkup_date, $_SESSION['user_id']]);

        $new_id = $pdo->lastInsertId();
        $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'INSERT', 'prenatal_checkups', ?, 'Recorded prenatal checkup')");
        $log->execute([$_SESSION['user_id'], $new_id]);

        $success = 'Checkup recorded successfully.';
    }
}

// List all checkups for this maternal record
$checkups = $pdo->prepare("SELECT * FROM prenatal_checkups WHERE maternal_record_id = ? ORDER BY checkup_date DESC");
$checkups->execute([$maternal_record_id]);
$checkups = $checkups->fetchAll(PDO::FETCH_ASSOC);

require 'checkups_view.php';