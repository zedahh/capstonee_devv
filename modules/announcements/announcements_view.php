<?php if (!isset($announcements)) { return; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Public Announcements</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/css/custom.css" rel="stylesheet">
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
            <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($draft_title) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Target purok</label>
            <select name="target_purok" class="form-select">
              <option value="All" <?= $draft_purok === '' || $draft_purok === 'All' ? 'selected' : '' ?>>All puroks</option>
              <option value="1" <?= $draft_purok === '1' ? 'selected' : '' ?>>Purok 1</option>
              <option value="2" <?= $draft_purok === '2' ? 'selected' : '' ?>>Purok 2</option>
              <option value="3" <?= $draft_purok === '3' ? 'selected' : '' ?>>Purok 3</option>
              <option value="4" <?= $draft_purok === '4' ? 'selected' : '' ?>>Purok 4</option>
            </select>
          </div>
         <div class="col-md-12">
            <label class="form-label">Content</label>
            <textarea name="content" class="form-control" rows="3" required></textarea>
          </div>
          <div class="col-md-12">
            <div class="form-check">
              <input type="checkbox" name="send_sms" value="1" class="form-check-input" id="sendSmsCheck">
              <label class="form-check-label" for="sendSmsCheck">Also send SMS to residents with phone numbers on file (matching the target purok above)</label>
            </div>
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