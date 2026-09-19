<?php if (!isset($archived_items)) { return; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/png" href="../../assets/images/barangay_logo.png">
<title>Archive</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/css/custom.css" rel="stylesheet">
<style>
  :root {
    --bhms-blue: #1B5FC0;
    --bhms-blue-dark: #123F87;
    --bhms-blue-darker: #0B2C61;
    --bhms-blue-light: #EAF2FF;
    --bhms-success: #2E7D52;
    --bhms-success-darker: #164430;
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
    --bhms-shadow-lg: 0 16px 40px rgba(30,41,59,0.16);
    --bhms-sidebar-width: 264px;
    --bhms-topbar-height: 68px;
  }

  body.bhms-app-body {
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: var(--bhms-gray-800);
    background: linear-gradient(160deg, #eef4fc 0%, #f7f9fa 45%, #eaf2fb 100%);
    -webkit-font-smoothing: antialiased;
  }
  h1,h2,h3,h4,h5,h6 { font-family: 'Poppins', sans-serif; font-weight: 600; color: var(--bhms-gray-800); }
  a { color: var(--bhms-blue); text-decoration: none; }
  a:hover { color: var(--bhms-blue-dark); }

  .bhms-shell { display: flex; min-height: 100vh; }
  .bhms-sidebar-checkbox { display: none; }
  .bhms-sidebar {
    width: var(--bhms-sidebar-width);
    position: fixed; top: 0; left: 0; bottom: 0;
    display: flex; flex-direction: column;
    background: linear-gradient(180deg, var(--bhms-blue-darker) 0%, var(--bhms-blue-dark) 55%, var(--bhms-blue) 100%);
    color: #fff; z-index: 1030;
    transition: transform 0.25s ease;
  }

.bhms-nav::-webkit-scrollbar {
  width: 10px;
}
.bhms-nav::-webkit-scrollbar-track {
  background: transparent;
  border-radius: 999px;
}
.bhms-nav::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,0.25);
  border-radius: 999px;
  border: 2px solid transparent;
  background-clip: padding-box;
  transition: background 0.2s ease;
}
.bhms-nav::-webkit-scrollbar-thumb:hover {
  background: rgba(255,255,255,0.6);
  background-clip: padding-box;
}
.bhms-nav::-webkit-scrollbar-button {
  display: block;
  height: 12px;
  background-color: transparent;
  background-repeat: no-repeat;
  background-position: center;
  background-size: 7px;
  transition: background-color 0.2s ease;
}
.bhms-nav::-webkit-scrollbar-button:hover {
  background-color: rgba(255,255,255,0.15);
}
.bhms-nav::-webkit-scrollbar-button:vertical:start:decrement {
  border-radius: 999px 999px 0 0;
  background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path fill='white' fill-opacity='0.6' d='M12 6l7 8H5z'/></svg>");
}
.bhms-nav::-webkit-scrollbar-button:vertical:end:increment {
  border-radius: 0 0 999px 999px;
  background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'><path fill='white' fill-opacity='0.6' d='M12 18l-7-8h14z'/></svg>");
}
  .bhms-sidebar-brand { display: flex; align-items: center; gap: 0.8rem; padding: 1.5rem 1.35rem; border-bottom: 1px solid rgba(255,255,255,0.14); }
  .bhms-sidebar-brand i, .bhms-sidebar-brand img.brand-logo {
    font-size: 1.6rem; color: #fff; background: rgba(255,255,255,0.14);
    height: 42px; width: 42px; display: flex; align-items: center; justify-content: center;
    border-radius: var(--bhms-radius-sm); flex-shrink: 0;
  }
  .bhms-sidebar-brand img.brand-logo { object-fit: cover; border-radius: 50%; padding: 2px; }
  .bhms-brand-title { display: block; font-weight: 600; font-size: 0.95rem; line-height: 1.25; }
  .bhms-brand-sub { display: block; font-size: 0.78rem; opacity: 0.85; line-height: 1.2; }
  .bhms-nav { flex: 1 1 auto; overflow-y: auto; padding: 1rem 0.75rem; }
  .bhms-nav-link {
    display: flex; align-items: center; gap: 0.75rem; padding: 0.62rem 0.9rem; margin-bottom: 0.2rem;
    border-radius: 10px; color: rgba(255,255,255,0.85); font-size: 0.885rem; font-weight: 500;
    transition: background 0.15s ease, color 0.15s ease;
  }
  .bhms-nav-link i { width: 18px; text-align: center; font-size: 0.95rem; }
  .bhms-nav-link:hover { background: rgba(255,255,255,0.12); color: #fff; }
  .bhms-nav-link.active { background: #fff; color: var(--bhms-blue-dark); font-weight: 600; box-shadow: var(--bhms-shadow-sm); }
  .bhms-nav-divider { font-size: 0.66rem; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.6; padding: 0.85rem 0.9rem 0.3rem; }
  .bhms-sidebar-footer { padding: 0.85rem 0.75rem; border-top: 1px solid rgba(255,255,255,0.14); }
  .bhms-logout-link:hover { background: rgba(214,69,69,0.4); }
  .bhms-overlay { display: none; position: fixed; inset: 0; background: rgba(20,24,28,0.45); z-index: 1020; }
  .bhms-main { flex: 1 1 auto; margin-left: var(--bhms-sidebar-width); display: flex; flex-direction: column; min-height: 100vh; min-width: 0; }
  .bhms-topbar {
    height: var(--bhms-topbar-height); background: #fff; border-bottom: 1px solid var(--bhms-gray-200);
    display: flex; align-items: center; gap: 1rem; padding: 0 1.5rem; position: sticky; top: 0; z-index: 900;
  }
  .bhms-menu-btn { display: none; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: var(--bhms-radius-sm); color: var(--bhms-blue-dark); font-size: 1.05rem; cursor: pointer; flex-shrink: 0; }
  .bhms-menu-btn:hover { background: var(--bhms-gray-100); }
  .bhms-topbar-title { font-weight: 600; font-size: 1.02rem; color: var(--bhms-gray-800); flex: 1 1 auto; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .bhms-topbar-user { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--bhms-gray-600); white-space: nowrap; }
  .bhms-topbar-user i { font-size: 1.3rem; color: var(--bhms-gray-400); }
  .bhms-topbar-user .bhms-role-pill { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.04em; background: var(--bhms-blue-light); color: var(--bhms-blue-dark); padding: 0.15rem 0.55rem; border-radius: 999px; font-weight: 600; }
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

  .card { border: 1px solid var(--bhms-gray-200); border-radius: var(--bhms-radius); box-shadow: var(--bhms-shadow-sm); }
  .card-body { padding: 1.5rem; }
  .btn { border-radius: 10px; font-weight: 500; padding: 0.5rem 1.1rem; font-size: 0.88rem; }
  .btn-sm { padding: 0.32rem 0.75rem; font-size: 0.8rem; border-radius: 8px; }
  .btn-outline-secondary { color: var(--bhms-gray-600); border-color: var(--bhms-gray-300); }
  .btn-outline-secondary:hover { background: var(--bhms-gray-600); border-color: var(--bhms-gray-600); }
  .btn-outline-primary { color: var(--bhms-blue-dark); border-color: var(--bhms-blue); }
  .btn-outline-primary:hover { background: var(--bhms-blue); border-color: var(--bhms-blue); }
  .btn-outline-danger { color: var(--bhms-danger); border-color: var(--bhms-danger); }
  .btn-outline-danger:hover { background: var(--bhms-danger); border-color: var(--bhms-danger); }
  .alert { border: none; border-left: 4px solid transparent; border-radius: var(--bhms-radius-sm); font-size: 0.9rem; padding: 0.9rem 1.1rem; }
  .alert-danger { background: var(--bhms-danger-light); color: #8a2c2c; border-left-color: var(--bhms-danger); }
  .alert-success { background: var(--bhms-success-light); color: var(--bhms-success-darker); border-left-color: var(--bhms-success); }
  .form-control { border-radius: 10px; border: 1px solid var(--bhms-gray-300); padding: 0.55rem 0.9rem; font-size: 0.9rem; }
  .form-control:focus { border-color: var(--bhms-blue); box-shadow: 0 0 0 3px rgba(27,95,192,0.14); }
  .modal-content { border: none; border-radius: var(--bhms-radius-lg); box-shadow: var(--bhms-shadow-lg); }

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

  .archive-table-card { background: #fff; border-radius: var(--bhms-radius-lg); box-shadow: var(--bhms-shadow-sm); overflow: hidden; }
  .table { margin-bottom: 0; border-collapse: separate; border-spacing: 0; }
  .table thead th {
    background: linear-gradient(135deg, var(--bhms-blue-light), #eaf2fb);
    color: var(--bhms-blue-dark);
    font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;
    border-bottom: none; padding: 0.85rem 1rem; white-space: nowrap;
  }
  .table td { padding: 0.7rem 1rem; vertical-align: middle; font-size: 0.88rem; border-color: var(--bhms-gray-100); }
  .table-striped > tbody > tr:nth-of-type(odd) > * { background-color: var(--bhms-gray-50); }
  .table > tbody > tr:hover > * { background-color: var(--bhms-blue-light); }
  .type-badge { font-size: 0.7rem; padding: 0.3em 0.7em; border-radius: 999px; font-weight: 600; background: var(--bhms-blue-light); color: var(--bhms-blue-dark); }

  /* Floating toast notifications (centered) */
  .bhms-toast-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(20,24,28,0.45);
    z-index: 1999;
    animation: bhmsBackdropIn 0.2s ease;
  }
  .bhms-toast-container {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 2000;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    width: calc(100% - 48px);
    max-width: 420px;
  }
  .bhms-toast {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    background: #fff;
    border-radius: var(--bhms-radius-lg);
    box-shadow: var(--bhms-shadow-md);
    padding: 1.25rem 1.4rem;
    border-left: 4px solid transparent;
    animation: bhmsToastIn 0.25s ease;
  }
  .bhms-toast-danger { border-left-color: var(--bhms-danger); }
  .bhms-toast-success { border-left-color: var(--bhms-success); }
  .bhms-toast-icon { font-size: 1.4rem; flex-shrink: 0; margin-top: 0.1rem; }
  .bhms-toast-danger .bhms-toast-icon { color: var(--bhms-danger); }
  .bhms-toast-success .bhms-toast-icon { color: var(--bhms-success); }
  .bhms-toast-body { flex: 1 1 auto; min-width: 0; }
  .bhms-toast-title { font-weight: 600; font-size: 0.95rem; margin-bottom: 0.2rem; }
  .bhms-toast-danger .bhms-toast-title { color: #8a2c2c; }
  .bhms-toast-success .bhms-toast-title { color: var(--bhms-success-darker); }
  .bhms-toast-message { font-size: 0.88rem; color: var(--bhms-gray-600); line-height: 1.45; word-break: break-word; }
  .bhms-toast-ok {
    display: block;
    margin-left: auto;
    margin-top: 0.9rem;
    border: none;
    background: linear-gradient(135deg, var(--bhms-blue), var(--bhms-blue-dark));
    color: #fff;
    font-weight: 600;
    font-size: 0.8rem;
    padding: 0.4rem 1.1rem;
    border-radius: 8px;
    cursor: pointer;
    transition: filter 0.15s ease;
  }
  .bhms-toast-ok:hover { filter: brightness(0.95); }
  .bhms-toast-content { display: flex; flex-direction: column; flex: 1 1 auto; min-width: 0; }
  .bhms-toast-row { display: flex; align-items: flex-start; gap: 0.75rem; }
  .bhms-toast.bhms-toast-hide { animation: bhmsToastOut 0.18s ease forwards; }
  .bhms-toast-backdrop.bhms-toast-hide { animation: bhmsBackdropOut 0.18s ease forwards; }
  @keyframes bhmsToastIn {
    from { opacity: 0; transform: scale(0.92); }
    to { opacity: 1; transform: scale(1); }
  }
  @keyframes bhmsToastOut {
    from { opacity: 1; transform: scale(1); }
    to { opacity: 0; transform: scale(0.92); }
  }
  @keyframes bhmsBackdropIn { from { opacity: 0; } to { opacity: 1; } }
  @keyframes bhmsBackdropOut { from { opacity: 1; } to { opacity: 0; } }
  @media (max-width: 576px) {
    .bhms-toast-container { width: calc(100% - 32px); }
  }
</style>
</head>
<body class="bhms-app-body">
<div class="bhms-shell">
  <input type="checkbox" id="bhmsSidebarToggle" class="bhms-sidebar-checkbox">
  <aside class="bhms-sidebar">
    <div class="bhms-sidebar-brand">
      <img src="../../assets/images/barangay_logo.png" alt="Barangay Santa Ines Seal" class="brand-logo">
      <div>
        <span class="bhms-brand-title">IneSight</span>
        <span class="bhms-brand-sub">Health Monitoring & Decision Support</span>
      </div>
    </div>
    <nav class="bhms-nav">
      <a href="../dashboard/dashboard.php" class="bhms-nav-link"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
      <a href="../residents/residents.php" class="bhms-nav-link"><i class="fa-solid fa-users"></i><span>Resident Profiling</span></a>
      <a href="../maternal/maternal.php" class="bhms-nav-link"><i class="fa-solid fa-person-pregnant"></i><span>Maternal Health</span></a>
      <a href="../infant/infant.php" class="bhms-nav-link"><i class="fa-solid fa-baby"></i><span>Infant Monitoring</span></a>
      <a href="../vaccination/vaccination.php" class="bhms-nav-link"><i class="fa-solid fa-syringe"></i><span>Vaccination Records</span></a>
      <a href="../disease/disease.php" class="bhms-nav-link"><i class="fa-solid fa-virus"></i><span>Disease Recording</span></a>
      <a href="../heatmap/heatmap.php" class="bhms-nav-link"><i class="fa-solid fa-map-location-dot"></i><span>Heatmap</span></a>
      <a href="../reports/reports.php" class="bhms-nav-link"><i class="fa-solid fa-file-lines"></i><span>Reports</span></a>
      <a href="../announcements/announcements.php" class="bhms-nav-link"><i class="fa-solid fa-bullhorn"></i><span>Announcements</span></a>
      <?php if ($_SESSION['role'] === 'administrator'): ?>
      <div class="bhms-nav-divider">Admin</div>
      <a href="../admin/audit_log.php" class="bhms-nav-link"><i class="fa-solid fa-clipboard-list"></i><span>Audit Log</span></a>
      <a href="../admin/lgu_contacts.php" class="bhms-nav-link"><i class="fa-solid fa-address-book"></i><span>LGU Contacts</span></a>
            <a href="../admin/user_management.php" class="bhms-nav-link"><i class="fa-solid fa-user-gear"></i><span>User Management</span></a>
      <a href="../admin/archive.php" class="bhms-nav-link active"><i class="fa-solid fa-box-archive"></i><span>Archive</span></a>
      <?php endif; ?>
    </nav>
    <div class="bhms-sidebar-footer">
      <div class="bhms-nav-divider">Account</div>
      <a href="../auth/my_account.php" class="bhms-nav-link"><i class="fa-solid fa-user-gear"></i><span>My Account</span></a>
      <a href="../auth/logout.php" class="bhms-nav-link bhms-logout-link"><i class="fa-solid fa-right-from-bracket"></i><span>Log out</span></a>
    </div>
  </aside>
  <label for="bhmsSidebarToggle" class="bhms-overlay"></label>
  <div class="bhms-main">
    <header class="bhms-topbar">
      <label for="bhmsSidebarToggle" class="bhms-menu-btn" aria-label="Toggle navigation"><i class="fa-solid fa-bars"></i></label>
      <div class="bhms-topbar-title">Archive</div>
      <div class="bhms-topbar-user">
        <i class="fa-regular fa-circle-user"></i>
        <span><?= htmlspecialchars($_SESSION['full_name']) ?></span>
        <span class="bhms-role-pill"><?= $_SESSION['role'] === 'administrator' ? 'Administrator' : 'BHW' ?></span>
      </div>
    </header>
    <main class="bhms-content">

<?php if ($error || $success): ?>
<div class="bhms-toast-backdrop" id="bhmsToastBackdrop"></div>
<div class="bhms-toast-container" id="bhmsToastContainer">
  <?php if ($error): ?>
  <div class="bhms-toast bhms-toast-danger" id="bhmsToastError">
    <div class="bhms-toast-content">
      <div class="bhms-toast-row">
        <div class="bhms-toast-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
        <div class="bhms-toast-body">
          <div class="bhms-toast-title">Something went wrong</div>
          <div class="bhms-toast-message"><?= htmlspecialchars($error) ?></div>
        </div>
      </div>
      <button type="button" class="bhms-toast-ok" onclick="bhmsDismissToast('bhmsToastError')">OK</button>
    </div>
  </div>
  <?php endif; ?>
  <?php if ($success): ?>
  <div class="bhms-toast bhms-toast-success" id="bhmsToastSuccess">
    <div class="bhms-toast-content">
      <div class="bhms-toast-row">
        <div class="bhms-toast-icon"><i class="fa-solid fa-circle-check"></i></div>
        <div class="bhms-toast-body">
          <div class="bhms-toast-title">Success</div>
          <div class="bhms-toast-message"><?= htmlspecialchars($success) ?></div>
        </div>
      </div>
      <button type="button" class="bhms-toast-ok" onclick="bhmsDismissToast('bhmsToastSuccess')">OK</button>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

    <div class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fa-solid fa-box-archive me-2" style="color:var(--bhms-blue);"></i>Archive</h3>
        <a href="../dashboard/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
      </div>

      <div class="card">
        <div class="card-body">
                    <p class="text-muted small mb-3">Archived records are hidden from their original module but not permanently deleted. Restore a record to bring it back, or permanently delete it if you're certain it's no longer needed.</p>
          <?php if (!empty($archived_items)): ?>
          <?php $unique_types = array_unique(array_column($archived_items, 'label')); sort($unique_types); ?>
          <div class="d-flex flex-wrap gap-2 mb-3">
            <input type="text" id="liveSearch" class="form-control form-control-sm" style="max-width:250px;" placeholder="Search archived records...">
            <select id="filterType" class="form-select form-select-sm" style="max-width:190px;">
              <option value="">All Record Types</option>
              <?php foreach ($unique_types as $ut): ?>
                <option value="<?= htmlspecialchars($ut) ?>"><?= htmlspecialchars($ut) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
          <?php if (empty($archived_items)): ?>
            <p class="text-muted text-center py-4 mb-0">The archive is empty. Nothing has been archived yet.</p>
          <?php else: ?>
          <div class="archive-table-card">
          <div class="table-responsive">
          <div class="table-responsive">
<table class="table table-striped">
            <thead><tr><th>Type</th><th>Details</th><th>Archived by</th><th>Archived on</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($archived_items as $item): ?>
              <tr data-type="<?= htmlspecialchars($item['label']) ?>">
                <td><span class="type-badge"><?= htmlspecialchars($item['label']) ?></span></td>
                <td><?= $item['description'] ?></td>
                <td><?= htmlspecialchars($item['archived_by']) ?></td>
                <td><?= $item['archived_at'] ? htmlspecialchars(date('M j, Y g:i A', strtotime($item['archived_at']))) : 'Unknown' ?></td>
                <td>
                  <a href="?restore=<?= $item['type'] ?>:<?= $item['id'] ?>" class="btn btn-sm btn-outline-primary" onclick="return confirm('Restore this record? It will reappear in its original module.')">Restore</a>
                  <button type="button" class="btn btn-sm btn-outline-danger"
                    data-type="<?= $item['type'] ?>" data-id="<?= $item['id'] ?>" data-description="<?= $item['description'] ?>"
                    onclick="openDeleteModal(this)">Delete Forever</button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          </div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-body p-4">
              <h5 class="mb-3"><i class="fa-solid fa-triangle-exclamation me-2" style="color:var(--bhms-danger);"></i>Permanently Delete Record</h5>
              <p class="text-muted small">This action cannot be undone. To confirm, type the record name exactly as shown below:</p>
              <p class="fw-bold" id="deleteModalTarget"></p>
              <input type="text" id="deleteConfirmInput" class="form-control mb-3" autocomplete="off" placeholder="Type here to confirm">
              <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger btn-sm" id="deleteConfirmBtn" disabled>Delete Forever</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    </main>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function applyArchiveFilters() {
    const searchEl = document.getElementById('liveSearch');
    const typeEl = document.getElementById('filterType');
    if (!searchEl) { return; }
    const query = searchEl.value.toLowerCase();
    const type = typeEl.value;

    document.querySelectorAll('.archive-table-card tbody tr').forEach(function(row) {
        const matchesText = row.textContent.toLowerCase().includes(query);
        const matchesType = !type || row.dataset.type === type;
        row.style.display = (matchesText && matchesType) ? '' : 'none';
    });
}
document.getElementById('liveSearch')?.addEventListener('input', applyArchiveFilters);
document.getElementById('filterType')?.addEventListener('change', applyArchiveFilters);
</script>
<script>
document.querySelectorAll('.bhms-nav-link').forEach(function (link) {
  link.addEventListener('click', function () {
    var cb = document.getElementById('bhmsSidebarToggle');
    if (cb) { cb.checked = false; }
  });
});

let deleteModalInstance = null;
let currentDeleteType = '';
let currentDeleteId = '';
let currentDeleteTarget = '';

function openDeleteModal(btn) {
    currentDeleteType = btn.dataset.type;
    currentDeleteId = btn.dataset.id;
    currentDeleteTarget = btn.dataset.description;

    document.getElementById('deleteModalTarget').innerText = currentDeleteTarget;
    document.getElementById('deleteConfirmInput').value = '';
    document.getElementById('deleteConfirmBtn').disabled = true;

    if (!deleteModalInstance) {
        deleteModalInstance = new bootstrap.Modal(document.getElementById('deleteModal'));
    }
    deleteModalInstance.show();
}

document.getElementById('deleteConfirmInput').addEventListener('input', function() {
    document.getElementById('deleteConfirmBtn').disabled = (this.value !== currentDeleteTarget);
});

document.getElementById('deleteConfirmBtn').addEventListener('click', function() {
    window.location.href = '?delete=' + currentDeleteType + ':' + currentDeleteId;
});
</script>
<script>
function bhmsDismissToast(id) {
  var el = document.getElementById(id);
  if (!el) { return; }
  el.classList.add('bhms-toast-hide');
  el.addEventListener('animationend', function () {
    el.remove();
    var container = document.getElementById('bhmsToastContainer');
    var backdrop = document.getElementById('bhmsToastBackdrop');
    if (backdrop && container && container.children.length === 0) {
      backdrop.classList.add('bhms-toast-hide');
      backdrop.addEventListener('animationend', function () {
        backdrop.remove();
      }, { once: true });
    }
  }, { once: true });
}
document.getElementById('bhmsToastBackdrop')?.addEventListener('click', function () {
  document.querySelectorAll('#bhmsToastContainer .bhms-toast').forEach(function (t) {
    bhmsDismissToast(t.id);
  });
});
// Auto-dismiss every toast after 3 seconds
document.querySelectorAll('#bhmsToastContainer .bhms-toast').forEach(function (toast) {
  setTimeout(function () {
    bhmsDismissToast(toast.id);
  }, 3000);
});
</script>
</body>
</html>