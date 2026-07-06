<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';

$total_residents = $pdo->query("SELECT COUNT(*) FROM residents WHERE is_active = 1")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></h3>
    <?php if ($_SESSION['role'] === 'administrator'): ?>
  <a href="../admin/audit_log.php" class="btn btn-outline-secondary btn-sm me-2">Audit log</a>
<?php endif; ?>
<a href="../auth/logout.php" class="btn btn-outline-danger btn-sm">Log out</a>
  </div>
  <div class="row g-3">
    <div class="col-md-3"><div class="card p-3 text-center"><h5>Total residents</h5><p class="fs-3 mb-0"><?= $total_residents ?></p></div></div>
    <div class="col-md-3"><div class="card p-3 text-center"><h5>Pregnant women</h5><p class="fs-3 mb-0">--</p></div></div>
    <div class="col-md-3"><div class="card p-3 text-center"><h5>Infants 0-12mo</h5><p class="fs-3 mb-0">--</p></div></div>
    <div class="col-md-3"><div class="card p-3 text-center"><h5>Disease cases</h5><p class="fs-3 mb-0">--</p></div></div>
  </div>
  <div class="mt-4">
    <a href="../residents/residents.php" class="btn btn-primary btn-sm">Go to Resident Profiling</a>
  </div>
</div>
</body>
</html>