<?php if (!isset($announcements)) { return; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Barangay Santa Ines - Health Announcements</title>
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
    --bhms-blue-light: #E8F1FA;
    --bhms-gray-600: #5C6670;
    --bhms-gray-800: #2C333A;
    --bhms-gray-200: #E3E7EA;
    --bhms-radius-lg: 16px;
    --bhms-radius: 12px;
    --bhms-shadow-sm: 0 1px 3px rgba(30,41,59,0.06), 0 1px 2px rgba(30,41,59,0.08);
    --bhms-shadow-md: 0 8px 24px rgba(30,41,59,0.10);
  }

  body {
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: var(--bhms-gray-800);
    background: linear-gradient(160deg, #f4faf7 0%, #f7f9fa 45%, #eaf2fb 100%);
    -webkit-font-smoothing: antialiased;
  }

  .masthead {
    background: linear-gradient(135deg, var(--bhms-green-darker) 0%, var(--bhms-green-dark) 55%, var(--bhms-green) 100%);
    color: #fff;
    padding: 2.75rem 1.5rem 3.5rem;
    text-align: center;
  }
  .masthead .badge-eyebrow {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: rgba(255,255,255,0.14);
    padding: 0.35rem 0.9rem; border-radius: 999px;
    font-size: 0.78rem; font-weight: 500; letter-spacing: 0.03em;
    margin-bottom: 0.9rem;
  }
  .masthead h1 { font-weight: 600; font-size: 1.6rem; margin-bottom: 0.35rem; }
  .masthead p { opacity: 0.85; font-size: 0.92rem; margin: 0; }

  .content-wrap { max-width: 640px; margin: -2rem auto 0; padding: 0 1.25rem 3rem; }

  .record-cta {
    background: #fff;
    border-radius: var(--bhms-radius-lg);
    box-shadow: var(--bhms-shadow-md);
    padding: 1.4rem 1.5rem;
    display: flex; align-items: center; gap: 1rem;
    text-decoration: none;
    border: 1px solid var(--bhms-gray-200);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    margin-bottom: 2rem;
  }
  .record-cta:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(30,41,59,0.14); }
  .record-cta-icon {
    width: 52px; height: 52px; border-radius: 14px; flex-shrink: 0;
    background: var(--bhms-blue-light); color: var(--bhms-blue);
    display: flex; align-items: center; justify-content: center; font-size: 1.4rem;
  }
  .record-cta-text h6 { margin: 0 0 0.15rem; font-weight: 600; color: var(--bhms-gray-800); font-size: 0.98rem; }
  .record-cta-text p { margin: 0; font-size: 0.82rem; color: var(--bhms-gray-600); }
  .record-cta-arrow { margin-left: auto; color: var(--bhms-gray-600); font-size: 0.9rem; }

  .section-label {
    font-size: 0.78rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em;
    color: var(--bhms-gray-600); margin-bottom: 1rem; padding-left: 0.1rem;
  }

  .announcement-card {
    background: #fff;
    border-radius: var(--bhms-radius);
    box-shadow: var(--bhms-shadow-sm);
    border-left: 4px solid var(--bhms-green);
    padding: 1.25rem 1.4rem;
    margin-bottom: 1rem;
  }
  .announcement-card.purok-scoped { border-left-color: var(--bhms-blue); }
  .announcement-top { display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.6rem; }
  .announcement-icon {
    width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--bhms-green), var(--bhms-blue));
    color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;
  }
  .announcement-card h6 { font-weight: 600; margin-bottom: 0.15rem; }
  .announcement-meta { font-size: 0.76rem; color: var(--bhms-gray-600); display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap; }
  .purok-badge {
    font-size: 0.68rem; font-weight: 600; padding: 0.2rem 0.6rem; border-radius: 999px;
    background: var(--bhms-green-light); color: var(--bhms-green-darker);
  }
  .announcement-card p.content-text { font-size: 0.92rem; color: var(--bhms-gray-800); margin: 0.5rem 0 0; line-height: 1.55; }

  .empty-state {
    text-align: center; padding: 3rem 1.5rem; background: #fff;
    border-radius: var(--bhms-radius-lg); box-shadow: var(--bhms-shadow-sm);
  }
  .empty-state i { font-size: 2.2rem; color: var(--bhms-gray-200); margin-bottom: 1rem; }
  .empty-state p { color: var(--bhms-gray-600); margin: 0; font-size: 0.92rem; }
</style>
</head>
<body>

<div class="masthead">
  <span class="badge-eyebrow"><i class="fa-solid fa-notes-medical"></i> Barangay Santa Ines Health Center</span>
  <h1>Health Announcements</h1>
  <p>Vaccination schedules, health advisories, and community updates</p>
</div>

<div class="content-wrap">

  <a href="../residents/my_record.php" class="record-cta">
    <div class="record-cta-icon"><i class="fa-solid fa-qrcode"></i></div>
    <div class="record-cta-text">
      <h6>Check your own health record</h6>
      <p>Look up your record using your personal QR code</p>
    </div>
    <i class="fa-solid fa-chevron-right record-cta-arrow"></i>
  </a>

  <div class="section-label">Latest updates</div>

  <?php if (empty($announcements)): ?>
    <div class="empty-state">
      <i class="fa-regular fa-bell"></i>
      <p>No announcements right now. Please check back soon.</p>
    </div>
  <?php endif; ?>

  <?php foreach ($announcements as $a): ?>
  <?php $is_scoped = htmlspecialchars($a['target_purok']) !== 'All'; ?>
  <div class="announcement-card <?= $is_scoped ? 'purok-scoped' : '' ?>">
    <div class="announcement-top">
      <div class="announcement-icon"><i class="fa-solid fa-bullhorn"></i></div>
      <div>
        <h6><?= htmlspecialchars($a['title']) ?></h6>
        <div class="announcement-meta">
          <span class="purok-badge"><?= $is_scoped ? 'Purok ' . htmlspecialchars($a['target_purok']) : 'All puroks' ?></span>
          <span><i class="fa-regular fa-calendar me-1"></i><?= htmlspecialchars(date('F j, Y', strtotime($a['created_at']))) ?></span>
        </div>
      </div>
    </div>
    <p class="content-text"><?= nl2br(htmlspecialchars($a['content'])) ?></p>
  </div>
  <?php endforeach; ?>

</div>
</body>
</html>