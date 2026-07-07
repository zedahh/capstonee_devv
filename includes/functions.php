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
    // SIMULATED SEND — no real API call yet. Once a Semaphore API key is added,
    // replace the inside of this function with a real cURL call to Semaphore's API,
    // keeping the same function name/parameters so nothing else needs to change.

    $stmt = $pdo->prepare("INSERT INTO sms_log (phone_number, message, purpose, status, sent_by) VALUES (?, ?, ?, 'simulated', ?)");
    $stmt->execute([$phone_number, $message, $purpose, $user_id]);

    return true;
}