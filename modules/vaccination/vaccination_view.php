<?php
/** @var array $epi_vaccines */
/** @var string $error */
/** @var array $infants */
/** @var array $records */
/** @var string $success */
if (!isset($records)) { return; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Vaccination Records</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/css/custom.css" rel="stylesheet">
<style>
  :root {
    --bhms-green: #2E7D52;
    --bhms-green-dark: #1F5C3B;
    --bhms-green-darker: #164430;
    --bhms-green-light: #E6F4EC;
    --bhms-blue: #185FA5;
    --bhms-blue-dark: #0F477F;
    --bhms-gray-50: #F7F9FA;
    --bhms-gray-100: #EEF1F3;
    --bhms-gray-200: #E3E7EA;
    --bhms-gray-300: #D3D9DE;
    --bhms-gray-400: #9CA6AD;
    --bhms-gray-600: #5C6670;
    --bhms-gray-800: #2C333A;
    --bhms-danger: #D64545;
    --bhms-danger-light: #FBEAEA;
    --bhms-success-light: #E6F4EC;
    --bhms-radius-lg: 16px;
    --bhms-radius: 12px;
    --bhms-radius-sm: 8px;
    --bhms-shadow-sm: 0 1px 3px rgba(30,41,59,0.06), 0 1px 2px rgba(30,41,59,0.08);
    --bhms-shadow-md: 0 8px 24px rgba(30,41,59,0.10);
    --bhms-sidebar-width: 264px;
    --bhms-topbar-height: 68px;
  }

  body.bhms-app-body {
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: var(--bhms-gray-800);
    background: linear-gradient(160deg, #f4faf7 0%, #f7f9fa 45%, #eaf2fb 100%);
    -webkit-font-smoothing: antialiased;
  }
  h1,h2,h3,h4,h5,h6 { font-family: 'Poppins', sans-serif; font-weight: 600; color: var(--bhms-gray-800); }
  a { color: var(--bhms-blue); text-decoration: none; }
  a:hover { color: var(--bhms-blue-dark); }

  /* ---- App shell: sidebar + topbar ---- */
  .bhms-shell { display: flex; min-height: 100vh; }
  .bhms-sidebar-checkbox { display: none; }
  .bhms-sidebar {
    width: var(--bhms-sidebar-width);
    position: fixed; top: 0; left: 0; bottom: 0;
    display: flex; flex-direction: column;
    background: linear-gradient(180deg, var(--bhms-green-darker) 0%, var(--bhms-green-dark) 55%, var(--bhms-green) 100%);
    color: #fff; z-index: 1030;
    transition: transform 0.25s ease;
  }
  .bhms-sidebar-brand { display: flex; align-items: center; gap: 0.8rem; padding: 1.5rem 1.35rem; border-bottom: 1px solid rgba(255,255,255,0.14); }
  .bhms-sidebar-brand i {
    font-size: 1.6rem; color: #fff; background: rgba(255,255,255,0.14);
    height: 42px; width: 42px; display: flex; align-items: center; justify-content: center;
    border-radius: var(--bhms-radius-sm); flex-shrink: 0;
  }
  .bhms-brand-title { display: block; font-weight: 600; font-size: 0.95rem; line-height: 1.25; }
  .bhms-brand-sub { display: block; font-size: 0.72rem; opacity: 0.78; line-height: 1.2; }
  .bhms-nav { flex: 1 1 auto; overflow-y: auto; padding: 1rem 0.75rem; }
  .bhms-nav-link {
    display: flex; align-items: center; gap: 0.75rem; padding: 0.62rem 0.9rem; margin-bottom: 0.2rem;
    border-radius: 10px; color: rgba(255,255,255,0.85); font-size: 0.885rem; font-weight: 500;
    transition: background 0.15s ease, color 0.15s ease;
  }
  .bhms-nav-link i { width: 18px; text-align: center; font-size: 0.95rem; }
  .bhms-nav-link:hover { background: rgba(255,255,255,0.12); color: #fff; }
  .bhms-nav-link.active { background: #fff; color: var(--bhms-green-dark); font-weight: 600; box-shadow: var(--bhms-shadow-sm); }
  .bhms-nav-divider { font-size: 0.66rem; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.6; padding: 0.85rem 0.9rem 0.3rem; }
  .bhms-sidebar-footer { padding: 0.85rem 0.75rem; border-top: 1px solid rgba(255,255,255,0.14); }
  .bhms-logout-link:hover { background: rgba(214,69,69,0.4); }
  .bhms-overlay { display: none; position: fixed; inset: 0; background: rgba(20,24,28,0.45); z-index: 1020; }
  .bhms-main { flex: 1 1 auto; margin-left: var(--bhms-sidebar-width); display: flex; flex-direction: column; min-height: 100vh; min-width: 0; }
  .bhms-topbar {
    height: var(--bhms-topbar-height); background: #fff; border-bottom: 1px solid var(--bhms-gray-200);
    display: flex; align-items: center; gap: 1rem; padding: 0 1.5rem; position: sticky; top: 0; z-index: 900;
  }
  .bhms-menu-btn { display: none; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: var(--bhms-radius-sm); color: var(--bhms-green-dark); font-size: 1.05rem; cursor: pointer; flex-shrink: 0; }
  .bhms-menu-btn:hover { background: var(--bhms-gray-100); }
  .bhms-topbar-title { font-weight: 600; font-size: 1.02rem; color: var(--bhms-gray-800); flex: 1 1 auto; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .bhms-topbar-user { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--bhms-gray-600); white-space: nowrap; }
  .bhms-topbar-user i { font-size: 1.3rem; color: var(--bhms-gray-400); }
  .bhms-topbar-user .bhms-role-pill { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.04em; background: var(--bhms-green-light); color: var(--bhms-green-dark); padding: 0.15rem 0.55rem; border-radius: 999px; font-weight: 600; }
  .bhms-content { flex: 1 1 auto; padding-bottom: 2rem; }

  @media (max-width: 992px) {
    .bhms-sidebar { transform: translateX(-100%); }
    .bhms-sidebar-checkbox:checked ~ .bhms-sidebar { transform: translateX(0); }
    .bhms-sidebar-checkbox:checked ~ .bhms-overlay { display: block; }
    .bhms-main { margin-left: 0; }
    .bhms-menu-btn { display: flex; }
    .bhms-topbar-title { font-size: 0.95rem; }
  }
  @media (max-width: 576px) {
    .bhms-topbar-user span:not(.bhms-role-pill) { display: none; }
  }

  
  .card { border: 1px solid var(--bhms-gray-200); border-radius: var(--bhms-radius); box-shadow: var(--bhms-shadow-sm); transition: box-shadow 0.2s ease; }
  .card:hover { box-shadow: var(--bhms-shadow-md); }
  .card-body { padding: 1.5rem; }
  .card-title { font-weight: 600; color: var(--bhms-gray-800); margin-bottom: 1rem; display: flex; align-items: center; }
  .btn { border-radius: 10px; font-weight: 500; padding: 0.5rem 1.1rem; font-size: 0.88rem; transition: transform 0.12s ease, box-shadow 0.12s ease, background-color 0.15s ease, border-color 0.15s ease; }
  .btn-sm { padding: 0.32rem 0.75rem; font-size: 0.8rem; border-radius: 8px; }
  .btn:active { transform: translateY(1px); }
  .btn-primary { background: linear-gradient(135deg, var(--bhms-green), var(--bhms-blue)); border: none; }
  .btn-primary:hover, .btn-primary:focus { filter: brightness(0.95); transform: translateY(-1px); box-shadow: var(--bhms-shadow-md); color: #fff; }
  .btn-outline-secondary { color: var(--bhms-gray-600); border-color: var(--bhms-gray-300); }
  .btn-outline-secondary:hover { background: var(--bhms-gray-600); border-color: var(--bhms-gray-600); }
  .alert { border: none; border-left: 4px solid transparent; border-radius: var(--bhms-radius-sm); font-size: 0.9rem; padding: 0.9rem 1.1rem; }
  .alert-danger { background: var(--bhms-danger-light); color: #8a2c2c; border-left-color: var(--bhms-danger); }
  .alert-success { background: var(--bhms-success-light); color: var(--bhms-green-darker); border-left-color: var(--bhms-green); }
  .form-label { font-weight: 500; font-size: 0.85rem; color: var(--bhms-gray-600); margin-bottom: 0.35rem; }
  .form-control, .form-select {
    border-radius: 10px; border: 1px solid var(--bhms-gray-300); padding: 0.55rem 0.9rem; font-size: 0.9rem;
  }
  .form-control:focus, .form-select:focus { border-color: var(--bhms-green); box-shadow: 0 0 0 3px rgba(46,125,82,0.14); }

  
  .bhms-content .container > .d-flex.justify-content-between.align-items-center.mb-4 {
    background: rgba(255,255,255,0.75);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.6);
    border-radius: var(--bhms-radius-lg);
    padding: 1.1rem 1.4rem;
    box-shadow: var(--bhms-shadow-sm);
  }
  .bhms-content h3 { margin-bottom: 0; font-size: 1.25rem; display: flex; align-items: center; }

  

  .vaccination-table-card {
    background: #fff;
    border-radius: var(--bhms-radius-lg);
    box-shadow: var(--bhms-shadow-sm);
    overflow: hidden;
  }
  .table { margin-bottom: 0; border-collapse: separate; border-spacing: 0; }
  .table thead th {
    background: linear-gradient(135deg, var(--bhms-green-light), #eaf2fb);
    color: var(--bhms-green-dark);
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
    border-bottom: none;
    padding: 0.85rem 1rem;
    white-space: nowrap;
  }
  .table td { padding: 0.7rem 1rem; vertical-align: middle; font-size: 0.88rem; border-color: var(--bhms-gray-100); }
  .table-striped > tbody > tr:nth-of-type(odd) > * { background-color: var(--bhms-gray-50); }
  .table > tbody > tr:hover > * { background-color: var(--bhms-green-light); }

  .vaccine-name-cell { display: flex; align-items: center; gap: 0.6rem; }
  .vaccine-icon {
    width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--bhms-green), var(--bhms-blue));
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem;
  }
</style>
</head>
<body class="bhms-app-body">
<div class="bhms-shell">
  <input type="checkbox" id="bhmsSidebarToggle" class="bhms-sidebar-checkbox">
  <aside class="bhms-sidebar">
    <div class="bhms-sidebar-brand">
      <i class="fa-solid fa-notes-medical"></i>
      <div>
        <span class="bhms-brand-title">Barangay Santa Ines</span>
        <span class="bhms-brand-sub">Health Monitoring System</span>
      </div>
    </div>
    <nav class="bhms-nav">
      <a href="../dashboard/dashboard.php" class="bhms-nav-link"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
      <a href="../residents/residents.php" class="bhms-nav-link"><i class="fa-solid fa-users"></i><span>Resident Profiling</span></a>
      <a href="../maternal/maternal.php" class="bhms-nav-link"><i class="fa-solid fa-person-pregnant"></i><span>Maternal Health</span></a>
      <a href="../infant/infant.php" class="bhms-nav-link"><i class="fa-solid fa-baby"></i><span>Infant Monitoring</span></a>
      <a href="../vaccination/vaccination.php" class="bhms-nav-link active"><i class="fa-solid fa-syringe"></i><span>Vaccination Records</span></a>
      <a href="../disease/disease.php" class="bhms-nav-link"><i class="fa-solid fa-virus"></i><span>Disease Recording</span></a>
      <a href="../heatmap/heatmap.php" class="bhms-nav-link"><i class="fa-solid fa-map-location-dot"></i><span>Heatmap</span></a>
      <a href="../reports/reports.php" class="bhms-nav-link"><i class="fa-solid fa-file-lines"></i><span>Reports</span></a>
      <a href="../announcements/announcements.php" class="bhms-nav-link"><i class="fa-solid fa-bullhorn"></i><span>Announcements</span></a>
      <?php if ($_SESSION['role'] === 'administrator'): ?>
      <div class="bhms-nav-divider">Admin</div>
      <a href="../admin/audit_log.php" class="bhms-nav-link"><i class="fa-solid fa-clipboard-list"></i><span>Audit Log</span></a>
      <a href="../admin/lgu_contacts.php" class="bhms-nav-link"><i class="fa-solid fa-address-book"></i><span>LGU Contacts</span></a>
      <?php endif; ?>
    </nav>
    <div class="bhms-sidebar-footer">
      <a href="../auth/logout.php" class="bhms-nav-link bhms-logout-link"><i class="fa-solid fa-right-from-bracket"></i><span>Log out</span></a>
    </div>
  </aside>
  <label for="bhmsSidebarToggle" class="bhms-overlay"></label>
  <div class="bhms-main">
    <header class="bhms-topbar">
      <label for="bhmsSidebarToggle" class="bhms-menu-btn" aria-label="Toggle navigation"><i class="fa-solid fa-bars"></i></label>
      <div class="bhms-topbar-title">Vaccination Records</div>
      <div class="bhms-topbar-user">
        <i class="fa-regular fa-circle-user"></i>
        <span><?= htmlspecialchars($_SESSION['full_name']) ?></span>
        <span class="bhms-role-pill"><?= $_SESSION['role'] === 'administrator' ? 'Administrator' : 'BHW' ?></span>
      </div>
    </header>
    <main class="bhms-content">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="fa-solid fa-syringe me-2" style="color:var(--bhms-green);"></i>Vaccination Records</h3>
    <a href="../dashboard/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
  </div>

  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title"><i class="fa-solid fa-syringe me-2"></i>Record a vaccination</h5>
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
            <select name="vaccine_name" class="form-select" required>
              <option value="">Select vaccine</option>
              <?php foreach ($epi_vaccines as $v): ?>
                <option value="<?= htmlspecialchars($v) ?>"><?= htmlspecialchars($v) ?></option>
              <?php endforeach; ?>
            </select>
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
        <button type="submit" class="btn btn-primary mt-3"><i class="fa-solid fa-plus me-2"></i>Add record</button>
      </form>
    </div>
  </div>

  <h5><i class="fa-solid fa-list-check me-2"></i>All vaccination records</h5>
  <div class="vaccination-table-card">
  <table class="table table-striped">
    <thead>
      <tr><th>Infant</th><th>Purok</th><th>Vaccine</th><th>Date given</th><th>Given by</th><th>Notes</th></tr>
    </thead>
    <tbody>
      <?php foreach ($records as $rec): ?>
      <tr>
        <td><?= htmlspecialchars($rec['last_name'] . ', ' . $rec['first_name']) ?></td>
        <td>Purok <?= htmlspecialchars($rec['purok']) ?></td>
        <td><div class="vaccine-name-cell"><div class="vaccine-icon"><i class="fa-solid fa-syringe"></i></div><?= htmlspecialchars($rec['vaccine_name']) ?></div></td>
        <td><?= htmlspecialchars($rec['date_administered']) ?></td>
        <td><?= htmlspecialchars($rec['worker_name'] ?? 'Unknown') ?></td>
        <td><?= htmlspecialchars($rec['notes']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
    </main>
  </div>
</div>
<script>
document.querySelectorAll('.bhms-nav-link').forEach(function (link) {
  link.addEventListener('click', function () {
    var cb = document.getElementById('bhmsSidebarToggle');
    if (cb) { cb.checked = false; }
  });
});
</script>
</body>
</html>