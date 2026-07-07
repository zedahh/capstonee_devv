<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';
require '../../includes/functions.php';
$prenatal_schedule = $pdo->query("SELECT * FROM prenatal_visit_schedule")->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resident_id = $_POST['resident_id'] ?? '';
    $lmp_date = $_POST['lmp_date'] ?: null;
    $edd_date = $_POST['edd_date'] ?: null;
    $gravida = $_POST['gravida'] ?: null;
    $para = $_POST['para'] ?: null;
    $health_conditions = trim($_POST['health_conditions'] ?? '');
    $monitoring_status = $_POST['monitoring_status'] ?? 'Ongoing';

    if ($resident_id === '') {
        $error = 'Please select a resident.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO maternal_records 
            (resident_id, lmp_date, edd_date, gravida, para, health_conditions, monitoring_status)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$resident_id, $lmp_date, $edd_date, $gravida, $para, $health_conditions, $monitoring_status]);

        $new_id = $pdo->lastInsertId();
        $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'INSERT', 'maternal_records', ?, 'Registered maternal record')");
        $log->execute([$_SESSION['user_id'], $new_id]);

        $success = 'Maternal record added successfully.';
    }
}

$female_residents = $pdo->query("SELECT resident_id, first_name, last_name, purok FROM residents WHERE gender = 'Female' AND is_active = 1 ORDER BY last_name")->fetchAll(PDO::FETCH_ASSOC);

$records = $pdo->query("
    SELECT maternal_records.*, residents.first_name, residents.last_name, residents.purok
    FROM maternal_records
    JOIN residents ON maternal_records.resident_id = residents.resident_id
    ORDER BY maternal_records.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Maternal Health Monitoring</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Maternal Health Monitoring</h3>
    <a href="../dashboard/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
  </div>

  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Register pregnant resident</h5>
      <form method="POST" action="">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Resident</label>
            <select name="resident_id" class="form-select" required>
              <option value="">Select resident</option>
              <?php foreach ($female_residents as $fr): ?>
                <option value="<?= $fr['resident_id'] ?>"><?= htmlspecialchars($fr['last_name'] . ', ' . $fr['first_name']) ?> (Purok <?= $fr['purok'] ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Last menstrual period (LMP)</label>
            <input type="date" name="lmp_date" id="lmp_date" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Expected delivery date (EDD)</label>
            <input type="date" name="edd_date" id="edd_date" class="form-control">
          </div>
          <div class="col-md-2">
            <label class="form-label">Gravida</label>
            <input type="number" name="gravida" class="form-control" min="0">
          </div>
          <div class="col-md-2">
            <label class="form-label">Para</label>
            <input type="number" name="para" class="form-control" min="0">
          </div>
          <div class="col-md-4">
            <label class="form-label">Monitoring status</label>
            <select name="monitoring_status" class="form-select">
              <option value="Ongoing">Ongoing</option>
              <option value="High-risk">High-risk</option>
              <option value="Delivered">Delivered</option>
              <option value="Postpartum">Postpartum</option>
            </select>
          </div>
          <div class="col-md-12">
            <label class="form-label">Health conditions / notes</label>
            <textarea name="health_conditions" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Add record</button>
      </form>
    </div>
  </div>

  <h5>All maternal records</h5>
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Name</th><th>Purok</th><th>LMP</th><th>EDD</th><th>Status</th><th>Visit compliance</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($records as $rec): $compliance = getPrenatalComplianceStatus($pdo, $rec['maternal_record_id'], $rec['lmp_date'], $rec['monitoring_status'], $prenatal_schedule); ?>
      <tr>
        <td><?= htmlspecialchars($rec['last_name'] . ', ' . $rec['first_name']) ?></td>
        <td>Purok <?= htmlspecialchars($rec['purok']) ?></td>
        <td><?= htmlspecialchars($rec['lmp_date']) ?></td>
        <td><?= htmlspecialchars($rec['edd_date']) ?></td>
        <td><?= htmlspecialchars($rec['monitoring_status']) ?></td>
        <td><span class="badge bg-<?= $compliance['badge'] ?>"><?= htmlspecialchars($compliance['label']) ?></span></td>
        <td><a href="checkups.php?id=<?= $rec['maternal_record_id'] ?>" class="btn btn-sm btn-outline-primary">View checkups</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
document.getElementById('lmp_date').addEventListener('change', function() {
    if (this.value && !document.getElementById('edd_date').value) {
        const lmp = new Date(this.value);
        lmp.setDate(lmp.getDate() + 280);
        document.getElementById('edd_date').value = lmp.toISOString().split('T')[0];
    }
});
</script>
</body>
</html>