<?php
// Shared helper functions used across multiple modules

function getFicStatus($pdo, $infant_record_id, $birth_date, $epi_schedule) {
    $age_weeks = floor((time() - strtotime($birth_date)) / (7 * 24 * 60 * 60));

   $given = $pdo->prepare("SELECT vaccine_name FROM vaccination_records WHERE infant_record_id = ? AND is_active = 1");
    $given->execute([$infant_record_id]);
    $given_vaccines = $given->fetchAll(PDO::FETCH_COLUMN);

    $overdue_count = 0;
    $total_due_count = 0;

    foreach ($epi_schedule as $vaccine) {
        $due_at = $vaccine['recommended_age_weeks'] + $vaccine['grace_period_weeks'];
        if ($age_weeks >= $vaccine['recommended_age_weeks']) {
            $total_due_count++;
            if (!in_array($vaccine['vaccine_name'], $given_vaccines) && $age_weeks > $due_at) {
                $overdue_count++;
            }
        }
    }

    if ($overdue_count > 0) {
        return ['label' => "Overdue ($overdue_count)", 'badge' => 'danger', 'overdue_count' => $overdue_count];
    } elseif ($total_due_count > 0 && count($given_vaccines) >= $total_due_count) {
        return ['label' => 'Complete for age', 'badge' => 'success', 'overdue_count' => 0];
    } elseif ($total_due_count > 0) {
        return ['label' => 'In progress', 'badge' => 'secondary', 'overdue_count' => 0];
    }
    return ['label' => 'Too young for schedule', 'badge' => 'secondary', 'overdue_count' => 0];
}

function getPrenatalComplianceStatus($pdo, $maternal_record_id, $lmp_date, $monitoring_status, $schedule) {
    if (in_array($monitoring_status, ['Delivered', 'Postpartum'])) {
        return ['label' => 'Delivered/postpartum', 'badge' => 'secondary', 'behind' => false];
    }
    if (!$lmp_date) {
        return ['label' => 'No LMP recorded', 'badge' => 'secondary', 'behind' => false];
    }

    $weeks_pregnant = floor((time() - strtotime($lmp_date)) / (7 * 24 * 60 * 60));

    $stmt = $pdo->prepare("SELECT checkup_date FROM prenatal_checkups WHERE maternal_record_id = ?");
    $stmt->execute([$maternal_record_id]);
    $checkup_dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $visits_per_trimester = [1 => 0, 2 => 0, 3 => 0];
    foreach ($checkup_dates as $cd) {
        $week_at_visit = floor((strtotime($cd) - strtotime($lmp_date)) / (7 * 24 * 60 * 60));
        foreach ($schedule as $row) {
            if ($week_at_visit >= $row['start_week'] && $week_at_visit <= $row['end_week']) {
                $visits_per_trimester[$row['trimester']]++;
            }
        }
    }

    $behind_trimesters = [];
    foreach ($schedule as $row) {
        $enforce_at = $row['start_week'] + $row['grace_period_weeks'];
        if ($weeks_pregnant >= $enforce_at && $visits_per_trimester[$row['trimester']] < $row['min_visits']) {
            $behind_trimesters[] = $row['trimester'];
        }
    }

    if (count($behind_trimesters) > 0) {
        return ['label' => 'Behind schedule (T' . implode(',', $behind_trimesters) . ')', 'badge' => 'danger', 'behind' => true];
    }
    return ['label' => 'On track', 'badge' => 'success', 'behind' => false];
}



function sendSms($pdo, $phone_number, $message, $purpose, $user_id) {
    // SIMULATED SEND — logs to sms_log as if sent, no real API call.
    // At deployment, replace the body of this function with a real call to
    // Semaphore's API (https://semaphore.co/api/v4/messages), keeping the same
    // function name and parameters so nothing else in the system needs to change.

    $stmt = $pdo->prepare("INSERT INTO sms_log (phone_number, message, purpose, status, sent_by) VALUES (?, ?, ?, 'simulated', ?)");
    $stmt->execute([$phone_number, $message, $purpose, $user_id]);

    return true;
}



