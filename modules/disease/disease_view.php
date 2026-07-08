<?php if (!isset($cases)) { return; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Disease and Illness Case Recording</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/css/custom.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Disease and Illness Case Recording</h3>
    <a href="../dashboard/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
  </div>

  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title"><?= $edit_case ? 'Update case' : 'Record new case' ?></h5>
      <form method="POST" action="">
        <input type="hidden" name="case_id" value="<?= htmlspecialchars($edit_case['case_id'] ?? '') ?>">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Resident</label>
            <select name="resident_id" class="form-select" required>
              <option value="">Select resident</option>
              <?php foreach ($residents as $r): ?>
                <option value="<?= $r['resident_id'] ?>" <?= (string)($edit_case['resident_id'] ?? '') === (string)$r['resident_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($r['last_name'] . ', ' . $r['first_name']) ?> (Purok <?= $r['purok'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Disease name</label>
            <input type="text" name="disease_name" class="form-control" required placeholder="e.g. Dengue, Diarrhea"
              value="<?= htmlspecialchars($edit_case['disease_name'] ?? '') ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Date reported</label>
            <input type="date" name="date_reported" class="form-control" required
              value="<?= htmlspecialchars($edit_case['date_reported'] ?? '') ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="Active" <?= ($edit_case['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
              <option value="Under monitoring" <?= ($edit_case['status'] ?? '') === 'Under monitoring' ? 'selected' : '' ?>>Under monitoring</option>
              <option value="Recovered" <?= ($edit_case['status'] ?? '') === 'Recovered' ? 'selected' : '' ?>>Recovered</option>
            </select>
          </div>
          <div class="col-md-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2"><?= htmlspecialchars($edit_case['notes'] ?? '') ?></textarea>
          </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3"><?= $edit_case ? 'Update case' : 'Record case' ?></button>
        <?php if ($edit_case): ?>
          <a href="disease.php" class="btn btn-outline-secondary mt-3">Cancel edit</a>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <h5>All disease cases</h5>
  <table class="table table-striped">
    <thead>
      <tr><th>Resident</th><th>Purok</th><th>Disease</th><th>Date reported</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($cases as $c): ?>
      <tr>
        <td><?= htmlspecialchars($c['last_name'] . ', ' . $c['first_name']) ?></td>
        <td>Purok <?= htmlspecialchars($c['purok']) ?></td>
        <td><?= htmlspecialchars($c['disease_name']) ?></td>
        <td><?= htmlspecialchars($c['date_reported']) ?></td>
        <td><?= htmlspecialchars($c['status']) ?></td>
        <td><a href="?edit=<?= $c['case_id'] ?>" class="btn btn-sm btn-outline-primary">Edit / update status</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>