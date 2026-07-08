<?php if (!isset($contacts)) { return; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LGU Contacts</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/css/custom.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>LGU / Barangay Office Contacts</h3>
    <a href="../dashboard/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
  </div>

  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Add contact</h5>
      <form method="POST" action="">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Name</label>
            <input type="text" name="contact_name" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Designation</label>
            <input type="text" name="designation" class="form-control" placeholder="e.g. Punong Barangay">
          </div>
          <div class="col-md-4">
            <label class="form-label">Phone number</label>
            <input type="text" name="phone_number" class="form-control" placeholder="09XXXXXXXXX" required>
          </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Add contact</button>
      </form>
    </div>
  </div>

  <h5>Current contacts</h5>
  <table class="table table-striped">
    <thead><tr><th>Name</th><th>Designation</th><th>Phone</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($contacts as $c): ?>
      <tr>
        <td><?= htmlspecialchars($c['contact_name']) ?></td>
        <td><?= htmlspecialchars($c['designation']) ?></td>
        <td><?= htmlspecialchars($c['phone_number']) ?></td>
        <td><a href="?delete=<?= $c['contact_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this contact?')">Remove</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>