<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/init.inc.php');

header('Content-Type: application/json');

$userId = isset($_GET['userId']) ? (int)$_GET['userId'] : 0;

if ($userId <= 0) {
    echo json_encode(['error' => 'Invalid User ID']);
    exit;
}

// Fetch user data using established helpers (now in functions.inc.php)
// We need to simulate the batch fetch structure for getAvatarData
$userColors = [];
$userWearables = [];

$colorStmt = $db->prepare("SELECT * FROM user_colors WHERE user = ?");
$colorStmt->execute([$userId]);
if ($color = $colorStmt->fetch(PDO::FETCH_OBJ)) {
    $userColors[$userId] = $color;
}

$wearableStmt = $db->prepare("SELECT * FROM user_data WHERE user = ?");
$wearableStmt->execute([$userId]);
if ($wearable = $wearableStmt->fetch(PDO::FETCH_OBJ)) {
    $userWearables[$userId] = $wearable;
}

$avatarData = getAvatarData($userId, $userColors, $userWearables);

echo json_encode($avatarData);
?>
