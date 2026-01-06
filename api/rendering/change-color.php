<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

// Debug logging
error_log("change-color.php called with POST data: " . print_r($_POST, true));
error_log("change-color.php session data: " . print_r($_SESSION, true));
error_log("change-color.php AUTH value: " . ($AUTH ? 'true' : 'false'));

if(isset($_POST['indicator']) && isset($_POST['part']) && $AUTH) {
    // Validate inputs
    if(!is_numeric($_POST['indicator']) || $_POST['indicator'] < 1 || $_POST['indicator'] > 18) {
        die(json_encode(['success' => false, 'message' => 'Invalid color indicator']));
    }
    
    // Flood control
    if($my->flood >= time()) {
        die(json_encode([
            'success' => false, 
            'message' => 'Please wait another '.($my->flood - time()).' seconds before changing your colors.'
        ]));
    }

    // Color mapping
    $Colors = [
        1 => '0.013702, 0.502886, 0.332452', 
        2 => '0.011612, 0.351533, 0.234551', 
        3 => '0.027321, 0.603827, 0.162029', 
        4 => '0.033105, 0.313989, 0.708376', 
        5 => '0.000000, 0.132868, 0.318547', 
        6 => '0.371238, 0.124772, 0.520996', 
        7 => '0.270498, 0.056128, 0.417885', 
        8 => '0.046665, 0.090842, 0.155926', 
        9 => '0.015996, 0.028426, 0.046665', 
        10 => '0.887923, 0.558341, 0.005605', 
        11 => '0.896269, 0.332452, 0.009721', 
        12 => '0.806952, 0.070360, 0.045186', 
        13 => '0.527115, 0.039546, 0.024158', 
        14 => '0.723055, 0.791298, 0.806952', 
        15 => '0.508881, 0.545725, 0.577581', 
        16 => '1.000000, 0.708376, 0.412543', 
        17 => '0, 0, 0', 
        18 => '0.564712, 0.238398, 0.054480'
    ];

    // Sanitize part name
    $allowedParts = ['head', 'torso', 'left_arm', 'right_arm', 'left_leg', 'right_leg'];
    $part = $_POST['part'];
    if(!in_array($part, $allowedParts)) {
        die(json_encode(['success' => false, 'message' => 'Invalid body part']));
    }

    try {
        // Check if user_colors table exists, create if not
        $tableCheck = $db->query("SHOW TABLES LIKE 'user_colors'");
        if($tableCheck->rowCount() == 0) {
            $createTable = $db->prepare("
                CREATE TABLE user_colors (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user INT NOT NULL,
                    head VARCHAR(20) DEFAULT 'FFFFFF',
                    torso VARCHAR(20) DEFAULT '3398db',
                    left_arm VARCHAR(20) DEFAULT 'FFFFFF',
                    right_arm VARCHAR(20) DEFAULT 'FFFFFF',
                    left_leg VARCHAR(20) DEFAULT 'FFFFFF',
                    right_leg VARCHAR(20) DEFAULT 'FFFFFF',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY unique_user (user)
                )
            ");
            $createTable->execute();
            error_log("Created user_colors table");
        }
        
        // Ensure user_colors row exists for this user, if not, insert with defaults
        $check = $db->prepare('SELECT * FROM user_colors WHERE user = ?');
        $check->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $check->execute();
        if($check->rowCount() == 0) {
            $insert = $db->prepare('INSERT INTO user_colors (user, head, torso, left_arm, right_arm, left_leg, right_leg) VALUES (?, "FFFFFF", "3398db", "FFFFFF", "FFFFFF", "FFFFFF", "FFFFFF")');
            $insert->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
            $insert->execute();
            error_log("Created user colors record for user " . $_SESSION['id']);
        }
        // Update color in database
        $change = $db->prepare('UPDATE user_colors SET '.$part.' = ? WHERE user = ?'); 
        $change->bindValue(1, $Colors[$_POST['indicator']], PDO::PARAM_STR);
        $change->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
        $change->execute();

        // Update flood control
        $flood = $db->prepare('UPDATE users SET flood = ? WHERE id = ?');
        $flood->bindValue(1, time() + 5, PDO::PARAM_STR);
        $flood->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
        $flood->execute();

        // Return updated color information
        $getColors = $db->prepare('SELECT * FROM user_colors WHERE user = ?');
        $getColors->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
        $getColors->execute();
        $gc = $getColors->fetch(PDO::FETCH_OBJ);

        // Convert color string to RGB array (0-1 range)
        function colorstr2rgb($str) {
            $arr = array_map('floatval', explode(',', str_replace(' ', '', $str)));
            if(count($arr) === 3) return $arr;
            return [1,1,1];
        }
        $response = [
            'success' => true,
            'part' => $part,
            'color' => $Colors[$_POST['indicator']],
            'rgb' => [
                'head' => colorstr2rgb($gc->head),
                'torso' => colorstr2rgb($gc->torso),
                'left_arm' => colorstr2rgb($gc->left_arm),
                'right_arm' => colorstr2rgb($gc->right_arm),
                'left_leg' => colorstr2rgb($gc->left_leg),
                'right_leg' => colorstr2rgb($gc->right_leg)
            ]
        ];

        echo json_encode($response);
        error_log("change-color.php response: " . json_encode($response));
        
    } catch (Exception $e) {
        error_log("change-color.php error: " . $e->getMessage());
        die(json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]));
    }
} else {
    error_log("change-color.php invalid request - AUTH: " . ($AUTH ? 'true' : 'false') . ", POST: " . print_r($_POST, true));
    die(json_encode(['success' => false, 'message' => 'Invalid request']));
}

// Helper function to convert hex to RGB array
function hex2rgb($hex) {
    if (empty($hex)) return [1, 1, 1];
    
    $hex = str_replace('#', '', $hex);
    
    if (strlen($hex) == 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    
    if (!preg_match('/^[a-f0-9]{6}$/i', $hex)) {
        return [1, 1, 1];
    }
    
    $r = hexdec(substr($hex, 0, 2)) / 255;
    $g = hexdec(substr($hex, 2, 2)) / 255;
    $b = hexdec(substr($hex, 4, 2)) / 255;
    
    return [$r, $g, $b];
}