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