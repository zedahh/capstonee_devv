<?php if (!isset($announcements)) { return; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Barangay Santa Ines - Health Announcements</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/css/custom.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <h2 class="mb-4 text-center">Barangay Santa Ines Health Announcements</h2>
  <p class="text-center mb-4"><a href="../residents/my_record.php">Check your own health record</a></p>
  <?php if (empty($announcements)): ?>
    <p class="text-center text-muted">No announcements at this time.</p>
  <?php endif; ?>
  <?php foreach ($announcements as $a): ?>
  <div class="card mb-3 shadow-sm">
    <div class="card-body">
      <h5><?= htmlspecialchars($a['title']) ?></h5>
      <p><?= nl2br(htmlspecialchars($a['content'])) ?></p>
      <small class="text-muted">
        <?= htmlspecialchars($a['target_purok']) === 'All' ? 'All puroks' : 'Purok ' . htmlspecialchars($a['target_purok']) ?>
        · <?= htmlspecialchars($a['created_at']) ?>
      </small>
    </div>
  </div>
  <?php endforeach; ?>
</div>
</body>
</html>