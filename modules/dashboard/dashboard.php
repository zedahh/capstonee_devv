<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';
require '../../includes/functions.php';

$total_residents = $pdo->query("SELECT COUNT(*) FROM residents WHERE is_active = 1")->fetchColumn();
$pregnant_count = $pdo->query("SELECT COUNT(*) FROM maternal_records WHERE monitoring_status IN ('Ongoing', 'High-risk') AND is_active = 1")->fetchColumn();
$infant_count = $pdo->query("SELECT COUNT(*) FROM infant_records ir JOIN residents r ON ir.resident_id = r.resident_id WHERE r.birth_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND ir.is_active = 1")->fetchColumn();
$disease_count = $pdo->query("SELECT COUNT(*) FROM disease_cases WHERE status IN ('Active', 'Under monitoring') AND is_active = 1")->fetchColumn();

// Total cases reported this calendar month, regardless of current status
$cases_this_month = $pdo->query("
    SELECT COUNT(*) FROM disease_cases
    WHERE YEAR(date_reported) = YEAR(CURDATE()) AND MONTH(date_reported) = MONTH(CURDATE()) AND is_active = 1
")->fetchColumn();

// Threshold alert check: any disease + purok combination currently over threshold
// HAVING case_count >= 1 just avoids empty rows; real risk classification is rate-based below
$threshold_alerts = $pdo->query("
    SELECT r.purok, dc.disease_name, COUNT(*) as case_count
    FROM disease_cases dc
    JOIN residents r ON dc.resident_id = r.resident_id
   WHERE dc.status IN ('Active', 'Under monitoring') AND dc.is_active = 1
    GROUP BY r.purok, dc.disease_name
    HAVING case_count >= 1
    ORDER BY case_count DESC
")->fetchAll(PDO::FETCH_ASSOC);

function getRiskLevel($count, $population) {
    if ($population <= 0) return 'low';
    $rate = ($count / $population) * 100;
    if ($rate >= 7) return 'high';
    if ($rate >= 3) return 'moderate';
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
    WHERE date_reported >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH) AND is_active = 1
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
$maternal_for_compliance = $pdo->query("SELECT maternal_record_id, lmp_date, monitoring_status FROM maternal_records WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);

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
    WHERE infant_records.is_active = 1
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
    WHERE dc.status IN ('Active', 'Under monitoring') AND dc.is_active = 1
    GROUP BY r.purok
")->fetchAll(PDO::FETCH_ASSOC);
$purok_chart_data = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
foreach ($purok_chart_raw as $row) {
    $purok_chart_data[(int) $row['purok']] = (int) $row['total'];
}
// Resident population per purok, for population-aware tooltips
$purok_population_raw = $pdo->query("
    SELECT purok, COUNT(*) as total
    FROM residents
    WHERE is_active = 1
    GROUP BY purok
")->fetchAll(PDO::FETCH_ASSOC);
$purok_population = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
foreach ($purok_population_raw as $row) {
    $purok_population[(int) $row['purok']] = (int) $row['total'];
}

// Chart 2: total cases reported per month, last 6 months (line chart)
$monthly_totals_raw = $pdo->query("
    SELECT DATE_FORMAT(date_reported, '%Y-%m') as ym, COUNT(*) as total
    FROM disease_cases
    WHERE date_reported >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) AND is_active = 1
    GROUP BY ym ORDER BY ym
")->fetchAll(PDO::FETCH_ASSOC);
$monthly_labels = array_column($monthly_totals_raw, 'ym');
$monthly_values = array_map('intval', array_column($monthly_totals_raw, 'total'));

require 'dashboard_view.php';