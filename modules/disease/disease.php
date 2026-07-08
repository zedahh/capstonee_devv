<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';

$error = '';
$success = '';
$edit_case = null;

// Load a case into the form for editing (mainly for status updates)
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM disease_cases WHERE case_id = ?");
    $stmt->execute([$id]);
    $edit_case = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle add or update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $case_id = $_POST['case_id'] ?? '';
    $resident_id = $_POST['resident_id'] ?? '';
    $disease_name = trim($_POST['disease_name'] ?? '');
    $date_reported = $_POST['date_reported'] ?? '';
    $status = $_POST['status'] ?? 'Active';
    $notes = trim($_POST['notes'] ?? '');

    if ($resident_id === '' || $disease_name === '' || $date_reported === '') {
        $error = 'Please select a resident, disease name, and date reported.';
    } elseif ($case_id !== '') {
        // UPDATE existing case
        $stmt = $pdo->prepare("UPDATE disease_cases SET resident_id=?, disease_name=?, date_reported=?, status=?, notes=? WHERE case_id=?");
        $stmt->execute([$resident_id, $disease_name, $date_reported, $status, $notes, $case_id]);

        $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'UPDATE', 'disease_cases', ?, ?)");
        $log->execute([$_SESSION['user_id'], $case_id, "Updated case: $disease_name (status: $status)"]);

        $success = 'Case updated successfully.';
    } else {
        // INSERT new case
        $stmt = $pdo->prepare("INSERT INTO disease_cases (resident_id, disease_name, date_reported, status, notes, recorded_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$resident_id, $disease_name, $date_reported, $status, $notes, $_SESSION['user_id']]);

        $new_id = $pdo->lastInsertId();
        $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'INSERT', 'disease_cases', ?, ?)");
        $log->execute([$_SESSION['user_id'], $new_id, "Recorded case: $disease_name"]);

        $success = 'Case recorded successfully.';
    }
}

// Dropdown: all residents
$residents = $pdo->query("SELECT resident_id, first_name, last_name, purok FROM residents WHERE is_active = 1 ORDER BY last_name")->fetchAll(PDO::FETCH_ASSOC);

// List all cases, joined with resident info
$cases = $pdo->query("
    SELECT disease_cases.*, r.first_name, r.last_name, r.purok
    FROM disease_cases
    JOIN residents r ON disease_cases.resident_id = r.resident_id
    ORDER BY disease_cases.date_reported DESC
")->fetchAll(PDO::FETCH_ASSOC);

require 'disease_view.php';