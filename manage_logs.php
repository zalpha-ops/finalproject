<?php
// manage_log.php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Build filters
$where = [];
$params = [];

if (!empty($_GET['action'])) {
    $where[] = "action LIKE :action";
    $params[':action'] = "%".$_GET['action']."%";
}
if (!empty($_GET['admin_id'])) {
    $where[] = "admin_id = :admin_id";
    $params[':admin_id'] = $_GET['admin_id'];
}
if (!empty($_GET['from'])) {
    $where[] = "timestamp >= :from";
    $params[':from'] = $_GET['from'];
}
if (!empty($_GET['to'])) {
    $where[] = "timestamp <= :to";
    $params[':to'] = $_GET['to'];
}

$sql = "SELECT id, admin_id, action, details, timestamp FROM logs";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY timestamp DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Logs</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-4">
  <h2 class="mb-4">📝 Manage Logs</h2>

  <!-- Search & Filter -->
  <form method="get" class="mb-3 d-flex gap-2">
    <input type="text" name="action" class="form-control" placeholder="Filter by action" value="<?= htmlspecialchars($_GET['action'] ?? '') ?>">
    <input type="text" name="admin_id" class="form-control" placeholder="Filter by Admin ID" value="<?= htmlspecialchars($_GET['admin_id'] ?? '') ?>">
    <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
    <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
    <button type="submit" class="btn btn-primary">🔍 Search</button>
    <!-- Reset now reloads the same file -->
    <a href="./manage_log.php" class="btn btn-secondary">Reset</a>
  </form>

  <!-- Actions -->
  <div class="mb-3">
    <a href="export_logs.php" class="btn btn-success">📤 Export Logs (CSV)</a>
    <a href="clear_logs.php" class="btn btn-danger"
       onclick="return confirm('Clear all logs?')">🧹 Clear All Logs</a>
    <a href="admin_dashboard.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>
  </div>

  <!-- Logs Table -->
  <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Admin ID</th>
          <th>Action</th>
          <th>Details</th>
          <th>Timestamp</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($logs): foreach ($logs as $log): ?>
          <?php
            $badgeClass = match($log['action']) {
                'Error' => 'danger',
                'Login' => 'success',
                'Update' => 'info',
                default => 'secondary'
            };
          ?>
          <tr>
            <td><?= $log['id'] ?></td>
            <td><?= $log['admin_id'] ?></td>
            <td><span class="badge bg-<?= $badgeClass ?>"><?= htmlspecialchars($log['action']) ?></span></td>
            <td style="max-width: 480px; white-space: pre-wrap;"><?= htmlspecialchars($log['details']) ?></td>
            <td><?= $log['timestamp'] ?></td>
            <td>
              <a href="delete_log.php?id=<?= $log['id'] ?>" class="btn btn-sm btn-danger"
                 onclick="return confirm('Delete this log entry?')">🗑️ Delete</a>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="6" class="text-center">No logs found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
