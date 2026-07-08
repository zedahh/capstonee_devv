<?php if (!isset($residents)) { return; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Resident Profiling</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/css/custom.css" rel="stylesheet">
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