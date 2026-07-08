<?php if (!isset($insights_html)) { return; } ?>
<?php
$html = "
<h2>Barangay Santa Ines Health Summary Report</h2>
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