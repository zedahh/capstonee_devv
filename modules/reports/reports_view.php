<?php if (!isset($insights)) { return; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reports</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/css/custom.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Reports Generation</h3>
    <a href="../dashboard/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <h5>Key insights</h5>
      <p class="text-muted small mb-2">Auto-generated from recorded data. Rule-based summaries, not AI-generated predictions.</p>
      <ul>
        <?php foreach ($insights as $insight): ?>
          <li><?= htmlspecialchars($insight) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-body">
      <h5>Barangay Health Summary</h5>
      <table class="table table-sm">
        <tr><td>Total residents</td><td><?= $total_residents ?></td></tr>
        <tr><td>Active/high-risk pregnancies</td><td><?= $total_maternal ?></td></tr>
        <tr><td>Infants (0-12 months)</td><td><?= $total_infants ?></td></tr>
        <tr><td>Total vaccinations administered</td><td><?= $total_vaccinations ?></td></tr>
        <tr><td>Active disease cases</td><td><?= $total_disease_cases ?></td></tr>
      </table>

      <h6 class="mt-4">Disease cases by type</h6>
      <table class="table table-sm table-striped">
        <thead><tr><th>Disease</th><th>Total cases</th></tr></thead>
        <tbody>
          <?php foreach ($disease_breakdown as $d): ?>
          <tr><td><?= htmlspecialchars($d['disease_name']) ?></td><td><?= $d['total'] ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <h6 class="mt-4">Residents by purok</h6>
      <table class="table table-sm table-striped">
        <thead><tr><th>Purok</th><th>Total residents</th></tr></thead>
        <tbody>
          <?php foreach ($purok_breakdown as $p): ?>
          <tr><td>Purok <?= $p['purok'] ?></td><td><?= $p['total'] ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <a href="generate_pdf.php" class="btn btn-primary mt-3" target="_blank">Download as PDF</a>
    </div>
  </div>
</div>
</body>
</html>