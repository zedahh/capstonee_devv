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

$error = '';
$success = '';

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare("UPDATE lgu_contacts SET is_active = 0 WHERE contact_id = ?")->execute([$id]);
    header('Location: lgu_contacts.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact_name = trim($_POST['contact_name'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');

    if ($contact_name === '' || $phone_number === '') {
        $error = 'Name and phone number are required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO lgu_contacts (contact_name, designation, phone_number) VALUES (?, ?, ?)");
        $stmt->execute([$contact_name, $designation, $phone_number]);
        $success = 'Contact added successfully.';
    }
}

$contacts = $pdo->query("SELECT * FROM lgu_contacts WHERE is_active = 1 ORDER BY contact_name")->fetchAll(PDO::FETCH_ASSOC);

require 'lgu_contacts_view.php';