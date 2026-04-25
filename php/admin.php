<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
require_once 'config.php';

$action = trim($_GET['action'] ?? $_POST['action'] ?? '');

switch ($action) {

  // ---- Get Dashboard Stats ----
  case 'stats':
    $total    = $conn->query("SELECT COUNT(*) as c FROM donors")->fetch_assoc()['c'];
    $active   = $conn->query("SELECT COUNT(*) as c FROM donors WHERE is_active=1")->fetch_assoc()['c'];
    $pending  = $conn->query("SELECT COUNT(*) as c FROM donors WHERE is_verified=0")->fetch_assoc()['c'];
    $requests = $conn->query("SELECT COUNT(*) as c FROM blood_requests WHERE status='active'")->fetch_assoc()['c'];
    respond(true, 'Stats loaded.', [
      'total_donors'    => (int)$total,
      'active_donors'   => (int)$active,
      'pending'         => (int)$pending,
      'active_requests' => (int)$requests,
    ]);
    break;

  // ---- Get Pending Donors ----
  case 'pending':
    $result = $conn->query(
      "SELECT id, full_name, phone, blood_group, district, created_at
       FROM donors WHERE is_verified=0 ORDER BY created_at DESC"
    );
    $rows = [];
    while ($r = $result->fetch_assoc()) $rows[] = $r;
    respond(true, count($rows) . ' pending.', $rows);
    break;

  // ---- Get All Donors ----
  case 'donors':
    $result = $conn->query(
      "SELECT id, full_name, phone, blood_group, district, area,
              is_active, is_verified, last_donation, created_at
       FROM donors ORDER BY created_at DESC"
    );
    $rows = [];
    while ($r = $result->fetch_assoc()) $rows[] = $r;
    respond(true, count($rows) . ' donors.', $rows);
    break;

  // ---- Approve Donor ----
  case 'approve':
    $id = intval($_POST['donor_id'] ?? 0);
    if (!$id) respond(false, 'Invalid donor ID.');
    $conn->query("UPDATE donors SET is_verified=1 WHERE id=$id");
    respond(true, 'Donor approved.');
    break;

  // ---- Reject / Delete Donor ----
  case 'reject':
    $id = intval($_POST['donor_id'] ?? 0);
    if (!$id) respond(false, 'Invalid donor ID.');
    $conn->query("DELETE FROM donors WHERE id=$id");
    respond(true, 'Donor rejected and removed.');
    break;

  // ---- Toggle Active Status ----
  case 'toggle':
    $id = intval($_POST['donor_id'] ?? 0);
    if (!$id) respond(false, 'Invalid donor ID.');
    $conn->query(
      "UPDATE donors SET is_active = IF(is_active=1,0,1) WHERE id=$id"
    );
    respond(true, 'Status updated.');
    break;

  // ---- Get All Requests ----
  case 'requests':
    $result = $conn->query(
      "SELECT * FROM blood_requests ORDER BY created_at DESC"
    );
    $rows = [];
    while ($r = $result->fetch_assoc()) $rows[] = $r;
    respond(true, count($rows) . ' requests.', $rows);
    break;

  // ---- Close Request ----
  case 'close_request':
    $id = intval($_POST['request_id'] ?? 0);
    if (!$id) respond(false, 'Invalid request ID.');
    $conn->query("UPDATE blood_requests SET status='closed' WHERE id=$id");
    respond(true, 'Request closed.');
    break;

  default:
    respond(false, 'Unknown action.');
}

$conn->close();
?>