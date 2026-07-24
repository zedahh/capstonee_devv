<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';

$error = '';
$success = '';
$edit_resident = null;

// Pick up one-time flash messages left by a redirect (see Post/Redirect/Get below)
if (isset($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}
if (isset($_SESSION['flash_error'])) {
    $error = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

// Handle delete (deactivate, not permanently erase)
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("UPDATE residents SET is_active = 0 WHERE resident_id = ?");
    $stmt->execute([$id]);

    $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'DELETE', 'residents', ?, 'Deactivated resident')");
    $log->execute([$_SESSION['user_id'], $id]);

    header('Location: residents.php');
    exit;
}

// Load a resident into the form for editing
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM residents WHERE resident_id = ?");
    $stmt->execute([$id]);
    $edit_resident = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle add or update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resident_id = $_POST['resident_id'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $suffix = trim($_POST['suffix'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $purok = $_POST['purok'] ?? '';
    $address_line = trim($_POST['address_line'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');

    if ($first_name === '' || $middle_name === '' || $last_name === '' || $birth_date === '' || $gender === '' || $purok === '' || $address_line === '') {
        $error = 'Please fill in all required fields: first name, middle name, last name, birth date, gender, purok, and address.';
    } elseif ($resident_id !== '') {
        $stmt = $pdo->prepare("UPDATE residents SET first_name=?, middle_name=?, last_name=?, suffix=?, birth_date=?, gender=?, purok=?, address_line=?, contact_number=? WHERE resident_id=?");
        $stmt->execute([$first_name, $middle_name, $last_name, $suffix, $birth_date, $gender, $purok, $address_line, $contact_number, $resident_id]);

        $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'UPDATE', 'residents', ?, ?)");
        $log->execute([$_SESSION['user_id'], $resident_id, "Updated resident: $first_name $last_name"]);

        $_SESSION['flash_success'] = 'Resident updated successfully.';
        header('Location: residents.php');
        exit;
    } else {
        $qr_code = 'RES-' . bin2hex(random_bytes(12));
        $stmt = $pdo->prepare("INSERT INTO residents 
            (qr_code, first_name, middle_name, last_name, suffix, birth_date, gender, purok, address_line, contact_number)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$qr_code, $first_name, $middle_name, $last_name, $suffix, $birth_date, $gender, $purok, $address_line, $contact_number]);

        $new_id = $pdo->lastInsertId();
        $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'INSERT', 'residents', ?, ?)");
        $log->execute([$_SESSION['user_id'], $new_id, "Added resident: $first_name $last_name"]);

        $_SESSION['flash_success'] = 'Resident added successfully.';
        header('Location: residents.php');
        exit;
    }
}

$residents = $pdo->query("SELECT * FROM residents WHERE is_active = 1 ORDER BY last_name, first_name")->fetchAll(PDO::FETCH_ASSOC);

require 'residents_view.php';