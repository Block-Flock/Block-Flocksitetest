<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

// Only allow admin access
if (!$AUTH || $my->administrator < 1) {
    die('Access denied. Admin only.');
}

echo "<h1>Fixing User Profiles</h1>";

// Get all users
$getUsers = $db->prepare("SELECT * FROM users ORDER BY id");
$getUsers->execute();

$fixedCount = 0;
$totalUsers = 0;

while ($user = $getUsers->fetch(PDO::FETCH_OBJ)) {
    $totalUsers++;
    $needsFix = false;
    
    // Check if user has colors
    $checkColors = $db->prepare("SELECT * FROM user_colors WHERE user = ?");
    $checkColors->bindValue(1, $user->id, PDO::PARAM_INT);
    $checkColors->execute();
    
    if ($checkColors->rowCount() == 0) {
        // Create default user colors
        $insertColors = $db->prepare('INSERT INTO user_colors (user, head, torso, left_arm, right_arm, left_leg, right_leg) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $insertColors->bindValue(1, $user->id, PDO::PARAM_INT);
        $insertColors->bindValue(2, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR);
        $insertColors->bindValue(3, '0.033105, 0.313989, 0.708376', PDO::PARAM_STR);
        $insertColors->bindValue(4, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR);
        $insertColors->bindValue(5, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR);
        $insertColors->bindValue(6, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR);
        $insertColors->bindValue(7, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR);
        $insertColors->execute();
        echo "Created colors for user {$user->username} (ID: {$user->id})<br>";
        $needsFix = true;
    }
    
    // Check if user has user_data
    $checkUserData = $db->prepare("SELECT * FROM user_data WHERE user = ?");
    $checkUserData->bindValue(1, $user->id, PDO::PARAM_INT);
    $checkUserData->execute();
    
    if ($checkUserData->rowCount() == 0) {
        // Create default user data
        $insertUserData = $db->prepare('INSERT INTO user_data (user, hat1, hat2, hat3, face, shirt, pants, gear) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $insertUserData->bindValue(1, $user->id, PDO::PARAM_INT);
        $insertUserData->bindValue(2, '0', PDO::PARAM_STR);
        $insertUserData->bindValue(3, '0', PDO::PARAM_STR);
        $insertUserData->bindValue(4, '0', PDO::PARAM_STR);
        $insertUserData->bindValue(5, '0', PDO::PARAM_STR);
        $insertUserData->bindValue(6, '0', PDO::PARAM_STR);
        $insertUserData->bindValue(7, '0', PDO::PARAM_STR);
        $insertUserData->bindValue(8, '0', PDO::PARAM_STR);
        $insertUserData->execute();
        echo "Created user_data for user {$user->username} (ID: {$user->id})<br>";
        $needsFix = true;
    }
    
    // Check if user has avatar_hash
    if (empty($user->avatar_hash) || $user->avatar_hash == 'NULL') {
        $updateAvatar = $db->prepare('UPDATE users SET avatar_hash = ? WHERE id = ?');
        $updateAvatar->bindValue(1, 'default', PDO::PARAM_STR);
        $updateAvatar->bindValue(2, $user->id, PDO::PARAM_INT);
        $updateAvatar->execute();
        echo "Fixed avatar_hash for user {$user->username} (ID: {$user->id})<br>";
        $needsFix = true;
    }
    
    // Check if user has blurb
    if (empty($user->blurb)) {
        $updateBlurb = $db->prepare('UPDATE users SET blurb = ? WHERE id = ?');
        $updateBlurb->bindValue(1, 'Welcome to Bricktaria!', PDO::PARAM_STR);
        $updateBlurb->bindValue(2, $user->id, PDO::PARAM_INT);
        $updateBlurb->execute();
        echo "Added default blurb for user {$user->username} (ID: {$user->id})<br>";
        $needsFix = true;
    }
    
    if ($needsFix) {
        $fixedCount++;
    }
}

echo "<br><strong>Fix complete!</strong><br>";
echo "Total users processed: {$totalUsers}<br>";
echo "Users fixed: {$fixedCount}<br>";
echo "<br><a href='/admin'>Back to Admin</a>";
?> 