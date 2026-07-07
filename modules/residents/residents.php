<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';

$error = '';
$success = '';
$edit_resident = null;

// Handle delete (deactivate, not permanently erase)
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("UPDATE residents SET is_active = 0 WHERE resident_id = ?");
    $stmt->execute([$id]);

    $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'DELETE', 'residents', ?, 'Deactivated resident')");
    $log->execute([$_SESSION['user_id'], $id]);

    header('Location: residents.php');
    exit;
}

// Load a resident into the form for editing
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM residents WHERE resident_id = ?");
    $stmt->execute([$id]);
    $edit_resident = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Handle add or update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resident_id = $_POST['resident_id'] ?? '';
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $suffix = trim($_POST['suffix'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $purok = $_POST['purok'] ?? '';
    $address_line = trim($_POST['address_line'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');

    if ($first_name === '' || $last_name === '' || $birth_date === '' || $gender === '' || $purok === '') {
        $error = 'Please fill in all required fields.';
    } elseif ($resident_id !== '') {
        $stmt = $pdo->prepare("UPDATE residents SET first_name=?, middle_name=?, last_name=?, suffix=?, birth_date=?, gender=?, purok=?, address_line=?, contact_number=? WHERE resident_id=?");
        $stmt->execute([$first_name, $middle_name, $last_name, $suffix, $birth_date, $gender, $purok, $address_line, $contact_number, $resident_id]);

        $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'UPDATE', 'residents', ?, ?)");
        $log->execute([$_SESSION['user_id'], $resident_id, "Updated resident: $first_name $last_name"]);

        $success = 'Resident updated successfully.';
    } else {
        $qr_code = 'RES-' . bin2hex(random_bytes(12));
        $stmt = $pdo->prepare("INSERT INTO residents 
            (qr_code, first_name, middle_name, last_name, suffix, birth_date, gender, purok, address_line, contact_number)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$qr_code, $first_name, $middle_name, $last_name, $suffix, $birth_date, $gender, $purok, $address_line, $contact_number]);

        $new_id = $pdo->lastInsertId();
        $log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, details) VALUES (?, 'INSERT', 'residents', ?, ?)");
        $log->execute([$_SESSION['user_id'], $new_id, "Added resident: $first_name $last_name"]);

        $success = 'Resident added successfully.';
    }
}

$residents = $pdo->query("SELECT * FROM residents WHERE is_active = 1 ORDER BY last_name, first_name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Resident Profiling</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Resident Profiling</h3>
    <a href="../dashboard/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
  </div>

  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title"><?= $edit_resident ? 'Edit resident' : 'Add new resident' ?></h5>
      <form method="POST" action="">
        <input type="hidden" name="resident_id" value="<?= htmlspecialchars($edit_resident['resident_id'] ?? '') ?>">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">First name</label>
            <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($edit_resident['first_name'] ?? '') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Middle name</label>
            <input type="text" name="middle_name" class="form-control" value="<?= htmlspecialchars($edit_resident['middle_name'] ?? '') ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Last name</label>
            <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($edit_resident['last_name'] ?? '') ?>">
          </div>
          <div class="col-md-1">
            <label class="form-label">Suffix</label>
            <input type="text" name="suffix" class="form-control" value="<?= htmlspecialchars($edit_resident['suffix'] ?? '') ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Birth date</label>
            <input type="date" name="birth_date" class="form-control" required value="<?= htmlspecialchars($edit_resident['birth_date'] ?? '') ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select" required>
              <option value="">Select</option>
              <option value="Male" <?= ($edit_resident['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
              <option value="Female" <?= ($edit_resident['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Purok</label>
            <select name="purok" class="form-select" required>
              <option value="">Select</option>
              <?php for ($p = 1; $p <= 4; $p++): ?>
              <option value="<?= $p ?>" <?= (string)($edit_resident['purok'] ?? '') === (string)$p ? 'selected' : '' ?>>Purok <?= $p ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Contact number</label>
            <input type="text" name="contact_number" class="form-control" value="<?= htmlspecialchars($edit_resident['contact_number'] ?? '') ?>">
          </div>
          <div class="col-md-12">
            <label class="form-label">Address</label>
            <input type="text" name="address_line" class="form-control" value="<?= htmlspecialchars($edit_resident['address_line'] ?? '') ?>">
          </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3"><?= $edit_resident ? 'Update resident' : 'Add resident' ?></button>
        <?php if ($edit_resident): ?>
          <a href="residents.php" class="btn btn-outline-secondary mt-3">Cancel edit</a>
        <?php endif; ?>
      </form>
    </div>
  </div>

<input type="text" id="liveSearch" class="form-control form-control-sm mb-3" style="max-width:300px;" placeholder="Type to search by name...">

  <h5>All residents</h5>
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Name</th><th>Birth date</th><th>Gender</th><th>Purok</th><th>Contact</th><th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($residents as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['last_name'] . ', ' . $r['first_name'] . ' ' . $r['middle_name']) ?></td>
        <td><?= htmlspecialchars($r['birth_date']) ?></td>
        <td><?= htmlspecialchars($r['gender']) ?></td>
        <td>Purok <?= htmlspecialchars($r['purok']) ?></td>
        <td><?= htmlspecialchars($r['contact_number']) ?></td>
        <td>
          <button type="button" class="btn btn-sm btn-outline-success"
          onclick="showQr('<?= htmlspecialchars($r['qr_code']) ?>', '<?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?>')">View QR</button>
          <a href="?edit=<?= $r['resident_id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
          <a href="?delete=<?= $r['resident_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deactivate this resident?')">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- QR modal -->
<div class="modal fade" id="qrModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4">
      <h5 id="qrResidentName" class="mb-3"></h5>
      <div id="qrCanvas" class="d-flex justify-content-center mb-3"></div>
      <div>
        <button class="btn btn-primary btn-sm" onclick="window.print()">Print</button>
        <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
let qrModalInstance = null;

function showQr(code, name) {
    document.getElementById('qrResidentName').innerText = name;
    document.getElementById('qrCanvas').innerHTML = '';
    new QRCode(document.getElementById('qrCanvas'), {
        text: code,
        width: 200,
        height: 200
    });
    if (!qrModalInstance) {
        qrModalInstance = new bootstrap.Modal(document.getElementById('qrModal'));
    }
    qrModalInstance.show();
}
</script>
<script>
document.getElementById('liveSearch').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    const rows = document.querySelectorAll('table tbody tr');
    rows.forEach(function(row) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
});
</script>
</body>
</html>