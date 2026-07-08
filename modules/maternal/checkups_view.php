<?php if (!isset($record)) { return; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Prenatal Checkups</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/css/custom.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Prenatal checkups — <?= htmlspecialchars($record['first_name'] . ' ' . $record['last_name']) ?></h3>
    <a href="maternal.php" class="btn btn-outline-secondary btn-sm">Back to maternal records</a>
  </div>

  <p class="text-muted">Purok <?= htmlspecialchars($record['purok']) ?> · Status: <?= htmlspecialchars($record['monitoring_status']) ?> · EDD: <?= htmlspecialchars($record['edd_date']) ?></p>

  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Record a checkup</h5>
      <form method="POST" action="">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Checkup date</label>
            <input type="date" name="checkup_date" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Next checkup date</label>
            <input type="date" name="next_checkup_date" class="form-control">
          </div>
          <div class="col-md-12">
            <label class="form-label">Findings / notes</label>
            <textarea name="findings" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Add checkup</button>
      </form>
    </div>
  </div>

  <h5>Checkup history</h5>
  <table class="table table-striped">
    <thead>
      <tr><th>Checkup date</th><th>Findings</th><th>Next checkup</th></tr>
    </thead>
    <tbody>
      <?php foreach ($checkups as $c): ?>
      <tr>
        <td><?= htmlspecialchars($c['checkup_date']) ?></td>
        <td><?= htmlspecialchars($c['findings']) ?></td>
        <td><?= htmlspecialchars($c['next_checkup_date']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>