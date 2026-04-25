<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
require_once 'config.php';

$donor_id = intval($_GET['donor_id'] ?? 0);

if (!$donor_id) {
    respond(false, 'Donor ID required.');
}

$result = $conn->query(
    "SELECT id, full_name, phone, email, blood_group, gender,
            date_of_birth, weight, district, area, address,
            last_donation, next_eligible, is_active, is_verified,
            created_at
     FROM donors WHERE id=$donor_id LIMIT 1"
);

if ($result->num_rows === 0) {
    respond(false, 'Donor not found.');
}

$donor = $result->fetch_assoc();

// Get donation history
$history = $conn->query(
    "SELECT * FROM donations WHERE donor_id=$donor_id ORDER BY donated_on DESC"
);
$donations = [];
while ($row = $history->fetch_assoc()) {
    $donations[] = $row;
}

$donor['donations'] = $donations;
$donor['total_donations'] = count($donations);

respond(true, 'Profile loaded.', $donor);
$conn->close();
?>