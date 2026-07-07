<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';

$error = '';
$success = '';

// Handle new infant registration (creates a resident + infant record together)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $suffix = trim($_POST['suffix'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $purok = $_POST['purok'] ?? '';
    $mother_resident_id = $_POST['mother_resident_id'] ?: null;
    $birth_weight_kg = $_POST['birth_weight_kg'] ?: null;
    $birth_length_cm = $_POST['birth_length_cm'] ?: null;
    $monitoring_status = $_POST['monitoring_status'] ?? 'Normal';

   if ($first_name === '' || $last_name === '' || $birth_date === '' || $gender === '' || $purok === '') {
        $error = 'Please fill in all required fields.';
    } elseif ($birth_weight_kg !== null && $birth_weight_kg !== '' && ($birth_weight_kg < 0.3 || $birth_weight_kg > 7)) {
        $error = 'Birth weight should realistically be between 0.3 kg and 7 kg. Please check the value entered.';
    } elseif ($birth_length_cm !== null && $birth_length_cm !== '' && ($birth_length_cm < 25 || $birth_length_cm > 60)) {
        $error = 'Birth length should realistically be between 25 cm and 60 cm. Please check the value entered.';
    } else {
        // Step 1: create the resident record for this infant
        $qr_code = uniqid('RES-', true);
        $stmt = $pdo->prepare("INSERT INTO residents 
            (qr_code, first_name, middle_name, last_name, suffix, birth_date, gender, purok)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$qr_code, $first_name, $middle_name, $last_name, $suffix, $birth_date, $gender, $purok]);
        $new_resident_id = $pdo->lastInsertId();

        $log1 = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'INSERT', 'residents', ?, ?)");
        $log1->execute([$_SESSION['user_id'], $new_resident_id, "Added infant resident: $first_name $last_name"]);

        // Step 2: create the infant record linked to that resident
        $stmt2 = $pdo->prepare("INSERT INTO infant_records 
            (resident_id, mother_resident_id, birth_weight_kg, birth_length_cm, monitoring_status)
            VALUES (?, ?, ?, ?, ?)");
        $stmt2->execute([$new_resident_id, $mother_resident_id, $birth_weight_kg, $birth_length_cm, $monitoring_status]);
        $new_infant_id = $pdo->lastInsertId();

        $log2 = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'INSERT', 'infant_records', ?, 'Registered infant monitoring record')");
        $log2->execute([$_SESSION['user_id'], $new_infant_id]);

        $success = 'Infant registered successfully.';
    }
}

// Dropdown: female residents, to optionally link as mother
$mothers = $pdo->query("SELECT resident_id, first_name, last_name FROM residents WHERE gender = 'Female' AND is_active = 1 ORDER BY last_name")->fetchAll(PDO::FETCH_ASSOC);



require '../../includes/functions.php';

// Fully Immunized Child (FIC) status check against the DOH EPI schedule
$epi_schedule = $pdo->query("SELECT * FROM epi_schedule")->fetchAll(PDO::FETCH_ASSOC);


// List all infants with resident + mother info
$infants = $pdo->query("
    SELECT infant_records.*, r.first_name, r.last_name, r.birth_date, r.purok,
           m.first_name AS mother_first, m.last_name AS mother_last
    FROM infant_records
    JOIN residents r ON infant_records.resident_id = r.resident_id
    LEFT JOIN residents m ON infant_records.mother_resident_id = m.resident_id
    ORDER BY r.birth_date DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Infant Monitoring</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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