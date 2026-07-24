<?php if (!isset($error)) { return; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - Barangay Santa Ines Health System</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/css/custom.css" rel="stylesheet">
<style>
  body.bg-light {
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  }
  .login-bg {
    position: relative;
    min-height: 100vh;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    background: linear-gradient(160deg, #eef4fc 0%, #eef6fb 50%, #eaf2fb 100%);
  }
  .login-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    pointer-events: none;
    animation: bhmsFloat 10s ease-in-out infinite;
  }
  .login-blob-1 { width: 420px; height: 420px; background: #1B5FC0; top: -140px; left: -120px; opacity: 0.28; }
  .login-blob-2 { width: 380px; height: 380px; background: #4C8DF6; bottom: -160px; right: -100px; opacity: 0.22; animation-delay: -3s; }
  .login-blob-3 { width: 220px; height: 220px; background: #123F87; top: 55%; left: 8%; opacity: 0.12; animation-delay: -6s; }
  @keyframes bhmsFloat {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(20px, -25px) scale(1.05); }
  }
  .login-wrap { position: relative; z-index: 2; width: 100%; max-width: 420px; }
  .login-glass-card {
    background: rgba(255, 255, 255, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.6);
    border-radius: 16px;
    box-shadow: 0 16px 40px rgba(30, 41, 59, 0.16);
    padding: 2.5rem 2.25rem;
    animation: bhmsCardIn 0.5s ease both;
  }
  @keyframes bhmsCardIn {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .login-icon-ring {
    width: 78px; height: 78px;
    margin: 0 auto 1.25rem;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(27,95,192,0.15), rgba(18,63,135,0.15));
    display: flex; align-items: center; justify-content: center;
  }
  .login-icon-badge {
    width: 58px; height: 58px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1B5FC0, #123F87);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    box-shadow: 0 8px 24px rgba(30, 41, 59, 0.10);
  }
  .login-title { font-weight: 600; color: #2C333A; }
  .login-subtitle { font-size: 0.85rem; }
  .login-input-group { position: relative; }
  .login-input-icon {
    position: absolute;
    left: 0.9rem; top: 50%;
    transform: translateY(-50%);
    color: #9CA6AD;
    font-size: 0.9rem;
    pointer-events: none;
  }
  .login-input { padding-left: 2.4rem !important; }
  .login-password-toggle {
    position: absolute;
    right: 0.6rem; top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #9CA6AD;
    font-size: 0.9rem;
    cursor: pointer;
    padding: 0.25rem 0.4rem;
  }
  .login-password-toggle:hover { color: #5C6670; }
  .login-submit-btn {
    background: linear-gradient(135deg, #1B5FC0, #123F87);
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.65rem 1rem;
    font-weight: 600;
    border-radius: 10px;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
  }
  .login-submit-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(27, 95, 192, 0.28); color: #fff; }
  .login-btn-arrow { transition: transform 0.2s ease; }
  .login-submit-btn:hover .login-btn-arrow { transform: translateX(4px); }
  .login-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: center;
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px solid #E3E7EA;
  }
  .login-badges span {
    font-size: 0.7rem;
    color: #5C6670;
    background: #EEF1F3;
    padding: 0.3rem 0.65rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
  }
  .login-badges i { color: #1B5FC0; font-size: 0.75rem; }
  .login-record-link a { font-size: 0.85rem; }
  @media (prefers-reduced-motion: reduce) {
    .login-blob, .login-glass-card { animation: none; }
  }
</style>
</head>
<body class="bg-light">
<div class="login-bg">
  <span class="login-blob login-blob-1" aria-hidden="true"></span>
  <span class="login-blob login-blob-2" aria-hidden="true"></span>
  <span class="login-blob login-blob-3" aria-hidden="true"></span>
  <div class="login-wrap">
    <div class="login-glass-card">
      <div class="login-icon-ring">
        <div class="login-icon-badge">
          <i class="fa-solid fa-notes-medical"></i>
        </div>
      </div>
      <h4 class="text-center mb-1 login-title">Barangay Santa Ines</h4>
      <p class="text-center text-muted mb-4 login-subtitle">Health Monitoring System</p>

      <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <div class="login-input-group">
            <i class="fa-solid fa-user login-input-icon"></i>
            <input type="text" name="username" class="form-control login-input" required autofocus placeholder="Enter your username">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <div class="login-input-group">
            <i class="fa-solid fa-lock login-input-icon"></i>
            <input type="password" name="password" id="loginPasswordField" class="form-control login-input" required placeholder="Enter your password">
            <button type="button" class="login-password-toggle" id="loginPasswordToggle" aria-label="Show password" tabindex="-1">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 login-submit-btn">
          <span>Log in</span>
          <i class="fa-solid fa-arrow-right-long login-btn-arrow"></i>
        </button>
      </form>

      <div class="login-badges">
        <span><i class="fa-solid fa-shield-halved"></i>Secure login</span>
        <span><i class="fa-solid fa-user-lock"></i>Role-based access</span>
        <span><i class="fa-solid fa-map-location-dot"></i>Purok-level privacy</span>
      </div>

      <p class="text-center mt-4 mb-0 login-record-link"><a href="../residents/my_record.php"><i class="fa-solid fa-id-card me-1"></i> Resident? Check your own health record here</a></p>
    </div>
  </div>
</div>
<script>
document.getElementById('loginPasswordToggle').addEventListener('click', function () {
  var field = document.getElementById('loginPasswordField');
  var icon = this.querySelector('i');
  if (field.type === 'password') {
    field.type = 'text';
    icon.classList.remove('fa-eye');
    icon.classList.add('fa-eye-slash');
  } else {
    field.type = 'password';
    icon.classList.remove('fa-eye-slash');
    icon.classList.add('fa-eye');
  }
});
</script>
</body>
</html>