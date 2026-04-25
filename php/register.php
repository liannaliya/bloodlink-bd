<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.');
}

// Get and sanitize inputs
$full_name   = trim($conn->real_escape_string($_POST['full_name']   ?? ''));
$phone       = trim($conn->real_escape_string($_POST['phone']       ?? ''));
$email       = trim($conn->real_escape_string($_POST['email']       ?? ''));
$password    =      $_POST['password']    ?? '';
$blood_group = trim($conn->real_escape_string($_POST['blood_group'] ?? ''));
$gender      = trim($conn->real_escape_string($_POST['gender']      ?? ''));
$dob         = trim($conn->real_escape_string($_POST['dob']         ?? ''));
$weight      = trim($conn->real_escape_string($_POST['weight']      ?? ''));
$district    = trim($conn->real_escape_string($_POST['district']    ?? ''));
$area        = trim($conn->real_escape_string($_POST['area']        ?? ''));
$address     = trim($conn->real_escape_string($_POST['address']     ?? ''));
$last_don    = trim($conn->real_escape_string($_POST['last_donation']?? ''));
$med_notes   = trim($conn->real_escape_string($_POST['med_notes']   ?? ''));

// Validate required fields
if (!$full_name || !$phone || !$password || !$blood_group ||
    !$gender    || !$dob   || !$weight   || !$district || !$area) {
    respond(false, 'Please fill in all required fields.');
}

if (strlen($phone) < 11) {
    respond(false, 'Please enter a valid phone number.');
}

if (strlen($password) < 6) {
    respond(false, 'Password must be at least 6 characters.');
}

// Check if phone already exists
$check = $conn->query("SELECT id FROM donors WHERE phone='$phone'");
if ($check->num_rows > 0) {
    respond(false, 'This phone number is already registered.');
}

// Hash password
$hashed = password_hash($password, PASSWORD_BCRYPT);

// Calculate next eligible date
$next_eligible = '';
if ($last_don) {
    $d = new DateTime($last_don);
    $d->modify('+90 days');
    $next_eligible = $d->format('Y-m-d');
}

// Insert donor
$sql = "INSERT INTO donors
  (full_name, phone, email, password, blood_group, gender,
   date_of_birth, weight, district, area, address,
   last_donation, next_eligible, med_notes, is_active, is_verified)
VALUES
  ('$full_name','$phone','$email','$hashed','$blood_group','$gender',
   '$dob','$weight','$district','$area','$address',
   " . ($last_don ? "'$last_don'" : "NULL") . ",
   " . ($next_eligible ? "'$next_eligible'" : "NULL") . ",
   '$med_notes', 1, 1)";

if ($conn->query($sql)) {
    respond(true, 'Registration successful! Awaiting admin verification.', [
        'donor_id' => $conn->insert_id
    ]);
} else {
    respond(false, 'Registration failed: ' . $conn->error);
}

$conn->close();
?>