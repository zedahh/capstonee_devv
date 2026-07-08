<?php if (!isset($error)) { return; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - Barangay Santa Ines Health System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/css/custom.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center" style="min-height:100vh;">
  <div class="card shadow-sm" style="width:100%; max-width:400px;">
    <div class="card-body p-4">
      <h4 class="text-center mb-3">Barangay Santa Ines</h4>
      <p class="text-center text-muted mb-4">Health Monitoring System</p>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Log in</button>
      </form>
      <p class="text-center mt-3 mb-0"><a href="../residents/my_record.php">Resident? Check your own health record here</a></p>
    </div>
  </div>
</div>
</body>
</html>