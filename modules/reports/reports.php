<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';
require '../../includes/functions.php';

$total_residents = $pdo->query("SELECT COUNT(*) FROM residents WHERE is_active = 1")->fetchColumn();
$total_maternal = $pdo->query("SELECT COUNT(*) FROM maternal_records WHERE monitoring_status IN ('Ongoing', 'High-risk')")->fetchColumn();
$total_infants = $pdo->query("SELECT COUNT(*) FROM infant_records ir JOIN residents r ON ir.resident_id = r.resident_id WHERE r.birth_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)")->fetchColumn();
$total_vaccinations = $pdo->query("SELECT COUNT(*) FROM vaccination_records")->fetchColumn();
$total_disease_cases = $pdo->query("SELECT COUNT(*) FROM disease_cases WHERE status IN ('Active', 'Under monitoring')")->fetchColumn();

$disease_breakdown = $pdo->query("
    SELECT disease_name, COUNT(*) as total
    FROM disease_cases
    GROUP BY disease_name
    ORDER BY total DESC
")->fetchAll(PDO::FETCH_ASSOC);

$purok_breakdown = $pdo->query("
    SELECT r.purok, COUNT(*) as total
    FROM residents r
    WHERE r.is_active = 1
    GROUP BY r.purok
    ORDER BY r.purok
")->fetchAll(PDO::FETCH_ASSOC);

// --- Interpretive insights (rule-based, transparent, no AI claim) ---

$insights = [];

// Purok with the most residents
$purok_by_residents = $purok_breakdown;
usort($purok_by_residents, fn($a, $b) => $b['total'] <=> $a['total']);
if (!empty($purok_by_residents) && $purok_by_residents[0]['total'] > 0) {
    $insights[] = "Purok " . $purok_by_residents[0]['purok'] . " has the highest number of registered residents (" . $purok_by_residents[0]['total'] . ").";
}

// Purok + disease combination with the highest active case count
$top_case_combo = $pdo->query("
    SELECT r.purok, dc.disease_name, COUNT(*) as total
    FROM disease_cases dc
    JOIN residents r ON dc.resident_id = r.resident_id
    WHERE dc.status IN ('Active', 'Under monitoring')
    GROUP BY r.purok, dc.disease_name
    ORDER BY total DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);
if ($top_case_combo) {
    $insights[] = "Purok " . $top_case_combo['purok'] . " currently reports the highest concentration of active cases, driven primarily by " . $top_case_combo['disease_name'] . " (" . $top_case_combo['total'] . " case" . ($top_case_combo['total'] > 1 ? 's' : '') . ").";
}

// FIC (immunization) completion summary
$epi_schedule = $pdo->query("SELECT * FROM epi_schedule")->fetchAll(PDO::FETCH_ASSOC);
$infants_all = $pdo->query("
    SELECT infant_records.infant_record_id, r.birth_date
    FROM infant_records
    JOIN residents r ON infant_records.resident_id = r.resident_id
")->fetchAll(PDO::FETCH_ASSOC);
$fic_complete = 0;
$fic_overdue = 0;
$fic_total = count($infants_all);
foreach ($infants_all as $inf) {
    $fic = getFicStatus($pdo, $inf['infant_record_id'], $inf['birth_date'], $epi_schedule);
    if ($fic['badge'] === 'success') $fic_complete++;
    if ($fic['overdue_count'] > 0) $fic_overdue++;
}
if ($fic_total > 0) {
    $pct = round(($fic_complete / $fic_total) * 100);
    $insights[] = "$fic_complete out of $fic_total infants ($pct%) are fully immunized for their age; $fic_overdue remain overdue on DOH-scheduled vaccinations.";
}

// Prenatal visit compliance summary
$prenatal_schedule = $pdo->query("SELECT * FROM prenatal_visit_schedule")->fetchAll(PDO::FETCH_ASSOC);
$maternal_all = $pdo->query("SELECT maternal_record_id, lmp_date, monitoring_status FROM maternal_records")->fetchAll(PDO::FETCH_ASSOC);
$prenatal_behind = 0;
$prenatal_total = count($maternal_all);
foreach ($maternal_all as $mat) {
    $comp = getPrenatalComplianceStatus($pdo, $mat['maternal_record_id'], $mat['lmp_date'], $mat['monitoring_status'], $prenatal_schedule);
    if ($comp['behind']) $prenatal_behind++;
}
if ($prenatal_total > 0) {
    $insights[] = "$prenatal_behind out of $prenatal_total pregnant resident" . ($prenatal_total > 1 ? 's are' : ' is') . " currently behind on the recommended prenatal visit schedule.";
}

// Month-over-month total case trend
$monthly_totals = $pdo->query("
    SELECT DATE_FORMAT(date_reported, '%Y-%m') as ym, COUNT(*) as total
    FROM disease_cases
    WHERE date_reported >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)
    GROUP BY ym ORDER BY ym
")->fetchAll(PDO::FETCH_ASSOC);
if (count($monthly_totals) >= 2) {
    $last = (int) $monthly_totals[count($monthly_totals) - 1]['total'];
    $prev = (int) $monthly_totals[count($monthly_totals) - 2]['total'];
    if ($last > $prev) {
        $insights[] = "Active disease cases have increased from $prev to $last compared to the previous month.";
    } elseif ($last < $prev) {
        $insights[] = "Active disease cases have declined from $prev to $last compared to the previous month.";
    } else {
        $insights[] = "Active disease cases have remained stable at $last compared to the previous month.";
    }
} else {
    $insights[] = "Not enough recorded history yet to determine a month-over-month case trend.";
}

require 'reports_view.php';