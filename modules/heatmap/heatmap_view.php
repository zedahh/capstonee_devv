<?php
/** @var array $case_points */
/** @var string $end_date */
/** @var bool $is_filtered */
/** @var array $purok_counts */
/** @var array $ranking */
/** @var string $start_date */
if (!isset($case_points)) { return; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Heatmap Visualization</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/css/custom.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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

  
  .btn { border-radius: 10px; font-weight: 500; padding: 0.5rem 1.1rem; font-size: 0.88rem; transition: transform 0.12s ease, box-shadow 0.12s ease, background-color 0.15s ease, border-color 0.15s ease; }
  .btn-sm { padding: 0.32rem 0.75rem; font-size: 0.8rem; border-radius: 8px; }
  .btn-primary { background: linear-gradient(135deg, var(--bhms-green), var(--bhms-blue)); border: none; }
  .btn-primary:hover, .btn-primary:focus { filter: brightness(0.95); color: #fff; }
  .btn-outline-secondary { color: var(--bhms-gray-600); border-color: var(--bhms-gray-300); }
  .btn-outline-secondary:hover { background: var(--bhms-gray-600); border-color: var(--bhms-gray-600); }
  .form-control, .form-select {
    border-radius: 10px; border: 1px solid var(--bhms-gray-300); font-size: 0.85rem;
  }
  .form-control:focus { border-color: var(--bhms-green); box-shadow: 0 0 0 3px rgba(46,125,82,0.14); }


  .bhms-content .container > .d-flex.justify-content-between.align-items-center.mb-3 {
    background: rgba(255,255,255,0.75);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.6);
    border-radius: var(--bhms-radius-lg);
    padding: 1.1rem 1.4rem;
    box-shadow: var(--bhms-shadow-sm);
  }
  .bhms-content h3 { margin-bottom: 0; font-size: 1.25rem; display: flex; align-items: center; }

  

  .map-card, .ranking-card {
    background: #fff;
    border-radius: var(--bhms-radius-lg);
    box-shadow: var(--bhms-shadow-sm);
    padding: 1rem;
  }
  .table thead th {
    background: linear-gradient(135deg, var(--bhms-green-light), #eaf2fb);
    color: var(--bhms-green-dark);
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
    border-bottom: none;
  }
  .table-striped > tbody > tr:nth-of-type(odd) > * { background-color: var(--bhms-gray-50); }
  .table > tbody > tr:hover > * { background-color: var(--bhms-green-light); }
</style>
<style>
  #map { height: 480px; border-radius: 8px; }
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
      <a href="../vaccination/vaccination.php" class="bhms-nav-link"><i class="fa-solid fa-syringe"></i><span>Vaccination Records</span></a>
      <a href="../disease/disease.php" class="bhms-nav-link"><i class="fa-solid fa-virus"></i><span>Disease Recording</span></a>
      <a href="../heatmap/heatmap.php" class="bhms-nav-link active"><i class="fa-solid fa-map-location-dot"></i><span>Heatmap</span></a>
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
      <div class="bhms-topbar-title">Heatmap Visualization</div>
      <div class="bhms-topbar-user">
        <i class="fa-regular fa-circle-user"></i>
        <span><?= htmlspecialchars($_SESSION['full_name']) ?></span>
        <span class="bhms-role-pill"><?= $_SESSION['role'] === 'administrator' ? 'Administrator' : 'BHW' ?></span>
      </div>
    </header>
    <main class="bhms-content">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3><i class="fa-solid fa-map-location-dot me-2" style="color:var(--bhms-green);"></i>Heatmap Visualization by Purok</h3>
    <a href="../dashboard/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
  </div>

  <form method="GET" action="" class="row g-2 align-items-center mb-2">
    <div class="col-auto"><label class="col-form-label col-form-label-sm">From</label></div>
    <div class="col-auto"><input type="date" name="start_date" class="form-control form-control-sm" value="<?= htmlspecialchars($start_date) ?>"></div>
    <div class="col-auto"><label class="col-form-label col-form-label-sm">To</label></div>
    <div class="col-auto"><input type="date" name="end_date" class="form-control form-control-sm" value="<?= htmlspecialchars($end_date) ?>"></div>
    <div class="col-auto"><button type="submit" class="btn btn-sm btn-primary">Apply date range</button></div>
    <?php if ($is_filtered): ?>
    <div class="col-auto"><a href="heatmap.php" class="btn btn-sm btn-outline-secondary">Clear (show current)</a></div>
    <?php endif; ?>
  </form>

  <p class="text-muted small mb-3">
    <?php if ($is_filtered): ?>
      Showing all cases reported between <strong><?= htmlspecialchars($start_date) ?></strong> and <strong><?= htmlspecialchars($end_date) ?></strong>.
    <?php else: ?>
      Showing current <strong>active / under-monitoring</strong> cases. Click a dot to see case details.
    <?php endif; ?>
  </p>

  <div class="row">
    <div class="col-md-8">
      <div class="map-card">
      <div id="map"></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="ranking-card">
      <h5><i class="fa-solid fa-ranking-star me-2" style="color:var(--bhms-blue);"></i>Purok ranking</h5>
      <table class="table table-striped">
        <thead><tr><th>Purok</th><th>Cases</th><th>Risk level</th></tr></thead>
        <tbody>
          <?php foreach ($ranking as $purok => $count): $risk = getRiskLevel($count); ?>
          <tr>
            <td>Purok <?= $purok ?></td>
            <td><?= $count ?></td>
            <td><span style="color: <?= $risk['color'] ?>; font-weight: bold;"><?= $risk['level'] ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="text-muted small">Individual dots show an approximate location within each resident's actual purok — not their real address, since only purok-level data is captured. Risk levels: Low (0-4), Moderate (5-9), High (10+).</p>
      </div>
    </div>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
const purokData = <?= json_encode($purok_counts) ?>;
const casePoints = <?= json_encode($case_points) ?>;

function getColor(count) {
    if (count >= 10) return '#E24B4A';
    if (count >= 5) return '#EF9F27';
    return '#639922';
}

const map = L.map('map');

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19
}).addTo(map);

