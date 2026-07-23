<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';
require '../../includes/functions.php';

$error = '';
$success = '';
if (isset($_GET['archive'])) {
    $archive_id = (int) $_GET['archive'];
    $pdo->prepare("UPDATE infant_records SET is_active = 0 WHERE infant_record_id = ?")->execute([$archive_id]);

    $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'ARCHIVE', 'infant_records', ?, 'Archived infant record')");
    $log->execute([$_SESSION['user_id'], $archive_id]);

    header('Location: infant.php');
    exit;
}

// Handle new infant registration (creates a resident + infant record together)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $suffix = trim($_POST['suffix'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $purok = $_POST['purok'] ?? '';
    $mother_resident_id = $_POST['mother_resident_id'] ?: null;
    $birth_weight_kg = $_POST['birth_weight_kg'] ?: null;
    $birth_length_cm = $_POST['birth_length_cm'] ?: null;
    $monitoring_status = $_POST['monitoring_status'] ?? 'Normal';

   if ($first_name === '' || $last_name === '' || $birth_date === '' || $gender === '' || $purok === '') {
        $error = 'Please fill in all required fields.';
    } elseif ($birth_weight_kg !== null && $birth_weight_kg !== '' && ($birth_weight_kg < 0.3 || $birth_weight_kg > 7)) {
        $error = 'Birth weight should realistically be between 0.3 kg and 7 kg. Please check the value entered.';
    } elseif ($birth_length_cm !== null && $birth_length_cm !== '' && ($birth_length_cm < 25 || $birth_length_cm > 60)) {
        $error = 'Birth length should realistically be between 25 cm and 60 cm. Please check the value entered.';
    } else {
        // Step 1: create the resident record for this infant
        $qr_code = 'RES-' . bin2hex(random_bytes(12));
        $stmt = $pdo->prepare("INSERT INTO residents 
            (qr_code, first_name, middle_name, last_name, suffix, birth_date, gender, purok)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$qr_code, $first_name, $middle_name, $last_name, $suffix, $birth_date, $gender, $purok]);
        $new_resident_id = $pdo->lastInsertId();

        $log1 = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'INSERT', 'residents', ?, ?)");
        $log1->execute([$_SESSION['user_id'], $new_resident_id, "Added infant resident: $first_name $last_name"]);

        // Step 2: create the infant record linked to that resident
        $stmt2 = $pdo->prepare("INSERT INTO infant_records 
            (resident_id, mother_resident_id, birth_weight_kg, birth_length_cm, monitoring_status)
            VALUES (?, ?, ?, ?, ?)");
        $stmt2->execute([$new_resident_id, $mother_resident_id, $birth_weight_kg, $birth_length_cm, $monitoring_status]);
        $new_infant_id = $pdo->lastInsertId();

        $log2 = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'INSERT', 'infant_records', ?, 'Registered infant monitoring record')");
        $log2->execute([$_SESSION['user_id'], $new_infant_id]);

        $success = 'Infant registered successfully.';
    }
}

// Dropdown: female residents, to optionally link as mother
$mothers = $pdo->query("SELECT resident_id, first_name, last_name FROM residents WHERE gender = 'Female' AND is_active = 1 ORDER BY last_name")->fetchAll(PDO::FETCH_ASSOC);

// Fully Immunized Child (FIC) status check against the DOH EPI schedule
$epi_schedule = $pdo->query("SELECT * FROM epi_schedule")->fetchAll(PDO::FETCH_ASSOC);

// List all infants with resident + mother info
$infants = $pdo->query("
    SELECT infant_records.*, r.first_name, r.last_name, r.birth_date, r.purok,
           m.first_name AS mother_first, m.last_name AS mother_last
    FROM infant_records
    JOIN residents r ON infant_records.resident_id = r.resident_id
    LEFT JOIN residents m ON infant_records.mother_resident_id = m.resident_id
    WHERE infant_records.is_active = 1
    ORDER BY r.birth_date DESC
")->fetchAll(PDO::FETCH_ASSOC);

require 'infant_view.php';