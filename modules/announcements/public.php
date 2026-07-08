<?php
require '../../config/database.php';

$announcements = $pdo->query("
    SELECT title, content, target_purok, created_at
    FROM announcements
    WHERE is_active = 1
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

require 'public_view.php';