<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

echo "<h1>User Profile System Test</h1>";

// Test 1: Check if we can access a user
$testUser = $db->prepare("SELECT * FROM users ORDER BY id LIMIT 1");
$testUser->execute();

if ($testUser->rowCount() > 0) {
    $user = $testUser->fetch(PDO::FETCH_OBJ);
    echo "<h2>Test User Found:</h2>";
    echo "ID: {$user->id}<br>";
    echo "Username: {$user->username}<br>";
    echo "Avatar Hash: " . ($user->avatar_hash ?: 'NULL') . "<br>";
    echo "Blurb: " . ($user->blurb ?: 'NULL') . "<br>";
    
    // Test 2: Check user colors
    $checkColors = $db->prepare("SELECT * FROM user_colors WHERE user = ?");
    $checkColors->bindValue(1, $user->id, PDO::PARAM_INT);
    $checkColors->execute();
    
    if ($checkColors->rowCount() > 0) {
        $colors = $checkColors->fetch(PDO::FETCH_OBJ);
        echo "<br><strong>User Colors:</strong><br>";
        echo "Head: {$colors->head}<br>";
        echo "Torso: {$colors->torso}<br>";
        echo "Left Arm: {$colors->left_arm}<br>";
        echo "Right Arm: {$colors->right_arm}<br>";
        echo "Left Leg: {$colors->left_leg}<br>";
        echo "Right Leg: {$colors->right_leg}<br>";
    } else {
        echo "<br><strong style='color: red;'>No user colors found!</strong><br>";
    }
    
    // Test 3: Check user data
    $checkUserData = $db->prepare("SELECT * FROM user_data WHERE user = ?");
    $checkUserData->bindValue(1, $user->id, PDO::PARAM_INT);
    $checkUserData->execute();
    
    if ($checkUserData->rowCount() > 0) {
        $userData = $checkUserData->fetch(PDO::FETCH_OBJ);
        echo "<br><strong>User Data:</strong><br>";
        echo "Hat1: {$userData->hat1}<br>";
        echo "Hat2: {$userData->hat2}<br>";
        echo "Hat3: {$userData->hat3}<br>";
        echo "Face: {$userData->face}<br>";
        echo "Shirt: {$userData->shirt}<br>";
        echo "Pants: {$userData->pants}<br>";
        echo "Gear: {$userData->gear}<br>";
    } else {
        echo "<br><strong style='color: red;'>No user data found!</strong><br>";
    }
    
    // Test 4: Check if profile page is accessible
    echo "<br><strong>Profile URL Test:</strong><br>";
    echo "Profile URL: <a href='/user/{$user->id}' target='_blank'>/user/{$user->id}</a><br>";
    echo "Alternative URL: <a href='/user/index.php?id={$user->id}' target='_blank'>/user/index.php?id={$user->id}</a><br>";
    
} else {
    echo "<strong style='color: red;'>No users found in database!</strong>";
}

// Test 5: Check database tables
echo "<br><br><h2>Database Table Check:</h2>";

$tables = ['users', 'user_colors', 'user_data', 'user_friends', 'user_bans'];
foreach ($tables as $table) {
    try {
        $checkTable = $db->prepare("SELECT COUNT(*) FROM `{$table}`");
        $checkTable->execute();
        $count = $checkTable->fetchColumn();
        echo "Table '{$table}': {$count} records<br>";
    } catch (PDOException $e) {
        echo "Table '{$table}': <strong style='color: red;'>ERROR - {$e->getMessage()}</strong><br>";
    }
}

// Test 6: Check UserProfileView table
try {
    $checkProfileView = $db->prepare("SELECT COUNT(*) FROM UserProfileView");
    $checkProfileView->execute();
    $count = $checkProfileView->fetchColumn();
    echo "Table 'UserProfileView': {$count} records<br>";
} catch (PDOException $e) {
    echo "Table 'UserProfileView': <strong style='color: orange;'>WARNING - Table doesn't exist: {$e->getMessage()}</strong><br>";
}

echo "<br><a href='/admin'>Back to Admin</a>";
?> 