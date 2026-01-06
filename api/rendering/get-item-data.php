<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/init.inc.php');

header('Content-Type: application/json');

$itemId = isset($_GET['itemId']) ? (int)$GET['itemId'] : 0;

if ($itemId <= 0) {
    echo json_encode(['error' => 'Invalid Item ID']);
    exit;
}

$stmt = $db->prepare("SELECT * FROM store_items WHERE id = ?");
$stmt->execute([$itemId]);
$item = $stmt->fetch(PDO::FETCH_OBJ);

if (!$item) {
    echo json_encode(['error' => 'Item not found']);
    exit;
}

// Map database type to renderer types
$typeMap = [
    1 => 'hat',
    7 => 'head',
    6 => 'accessory',
    2 => 'face',
    5 => 'gear',
    3 => 'shirt',
    4 => 'pants',
    8 => 'tshirt'
];

$renderType = $typeMap[$item->type] ?? 'accessory';
$itemsData = [
    'hats' => [],
    'face' => 'default',
    'accessory' => 0,
    'shirt' => 0,
    'pants' => 0,
    'tshirt' => 0,
    'gear' => 0
];

if ($renderType === 'hat') {
    $itemsData['hats'] = [(int)$item->id];
} elseif ($renderType === 'face') {
    $itemsData['face'] = (string)$item->id;
} else {
    $itemsData[$renderType] = (int)$item->id;
}

echo json_encode([
    'type' => $renderType,
    'items' => $itemsData,
    'colors' => [
        'head' => [1, 0.8, 0.6],
        'torso' => [0.2, 0.2, 0.8],
        'leftArm' => [1, 0.8, 0.6],
        'rightArm' => [1, 0.8, 0.6],
        'leftLeg' => [0.1, 0.1, 0.1],
        'rightLeg' => [0.1, 0.1, 0.1]
    ]
]);
?>
