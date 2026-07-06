<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';

$total_residents = $pdo->query("SELECT COUNT(*) FROM residents WHERE is_active = 1")->fetchColumn();
$total_maternal = $pdo->query("SELECT COUNT(*) FROM maternal_records WHERE monitoring_status IN ('Ongoing', 'High-risk')")->fetchColumn();
$total_infants = $pdo->query("SELECT COUNT(*) FROM infant_records ir JOIN residents r ON ir.resident_id = r.resident_id WHERE r.birth_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)")->fetchColumn();
$total_vaccinations = $pdo->query("SELECT COUNT(*) FROM vaccination_records")->fetchColumn();
$total_disease_cases = $pdo->query("SELECT COUNT(*) FROM disease_cases WHERE status IN ('Active', 'Under monitoring')")->fetchColumn();

$disease_breakdown = $pdo->query("
    SELECT disease_name, COUNT(*) as total
    FROM disease_cases
    GROUP BY disease_name
    ORDER BY total DESC
")->fetchAll(PDO::FETCH_ASSOC);

$purok_breakdown = $pdo->query("
    SELECT r.purok, COUNT(*) as total
    FROM residents r
    WHERE r.is_active = 1
    GROUP BY r.purok
    ORDER BY r.purok
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reports</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Reports Generation</h3>
    <a href="../dashboard/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
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