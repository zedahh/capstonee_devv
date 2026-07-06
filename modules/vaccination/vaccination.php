<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';

$error = '';
$success = '';

// Handle new vaccination entry
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $infant_record_id = $_POST['infant_record_id'] ?? '';
    $vaccine_name = trim($_POST['vaccine_name'] ?? '');
    $date_administered = $_POST['date_administered'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if ($infant_record_id === '' || $vaccine_name === '' || $date_administered === '') {
        $error = 'Please select an infant, vaccine name, and date administered.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO vaccination_records 
            (infant_record_id, vaccine_name, date_administered, administered_by, notes)
            VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$infant_record_id, $vaccine_name, $date_administered, $_SESSION['user_id'], $notes]);

        $new_id = $pdo->lastInsertId();
        $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'INSERT', 'vaccination_records', ?, ?)");
        $log->execute([$_SESSION['user_id'], $new_id, "Recorded vaccine: $vaccine_name"]);

        $success = 'Vaccination record added successfully.';
    }
}

// Dropdown: all infants
$infants = $pdo->query("
    SELECT infant_records.infant_record_id, r.first_name, r.last_name, r.purok
    FROM infant_records
    JOIN residents r ON infant_records.resident_id = r.resident_id
    ORDER BY r.last_name
")->fetchAll(PDO::FETCH_ASSOC);

// List all vaccination records, joined with infant + health worker info
$records = $pdo->query("
    SELECT vaccination_records.*, r.first_name, r.last_name, r.purok, u.full_name AS worker_name
    FROM vaccination_records
    JOIN infant_records ON vaccination_records.infant_record_id = infant_records.infant_record_id
    JOIN residents r ON infant_records.resident_id = r.resident_id
    LEFT JOIN users u ON vaccination_records.administered_by = u.user_id
    ORDER BY vaccination_records.date_administered DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Vaccination Records</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <input type="text" name="vaccine_name" class="form-control" required placeholder="e.g. BCG, Hepatitis B, Penta 1">
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