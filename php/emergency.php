<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.');
}

$patient  = trim($conn->real_escape_string($_POST['patient_name']   ?? ''));
$phone    = trim($conn->real_escape_string($_POST['contact_phone']  ?? ''));
$blood    = trim($conn->real_escape_string($_POST['blood_group']    ?? ''));
$hospital = trim($conn->real_escape_string($_POST['hospital']       ?? ''));
$district = trim($conn->real_escape_string($_POST['district']       ?? ''));
$urgency  = trim($conn->real_escape_string($_POST['urgency']        ?? 'urgent'));
$units    = intval($_POST['units_needed'] ?? 1);
$req_by   = trim($conn->real_escape_string($_POST['required_by']    ?? ''));
$notes    = trim($conn->real_escape_string($_POST['notes']          ?? ''));

if (!$patient || !$phone || !$blood || !$hospital || !$district) {
    respond(false, 'Please fill in all required fields.');
}

$req_by_val = $req_by ? "'$req_by'" : 'NULL';

$sql = "INSERT INTO blood_requests
  (patient_name, contact_phone, blood_group, hospital,
   district, urgency, units_needed, required_by, notes, status)
VALUES
  ('$patient','$phone','$blood','$hospital',
   '$district','$urgency',$units,$req_by_val,'$notes','active')";

if ($conn->query($sql)) {
    // Count notified donors (compatible + same district)
    $compat = [
        'A+'  => "'A+','A-','O+','O-'",
        'A-'  => "'A-','O-'",
        'B+'  => "'B+','B-','O+','O-'",
        'B-'  => "'B-','O-'",
        'AB+' => "'A+','A-','B+','B-','AB+','AB-','O+','O-'",
        'AB-' => "'A-','B-','AB-','O-'",
        'O+'  => "'O+','O-'",
        'O-'  => "'O-'",
    ];
    $groups = $compat[$blood] ?? "'$blood'";
    $count  = $conn->query(
        "SELECT COUNT(*) as c FROM donors
         WHERE blood_group IN ($groups)
         AND district='$district'
         AND is_active=1 AND is_verified=1"
    )->fetch_assoc()['c'];

    respond(true, 'Emergency broadcast sent!', [
        'request_id'      => $conn->insert_id,
        'donors_notified' => (int)$count
    ]);
} else {
    respond(false, 'Failed to submit request: ' . $conn->error);
}

$conn->close();
?>