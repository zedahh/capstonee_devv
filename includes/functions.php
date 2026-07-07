<?php
// Shared helper functions used across multiple modules

function getFicStatus($pdo, $infant_record_id, $birth_date, $epi_schedule) {
    $age_weeks = floor((time() - strtotime($birth_date)) / (7 * 24 * 60 * 60));

    $given = $pdo->prepare("SELECT vaccine_name FROM vaccination_records WHERE infant_record_id = ?");
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
    $boundary = [
        [120.8567776,14.8762978],[120.8571153,14.8762182],[120.8591311,14.8766909],
        [120.8597947,14.8766875],[120.8600435,14.8793746],[120.8598371,14.8816893],
        [120.8590697,14.8817061],[120.8590768,14.8820312],[120.859626,14.883261],
        [120.8570701,14.8846072],[120.8562524,14.8844414],[120.856117,14.8844124],
        [120.8555007,14.8842778],[120.8550624,14.8841486],[120.8542718,14.883937],
        [120.8543712,14.8836969],[120.8545938,14.8831593],[120.8539144,14.8831558],
        [120.8536389,14.8824141],[120.853711,14.8818023],[120.854778,14.8815123],
        [120.8548343,14.8814838],[120.854888,14.8814103],[120.8549466,14.8813035],
        [120.8550806,14.8810389],[120.8552541,14.8807367],[120.855352,14.8804896],
        [120.8554075,14.8803151],[120.8554244,14.880141],[120.8554305,14.8799733],
        [120.8554217,14.8797379],[120.8553875,14.8795751],[120.8553305,14.8794126],
        [120.8552505,14.8792006],[120.8553506,14.8791637],[120.8554096,14.8791248],
        [120.855419,14.8790898],[120.8555619,14.8786005],[120.8555728,14.878524],
        [120.8554552,14.878307],[120.85538,14.8781578],[120.8553278,14.878027],
        [120.8553014,14.8778824],[120.8552863,14.8777341],[120.8553144,14.8775469],
        [120.8553614,14.8773997],[120.8554151,14.877295],[120.8555169,14.8771625],
        [120.8556053,14.8770745],[120.8558857,14.8768799],[120.8561684,14.8766992],
        [120.8567776,14.8762978]
    ];

    $lats = array_column($boundary, 1);
    $lngs = array_column($boundary, 0);
    $minLat = min($lats); $maxLat = max($lats);
    $minLng = min($lngs); $maxLng = max($lngs);
    $midLat = ($minLat + $maxLat) / 2;
    $midLng = ($minLng + $maxLng) / 2;

    switch ((int) $purok) {
        case 1: $latRange = [$midLat, $maxLat]; $lngRange = [$minLng, $midLng]; break;
        case 2: $latRange = [$midLat, $maxLat]; $lngRange = [$midLng, $maxLng]; break;
        case 3: $latRange = [$minLat, $midLat]; $lngRange = [$minLng, $midLng]; break;
        case 4: $latRange = [$minLat, $midLat]; $lngRange = [$midLng, $maxLng]; break;
        default: $latRange = [$minLat, $maxLat]; $lngRange = [$minLng, $maxLng];
    }

    for ($attempt = 0; $attempt < 50; $attempt++) {
        $lat = $latRange[0] + mt_rand() / mt_getrandmax() * ($latRange[1] - $latRange[0]);
        $lng = $lngRange[0] + mt_rand() / mt_getrandmax() * ($lngRange[1] - $lngRange[0]);
        if (pointInPolygon($lat, $lng, $boundary)) {
            return [$lat, $lng];
        }
    }
    return [($latRange[0] + $latRange[1]) / 2, ($lngRange[0] + $lngRange[1]) / 2];
}