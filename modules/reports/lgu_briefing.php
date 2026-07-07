<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require '../../config/database.php';
require '../../vendor/autoload.php';

$purok = $_GET['purok'] ?? '';
$disease = $_GET['disease'] ?? '';
$count = (int) ($_GET['count'] ?? 0);

if ($purok === '' || $disease === '') {
    die('Missing alert details.');
}

// Log that a briefing was generated, same audit trail as everything else
$log = $pdo->prepare("INSERT INTO audit_logs (user_id, action, table_name, details) VALUES (?, 'GENERATE', 'lgu_briefing', ?)");
$log->execute([$_SESSION['user_id'], "LGU briefing generated: $disease in Purok $purok ($count cases)"]);

$today = date('F j, Y');
$prepared_by = htmlspecialchars($_SESSION['full_name']);

$html = "
<h3>Barangay Santa Ines Health Center</h3>
<p><strong>Health Advisory Memorandum</strong></p>
<hr>
<p><strong>To:</strong> The Punong Barangay and Barangay Council, Barangay Santa Ines, Plaridel, Bulacan<br>
<strong>From:</strong> Barangay Santa Ines Health Center<br>
<strong>Date:</strong> $today<br>
<strong>Subject:</strong> Health Advisory &ndash; $disease Cases in Purok $purok</p>
<hr>
<p>This is to formally inform your office that the Barangay Health Center has recorded <strong>$count active case(s)</strong> 
of <strong>$disease</strong> in <strong>Purok $purok</strong>, which has exceeded the configured monitoring threshold 
for this condition.</p>

<p>The health center respectfully recommends that the barangay office consider appropriate community-level action, 
which may include (but is not limited to): coordinated clean-up or vector control activities, public health advisories 
to affected residents, and/or resource support for the health center's response efforts.</p>

<p>Should the situation continue to escalate or require support beyond the health center's current capacity, 
the health center will coordinate further with the Rural Health Unit as appropriate.</p>

<p>For further details or clarification, please contact the Barangay Santa Ines Health Center directly.</p>

<br><br>
<p>Prepared by:<br>
<strong>$prepared_by</strong><br>
Barangay Santa Ines Health Center</p>
";

$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output('LGU_Briefing_' . $disease . '_Purok' . $purok . '_' . date('Y-m-d') . '.pdf', 'I');