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

require 'my_record_view.php';