<?php
try {
    $db = new PDO('sqlite:database/database.sqlite');
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        $count = $db->query("SELECT COUNT(*) FROM \"$t\"")->fetchColumn();
        echo "$t: $count\n";
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
