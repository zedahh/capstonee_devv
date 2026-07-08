<?php if (!isset($infants)) { return; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Infant Monitoring</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/css/custom.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Infant Monitoring</h3>
    <a href="../dashboard/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
  </div>

  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Register newborn</h5>
      <form method="POST" action="">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">First name</label>
            <input type="text" name="first_name" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Middle name</label>
            <input type="text" name="middle_name" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">Last name</label>
            <input type="text" name="last_name" class="form-control" required>
          </div>
          <div class="col-md-1">
            <label class="form-label">Suffix</label>
            <input type="text" name="suffix" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">Birth date</label>
            <input type="date" name="birth_date" class="form-control" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select" required>
              <option value="">Select</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Purok</label>
            <select name="purok" class="form-select" required>
              <option value="">Select</option>
              <?php for ($p = 1; $p <= 4; $p++): ?>
                <option value="<?= $p ?>">Purok <?= $p ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Mother (optional)</label>
            <select name="mother_resident_id" class="form-select">
              <option value="">Not in system / unknown</option>
              <?php foreach ($mothers as $m): ?>
                <option value="<?= $m['resident_id'] ?>"><?= htmlspecialchars($m['last_name'] . ', ' . $m['first_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Birth weight (kg)</label>
            <input type="number" step="0.01" name="birth_weight_kg" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">Birth length (cm)</label>
            <input type="number" step="0.1" name="birth_length_cm" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">Monitoring status</label>
            <select name="monitoring_status" class="form-select">
              <option value="Normal">Normal</option>
              <option value="Underweight">Underweight</option>
              <option value="At risk">At risk</option>
            </select>
          </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Register infant</button>
      </form>
    </div>
  </div>

  <h5>All infants (0–12 months)</h5>
  <table class="table table-striped">
    <thead>
      <tr><th>Name</th><th>Birth date</th><th>Purok</th><th>Mother</th><th>Status</th><th>FIC status</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($infants as $i): $fic = getFicStatus($pdo, $i['infant_record_id'], $i['birth_date'], $epi_schedule); ?>
      <tr>
        <td><?= htmlspecialchars($i['last_name'] . ', ' . $i['first_name']) ?></td>
        <td><?= htmlspecialchars($i['birth_date']) ?></td>
        <td>Purok <?= htmlspecialchars($i['purok']) ?></td>
        <td><?= $i['mother_last'] ? htmlspecialchars($i['mother_last'] . ', ' . $i['mother_first']) : '—' ?></td>
        <td><?= htmlspecialchars($i['monitoring_status']) ?></td>
        <td><span class="badge bg-<?= $fic['badge'] ?>"><?= htmlspecialchars($fic['label']) ?></span></td>
        <td><a href="growth.php?id=<?= $i['infant_record_id'] ?>" class="btn btn-sm btn-outline-primary">Growth records</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>