<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';
require '../../includes/functions.php';

$total_residents = $pdo->query("SELECT COUNT(*) FROM residents WHERE is_active = 1")->fetchColumn();
$pregnant_count = $pdo->query("SELECT COUNT(*) FROM maternal_records WHERE monitoring_status IN ('Ongoing', 'High-risk')")->fetchColumn();
$infant_count = $pdo->query("SELECT COUNT(*) FROM infant_records ir JOIN residents r ON ir.resident_id = r.resident_id WHERE r.birth_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)")->fetchColumn();
$disease_count = $pdo->query("SELECT COUNT(*) FROM disease_cases WHERE status IN ('Active', 'Under monitoring')")->fetchColumn();

// Total cases reported this calendar month, regardless of current status
$cases_this_month = $pdo->query("
    SELECT COUNT(*) FROM disease_cases
    WHERE YEAR(date_reported) = YEAR(CURDATE()) AND MONTH(date_reported) = MONTH(CURDATE())
")->fetchColumn();

// Threshold alert check: any disease + purok combination currently over threshold
$threshold_alerts = $pdo->query("
    SELECT r.purok, dc.disease_name, COUNT(*) as case_count
    FROM disease_cases dc
    JOIN residents r ON dc.resident_id = r.resident_id
    WHERE dc.status IN ('Active', 'Under monitoring')
    GROUP BY r.purok, dc.disease_name
    HAVING case_count >= 5
    ORDER BY case_count DESC
")->fetchAll(PDO::FETCH_ASSOC);

function getRiskLevel($count) {
    if ($count >= 10) return 'high';
    if ($count >= 5) return 'moderate';
    return 'low';
}

// Seasonal risk advisory check: any reference row matching the current month
$current_month = (int) date('n');
$seasonal_advisories = $pdo->query("
    SELECT * FROM seasonal_risk_reference
    WHERE (
        (start_month <= end_month AND $current_month BETWEEN start_month AND end_month)
        OR
        (start_month > end_month AND ($current_month >= start_month OR $current_month <= end_month))
    )
")->fetchAll(PDO::FETCH_ASSOC);

// Month-over-month trend: real case counts from the last 3 months, grouped by disease
$monthly_data = $pdo->query("
    SELECT disease_name, DATE_FORMAT(date_reported, '%Y-%m') as ym, COUNT(*) as case_count
    FROM disease_cases
    WHERE date_reported >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
    GROUP BY disease_name, ym
    ORDER BY disease_name, ym
")->fetchAll(PDO::FETCH_ASSOC);

$disease_trends = [];
foreach ($monthly_data as $row) {
    $disease_trends[$row['disease_name']][$row['ym']] = (int) $row['case_count'];
}

function getTrendInfo($months) {
    $keys = array_keys($months);
    if (count($keys) < 2) {
        return ['label' => 'Not enough data yet', 'badge' => 'secondary'];
    }
    $latest = $months[$keys[count($keys) - 1]];
    $previous = $months[$keys[count($keys) - 2]];
    if ($latest > $previous) {
        return ['label' => "Rising ($previous \u{2192} $latest)", 'badge' => 'danger'];
    } elseif ($latest < $previous) {
        return ['label' => "Declining ($previous \u{2192} $latest)", 'badge' => 'success'];
    } else {
        return ['label' => "Stable ($latest)", 'badge' => 'secondary'];
    }
}

// Count maternal records currently behind on prenatal visit compliance
$prenatal_schedule = $pdo->query("SELECT * FROM prenatal_visit_schedule")->fetchAll(PDO::FETCH_ASSOC);
$maternal_for_compliance = $pdo->query("SELECT maternal_record_id, lmp_date, monitoring_status FROM maternal_records")->fetchAll(PDO::FETCH_ASSOC);

$behind_maternal_count = 0;
foreach ($maternal_for_compliance as $mat) {
    $compliance = getPrenatalComplianceStatus($pdo, $mat['maternal_record_id'], $mat['lmp_date'], $mat['monitoring_status'], $prenatal_schedule);
    if ($compliance['behind']) {
        $behind_maternal_count++;
    }
}

// Count infants currently overdue on their DOH EPI vaccination schedule
$epi_schedule = $pdo->query("SELECT * FROM epi_schedule")->fetchAll(PDO::FETCH_ASSOC);
$infants_for_fic = $pdo->query("
    SELECT infant_records.infant_record_id, r.birth_date
    FROM infant_records
    JOIN residents r ON infant_records.resident_id = r.resident_id
")->fetchAll(PDO::FETCH_ASSOC);

$overdue_infant_count = 0;
foreach ($infants_for_fic as $inf) {
    $fic = getFicStatus($pdo, $inf['infant_record_id'], $inf['birth_date'], $epi_schedule);
    if ($fic['overdue_count'] > 0) {
        $overdue_infant_count++;
    }
}

// --- Chart data ---

// Chart 1: active cases per purok (bar chart)
$purok_chart_raw = $pdo->query("
    SELECT r.purok, COUNT(*) as total
    FROM disease_cases dc
    JOIN residents r ON dc.resident_id = r.resident_id
    WHERE dc.status IN ('Active', 'Under monitoring')
    GROUP BY r.purok
")->fetchAll(PDO::FETCH_ASSOC);
$purok_chart_data = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
foreach ($purok_chart_raw as $row) {
    $purok_chart_data[(int) $row['purok']] = (int) $row['total'];
}

// Chart 2: total cases reported per month, last 6 months (line chart)
$monthly_totals_raw = $pdo->query("
    SELECT DATE_FORMAT(date_reported, '%Y-%m') as ym, COUNT(*) as total
    FROM disease_cases
    WHERE date_reported >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY ym ORDER BY ym
")->fetchAll(PDO::FETCH_ASSOC);
$monthly_labels = array_column($monthly_totals_raw, 'ym');
$monthly_values = array_map('intval', array_column($monthly_totals_raw, 'total'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></h3>
    <div>
      <?php if ($_SESSION['role'] === 'administrator'): ?>
        <a href="../admin/audit_log.php" class="btn btn-outline-secondary btn-sm me-2">Audit log</a>
        <a href="../admin/lgu_contacts.php" class="btn btn-outline-secondary btn-sm me-2">LGU Contacts</a>
      <?php endif; ?>
      <a href="../auth/logout.php" class="btn btn-outline-danger btn-sm">Log out</a>
    </div>
  </div>

  <?php foreach ($threshold_alerts as $alert): $level = getRiskLevel($alert['case_count']); ?>
    <?php if ($level === 'high' || $level === 'moderate'): ?>
    <?php
      $draft_title = "Health Advisory: " . $alert['disease_name'] . " Alert in Purok " . $alert['purok'];
      $draft_content = "The health center has recorded " . $alert['case_count'] . " active " . $alert['disease_name'] . " case(s) in Purok " . $alert['purok'] . ", exceeding the alert threshold. Residents are advised to take necessary precautions. Please contact the health center for more information.";
    ?>
    <div class="alert <?= $level === 'high' ? 'alert-danger' : 'alert-warning' ?>">
      <strong>Active alert: Purok <?= htmlspecialchars($alert['purok']) ?></strong> —
      <?= htmlspecialchars($alert['disease_name']) ?> cases have reached <?= $level ?> risk level
      (<?= $alert['case_count'] ?> active cases) — exceeds the configured threshold.
      <a href="../heatmap/heatmap.php">View heatmap</a>
      ·
      <a href="../announcements/announcements.php?draft_title=<?= urlencode($draft_title) ?>&draft_content=<?= urlencode($draft_content) ?>&draft_purok=<?= urlencode($alert['purok']) ?>">Draft public advisory</a>
      ·
      <a href="../reports/lgu_briefing.php?purok=<?= urlencode($alert['purok']) ?>&disease=<?= urlencode($alert['disease_name']) ?>&count=<?= urlencode($alert['case_count']) ?>" target="_blank">Generate LGU briefing (PDF)</a>
      ·
      <a href="../admin/notify_lgu.php?purok=<?= urlencode($alert['purok']) ?>&disease=<?= urlencode($alert['disease_name']) ?>&count=<?= urlencode($alert['case_count']) ?>" onclick="return confirm('Send SMS notification to all LGU contacts?')">Notify LGU via SMS</a>
    </div>
    <?php endif; ?>
  <?php endforeach; ?>

  <?php if (isset($_GET['sms_sent'])): ?>
  <div class="alert alert-success"><?= (int) $_GET['sms_sent'] ?> SMS notification(s) sent to LGU contacts (simulated).</div>
  <?php endif; ?>

  <?php if ($overdue_infant_count > 0): ?>
  <div class="alert alert-warning">
    <strong><?= $overdue_infant_count ?> infant<?= $overdue_infant_count > 1 ? 's' : '' ?> overdue</strong> for DOH EPI vaccinations.
    <a href="../infant/infant.php">View infant monitoring</a>
  </div>
  <?php endif; ?>

  <?php if ($behind_maternal_count > 0): ?>
  <div class="alert alert-warning">
    <strong><?= $behind_maternal_count ?> pregnant resident<?= $behind_maternal_count > 1 ? 's' : '' ?> behind</strong> on prenatal visit schedule.
    <a href="../maternal/maternal.php">View maternal health</a>
  </div>
  <?php endif; ?>

  <?php foreach ($seasonal_advisories as $adv): ?>
  <div class="alert alert-info">
    <strong>Seasonal risk advisory — <?= htmlspecialchars($adv['disease_name']) ?>:</strong>
    <?= htmlspecialchars($adv['advisory_note']) ?>
  </div>
  <?php endforeach; ?>

  <div class="row g-3 mb-4">
    <div class="col-md-2"><div class="card p-3 text-center"><h6>Total residents</h6><p class="fs-4 mb-0"><?= $total_residents ?></p></div></div>
    <div class="col-md-2"><div class="card p-3 text-center"><h6>Pregnant women</h6><p class="fs-4 mb-0"><?= $pregnant_count ?></p></div></div>
    <div class="col-md-2"><div class="card p-3 text-center"><h6>Infants 0-12mo</h6><p class="fs-4 mb-0"><?= $infant_count ?></p></div></div>
    <div class="col-md-3"><div class="card p-3 text-center"><h6>Active disease cases</h6><p class="fs-4 mb-0"><?= $disease_count ?></p></div></div>
    <div class="col-md-3"><div class="card p-3 text-center"><h6>Cases reported this month</h6><p class="fs-4 mb-0"><?= $cases_this_month ?></p></div></div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <div class="card p-3">
        <h6>Active cases by purok</h6>
        <canvas id="purokChart" height="200"></canvas>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card p-3">
        <h6>Case trend, last 6 months</h6>
        <canvas id="trendChart" height="200"></canvas>
      </div>
    </div>
  </div>

  <?php if (!empty($disease_trends)): ?>
  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Disease trend indicators <small class="text-muted">(month-over-month, based on recorded cases)</small></h5>
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

  <div class="mb-3">
    <a href="../residents/residents.php" class="btn btn-primary btn-sm">Resident Profiling</a>
    <a href="../maternal/maternal.php" class="btn btn-primary btn-sm">Maternal Health</a>
    <a href="../infant/infant.php" class="btn btn-primary btn-sm">Infant Monitoring</a>
    <a href="../vaccination/vaccination.php" class="btn btn-primary btn-sm">Vaccination Records</a>
    <a href="../disease/disease.php" class="btn btn-primary btn-sm">Disease Recording</a>
    <a href="../heatmap/heatmap.php" class="btn btn-primary btn-sm">Heatmap</a>
    <a href="../reports/reports.php" class="btn btn-primary btn-sm">Reports</a>
    <a href="../announcements/announcements.php" class="btn btn-primary btn-sm">Announcements</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
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
        plugins: { legend: { display: false } },
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
</body>
</html>