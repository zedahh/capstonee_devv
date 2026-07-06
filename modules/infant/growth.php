<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';

$infant_record_id = (int) ($_GET['id'] ?? 0);
$error = '';
$success = '';

// Get the infant record + resident info
$stmt = $pdo->prepare("
    SELECT infant_records.*, r.first_name, r.last_name, r.birth_date, r.purok
    FROM infant_records
    JOIN residents r ON infant_records.resident_id = r.resident_id
    WHERE infant_record_id = ?
");
$stmt->execute([$infant_record_id]);
$infant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$infant) {
    die('Infant record not found.');
}

// Handle new growth entry
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $visit_date = $_POST['visit_date'] ?? '';
    $weight_kg = $_POST['weight_kg'] ?? '';
    $height_cm = $_POST['height_cm'] ?: null;
    $notes = trim($_POST['notes'] ?? '');

    if ($visit_date === '' || $weight_kg === '') {
        $error = 'Visit date and weight are required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO growth_monitoring 
            (infant_record_id, visit_date, weight_kg, height_cm, notes, recorded_by)
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$infant_record_id, $visit_date, $weight_kg, $height_cm, $notes, $_SESSION['user_id']]);

        $new_id = $pdo->lastInsertId();
        $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'INSERT', 'growth_monitoring', ?, 'Recorded growth visit')");
        $log->execute([$_SESSION['user_id'], $new_id]);

        $success = 'Growth visit recorded successfully.';
    }
}

// List all growth visits for this infant
$visits = $pdo->prepare("SELECT * FROM growth_monitoring WHERE infant_record_id = ? ORDER BY visit_date DESC");
$visits->execute([$infant_record_id]);
$visits = $visits->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Growth Monitoring</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Growth monitoring — <?= htmlspecialchars($infant['first_name'] . ' ' . $infant['last_name']) ?></h3>
    <a href="infant.php" class="btn btn-outline-secondary btn-sm">Back to infant list</a>
  </div>

  <p class="text-muted">Purok <?= htmlspecialchars($infant['purok']) ?> · Born <?= htmlspecialchars($infant['birth_date']) ?> · Status: <?= htmlspecialchars($infant['monitoring_status']) ?></p>

  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Record a growth visit</h5>
      <form method="POST" action="">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Visit date</label>
            <input type="date" name="visit_date" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Weight (kg)</label>
            <input type="number" step="0.01" name="weight_kg" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Height (cm)</label>
            <input type="number" step="0.1" name="height_cm" class="form-control">
          </div>
          <div class="col-md-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Add visit</button>
      </form>
    </div>
  </div>

  <h5>Growth history</h5>
  <table class="table table-striped">
    <thead>
      <tr><th>Visit date</th><th>Weight (kg)</th><th>Height (cm)</th><th>Notes</th></tr>
    </thead>
    <tbody>
      <?php foreach ($visits as $v): ?>
      <tr>
        <td><?= htmlspecialchars($v['visit_date']) ?></td>
        <td><?= htmlspecialchars($v['weight_kg']) ?></td>
        <td><?= htmlspecialchars($v['height_cm']) ?></td>
        <td><?= htmlspecialchars($v['notes']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>