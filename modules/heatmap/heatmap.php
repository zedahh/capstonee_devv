<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';
require '../../includes/functions.php';

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$is_filtered = ($start_date !== '' && $end_date !== '');

if ($is_filtered) {
    $stmt = $pdo->prepare("
        SELECT r.purok, COUNT(*) as case_count
        FROM disease_cases dc
        JOIN residents r ON dc.resident_id = r.resident_id
        WHERE dc.date_reported BETWEEN ? AND ?
        GROUP BY r.purok
    ");
    $stmt->execute([$start_date, $end_date]);
} else {
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

// Fetch individual active cases with resident info, for the clickable dots
if ($is_filtered) {
    $caseStmt = $pdo->prepare("
        SELECT dc.disease_name, dc.date_reported, dc.status, r.resident_id, r.first_name, r.last_name, r.purok, r.approx_lat, r.approx_lng
        FROM disease_cases dc
        JOIN residents r ON dc.resident_id = r.resident_id
        WHERE dc.date_reported BETWEEN ? AND ?
    ");
    $caseStmt->execute([$start_date, $end_date]);
} else {
    $caseStmt = $pdo->query("
        SELECT dc.disease_name, dc.date_reported, dc.status, r.resident_id, r.first_name, r.last_name, r.purok, r.approx_lat, r.approx_lng
        FROM disease_cases dc
        JOIN residents r ON dc.resident_id = r.resident_id
        WHERE dc.status IN ('Active', 'Under monitoring')
    ");
}
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

require 'heatmap_view.php';