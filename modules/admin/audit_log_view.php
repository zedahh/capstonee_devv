<?php if (!isset($logs)) { return; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Audit Log</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/css/custom.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Audit log</h3>
    <a href="../dashboard/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
  </div>

  <table class="table table-striped table-sm">
    <thead>
      <tr>
        <th>Date/time</th><th>User</th><th>Action</th><th>Table</th><th>Record ID</th><th>Details</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($logs as $log): ?>
      <tr>
        <td><?= htmlspecialchars($log['created_at']) ?></td>
        <td><?= htmlspecialchars($log['full_name'] ?? 'Unknown') ?></td>
        <td><?= htmlspecialchars($log['action']) ?></td>
        <td><?= htmlspecialchars($log['table_name']) ?></td>
        <td><?= htmlspecialchars($log['record_id']) ?></td>
        <td><?= htmlspecialchars($log['details']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>