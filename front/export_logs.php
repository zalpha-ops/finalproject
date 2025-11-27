<?php
// export_logs.php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=logs_export.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Admin ID', 'Action', 'Details', 'Timestamp']);

$stmt = $pdo->query("SELECT id, admin_id, action, details, timestamp FROM logs ORDER BY timestamp DESC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}
fclose($output);
exit;
?>
