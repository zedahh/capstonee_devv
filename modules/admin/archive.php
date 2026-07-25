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

// Maps each archive "type" to its table, primary key column, and audit log table_name
$archive_types = [
    'residents'    => ['table' => 'residents',            'pk' => 'resident_id',       'log_name' => 'residents'],
    'maternal'     => ['table' => 'maternal_records',      'pk' => 'maternal_record_id','log_name' => 'maternal_records'],
    'infant'       => ['table' => 'infant_records',        'pk' => 'infant_record_id',  'log_name' => 'infant_records'],
    'vaccination'  => ['table' => 'vaccination_records',   'pk' => 'vaccination_id',    'log_name' => 'vaccination_records'],
    'disease'      => ['table' => 'disease_cases',         'pk' => 'case_id',           'log_name' => 'disease_cases'],
];

// Handle restore
if (isset($_GET['restore'])) {
    [$type, $id] = explode(':', $_GET['restore']);
    $id = (int) $id;
    if (isset($archive_types[$type])) {
        $t = $archive_types[$type];
        $pdo->prepare("UPDATE {$t['table']} SET is_active = 1 WHERE {$t['pk']} = ?")->execute([$id]);

        $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'RESTORE', ?, ?, 'Restored from archive')");
        $log->execute([$_SESSION['user_id'], $t['log_name'], $id]);

        $success = 'Record restored successfully.';
    }
}

// Handle permanent delete
if (isset($_GET['delete'])) {
    [$type, $id] = explode(':', $_GET['delete']);
    $id = (int) $id;
    if (isset($archive_types[$type])) {
        $t = $archive_types[$type];
        try {
            $pdo->prepare("DELETE FROM {$t['table']} WHERE {$t['pk']} = ?")->execute([$id]);

            $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'PERMANENT_DELETE', ?, ?, 'Permanently deleted from archive')");
            $log->execute([$_SESSION['user_id'], $t['log_name'], $id]);

            $success = 'Record permanently deleted.';
        } catch (PDOException $e) {
            $error = 'Cannot permanently delete this record — other records (such as linked health data) still depend on it. Delete or restore those first.';
        }
    }
}

// Helper: find who archived a record and when, from the audit log
function getArchivedByInfo($pdo, $log_name, $id) {
    $stmt = $pdo->prepare("
        SELECT audit_logs.created_at, u.full_name
        FROM audit_logs
        LEFT JOIN users u ON audit_logs.user_id = u.user_id
        WHERE audit_logs.action = 'ARCHIVE' AND audit_logs.table_name = ? AND audit_logs.record_id = ?
        ORDER BY audit_logs.created_at DESC LIMIT 1
    ");
    $stmt->execute([$log_name, $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$archived_items = [];

// Residents
$rows = $pdo->query("SELECT resident_id, first_name, last_name, purok FROM residents WHERE is_active = 0")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $info = getArchivedByInfo($pdo, 'residents', $r['resident_id']);
    $archived_items[] = [
        'type' => 'residents', 'id' => $r['resident_id'],
        'label' => 'Resident', 'description' => htmlspecialchars($r['last_name'] . ', ' . $r['first_name'] . ' (Purok ' . $r['purok'] . ')'),
        'archived_by' => $info['full_name'] ?? 'Unknown', 'archived_at' => $info['created_at'] ?? null,
    ];
}

// Maternal
$rows = $pdo->query("
    SELECT maternal_records.maternal_record_id, r.first_name, r.last_name
    FROM maternal_records JOIN residents r ON maternal_records.resident_id = r.resident_id
    WHERE maternal_records.is_active = 0
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $info = getArchivedByInfo($pdo, 'maternal_records', $r['maternal_record_id']);
    $archived_items[] = [
        'type' => 'maternal', 'id' => $r['maternal_record_id'],
        'label' => 'Maternal Record', 'description' => htmlspecialchars($r['last_name'] . ', ' . $r['first_name']),
        'archived_by' => $info['full_name'] ?? 'Unknown', 'archived_at' => $info['created_at'] ?? null,
    ];
}

// Infant
$rows = $pdo->query("
    SELECT infant_records.infant_record_id, r.first_name, r.last_name
    FROM infant_records JOIN residents r ON infant_records.resident_id = r.resident_id
    WHERE infant_records.is_active = 0
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $info = getArchivedByInfo($pdo, 'infant_records', $r['infant_record_id']);
    $archived_items[] = [
        'type' => 'infant', 'id' => $r['infant_record_id'],
        'label' => 'Infant Record', 'description' => htmlspecialchars($r['last_name'] . ', ' . $r['first_name']),
        'archived_by' => $info['full_name'] ?? 'Unknown', 'archived_at' => $info['created_at'] ?? null,
    ];
}

// Vaccination
$rows = $pdo->query("
    SELECT vaccination_records.vaccination_id, vaccination_records.vaccine_name, r.first_name, r.last_name
    FROM vaccination_records
    JOIN infant_records ON vaccination_records.infant_record_id = infant_records.infant_record_id
    JOIN residents r ON infant_records.resident_id = r.resident_id
    WHERE vaccination_records.is_active = 0
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $info = getArchivedByInfo($pdo, 'vaccination_records', $r['vaccination_id']);
    $archived_items[] = [
        'type' => 'vaccination', 'id' => $r['vaccination_id'],
        'label' => 'Vaccination Record', 'description' => htmlspecialchars($r['vaccine_name'] . ' - ' . $r['last_name'] . ', ' . $r['first_name']),
        'archived_by' => $info['full_name'] ?? 'Unknown', 'archived_at' => $info['created_at'] ?? null,
    ];
}

// Disease
$rows = $pdo->query("
    SELECT disease_cases.case_id, disease_cases.disease_name, r.first_name, r.last_name
    FROM disease_cases JOIN residents r ON disease_cases.resident_id = r.resident_id
    WHERE disease_cases.is_active = 0
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $info = getArchivedByInfo($pdo, 'disease_cases', $r['case_id']);
    $archived_items[] = [
        'type' => 'disease', 'id' => $r['case_id'],
        'label' => 'Disease Case', 'description' => htmlspecialchars($r['disease_name'] . ' - ' . $r['last_name'] . ', ' . $r['first_name']),
        'archived_by' => $info['full_name'] ?? 'Unknown', 'archived_at' => $info['created_at'] ?? null,
    ];
}

// Most recently archived first
usort($archived_items, fn($a, $b) => strtotime($b['archived_at'] ?? '1970-01-01') <=> strtotime($a['archived_at'] ?? '1970-01-01'));

require 'archive_view.php';