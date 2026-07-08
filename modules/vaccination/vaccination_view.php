<?php if (!isset($records)) { return; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Vaccination Records</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/css/custom.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Vaccination Records</h3>
    <a href="../dashboard/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
  </div>

  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Record a vaccination</h5>
      <form method="POST" action="">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Infant</label>
            <select name="infant_record_id" class="form-select" required>
              <option value="">Select infant</option>
              <?php foreach ($infants as $inf): ?>
                <option value="<?= $inf['infant_record_id'] ?>"><?= htmlspecialchars($inf['last_name'] . ', ' . $inf['first_name']) ?> (Purok <?= $inf['purok'] ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Vaccine name</label>
            <select name="vaccine_name" class="form-select" required>
              <option value="">Select vaccine</option>
              <?php foreach ($epi_vaccines as $v): ?>
                <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Date administered</label>
            <input type="date" name="date_administered" class="form-control" required>
          </div>
          <div class="col-md-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Add record</button>
      </form>
    </div>
  </div>

  <h5>All vaccination records</h5>
  <table class="table table-striped">
    <thead>
      <tr><th>Infant</th><th>Purok</th><th>Vaccine</th><th>Date given</th><th>Given by</th><th>Notes</th></tr>
    </thead>
    <tbody>
      <?php foreach ($records as $rec): ?>
      <tr>
        <td><?= htmlspecialchars($rec['last_name'] . ', ' . $rec['first_name']) ?></td>
        <td>Purok <?= htmlspecialchars($rec['purok']) ?></td>
        <td><?= htmlspecialchars($rec['vaccine_name']) ?></td>
        <td><?= htmlspecialchars($rec['date_administered']) ?></td>
        <td><?= htmlspecialchars($rec['worker_name'] ?? 'Unknown') ?></td>
        <td><?= htmlspecialchars($rec['notes']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>