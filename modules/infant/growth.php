<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';

$infant_record_id = (int) ($_GET['id'] ?? 0);
$error = '';
$success = '';

// Get the infant record + resident info
$stmt = $pdo->prepare("
    SELECT infant_records.*, r.first_name, r.last_name, r.birth_date, r.purok
    FROM infant_records
    JOIN residents r ON infant_records.resident_id = r.resident_id
    WHERE infant_record_id = ?
");
$stmt->execute([$infant_record_id]);
$infant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$infant) {
    die('Infant record not found.');
}

// Handle new growth entry
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $visit_date = $_POST['visit_date'] ?? '';
    $weight_kg = $_POST['weight_kg'] ?? '';
    $height_cm = $_POST['height_cm'] ?: null;
    $notes = trim($_POST['notes'] ?? '');

   if ($visit_date === '' || $weight_kg === '') {
        $error = 'Visit date and weight are required.';
    } elseif ($weight_kg < 0.5 || $weight_kg > 15) {
        $error = 'Weight should realistically be between 0.5 kg and 15 kg for an infant. Please check the value entered.';
    } elseif ($height_cm !== null && $height_cm !== '' && ($height_cm < 25 || $height_cm > 90)) {
        $error = 'Height should realistically be between 25 cm and 90 cm for an infant. Please check the value entered.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO growth_monitoring 
            (infant_record_id, visit_date, weight_kg, height_cm, notes, recorded_by)
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$infant_record_id, $visit_date, $weight_kg, $height_cm, $notes, $_SESSION['user_id']]);

        $new_id = $pdo->lastInsertId();
        $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'INSERT', 'growth_monitoring', ?, 'Recorded growth visit')");
        $log->execute([$_SESSION['user_id'], $new_id]);

        $success = 'Growth visit recorded successfully.';
    }
}

// List all growth visits for this infant
$visits = $pdo->prepare("SELECT * FROM growth_monitoring WHERE infant_record_id = ? ORDER BY visit_date DESC");
$visits->execute([$infant_record_id]);
$visits = $visits->fetchAll(PDO::FETCH_ASSOC);

require 'growth_view.php';