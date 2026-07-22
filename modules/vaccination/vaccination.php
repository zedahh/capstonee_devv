<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';

$error = '';
$success = '';
if (isset($_GET['archive'])) {
    $archive_id = (int) $_GET['archive'];
    $pdo->prepare("UPDATE vaccination_records SET is_active = 0 WHERE vaccination_id = ?")->execute([$archive_id]);

    $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'ARCHIVE', 'vaccination_records', ?, 'Archived vaccination record')");
    $log->execute([$_SESSION['user_id'], $archive_id]);

    header('Location: vaccination.php');
    exit;
}

// Handle new vaccination entry
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $infant_record_id = $_POST['infant_record_id'] ?? '';
    $vaccine_name = trim($_POST['vaccine_name'] ?? '');
    $date_administered = $_POST['date_administered'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if ($infant_record_id === '' || $vaccine_name === '' || $date_administered === '') {
        $error = 'Please select an infant, vaccine name, and date administered.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO vaccination_records 
            (infant_record_id, vaccine_name, date_administered, administered_by, notes)
            VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$infant_record_id, $vaccine_name, $date_administered, $_SESSION['user_id'], $notes]);

        $new_id = $pdo->lastInsertId();
        $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'INSERT', 'vaccination_records', ?, ?)");
        $log->execute([$_SESSION['user_id'], $new_id, "Recorded vaccine: $vaccine_name"]);

        $success = 'Vaccination record added successfully.';
    }
}

$epi_vaccines = $pdo->query("SELECT vaccine_name FROM epi_schedule ORDER BY recommended_age_weeks")->fetchAll(PDO::FETCH_COLUMN);

// Dropdown: all infants
$infants = $pdo->query("
    SELECT infant_records.infant_record_id, r.first_name, r.last_name, r.purok
    FROM infant_records
    JOIN residents r ON infant_records.resident_id = r.resident_id
    WHERE infant_records.is_active = 1
    ORDER BY r.last_name
")->fetchAll(PDO::FETCH_ASSOC);

// List all vaccination records, joined with infant + health worker info
$records = $pdo->query("
    SELECT vaccination_records.*, r.first_name, r.last_name, r.purok, u.full_name AS worker_name
    FROM vaccination_records
    JOIN infant_records ON vaccination_records.infant_record_id = infant_records.infant_record_id
    JOIN residents r ON infant_records.resident_id = r.resident_id
    LEFT JOIN users u ON vaccination_records.administered_by = u.user_id
    WHERE vaccination_records.is_active = 1
    ORDER BY vaccination_records.date_administered DESC
")->fetchAll(PDO::FETCH_ASSOC);

require 'vaccination_view.php';