// Point-in-polygon test (ray casting) - determines if a lat/lng falls inside a polygon
function pointInPolygon($lat, $lng, $polygon) {
    $inside = false;
    $n = count($polygon);
    for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
        $xi = $polygon[$i][0]; $yi = $polygon[$i][1];
        $xj = $polygon[$j][0]; $yj = $polygon[$j][1];
        $intersect = (($yi > $lat) != ($yj > $lat)) &&
            ($lng < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi);
        if ($intersect) $inside = !$inside;
    }
    return $inside;
}

// Generates a random point inside the real Santa Ines boundary, within a given purok's quadrant
function generateApproxLocation($purok) {
    $boundaries = getPurokBoundaries();
    $polygon = $boundaries[(int) $purok] ?? null;
    if (!$polygon) {
        return [14.8804, 120.8568];
    }

    $lats = array_column($polygon, 1);
    $lngs = array_column($polygon, 0);
    $minLat = min($lats); $maxLat = max($lats);
    $minLng = min($lngs); $maxLng = max($lngs);

    for ($attempt = 0; $attempt < 100; $attempt++) {
        $lat = $minLat + mt_rand() / mt_getrandmax() * ($maxLat - $minLat);
        $lng = $minLng + mt_rand() / mt_getrandmax() * ($maxLng - $minLng);
        if (pointInPolygon($lat, $lng, $polygon)) {
            return [$lat, $lng];
        }
    }
    return [array_sum($lats) / count($lats), array_sum($lngs) / count($lngs)];
}
function getPurokBoundaries() {
    return [
        1 => [[120.8570674,14.8846093], [120.8555109,14.8842826], [120.8542731,14.883934], [120.8549029,14.8824545], [120.8559146,14.8826869], [120.8559633,14.8826052], [120.8596088,14.883268], [120.8570674,14.8846093]],
        2 => [[120.8596209,14.8832586], [120.8559584,14.8825927], [120.8559098,14.8826743], [120.8549054,14.882445], [120.855134,14.8818796], [120.8552434,14.8817948], [120.8590664,14.8817194], [120.8590786,14.8820367], [120.8596209,14.8832586]],
        3 => [[120.8545916,14.8831581], [120.8539131,14.8831549], [120.8536408,14.8824199], [120.8537088,14.8818042], [120.85483,14.881487], [120.8553577,14.8804787], [120.8554258,14.8801457], [120.8554209,14.8797248], [120.8552507,14.8792033], [120.8554112,14.8791217], [120.8555717,14.8785437], [120.8552993,14.8778841], [120.8553091,14.8775699], [120.8554112,14.8772998], [120.8556082,14.8770705], [120.8567755,14.8762978], [120.8571111,14.8762161], [120.8590421,14.8766684], [120.8592245,14.8770736], [120.8591759,14.8775951], [120.8590373,14.8778369], [120.8589059,14.8779249], [120.8580645,14.8778809], [120.8564497,14.8786788], [120.8561432,14.8789112], [120.8560824,14.8790526], [120.8561359,14.879329], [120.8569069,14.8801488], [120.8572522,14.8804127], [120.8578748,14.8811352], [120.8585655,14.881487], [120.8586506,14.88171], [120.8552434,14.8817823], [120.8551242,14.8818733], [120.8545916,14.8831581]],
        4 => [[120.8589059,14.8817131], [120.8586627,14.8817131], [120.85858,14.8814807], [120.8578845,14.8811289], [120.8572814,14.8804253], [120.8569652,14.8801928], [120.8561481,14.8793259], [120.8560922,14.8790589], [120.856153,14.8789175], [120.8563767,14.8787353], [120.8580645,14.8778935], [120.8589157,14.8779312], [120.8591467,14.8777144], [120.8592196,14.8773501], [120.8592294,14.8770171], [120.8590591,14.8766778], [120.8597912,14.8766873], [120.8600416,14.879329], [120.8598374,14.8816849], [120.8589059,14.8817131]],
    ];
}

