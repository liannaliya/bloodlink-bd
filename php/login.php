<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.');
}

$phone    = trim($conn->real_escape_string($_POST['phone']    ?? ''));
$password =      $_POST['password'] ?? '';
$role     = trim($conn->real_escape_string($_POST['role']     ?? 'donor'));

if (!$phone || !$password) {
    respond(false, 'Phone and password are required.');
}

// ---- ADMIN LOGIN ----
if ($role === 'admin') {
    $sql    = "SELECT * FROM admins WHERE phone='$phone' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows === 0) {
        respond(false, 'Admin account not found.');
    }

    $admin = $result->fetch_assoc();

    // For demo: plain text check (replace with password_verify in production)
    if ($password !== 'admin123') {
        respond(false, 'Incorrect password.');
    }

    respond(true, 'Admin login successful.', [
        'role'     => 'admin',
        'name'     => $admin['username'],
        'redirect' => 'admin-dashboard.html'
    ]);
}

// ---- DONOR LOGIN ----
$sql    = "SELECT * FROM donors WHERE phone='$phone' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    respond(false, 'No account found with this phone number.');
}

$donor = $result->fetch_assoc();

if (!password_verify($password, $donor['password'])) {
    respond(false, 'Incorrect password. Please try again.');
}



respond(true, 'Login successful.', [
    'role'      => 'donor',
    'donor_id'  => $donor['id'],
    'name'      => $donor['full_name'],
    'blood'     => $donor['blood_group'],
    'district'  => $donor['district'],
    'redirect'  => 'donor-profile.html'
]);

$conn->close();
?>