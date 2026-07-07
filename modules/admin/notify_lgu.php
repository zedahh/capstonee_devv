<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';
require '../../includes/functions.php';

$purok = $_GET['purok'] ?? '';
$disease = $_GET['disease'] ?? '';
$count = $_GET['count'] ?? '';

$contacts = $pdo->query("SELECT * FROM lgu_contacts WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);

$message = "BARANGAY SANTA INES HEALTH ALERT: $disease cases in Purok $purok have reached $count, exceeding threshold. Please coordinate with the health center for possible action.";

$sent_count = 0;
foreach ($contacts as $contact) {
    sendSms($pdo, $contact['phone_number'], $message, 'lgu_alert', $_SESSION['user_id']);
    $sent_count++;
}

$log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, details) VALUES (?, 'NOTIFY', 'lgu_contacts', ?)");
$log->execute([$_SESSION['user_id'], "SMS alert sent to $sent_count LGU contact(s): $disease in Purok $purok"]);

header('Location: ../dashboard/dashboard.php?sms_sent=' . $sent_count);
exit;