<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$is_filtered = ($start_date !== '' && $end_date !== '');

if ($is_filtered) {
    // Historical range mode: count all cases reported in this range, regardless of current status
    $stmt = $pdo->prepare("
        SELECT r.purok, COUNT(*) as case_count
        FROM disease_cases dc
        JOIN residents r ON dc.resident_id = r.resident_id
        WHERE dc.date_reported BETWEEN ? AND ?
        GROUP BY r.purok
    ");
    $stmt->execute([$start_date, $end_date]);
} else {
    // Default mode: current active/under-monitoring snapshot
    $stmt = $pdo->query("
        SELECT r.purok, COUNT(*) as case_count
        FROM disease_cases dc
        JOIN residents r ON dc.resident_id = r.resident_id
        WHERE dc.status IN ('Active', 'Under monitoring')
        GROUP BY r.purok
    ");
}
$counts_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

$purok_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
foreach ($counts_raw as $row) {
    $purok_counts[(int)$row['purok']] = (int)$row['case_count'];
}

function getRiskLevel($count) {
    if ($count >= 10) return ['level' => 'High', 'color' => '#E24B4A'];
    if ($count >= 5) return ['level' => 'Moderate', 'color' => '#EF9F27'];
    return ['level' => 'Low', 'color' => '#639922'];
}

$ranking = $purok_counts;
arsort($ranking);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Heatmap Visualization</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
  #map { height: 480px; border-radius: 8px; }
</style>
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Heatmap Visualization by Purok</h3>
    <a href="../dashboard/dashboard.php" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
  </div>

  <form method="GET" action="" class="row g-2 align-items-center mb-2">
    <div class="col-auto">
      <label class="col-form-label col-form-label-sm">From</label>
    </div>
    <div class="col-auto">
      <input type="date" name="start_date" class="form-control form-control-sm" value="<?= htmlspecialchars($start_date) ?>">
    </div>
    <div class="col-auto">
      <label class="col-form-label col-form-label-sm">To</label>
    </div>
    <div class="col-auto">
      <input type="date" name="end_date" class="form-control form-control-sm" value="<?= htmlspecialchars($end_date) ?>">
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-sm btn-primary">Apply date range</button>
    </div>
    <?php if ($is_filtered): ?>
    <div class="col-auto">
      <a href="heatmap.php" class="btn btn-sm btn-outline-secondary">Clear (show current)</a>
    </div>
    <?php endif; ?>
  </form>

  <p class="text-muted small mb-3">
    <?php if ($is_filtered): ?>
      Showing all cases reported between <strong><?= htmlspecialchars($start_date) ?></strong> and <strong><?= htmlspecialchars($end_date) ?></strong>, regardless of current status.
    <?php else: ?>
      Showing current <strong>active / under-monitoring</strong> cases (no date filter applied).
    <?php endif; ?>
  </p>

  <div class="row">
    <div class="col-md-8">
      <div id="map"></div>
    </div>
    <div class="col-md-4">
      <h5>Purok ranking</h5>
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
      <p class="text-muted small">Risk levels: Low (0-4), Moderate (5-9), High (10+) — placeholder thresholds, adjust once confirmed with health center staff. Purok subdivisions are approximated as quadrants clipped to the real barangay outline until an official purok map is available.</p>
    </div>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>
<script>
const purokData = <?= json_encode($purok_counts) ?>;

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

const boundaryLayer = L.geoJSON(santaInesBoundary, {
    style: { color: '#444', weight: 2, dashArray: '6 4', fill: false }
}).addTo(map);

map.fitBounds(boundaryLayer.getBounds());

const bounds = boundaryLayer.getBounds();
const sw = bounds.getSouthWest();
const ne = bounds.getNorthEast();
const midLat = (sw.lat + ne.lat) / 2;
const midLng = (sw.lng + ne.lng) / 2;

function makeRectPolygon(latMin, latMax, lngMin, lngMax) {
    return turf.polygon([[
        [lngMin, latMin], [lngMin, latMax], [lngMax, latMax], [lngMax, latMin], [lngMin, latMin]
    ]]);
}

const quadrantRects = {
    1: makeRectPolygon(midLat, ne.lat, sw.lng, midLng),
    2: makeRectPolygon(midLat, ne.lat, midLng, ne.lng),
    3: makeRectPolygon(sw.lat, midLat, sw.lng, midLng),
    4: makeRectPolygon(sw.lat, midLat, midLng, ne.lng)
};

const boundaryTurf = turf.polygon(santaInesBoundary.geometry.coordinates);

for (const purok in quadrantRects) {
    const clipped = turf.intersect(quadrantRects[purok], boundaryTurf);
    if (!clipped) continue;

    const count = purokData[purok] || 0;
    const color = getColor(count);

    L.geoJSON(clipped, {
        style: { color: '#555', weight: 1, fillColor: color, fillOpacity: 0.5 }
    }).bindPopup(`<strong>Purok ${purok}</strong><br>Cases: ${count}`).addTo(map);
}
</script>
</body>
</html>