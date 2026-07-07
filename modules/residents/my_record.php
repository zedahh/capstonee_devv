<?php
require '../../config/database.php';
require '../../includes/functions.php';

$code = trim($_GET['code'] ?? $_POST['code'] ?? '');
$resident = null;
$not_found = false;

if ($code !== '') {
    $stmt = $pdo->prepare("SELECT * FROM residents WHERE qr_code = ? AND is_active = 1");
    $stmt->execute([$code]);
    $resident = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$resident) {
        $not_found = true;
    }
}

$start_date = trim($_GET['start_date'] ?? '');
$end_date = trim($_GET['end_date'] ?? '');
$disease_cases = [];
$maternal_record = null;
$maternal_compliance = null;
$infant_record = null;
$fic_status = null;
$vaccinations = [];

if ($resident) {
   if ($start_date && $end_date) {
        $stmt = $pdo->prepare("SELECT disease_name, date_reported, status FROM disease_cases WHERE resident_id = ? AND date_reported BETWEEN ? AND ? ORDER BY date_reported DESC");
        $stmt->execute([$resident['resident_id'], $start_date, $end_date]);
    } else {
        $stmt = $pdo->prepare("SELECT disease_name, date_reported, status FROM disease_cases WHERE resident_id = ? ORDER BY date_reported DESC");
        $stmt->execute([$resident['resident_id']]);
    }
    $disease_cases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT * FROM maternal_records WHERE resident_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$resident['resident_id']]);
    $maternal_record = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($maternal_record) {
        $prenatal_schedule = $pdo->query("SELECT * FROM prenatal_visit_schedule")->fetchAll(PDO::FETCH_ASSOC);
        $maternal_compliance = getPrenatalComplianceStatus($pdo, $maternal_record['maternal_record_id'], $maternal_record['lmp_date'], $maternal_record['monitoring_status'], $prenatal_schedule);
    }

    $stmt = $pdo->prepare("SELECT * FROM infant_records WHERE resident_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$resident['resident_id']]);
    $infant_record = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($infant_record) {
        $epi_schedule = $pdo->query("SELECT * FROM epi_schedule")->fetchAll(PDO::FETCH_ASSOC);
        $fic_status = getFicStatus($pdo, $infant_record['infant_record_id'], $resident['birth_date'], $epi_schedule);

       if ($start_date && $end_date) {
            $stmt = $pdo->prepare("SELECT vaccine_name, date_administered FROM vaccination_records WHERE infant_record_id = ? AND date_administered BETWEEN ? AND ? ORDER BY date_administered");
            $stmt->execute([$infant_record['infant_record_id'], $start_date, $end_date]);
        } else {
            $stmt = $pdo->prepare("SELECT vaccine_name, date_administered FROM vaccination_records WHERE infant_record_id = ? ORDER BY date_administered");
            $stmt->execute([$infant_record['infant_record_id']]);
        }
        $vaccinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Health Record - Barangay Santa Ines</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 700px;">
  <h3 class="mb-4 text-center">My Health Record</h3>

 <form method="GET" action="" class="card p-3 mb-4">
    <label class="form-label">Enter your QR code (found on your health ID/slip)</label>
    <div class="input-group mb-2">
      <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($code) ?>" placeholder="e.g. RES-...">
      <button type="submit" class="btn btn-primary">Look up</button>
    </div>
    <label class="form-label small mb-1">Optional: filter records by date range</label>
    <div class="row g-2">
      <div class="col"><input type="date" name="start_date" class="form-control form-control-sm" value="<?= htmlspecialchars($start_date) ?>"></div>
      <div class="col"><input type="date" name="end_date" class="form-control form-control-sm" value="<?= htmlspecialchars($end_date) ?>"></div>
    </div>
  </form>

  <?php if ($not_found): ?>
    <div class="alert alert-warning">No record found. Please check your QR code and try again, or ask the health center for assistance.</div>
  <?php endif; ?>

  <?php if ($resident): ?>
  <div class="card p-4">
    <h5><?= htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']) ?></h5>
    <p class="text-muted mb-3">Purok <?= htmlspecialchars($resident['purok']) ?> · Born <?= htmlspecialchars($resident['birth_date']) ?></p>

    <?php if (!empty($disease_cases)): ?>
    <h6 class="mt-3">Illness records</h6>
    <table class="table table-sm">
      <thead><tr><th>Condition</th><th>Date reported</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($disease_cases as $d): ?>
        <tr><td><?= htmlspecialchars($d['disease_name']) ?></td><td><?= htmlspecialchars($d['date_reported']) ?></td><td><?= htmlspecialchars($d['status']) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>

    <?php if ($maternal_record): ?>
    <h6 class="mt-3">Maternal health</h6>
    <p>Status: <?= htmlspecialchars($maternal_record['monitoring_status']) ?> · Expected delivery: <?= htmlspecialchars($maternal_record['edd_date']) ?><br>
    Prenatal visit compliance: <span class="badge bg-<?= $maternal_compliance['badge'] ?>"><?= htmlspecialchars($maternal_compliance['label']) ?></span></p>
    <?php endif; ?>

    <?php if ($infant_record): ?>
    <h6 class="mt-3">Infant health</h6>
    <p>Vaccination status: <span class="badge bg-<?= $fic_status['badge'] ?>"><?= htmlspecialchars($fic_status['label']) ?></span></p>
    <?php if (!empty($vaccinations)): ?>
    <table class="table table-sm">
      <thead><tr><th>Vaccine</th><th>Date given</th></tr></thead>
      <tbody>
        <?php foreach ($vaccinations as $v): ?>
        <tr><td><?= htmlspecialchars($v['vaccine_name']) ?></td><td><?= htmlspecialchars($v['date_administered']) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
    <?php endif; ?>

    <?php if (empty($disease_cases) && !$maternal_record && !$infant_record): ?>
    <p class="text-muted">No additional health records on file yet.</p>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
</body>
</html>