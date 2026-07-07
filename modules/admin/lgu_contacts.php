<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
if ($_SESSION['role'] !== 'administrator') {
    die('Access denied. This page is for administrators only.');
}
require '../../config/database.php';

$error = '';
$success = '';

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo->prepare("UPDATE lgu_contacts SET is_active = 0 WHERE contact_id = ?")->execute([$id]);
    header('Location: lgu_contacts.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact_name = trim($_POST['contact_name'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');

    if ($contact_name === '' || $phone_number === '') {
        $error = 'Name and phone number are required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO lgu_contacts (contact_name, designation, phone_number) VALUES (?, ?, ?)");
        $stmt->execute([$contact_name, $designation, $phone_number]);
        $success = 'Contact added successfully.';
    }
}

$contacts = $pdo->query("SELECT * FROM lgu_contacts WHERE is_active = 1 ORDER BY contact_name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LGU Contacts</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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