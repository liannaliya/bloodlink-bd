<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
require_once 'config.php';

$blood    = trim($conn->real_escape_string($_GET['blood']    ?? ''));
$district = trim($conn->real_escape_string($_GET['district'] ?? ''));
$active   =      $_GET['active']   ?? '1';
$verified =      $_GET['verified'] ?? '1';

if (!$blood) {
    respond(false, 'Blood group is required.');
}

// Compatible donors map (from SRS Appendix A)
$compat = [
    'A+'  => ["'A+'","'A-'","'O+'","'O-'"],
    'A-'  => ["'A-'","'O-'"],
    'B+'  => ["'B+'","'B-'","'O+'","'O-'"],
    'B-'  => ["'B-'","'O-'"],
    'AB+' => ["'A+'","'A-'","'B+'","'B-'","'AB+'","'AB-'","'O+'","'O-'"],
    'AB-' => ["'A-'","'B-'","'AB-'","'O-'"],
    'O+'  => ["'O+'","'O-'"],
    'O-'  => ["'O-'"],
];

$groups = $compat[$blood] ?? ["'$blood'"];
$inList = implode(',', $groups);

$where = "blood_group IN ($inList)";
if ($active   === '1') $where .= " AND is_active=1";
if ($verified === '1') $where .= " AND is_verified=1";
if ($district)         $where .= " AND district='$district'";

$sql = "SELECT id, full_name, blood_group, district, area,
               is_active, is_verified, last_donation, next_eligible,
               phone
        FROM donors
        WHERE $where
        ORDER BY blood_group = '$blood' DESC, full_name ASC";

$result = $conn->query($sql);
$donors = [];

while ($row = $result->fetch_assoc()) {
    // Check eligibility
    $eligible = true;
    if ($row['next_eligible']) {
        $eligible = (new DateTime()) >= (new DateTime($row['next_eligible']));
    }
    $row['eligible'] = $eligible;

    // Mask phone for privacy (show last 4 digits)
    $row['phone_masked'] = substr($row['phone'], 0, 6) . '****';
    $donors[] = $row;
}

respond(true, count($donors) . ' donor(s) found.', $donors);
$conn->close();
?>