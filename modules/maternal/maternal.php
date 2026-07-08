<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';
require '../../includes/functions.php';
$prenatal_schedule = $pdo->query("SELECT * FROM prenatal_visit_schedule")->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resident_id = $_POST['resident_id'] ?? '';
    $lmp_date = $_POST['lmp_date'] ?: null;
    $edd_date = $_POST['edd_date'] ?: null;
    $gravida = $_POST['gravida'] ?: null;
    $para = $_POST['para'] ?: null;
    $health_conditions = trim($_POST['health_conditions'] ?? '');
    $monitoring_status = $_POST['monitoring_status'] ?? 'Ongoing';

    if ($resident_id === '') {
        $error = 'Please select a resident.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO maternal_records 
            (resident_id, lmp_date, edd_date, gravida, para, health_conditions, monitoring_status)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$resident_id, $lmp_date, $edd_date, $gravida, $para, $health_conditions, $monitoring_status]);

        $new_id = $pdo->lastInsertId();
        $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'INSERT', 'maternal_records', ?, 'Registered maternal record')");
        $log->execute([$_SESSION['user_id'], $new_id]);

        $success = 'Maternal record added successfully.';
    }
}

$female_residents = $pdo->query("SELECT resident_id, first_name, last_name, purok FROM residents WHERE gender = 'Female' AND is_active = 1 ORDER BY last_name")->fetchAll(PDO::FETCH_ASSOC);

$records = $pdo->query("
    SELECT maternal_records.*, residents.first_name, residents.last_name, residents.purok
    FROM maternal_records
    JOIN residents ON maternal_records.resident_id = residents.resident_id
    ORDER BY maternal_records.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

require 'maternal_view.php';