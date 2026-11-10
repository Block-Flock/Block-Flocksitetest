<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/db.php');

echo "<h1>Debug Group View</h1>";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Error: No valid group ID provided. Usage: debug_group_view.php?id=123");
}

$groupId = $_GET['id'];
echo "<h2>Debugging Group ID: $groupId</h2>";

// 1. Fetch Group Data
echo "<h3>1. Group Data Fetch</h3>";
try {
    $getGroup = $db->prepare('SELECT user_groups.*, users.id as userid, users.username FROM user_groups JOIN users ON user_groups.creator = users.id WHERE user_groups.id = ?');
    $getGroup->bindValue(1, $groupId, PDO::PARAM_INT);
    $getGroup->execute();
    $groupData = $getGroup->fetch(PDO::FETCH_ASSOC);

    if ($groupData) {
        echo "<pre>Group Object: " . print_r($groupData, true) . "</pre>";
    } else {
        echo "<p style='color:red;'>Group not found in database or creator missing (Join on users failed).</p>";
        
        // Check if group exists at least
        $checkGroupOnly = $db->prepare("SELECT * FROM user_groups WHERE id = ?");
        $checkGroupOnly->execute([$groupId]);
        $rawGroup = $checkGroupOnly->fetch(PDO::FETCH_ASSOC);
        if ($rawGroup) {
             echo "<pre>Raw Group (without join): " . print_r($rawGroup, true) . "</pre>";
             echo "<p>Creator ID: " . $rawGroup['creator'] . "</p>";
             
             // Check if creator exists
             $checkUser = $db->prepare("SELECT id, username FROM users WHERE id = ?");
             $checkUser->execute([$rawGroup['creator']]);
             $creator = $checkUser->fetch(PDO::FETCH_ASSOC);
             if (!$creator) {
                 echo "<p style='color:red;'>CREATOR MISSING in users table!</p>";
             } else {
                 echo "<p>Creator exists: " . print_r($creator, true) . "</p>";
             }
        }
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>SQL Error: " . $e->getMessage() . "</p>";
}

// 2. Member Count Query
echo "<h3>2. Member Count Check</h3>";
try {
    $countMembers = $db->prepare("SELECT COUNT(*) as total FROM user_group_members WHERE groupid = ?");
    $countMembers->execute([$groupId]);
    $res = $countMembers->fetch(PDO::FETCH_ASSOC);
    echo "<p>Count from user_group_members: " . ($res['total'] ?? 'N/A') . "</p>";
    if ($groupData) {
        echo "<p>Members field in user_groups: " . $groupData['members'] . "</p>";
    }
} catch (Exception $e) {
     echo "<p style='color:red;'>SQL Error: " . $e->getMessage() . "</p>";
}

// 3. Ranks Query
echo "<h3>3. Ranks Check</h3>";
try {
    $getRanks = $db->prepare("SELECT * FROM user_group_ranks WHERE groupid = ? ORDER BY indicator ASC");
    $getRanks->execute([$groupId]);
    $ranks = $getRanks->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>Ranks: " . print_r($ranks, true) . "</pre>";
} catch (Exception $e) {
     echo "<p style='color:red;'>SQL Error: " . $e->getMessage() . "</p>";
}

// 4. Secure Asset Path Check
echo "<h3>4. Secure Asset Path Check</h3>";
if ($groupData) {
    $logo = $groupData['logo'];
    // Assuming the base path based on standard layouts
    $possiblePath = $_SERVER['DOCUMENT_ROOT'] . "/JGFJFSHFSAH/core/rendering/group/" . $logo;
    echo "<p>Checking path: $possiblePath</p>";
    if (file_exists($possiblePath)) {
        echo "<p style='color:green;'>Logo file exists and is readable.</p>";
    } else {
        echo "<p style='color:red;'>Logo file NOT FOUND at $possiblePath</p>";
    }
} else {
    echo "<p>Cannot check logo path - no group data.</p>";
}

echo "<hr><p>Debug Complete.</p>";
