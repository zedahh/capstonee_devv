<?php
/** @var int $behind_maternal_count */
/** @var int $cases_this_month */
/** @var int $disease_count */
/** @var array $disease_trends */
/** @var int $infant_count */
/** @var array $monthly_labels */
/** @var array $monthly_values */
/** @var int $overdue_infant_count */
/** @var int $pregnant_count */
/** @var array $purok_chart_data */
/** @var array $seasonal_advisories */
/** @var array $threshold_alerts */
/** @var int $total_residents */
if (!isset($total_residents)) { return; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard</title>
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
    --bhms-blue-light: #E8F1FA;
    --bhms-gray-50: #F7F9FA;
    --bhms-gray-100: #EEF1F3;
    --bhms-gray-200: #E3E7EA;
    --bhms-gray-300: #D3D9DE;
    --bhms-gray-400: #9CA6AD;
    --bhms-gray-600: #5C6670;
    --bhms-gray-800: #2C333A;
    --bhms-danger: #D64545;
    --bhms-danger-light: #FBEAEA;
    --bhms-warning: #E0932E;
    --bhms-warning-light: #FDF2E4;
    --bhms-success-light: #E6F4EC;
    --bhms-info-light: #E8F1FA;
    --bhms-rose: #D6336C;
    --bhms-teal: #14919B;
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

  /* ---- Bootstrap component polish ---- */
  .card { border: 1px solid var(--bhms-gray-200); border-radius: var(--bhms-radius); box-shadow: var(--bhms-shadow-sm); transition: box-shadow 0.2s ease, transform 0.2s ease; }
  .card:hover { box-shadow: var(--bhms-shadow-md); }
  .card-body { padding: 1.5rem; }
  .card-title { font-weight: 600; color: var(--bhms-gray-800); margin-bottom: 1rem; }
  .btn { border-radius: 10px; font-weight: 500; padding: 0.5rem 1.1rem; font-size: 0.88rem; transition: transform 0.12s ease, box-shadow 0.12s ease, background-color 0.15s ease, border-color 0.15s ease; }
  .btn-sm { padding: 0.32rem 0.75rem; font-size: 0.8rem; border-radius: 8px; }
  .btn:active { transform: translateY(1px); }
  .btn-primary { background: var(--bhms-green); border-color: var(--bhms-green); }
  .btn-primary:hover, .btn-primary:focus { background: var(--bhms-green-dark); border-color: var(--bhms-green-dark); }
  .btn-outline-secondary { color: var(--bhms-gray-600); border-color: var(--bhms-gray-300); }
  .btn-outline-secondary:hover { background: var(--bhms-gray-600); border-color: var(--bhms-gray-600); }
  .btn-outline-danger { color: var(--bhms-danger); border-color: var(--bhms-danger); }
  .btn-outline-danger:hover { background: var(--bhms-danger); border-color: var(--bhms-danger); }
  .alert { border: none; border-left: 4px solid transparent; border-radius: var(--bhms-radius-sm); font-size: 0.9rem; padding: 0.9rem 1.1rem; }
  .alert-danger { background: var(--bhms-danger-light); color: #8a2c2c; border-left-color: var(--bhms-danger); }
  .alert-warning { background: var(--bhms-warning-light); color: #8a5a12; border-left-color: var(--bhms-warning); }
  .alert-success { background: var(--bhms-success-light); color: var(--bhms-green-darker); border-left-color: var(--bhms-green); }
  .alert-info { background: var(--bhms-info-light); color: var(--bhms-blue-dark); border-left-color: var(--bhms-blue); }
  .badge { font-weight: 600; padding: 0.4em 0.75em; border-radius: 999px; font-size: 0.72rem; letter-spacing: 0.02em; }
  .badge.bg-danger { background-color: var(--bhms-danger) !important; }
.badge.bg-warning { background-color: var(--bhms-warning) !important; color: #fff !important; }
.badge.bg-info { background-color: var(--bhms-blue) !important; }
  .table thead th { background: var(--bhms-green-light); color: var(--bhms-green-dark); font-size: 0.74rem; text-transform: uppercase; letter-spacing: 0.04em; font-weight: 600; border-bottom: none; padding: 0.75rem 0.9rem; }
  .table td { padding: 0.7rem 0.9rem; vertical-align: middle; font-size: 0.88rem; }

  /* ---- Page header row (existing markup, styled without touching classes) ---- */
  .bhms-content .container > .d-flex.justify-content-between.align-items-center.mb-4 {
    border-radius: var(--bhms-radius);
  }

  
  .welcome-banner {
    background: rgba(255,255,255,0.75);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.6);
    border-radius: var(--bhms-radius-lg);
    padding: 1.25rem 1.5rem;
    box-shadow: var(--bhms-shadow-sm);
    position: relative;
    overflow: hidden;
    animation: bhmsCardIn 0.5s ease both;
  }
  .welcome-banner-left { display: flex; align-items: center; gap: 1rem; }
  .welcome-icon-badge {
    width: 54px; height: 54px; border-radius: 50%;
    background: linear-gradient(135deg, var(--bhms-green), var(--bhms-blue));
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-size: 1.35rem; box-shadow: var(--bhms-shadow-md); flex-shrink: 0;
  }
  .welcome-banner-sub { font-size: 0.82rem; color: var(--bhms-gray-600); }

  @keyframes bhmsCardIn {
    from { opacity: 0; transform: translateY(14px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes bhmsStatIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .stat-card {
    position: relative;
    overflow: hidden;
    border-radius: var(--bhms-radius-lg) !important;
    padding-top: 1.5rem !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    opacity: 0;
    animation: bhmsStatIn 0.45s ease forwards;
  }
  .stat-card:hover { transform: translateY(-4px); box-shadow: var(--bhms-shadow-md); }
  .row.g-3.mb-4:first-of-type > div:nth-child(1) .stat-card { animation-delay: 0.02s; }
  .row.g-3.mb-4:first-of-type > div:nth-child(2) .stat-card { animation-delay: 0.08s; }
  .row.g-3.mb-4:first-of-type > div:nth-child(3) .stat-card { animation-delay: 0.14s; }
  .row.g-3.mb-4:first-of-type > div:nth-child(4) .stat-card { animation-delay: 0.20s; }
  .row.g-3.mb-4:first-of-type > div:nth-child(5) .stat-card { animation-delay: 0.26s; }

  .stat-card-icon {
    width: 42px; height: 42px; margin: 0 auto 0.65rem;
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-size: 1.05rem; color: #fff;
  }
  .stat-card-residents .stat-card-icon { background: var(--bhms-blue); }
  .stat-card-pregnant .stat-card-icon  { background: var(--bhms-rose); }
  .stat-card-infants .stat-card-icon   { background: var(--bhms-teal); }
  .stat-card-disease .stat-card-icon   { background: var(--bhms-danger); }
  .stat-card-monthly .stat-card-icon   { background: var(--bhms-warning); }

  .stat-card h6 { font-size: 0.76rem; color: var(--bhms-gray-600); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.3rem; }
  .stat-card .fs-4 { font-size: 1.85rem !important; font-weight: 700; }
  .stat-card-residents .fs-4 { color: var(--bhms-blue-dark); }
  .stat-card-pregnant .fs-4  { color: var(--bhms-rose); }
  .stat-card-infants .fs-4   { color: var(--bhms-teal); }
  .stat-card-disease .fs-4   { color: var(--bhms-danger); }
  .stat-card-monthly .fs-4   { color: #b3760f; }

  .chart-card { border-radius: var(--bhms-radius-lg) !important; transition: box-shadow 0.2s ease; }
  .chart-card:hover { box-shadow: var(--bhms-shadow-md); }
  .chart-card h6 { display: flex; align-items: center; font-weight: 600; }

  .quick-actions-card {
    background: rgba(255,255,255,0.75);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.6);
    border-radius: var(--bhms-radius-lg) !important;
  }
  .quick-actions-title { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--bhms-gray-600); font-weight: 600; margin-bottom: 1rem; }
  .quick-actions-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 0.65rem; }
  .quick-action-tile {
    display: flex !important; flex-direction: column; align-items: center; justify-content: center;
    gap: 0.4rem; padding: 1rem 0.5rem !important; text-align: center; white-space: normal;
    border-radius: 14px !important; border: none !important;
    background: linear-gradient(135deg, var(--bhms-green), var(--bhms-blue)) !important;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
  }
  .quick-action-tile:hover { transform: translateY(-3px); box-shadow: var(--bhms-shadow-md); color: #fff; }
  .quick-action-tile i { font-size: 1.15rem; margin: 0 !important; }
  .quick-action-tile span { font-size: 0.76rem; line-height: 1.2; }

  @media (prefers-reduced-motion: reduce) {
    .stat-card, .welcome-banner { animation: none; opacity: 1; }
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
      <a href="../dashboard/dashboard.php" class="bhms-nav-link active"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
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
      <div class="bhms-topbar-title">Dashboard</div>
      <div class="bhms-topbar-user">
        <i class="fa-regular fa-circle-user"></i>
        <span><?= htmlspecialchars($_SESSION['full_name']) ?></span>
        <span class="bhms-role-pill"><?= $_SESSION['role'] === 'administrator' ? 'Administrator' : 'BHW' ?></span>
      </div>
    </header>
    <main class="bhms-content dashboard-page">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4 welcome-banner">
    <div class="welcome-banner-left">
      <div class="welcome-icon-badge"><i class="fa-solid fa-hand-holding-heart"></i></div>
      <div>
        <h3 class="mb-0">Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></h3>
        <p class="welcome-banner-sub mb-0">Here's what's happening across the barangay today</p>
      </div>
    </div>
    <div>
      <?php if ($_SESSION['role'] === 'administrator'): ?>
        <a href="../admin/audit_log.php" class="btn btn-outline-secondary btn-sm me-2"><i class="fa-solid fa-clipboard-list me-1"></i>Audit log</a>
        <a href="../admin/lgu_contacts.php" class="btn btn-outline-secondary btn-sm me-2"><i class="fa-solid fa-address-book me-1"></i>LGU Contacts</a>
      <?php endif; ?>
      <a href="../auth/logout.php" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-right-from-bracket me-1"></i>Log out</a>
    </div>
  </div>

  <?php
    $active_alerts = [];
    foreach ($threshold_alerts as $alert) {
        $level = getRiskLevel($alert['case_count'], $purok_population[$alert['purok']] ?? 0);
        if ($level === 'high' || $level === 'moderate') {
            $alert['level'] = $level;
            $active_alerts[] = $alert;
        }
    }
    usort($active_alerts, fn($a, $b) => ($b['level'] === 'high' ? 1 : 0) <=> ($a['level'] === 'high' ? 1 : 0) ?: $b['case_count'] <=> $a['case_count']);
    $high_count = count(array_filter($active_alerts, fn($a) => $a['level'] === 'high'));
    $moderate_count = count($active_alerts) - $high_count;
  ?>
  <?php if (!empty($active_alerts)): ?>
  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">
        <i class="fa-solid fa-triangle-exclamation me-2" style="color:var(--bhms-danger);"></i>Active Health Alerts
        <span class="badge bg-danger ms-2"><?= $high_count ?> high</span>
        <span class="badge bg-warning text-dark ms-1"><?= $moderate_count ?> moderate</span>
      </h5>
      <div class="table-responsive">
      <table class="table table-sm align-middle mb-0">
        <thead><tr><th>Purok</th><th>Disease</th><th>Cases</th><th>Risk</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($active_alerts as $i => $alert): ?>
          <?php
            $draft_title = "Health Advisory: " . $alert['disease_name'] . " Alert in Purok " . $alert['purok'];
            $draft_content = "The health center has recorded " . $alert['case_count'] . " active " . $alert['disease_name'] . " case(s) in Purok " . $alert['purok'] . ", exceeding the alert threshold. Residents are advised to take necessary precautions. Please contact the health center for more information.";
          ?>
          <tr>
            <td>Purok <?= htmlspecialchars($alert['purok']) ?></td>
            <td><?= htmlspecialchars($alert['disease_name']) ?></td>
            <td><?= $alert['case_count'] ?></td>
            <td><span class="badge <?= $alert['level'] === 'high' ? 'bg-danger' : 'bg-warning text-dark' ?>"><?= ucfirst($alert['level']) ?></span></td>
            <td>
              <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">Actions</button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="../heatmap/heatmap.php">View heatmap</a></li>
                  <li><a class="dropdown-item" href="../announcements/announcements.php?draft_title=<?= urlencode($draft_title) ?>&draft_content=<?= urlencode($draft_content) ?>&draft_purok=<?= urlencode($alert['purok']) ?>">Draft public advisory</a></li>
                  <li><a class="dropdown-item" href="../reports/lgu_briefing.php?purok=<?= urlencode($alert['purok']) ?>&disease=<?= urlencode($alert['disease_name']) ?>&count=<?= urlencode($alert['case_count']) ?>" target="_blank">Generate LGU briefing (PDF)</a></li>
                  <li><a class="dropdown-item" href="../admin/notify_lgu.php?purok=<?= urlencode($alert['purok']) ?>&disease=<?= urlencode($alert['disease_name']) ?>&count=<?= urlencode($alert['case_count']) ?>" onclick="return confirm('Send SMS notification to all LGU contacts?')">Notify LGU via SMS</a></li>
                </ul>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (isset($_GET['sms_sent'])): ?>
  <div class="alert alert-success"><i class="fa-solid fa-circle-check me-2"></i><?= (int) $_GET['sms_sent'] ?> SMS notification(s) sent to LGU contacts (simulated).</div>
  <?php endif; ?>

  <?php if ($overdue_infant_count > 0): ?>
  <div class="alert alert-warning">
    <i class="fa-solid fa-syringe me-2"></i><strong><?= $overdue_infant_count ?> infant<?= $overdue_infant_count > 1 ? 's' : '' ?> overdue</strong> for DOH EPI vaccinations.
    <a href="../infant/infant.php">View infant monitoring</a>
  </div>
  <?php endif; ?>

  <?php if ($behind_maternal_count > 0): ?>
  <div class="alert alert-warning">
    <i class="fa-solid fa-person-pregnant me-2"></i><strong><?= $behind_maternal_count ?> pregnant resident<?= $behind_maternal_count > 1 ? 's' : '' ?> behind</strong> on prenatal visit schedule.
    <a href="../maternal/maternal.php">View maternal health</a>
  </div>
  <?php endif; ?>

  <?php if (!empty($seasonal_advisories)): ?>
  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title"><i class="fa-solid fa-cloud-sun-rain me-2" style="color:var(--bhms-blue);"></i>Seasonal Risk Advisories <span class="badge bg-info text-dark ms-2"><?= count($seasonal_advisories) ?></span></h5>
      <div class="accordion" id="seasonalAccordion">
        <?php foreach ($seasonal_advisories as $i => $adv): ?>
        <div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#seasonal<?= $i ?>">
              <?= htmlspecialchars($adv['disease_name']) ?>
            </button>
          </h2>
          <div id="seasonal<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#seasonalAccordion">
            <div class="accordion-body"><?= htmlspecialchars($adv['advisory_note']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="row g-3 mb-4">
    <div class="col-md-2"><div class="card p-3 text-center stat-card stat-card-residents"><div class="stat-card-icon"><i class="fa-solid fa-users"></i></div><h6>Total residents</h6><p class="fs-4 mb-0"><?= $total_residents ?></p></div></div>
    <div class="col-md-2"><div class="card p-3 text-center stat-card stat-card-pregnant"><div class="stat-card-icon"><i class="fa-solid fa-person-pregnant"></i></div><h6>Pregnant women</h6><p class="fs-4 mb-0"><?= $pregnant_count ?></p></div></div>
    <div class="col-md-2"><div class="card p-3 text-center stat-card stat-card-infants"><div class="stat-card-icon"><i class="fa-solid fa-baby"></i></div><h6>Infants 0-12mo</h6><p class="fs-4 mb-0"><?= $infant_count ?></p></div></div>
    <div class="col-md-3"><div class="card p-3 text-center stat-card stat-card-disease"><div class="stat-card-icon"><i class="fa-solid fa-virus"></i></div><h6>Active disease cases</h6><p class="fs-4 mb-0"><?= $disease_count ?></p></div></div>
    <div class="col-md-3"><div class="card p-3 text-center stat-card stat-card-monthly"><div class="stat-card-icon"><i class="fa-solid fa-calendar-check"></i></div><h6>Cases reported this month</h6><p class="fs-4 mb-0"><?= $cases_this_month ?></p></div></div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <div class="card p-3 chart-card">
        <h6><i class="fa-solid fa-chart-column me-2" style="color:var(--bhms-green);"></i>Active cases by purok</h6>
        <canvas id="purokChart" height="200"></canvas>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card p-3 chart-card">
        <h6><i class="fa-solid fa-chart-line me-2" style="color:var(--bhms-blue);"></i>Case trend, last 6 months</h6>
        <canvas id="trendChart" height="200"></canvas>
      </div>
    </div>
  </div>

  <?php if (!empty($disease_trends)): ?>
  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title"><i class="fa-solid fa-arrow-trend-up me-2"></i>Disease trend indicators <small class="text-muted">(month-over-month, based on recorded cases)</small></h5>
      <table class="table table-sm mb-0">
        <thead><tr><th>Disease</th><th>Trend</th></tr></thead>
        <tbody>
          <?php foreach ($disease_trends as $disease => $months): $trend = getTrendInfo($months); ?>
          <tr>
            <td><?= htmlspecialchars($disease) ?></td>
            <td><span class="badge bg-<?= $trend['badge'] ?>"><?= htmlspecialchars($trend['label']) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <div class="card quick-actions-card mb-3">
    <div class="card-body">
    <h6 class="quick-actions-title"><i class="fa-solid fa-bolt me-2"></i>Quick Actions</h6>
    <div class="mb-0 quick-actions-grid">
    <a href="../residents/residents.php" class="btn btn-primary btn-sm quick-action-tile"><i class="fa-solid fa-users"></i><span>Resident Profiling</span></a>
    <a href="../maternal/maternal.php" class="btn btn-primary btn-sm quick-action-tile"><i class="fa-solid fa-person-pregnant"></i><span>Maternal Health</span></a>
    <a href="../infant/infant.php" class="btn btn-primary btn-sm quick-action-tile"><i class="fa-solid fa-baby"></i><span>Infant Monitoring</span></a>
    <a href="../vaccination/vaccination.php" class="btn btn-primary btn-sm quick-action-tile"><i class="fa-solid fa-syringe"></i><span>Vaccination Records</span></a>
    <a href="../disease/disease.php" class="btn btn-primary btn-sm quick-action-tile"><i class="fa-solid fa-virus"></i><span>Disease Recording</span></a>
    <a href="../heatmap/heatmap.php" class="btn btn-primary btn-sm quick-action-tile"><i class="fa-solid fa-map-location-dot"></i><span>Heatmap</span></a>
    <a href="../reports/reports.php" class="btn btn-primary btn-sm quick-action-tile"><i class="fa-solid fa-file-lines"></i><span>Reports</span></a>
    <a href="../announcements/announcements.php" class="btn btn-primary btn-sm quick-action-tile"><i class="fa-solid fa-bullhorn"></i><span>Announcements</span></a>
    </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const purokPopulation = <?= json_encode($purok_population) ?>;

new Chart(document.getElementById('purokChart'), {
    type: 'bar',
    data: {
        labels: ['Purok 1', 'Purok 2', 'Purok 3', 'Purok 4'],
        datasets: [{
            label: 'Active cases',
            data: [
                <?= $purok_chart_data[1] ?>,
                <?= $purok_chart_data[2] ?>,
                <?= $purok_chart_data[3] ?>,
                <?= $purok_chart_data[4] ?>
            ],
            backgroundColor: ['#639922', '#639922', '#639922', '#639922']
        }]
    },
    options: {
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const purok = context.dataIndex + 1;
                        const cases = context.parsed.y;
                        const population = purokPopulation[purok] || 0;
                        if (population === 0) {
                            return cases + ' active case(s)';
                        }
                        const rate = ((cases / population) * 100).toFixed(1);
                        return cases + ' active case(s) out of ' + population + ' residents (' + rate + '%)';
                    }
                }
            }
        },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($monthly_labels) ?>,
        datasets: [{
            label: 'Total cases reported',
            data: <?= json_encode($monthly_values) ?>,
            borderColor: '#185FA5',
            backgroundColor: 'rgba(24,95,165,0.15)',
            fill: true,
            tension: 0.2
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});
</script>
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