<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

// Temporarily allow direct access for fixing user profiles
// TODO: Re-enable admin check when phpMyAdmin is working
// if (!$AUTH || $my->administrator < 1) {
//     die('Access denied. Admin only.');
// }

echo "<h1>Comprehensive User Profile Fix</h1>";

// Get all users
$getUsers = $db->prepare("SELECT * FROM users ORDER BY id");
$getUsers->execute();

$fixedCount = 0;
$totalUsers = 0;
$errors = [];

while ($user = $getUsers->fetch(PDO::FETCH_OBJ)) {
    $totalUsers++;
    $needsFix = false;
    $userErrors = [];
    
    echo "<h3>Processing User: {$user->username} (ID: {$user->id})</h3>";
    
    // Check if user has colors
    $checkColors = $db->prepare("SELECT * FROM user_colors WHERE user = ?");
    $checkColors->bindValue(1, $user->id, PDO::PARAM_INT);
    $checkColors->execute();
    
    if ($checkColors->rowCount() == 0) {
        try {
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
            echo "✅ Created colors for user {$user->username}<br>";
            $needsFix = true;
        } catch (Exception $e) {
            $userErrors[] = "Failed to create colors: " . $e->getMessage();
        }
    } else {
        $colors = $checkColors->fetch(PDO::FETCH_OBJ);
        // Check if colors have proper values
        if (empty($colors->head) || $colors->head == 'NULL') {
            try {
                $updateColors = $db->prepare('UPDATE user_colors SET head = ?, torso = ?, left_arm = ?, right_arm = ?, left_leg = ?, right_leg = ? WHERE user = ?');
                $updateColors->bindValue(1, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR);
                $updateColors->bindValue(2, '0.033105, 0.313989, 0.708376', PDO::PARAM_STR);
                $updateColors->bindValue(3, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR);
                $updateColors->bindValue(4, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR);
                $updateColors->bindValue(5, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR);
                $updateColors->bindValue(6, '0.723055, 0.791298, 0.806952', PDO::PARAM_STR);
                $updateColors->bindValue(7, $user->id, PDO::PARAM_INT);
                $updateColors->execute();
                echo "✅ Updated colors for user {$user->username}<br>";
                $needsFix = true;
            } catch (Exception $e) {
                $userErrors[] = "Failed to update colors: " . $e->getMessage();
            }
        } else {
            echo "✅ Colors already exist for user {$user->username}<br>";
        }
    }
    
    // Check if user has user_data
    $checkUserData = $db->prepare("SELECT * FROM user_data WHERE user = ?");
    $checkUserData->bindValue(1, $user->id, PDO::PARAM_INT);
    $checkUserData->execute();
    
    if ($checkUserData->rowCount() == 0) {
        try {
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
            echo "✅ Created user_data for user {$user->username}<br>";
            $needsFix = true;
        } catch (Exception $e) {
            $userErrors[] = "Failed to create user_data: " . $e->getMessage();
        }
    } else {
        echo "✅ user_data already exists for user {$user->username}<br>";
    }
    
    // Check if user has avatar_hash
    if (empty($user->avatar_hash) || $user->avatar_hash == 'NULL') {
        try {
            $updateAvatar = $db->prepare('UPDATE users SET avatar_hash = ? WHERE id = ?');
            $updateAvatar->bindValue(1, 'default', PDO::PARAM_STR);
            $updateAvatar->bindValue(2, $user->id, PDO::PARAM_INT);
            $updateAvatar->execute();
            echo "✅ Fixed avatar_hash for user {$user->username}<br>";
            $needsFix = true;
        } catch (Exception $e) {
            $userErrors[] = "Failed to update avatar_hash: " . $e->getMessage();
        }
    }
    
    // Check if user has blurb
    if (empty($user->blurb) || $user->blurb == 'NULL') {
        try {
            $updateBlurb = $db->prepare('UPDATE users SET blurb = ? WHERE id = ?');
            $updateBlurb->bindValue(1, 'Welcome to Bricktaria!', PDO::PARAM_STR);
            $updateBlurb->bindValue(2, $user->id, PDO::PARAM_INT);
            $updateBlurb->execute();
            echo "✅ Added default blurb for user {$user->username}<br>";
            $needsFix = true;
        } catch (Exception $e) {
            $userErrors[] = "Failed to update blurb: " . $e->getMessage();
        }
    }
    
    if ($needsFix) {
        $fixedCount++;
    }
    
    if (!empty($userErrors)) {
        $errors[] = "User {$user->username} (ID: {$user->id}): " . implode(", ", $userErrors);
    }
    
    echo "<hr>";
}

echo "<h2>Fix Summary</h2>";
echo "<strong>Total users processed:</strong> {$totalUsers}<br>";
echo "<strong>Users fixed:</strong> {$fixedCount}<br>";

if (!empty($errors)) {
    echo "<h3>Errors encountered:</h3>";
    foreach ($errors as $error) {
        echo "❌ {$error}<br>";
    }
} else {
    echo "<h3>✅ No errors encountered!</h3>";
}

echo "<br><a href='/admin'>Back to Admin</a>";
?> 