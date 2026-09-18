<?php if (!isset($insights_html)) { return; } ?>
<?php
$logo_path = __DIR__ . '/../../assets/images/barangay_logo.png';

$html = "
<table width='100%' style='border:none; margin-bottom: 6px;'>
<tr>
<td width='70' style='vertical-align: middle; border: none;'><img src='$logo_path' width='60'></td>
<td style='vertical-align: middle; border: none;'>
<h2 style='margin-bottom: 2px;'>Barangay Santa Ines Health Summary Report</h2>
<p style='margin: 0; font-size: 10px;'>Plaridel, Bulacan</p>
</td>
</tr>
</table>
<p>Generated: " . date('F j, Y g:i A') . "</p>
<h3>Key insights</h3>
<p style='font-size:10px;color:#666;'>Auto-generated from recorded data. Rule-based summaries, not AI-generated predictions.</p>
<ul>$insights_html</ul>

<h3>Summary</h3>
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