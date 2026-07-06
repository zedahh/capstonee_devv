<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';
require '../../vendor/autoload.php';

$total_residents = $pdo->query("SELECT COUNT(*) FROM residents WHERE is_active = 1")->fetchColumn();
$total_maternal = $pdo->query("SELECT COUNT(*) FROM maternal_records WHERE monitoring_status IN ('Ongoing', 'High-risk')")->fetchColumn();
$total_infants = $pdo->query("SELECT COUNT(*) FROM infant_records ir JOIN residents r ON ir.resident_id = r.resident_id WHERE r.birth_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)")->fetchColumn();
$total_vaccinations = $pdo->query("SELECT COUNT(*) FROM vaccination_records")->fetchColumn();
$total_disease_cases = $pdo->query("SELECT COUNT(*) FROM disease_cases WHERE status IN ('Active', 'Under monitoring')")->fetchColumn();

$disease_breakdown = $pdo->query("SELECT disease_name, COUNT(*) as total FROM disease_cases GROUP BY disease_name ORDER BY total DESC")->fetchAll(PDO::FETCH_ASSOC);
$purok_breakdown = $pdo->query("SELECT r.purok, COUNT(*) as total FROM residents r WHERE r.is_active = 1 GROUP BY r.purok ORDER BY r.purok")->fetchAll(PDO::FETCH_ASSOC);

$disease_rows = '';
foreach ($disease_breakdown as $d) {
    $disease_rows .= "<tr><td>" . htmlspecialchars($d['disease_name']) . "</td><td>{$d['total']}</td></tr>";
}

$purok_rows = '';
foreach ($purok_breakdown as $p) {
    $purok_rows .= "<tr><td>Purok {$p['purok']}</td><td>{$p['total']}</td></tr>";
}

$html = "
<h2>Barangay Santa Ines Health Summary Report</h2>
<p>Generated: " . date('F j, Y g:i A') . "</p>
<table border='1' cellpadding='5' width='100%'>
<tr><td>Total residents</td><td>$total_residents</td></tr>
<tr><td>Active/high-risk pregnancies</td><td>$total_maternal</td></tr>
<tr><td>Infants (0-12 months)</td><td>$total_infants</td></tr>
<tr><td>Total vaccinations administered</td><td>$total_vaccinations</td></tr>
<tr><td>Active disease cases</td><td>$total_disease_cases</td></tr>
</table>
<h3>Disease cases by type</h3>
<table border='1' cellpadding='5' width='100%'>
<tr><th>Disease</th><th>Total cases</th></tr>
$disease_rows
</table>
<h3>Residents by purok</h3>
<table border='1' cellpadding='5' width='100%'>
<tr><th>Purok</th><th>Total residents</th></tr>
$purok_rows
</table>
";

$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output('barangay_health_report_' . date('Y-m-d') . '.pdf', 'I');