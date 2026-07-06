<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';

$error = '';
$success = '';

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

    if ($title === '' || $content === '') {
        $error = 'Title and content are required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content, target_purok, posted_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $content, $target_purok, $_SESSION['user_id']]);

        $new_id = $pdo->lastInsertId();
        $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'INSERT', 'announcements', ?, ?)");
        $log->execute([$_SESSION['user_id'], $new_id, "Posted announcement: $title"]);

        $success = 'Announcement posted successfully.';
    }
}

$announcements = $pdo->query("
    SELECT announcements.*, u.full_name
    FROM announcements
    LEFT JOIN users u ON announcements.posted_by = u.user_id
    WHERE announcements.is_active = 1
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Public Announcements</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Public Announcements</h3>
    <a href="../dashboard/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
  </div>

  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Post new announcement</h5>
      <form method="POST" action="">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Target purok</label>
            <select name="target_purok" class="form-select">
              <option value="All">All puroks</option>
              <option value="1">Purok 1</option>
              <option value="2">Purok 2</option>
              <option value="3">Purok 3</option>
              <option value="4">Purok 4</option>
            </select>
          </div>
          <div class="col-md-12">
            <label class="form-label">Content</label>
            <textarea name="content" class="form-control" rows="3" required></textarea>
          </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Post announcement</button>
      </form>
    </div>
  </div>

  <h5>All active announcements</h5>
  <?php foreach ($announcements as $a): ?>
  <div class="card mb-2">
    <div class="card-body">
      <div class="d-flex justify-content-between">
        <h6><?= htmlspecialchars($a['title']) ?> <span class="badge bg-secondary"><?= htmlspecialchars($a['target_purok']) === 'All' ? 'All puroks' : 'Purok ' . htmlspecialchars($a['target_purok']) ?></span></h6>
        <a href="?delete=<?= $a['announcement_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this announcement?')">Remove</a>
      </div>
      <p class="mb-1"><?= nl2br(htmlspecialchars($a['content'])) ?></p>
      <small class="text-muted">Posted by <?= htmlspecialchars($a['full_name'] ?? 'Unknown') ?> on <?= htmlspecialchars($a['created_at']) ?></small>
    </div>
  </div>
  <?php endforeach; ?>
</div>
</body>
</html>