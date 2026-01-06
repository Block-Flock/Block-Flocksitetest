<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

$userId = 54; // Change this to check different users

echo "<h1>User Profile Check - User ID: $userId</h1>";

// Check if user exists
$checkUser = $db->prepare("SELECT * FROM users WHERE id = ?");
$checkUser->bindValue(1, $userId, PDO::PARAM_INT);
$checkUser->execute();

if ($checkUser->rowCount() == 0) {
    echo "❌ User does not exist!<br>";
    exit;
}

$user = $checkUser->fetch(PDO::FETCH_OBJ);
echo "<h2>User: {$user->username}</h2>";

// Check user_colors
$checkColors = $db->prepare("SELECT * FROM user_colors WHERE user = ?");
$checkColors->bindValue(1, $userId, PDO::PARAM_INT);
$checkColors->execute();

if ($checkColors->rowCount() == 0) {
    echo "❌ Missing user_colors record<br>";
} else {
    $colors = $checkColors->fetch(PDO::FETCH_OBJ);
    echo "✅ user_colors exists<br>";
    echo "Head: {$colors->head}<br>";
    echo "Torso: {$colors->torso}<br>";
    echo "Left Arm: {$colors->left_arm}<br>";
    echo "Right Arm: {$colors->right_arm}<br>";
    echo "Left Leg: {$colors->left_leg}<br>";
    echo "Right Leg: {$colors->right_leg}<br>";
}

// Check user_data
$checkUserData = $db->prepare("SELECT * FROM user_data WHERE user = ?");
$checkUserData->bindValue(1, $userId, PDO::PARAM_INT);
$checkUserData->execute();

if ($checkUserData->rowCount() == 0) {
    echo "❌ Missing user_data record<br>";
} else {
    $userData = $checkUserData->fetch(PDO::FETCH_OBJ);
    echo "✅ user_data exists<br>";
    echo "Hat1: {$userData->hat1}<br>";
    echo "Hat2: {$userData->hat2}<br>";
    echo "Hat3: {$userData->hat3}<br>";
    echo "Face: {$userData->face}<br>";
    echo "Shirt: {$userData->shirt}<br>";
    echo "Pants: {$userData->pants}<br>";
    echo "Gear: {$userData->gear}<br>";
}

// Check user_friends
$checkFriends = $db->prepare("SELECT * FROM user_friends WHERE sender = ? OR receiver = ?");
$checkFriends->bindValue(1, $userId, PDO::PARAM_INT);
$checkFriends->bindValue(2, $userId, PDO::PARAM_INT);
$checkFriends->execute();

echo "Friends: {$checkFriends->rowCount()} records<br>";

// Check user_bans
$checkBans = $db->prepare("SELECT * FROM user_bans WHERE user = ?");
$checkBans->bindValue(1, $userId, PDO::PARAM_INT);
$checkBans->execute();

echo "Bans: {$checkBans->rowCount()} records<br>";

// Check if profile page works
echo "<h2>Profile Page Test</h2>";
echo "<a href='/user?i=$userId' target='_blank'>Test Profile Page</a><br>";

// Check what happens when we try to access the profile
$testProfile = $db->prepare('SELECT * FROM users WHERE id = ?');
$testProfile->bindValue(1, $userId, PDO::PARAM_INT);
$testProfile->execute();

if ($testProfile->rowCount() == 0) {
    echo "❌ Profile page would fail - user not found in users table<br>";
} else {
    echo "✅ Profile page should work - user found in users table<br>";
}
?> 