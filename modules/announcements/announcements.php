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
$draft_title = $_GET['draft_title'] ?? '';
$draft_content = $_GET['draft_content'] ?? '';
$draft_purok = $_GET['draft_purok'] ?? '';

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("UPDATE announcements SET is_active = 0 WHERE announcement_id = ?");
    $stmt->execute([$id]);

    $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'DELETE', 'announcements', ?, 'Removed announcement')");
    $log->execute([$_SESSION['user_id'], $id]);

    header('Location: announcements.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $target_purok = $_POST['target_purok'] ?? 'All';
    $send_sms = isset($_POST['send_sms']);

    if ($title === '' || $content === '') {
        $error = 'Title and content are required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content, target_purok, posted_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $content, $target_purok, $_SESSION['user_id']]);

        $new_id = $pdo->lastInsertId();
        $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'INSERT', 'announcements', ?, ?)");
        $log->execute([$_SESSION['user_id'], $new_id, "Posted announcement: $title"]);

        $sms_sent_count = 0;
        if ($send_sms) {
            if ($target_purok === 'All') {
                $recStmt = $pdo->query("SELECT contact_number FROM residents WHERE is_active = 1 AND contact_number IS NOT NULL AND contact_number != ''");
            } else {
                $recStmt = $pdo->prepare("SELECT contact_number FROM residents WHERE is_active = 1 AND purok = ? AND contact_number IS NOT NULL AND contact_number != ''");
                $recStmt->execute([$target_purok]);
            }
            $recipients = $recStmt->fetchAll(PDO::FETCH_COLUMN);

            $sms_message = "BARANGAY SANTA INES ANNOUNCEMENT: $title - $content";
            foreach ($recipients as $phone) {
                sendSms($pdo, $phone, $sms_message, 'resident_announcement', $_SESSION['user_id']);
                $sms_sent_count++;
            }
        }

        $success = 'Announcement posted successfully.' . ($send_sms ? " SMS sent to $sms_sent_count resident(s) with phone numbers on file." : '');
    }
}

$announcements = $pdo->query("
    SELECT announcements.*, u.full_name
    FROM announcements
    LEFT JOIN users u ON announcements.posted_by = u.user_id
    WHERE announcements.is_active = 1
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

require 'announcements_view.php';