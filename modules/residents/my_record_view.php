<?php
/** @var string $code */
/** @var array $disease_cases */
/** @var string $end_date */
/** @var array $fic_status */
/** @var array|null $infant_record */
/** @var array $maternal_compliance */
/** @var array|null $maternal_record */
/** @var bool $not_found */
/** @var array|null $resident */
/** @var string $start_date */
/** @var array $vaccinations */
if (!isset($code)) { return; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Health Record - Barangay Santa Ines</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="../../assets/css/custom.css" rel="stylesheet">
<style>
  body.bg-light {
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: #2C333A;
    background: linear-gradient(160deg, #eef4fc 0%, #f7f9fa 45%, #eaf2fb 100%) !important;
    min-height: 100vh;
  }
  h1,h2,h3,h4,h5,h6 { font-family: 'Poppins', sans-serif; font-weight: 600; color: #2C333A; }

  .record-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 1.5rem;
  }
  .record-icon-badge {
    width: 58px; height: 58px;
    border-radius: 16px;
    background: linear-gradient(135deg, #1B5FC0, #123F87);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    box-shadow: 0 8px 24px rgba(30,41,59,0.10);
  }

  .card {
    border: 1px solid #E3E7EA;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(30,41,59,0.06), 0 1px 2px rgba(30,41,59,0.08);
  }
  .card-body, .card.p-4, .card.p-3 { padding: 1.5rem; }

  .btn { border-radius: 10px; font-weight: 500; transition: transform 0.12s ease, box-shadow 0.12s ease; }
  .btn-primary {
    background: linear-gradient(135deg, #1B5FC0, #123F87);
    border: none;
  }
  .btn-primary:hover, .btn-primary:focus {
    filter: brightness(0.95);
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(30,41,59,0.10);
    color: #fff;
  }

  .form-label { font-weight: 500; font-size: 0.85rem; color: #5C6670; }
  .form-control {
    border-radius: 10px;
    border: 1px solid #D3D9DE;
  }
  .form-control:focus {
    border-color: #1B5FC0;
    box-shadow: 0 0 0 3px rgba(27,95,192,0.14);
  }
  .input-group .btn-primary { border-radius: 0 10px 10px 0; }
  .input-group .form-control { border-radius: 10px 0 0 10px; }

  .alert {
    border: none;
    border-left: 4px solid transparent;
    border-radius: 8px;
  }
  .alert-warning { background: #FDF2E4; color: #8a5a12; border-left-color: #E0932E; }

  .badge { font-weight: 600; padding: 0.4em 0.75em; border-radius: 999px; font-size: 0.72rem; }

  .table thead th {
    background: linear-gradient(135deg, #EAF2FF, #eaf2fb);
    color: #123F87;
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
    border-bottom: none;
  }

  .resident-name-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.25rem; }
  .resident-icon {
    width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, #1B5FC0, #123F87);
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-size: 1.05rem;
  }
  .record-section-title { display: flex; align-items: center; font-weight: 600; }
</style>
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 700px;">
  <div class="record-header">
    <div class="record-icon-badge"><i class="fa-solid fa-id-card"></i></div>
    <h3 class="mb-0 text-center">My Health Record</h3>
  </div>

 <form method="GET" action="" class="card p-3 mb-4">
    <label class="form-label">Enter your QR code (found on your health ID/slip)</label>
    <div class="input-group mb-2">
      <input type="text" name="code" class="form-control" value="<?= htmlspecialchars($code) ?>" placeholder="e.g. RES-...">
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-1"></i> Look up</button>
    </div>
    <label class="form-label small mb-1">Optional: filter records by date range</label>
    <div class="row g-2">
      <div class="col"><input type="date" name="start_date" class="form-control form-control-sm" value="<?= htmlspecialchars($start_date) ?>"></div>
      <div class="col"><input type="date" name="end_date" class="form-control form-control-sm" value="<?= htmlspecialchars($end_date) ?>"></div>
    </div>
  </form>

  <?php if ($not_found): ?>
    <div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation me-2"></i>No record found. Please check your QR code and try again, or ask the health center for assistance.</div>
  <?php endif; ?>

  <?php if ($resident): ?>
  <div class="card p-4">
    <div class="resident-name-row"><div class="resident-icon"><i class="fa-solid fa-user"></i></div><h5 class="mb-0"><?= htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']) ?></h5></div>
    <p class="text-muted mb-3">Purok <?= htmlspecialchars($resident['purok']) ?> · Born <?= htmlspecialchars($resident['birth_date']) ?></p>

    <?php if (!empty($disease_cases)): ?>
    <h6 class="mt-3 record-section-title"><i class="fa-solid fa-virus me-2"></i>Illness records</h6>
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
    <h6 class="mt-3 record-section-title"><i class="fa-solid fa-person-pregnant me-2"></i>Maternal health</h6>
    <p>Status: <?= htmlspecialchars($maternal_record['monitoring_status']) ?> · Expected delivery: <?= htmlspecialchars($maternal_record['edd_date']) ?><br>
    Prenatal visit compliance: <span class="badge bg-<?= $maternal_compliance['badge'] ?>"><?= htmlspecialchars($maternal_compliance['label']) ?></span></p>
    <?php endif; ?>

    <?php if ($infant_record): ?>
    <h6 class="mt-3 record-section-title"><i class="fa-solid fa-baby me-2"></i>Infant health</h6>
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