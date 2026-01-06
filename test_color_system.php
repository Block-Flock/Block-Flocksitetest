<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

echo "<h1>Color System Test</h1>";

// Test database connection
echo "<h2>Database Connection Test</h2>";
try {
    $test = $db->query("SELECT 1");
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test user_colors table
echo "<h2>User Colors Table Test</h2>";
try {
    $tableCheck = $db->query("SHOW TABLES LIKE 'user_colors'");
    if($tableCheck->rowCount() > 0) {
        echo "✅ user_colors table exists<br>";
        
        // Check table structure
        $structure = $db->query("DESCRIBE user_colors");
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while($row = $structure->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check if current user has colors
        if(isset($_SESSION['id'])) {
            $userCheck = $db->prepare('SELECT * FROM user_colors WHERE user = ?');
            $userCheck->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
            $userCheck->execute();
            
            if($userCheck->rowCount() > 0) {
                $userColors = $userCheck->fetch(PDO::FETCH_OBJ);
                echo "<h3>Current User Colors (User ID: " . $_SESSION['id'] . "):</h3>";
                echo "<ul>";
                echo "<li>Head: " . $userColors->head . "</li>";
                echo "<li>Torso: " . $userColors->torso . "</li>";
                echo "<li>Left Arm: " . $userColors->left_arm . "</li>";
                echo "<li>Right Arm: " . $userColors->right_arm . "</li>";
                echo "<li>Left Leg: " . $userColors->left_leg . "</li>";
                echo "<li>Right Leg: " . $userColors->right_leg . "</li>";
                echo "</ul>";
            } else {
                echo "❌ No color record found for current user<br>";
            }
        } else {
            echo "⚠️ No user session found<br>";
        }
    } else {
        echo "❌ user_colors table does not exist<br>";
    }
} catch (Exception $e) {
    echo "❌ Error checking table: " . $e->getMessage() . "<br>";
}

// Test color change endpoint
echo "<h2>Color Change Endpoint Test</h2>";
if(isset($_SESSION['id'])) {
    echo "<form method='post'>";
    echo "<select name='test_part'>";
    echo "<option value='head'>Head</option>";
    echo "<option value='torso'>Torso</option>";
    echo "<option value='left_arm'>Left Arm</option>";
    echo "<option value='right_arm'>Right Arm</option>";
    echo "<option value='left_leg'>Left Leg</option>";
    echo "<option value='right_leg'>Right Leg</option>";
    echo "</select>";
    echo "<select name='test_indicator'>";
    for($i = 1; $i <= 18; $i++) {
        echo "<option value='$i'>Color $i</option>";
    }
    echo "</select>";
    echo "<input type='submit' value='Test Color Change'>";
    echo "</form>";
    
    if($_POST) {
        echo "<h3>Test Results:</h3>";
        
        // Simulate the color change
        $indicator = intval($_POST['test_indicator']);
        $part = $_POST['test_part'];
        
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
        
        try {
            // Update color
            $update = $db->prepare('UPDATE user_colors SET '.$part.' = ? WHERE user = ?');
            $update->bindValue(1, $Colors[$indicator], PDO::PARAM_STR);
            $update->bindValue(2, $_SESSION['id'], PDO::PARAM_INT);
            $update->execute();
            
            echo "✅ Color updated successfully<br>";
            echo "Part: $part<br>";
            echo "Color: " . $Colors[$indicator] . "<br>";
            
            // Verify the change
            $verify = $db->prepare('SELECT * FROM user_colors WHERE user = ?');
            $verify->bindValue(1, $_SESSION['id'], PDO::PARAM_INT);
            $verify->execute();
            $result = $verify->fetch(PDO::FETCH_OBJ);
            
            echo "Verified " . $part . ": " . $result->$part . "<br>";
            
        } catch (Exception $e) {
            echo "❌ Error updating color: " . $e->getMessage() . "<br>";
        }
    }
} else {
    echo "⚠️ No user session - cannot test color changes<br>";
}

echo "<h2>Session Information</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?> 