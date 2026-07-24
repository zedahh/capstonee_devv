<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';
require '../../includes/functions.php';
$purok_boundaries_json = json_encode(getPurokBoundaries());

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$is_filtered = ($start_date !== '' && $end_date !== '');
$selected_disease = trim($_GET['disease'] ?? '');

// List of diseases actually on record, for the filter dropdown
$available_diseases = $pdo->query("SELECT DISTINCT disease_name FROM disease_cases WHERE is_active = 1 ORDER BY disease_name")->fetchAll(PDO::FETCH_COLUMN);

// Build the WHERE clause once, shared by both queries below
$where_conditions = ['dc.is_active = 1'];
$params = [];

if ($is_filtered) {
    $where_conditions[] = 'dc.date_reported BETWEEN ? AND ?';
    $params[] = $start_date;
    $params[] = $end_date;
} else {
    $where_conditions[] = "dc.status IN ('Active', 'Under monitoring')";
}

if ($selected_disease !== '') {
    $where_conditions[] = 'dc.disease_name = ?';
    $params[] = $selected_disease;
}

$where_clause = implode(' AND ', $where_conditions);

$stmt = $pdo->prepare("
    SELECT r.purok, COUNT(*) as case_count
    FROM disease_cases dc
    JOIN residents r ON dc.resident_id = r.resident_id
    WHERE $where_clause
    GROUP BY r.purok
");
$stmt->execute($params);
$counts_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

$purok_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
foreach ($counts_raw as $row) {
    $purok_counts[(int)$row['purok']] = (int)$row['case_count'];
}

// Resident population per purok, so risk level reflects rate, not raw count
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

function getRiskLevel($count, $population) {
    if ($population <= 0) {
        return ['level' => 'Low', 'color' => '#639922', 'rate' => 0];
    }
    $rate = ($count / $population) * 100;
    if ($rate >= 7) return ['level' => 'High', 'color' => '#E24B4A', 'rate' => $rate];
    if ($rate >= 3) return ['level' => 'Moderate', 'color' => '#EF9F27', 'rate' => $rate];
    return ['level' => 'Low', 'color' => '#639922', 'rate' => $rate];
}

$ranking = $purok_counts;
arsort($ranking);

// Fetch individual cases with resident info, for the clickable dots (same filters applied)
$caseStmt = $pdo->prepare("
    SELECT dc.disease_name, dc.date_reported, dc.status, r.resident_id, r.first_name, r.last_name, r.purok, r.approx_lat, r.approx_lng
    FROM disease_cases dc
    JOIN residents r ON dc.resident_id = r.resident_id
    WHERE $where_clause
");
$caseStmt->execute($params);
$cases = $caseStmt->fetchAll(PDO::FETCH_ASSOC);

// Generate and save an approximate location for any resident who doesn't have one yet,
// then group all case records by resident so each person gets exactly one marker
$residents_map = [];
foreach ($cases as $c) {
    if ($c['approx_lat'] === null || $c['approx_lng'] === null) {
        [$lat, $lng] = generateApproxLocation($c['purok']);
        $upd = $pdo->prepare("UPDATE residents SET approx_lat = ?, approx_lng = ? WHERE resident_id = ?");
        $upd->execute([$lat, $lng, $c['resident_id']]);
        $c['approx_lat'] = $lat;
        $c['approx_lng'] = $lng;
    }

    $rid = $c['resident_id'];
    if (!isset($residents_map[$rid])) {
        $residents_map[$rid] = [
            'lat' => (float) $c['approx_lat'],
            'lng' => (float) $c['approx_lng'],
            'name' => $c['first_name'] . ' ' . $c['last_name'],
            'purok' => $c['purok'],
            'cases' => [],
        ];
    }
    $residents_map[$rid]['cases'][] = [
        'disease' => $c['disease_name'],
        'date' => $c['date_reported'],
        'status' => $c['status'],
    ];
}
$case_points = array_values($residents_map);
$purok_population_json = json_encode($purok_population);

require 'heatmap_view.php';