const santaInesBoundary = {
  "type": "Feature",
  "properties": { "name": "Santa Ines" },
  "geometry": {
    "type": "Polygon",
    "coordinates": [[
      [120.8567776,14.8762978],[120.8571153,14.8762182],[120.8591311,14.8766909],
      [120.8597947,14.8766875],[120.8600435,14.8793746],[120.8598371,14.8816893],
      [120.8590697,14.8817061],[120.8590768,14.8820312],[120.859626,14.883261],
      [120.8570701,14.8846072],[120.8562524,14.8844414],[120.856117,14.8844124],
      [120.8555007,14.8842778],[120.8550624,14.8841486],[120.8542718,14.883937],
      [120.8543712,14.8836969],[120.8545938,14.8831593],[120.8539144,14.8831558],
      [120.8536389,14.8824141],[120.853711,14.8818023],[120.854778,14.8815123],
      [120.8548343,14.8814838],[120.854888,14.8814103],[120.8549466,14.8813035],
      [120.8550806,14.8810389],[120.8552541,14.8807367],[120.855352,14.8804896],
      [120.8554075,14.8803151],[120.8554244,14.880141],[120.8554305,14.8799733],
      [120.8554217,14.8797379],[120.8553875,14.8795751],[120.8553305,14.8794126],
      [120.8552505,14.8792006],[120.8553506,14.8791637],[120.8554096,14.8791248],
      [120.855419,14.8790898],[120.8555619,14.8786005],[120.8555728,14.878524],
      [120.8554552,14.878307],[120.85538,14.8781578],[120.8553278,14.878027],
      [120.8553014,14.8778824],[120.8552863,14.8777341],[120.8553144,14.8775469],
      [120.8553614,14.8773997],[120.8554151,14.877295],[120.8555169,14.8771625],
      [120.8556053,14.8770745],[120.8558857,14.8768799],[120.8561684,14.8766992],
      [120.8567776,14.8762978]
    ]]
  }
};

const boundaryLayerTemp = L.geoJSON(santaInesBoundary);
map.fitBounds(boundaryLayerTemp.getBounds());

const purokBoundaries = <?= $purok_boundaries_json ?>;

for (const purok in purokBoundaries) {
    const count = purokData[purok] || 0;
    const color = getColor(count);
    const latlngs = purokBoundaries[purok].map(function(pt) { return [pt[1], pt[0]]; });

    L.polygon(latlngs, {
        color: '#164430',
        weight: 1.5,
        fillColor: color,
        fillOpacity: 0.55
    }).bindPopup(`<strong>Purok ${purok}</strong><br>Cases: ${count}`).addTo(map);
}

L.geoJSON(santaInesBoundary, {
    style: { color: '#164430', weight: 2.5, fill: false }
}).addTo(map);
casePoints.forEach(function(c) {
    let caseListHtml = c.cases.map(function(cs) {
        return `${cs.disease} &mdash; ${cs.date} &mdash; ${cs.status}`;
    }).join('<br>');

    L.circleMarker([c.lat, c.lng], {
        radius: 2,
        color: '#222',
        weight: 1,
        fillColor: '#333',
        fillOpacity: 0.85
    }).bindPopup(`<strong>${c.name}</strong><br>Purok ${c.purok}<br>${caseListHtml}`).addTo(map);
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