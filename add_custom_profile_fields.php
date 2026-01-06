<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

echo "<h1>Adding Custom Profile Fields</h1>";

try {
    // Add custom_css field
    $addCustomCSS = $db->prepare("ALTER TABLE users ADD COLUMN custom_css TEXT DEFAULT NULL");
    $addCustomCSS->execute();
    echo "✅ Added custom_css field to users table<br>";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "ℹ️ custom_css field already exists<br>";
    } else {
        echo "❌ Error adding custom_css field: " . $e->getMessage() . "<br>";
    }
}

try {
    // Add custom_theme field
    $addCustomTheme = $db->prepare("ALTER TABLE users ADD COLUMN custom_theme VARCHAR(50) DEFAULT 'default'");
    $addCustomTheme->execute();
    echo "✅ Added custom_theme field to users table<br>";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "ℹ️ custom_theme field already exists<br>";
    } else {
        echo "❌ Error adding custom_theme field: " . $e->getMessage() . "<br>";
    }
}

echo "<h2>Creating themes directory</h2>";

// Create themes directory if it doesn't exist
$themesDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/themes';
if (!is_dir($themesDir)) {
    if (mkdir($themesDir, 0755, true)) {
        echo "✅ Created themes directory at /assets/themes<br>";
    } else {
        echo "❌ Failed to create themes directory<br>";
    }
} else {
    echo "ℹ️ Themes directory already exists<br>";
}

echo "<h2>Creating sample theme files</h2>";

// Create a sample dark theme
$darkTheme = $themesDir . '/dark.css';
if (!file_exists($darkTheme)) {
    $darkCSS = "/* Dark Theme for User Profiles */
body {
    background-color: #1a1a1a !important;
    color: #ffffff !important;
}

.box {
    background-color: #2d2d2d !important;
    border: 1px solid #404040 !important;
}

.novello-title {
    color: #ffffff !important;
}

.has-text-grey-light {
    color: #b0b0b0 !important;
}

.button {
    background-color: #404040 !important;
    border-color: #606060 !important;
    color: #ffffff !important;
}

.button:hover {
    background-color: #505050 !important;
}

.button.is-success {
    background-color: #23d160 !important;
}

.button.is-danger {
    background-color: #ff3860 !important;
}

.button.is-primary {
    background-color: #00d1b2 !important;
}

.button.is-warning {
    background-color: #ffdd57 !important;
    color: #363636 !important;
}";
    
    if (file_put_contents($darkTheme, $darkCSS)) {
        echo "✅ Created dark theme<br>";
    } else {
        echo "❌ Failed to create dark theme<br>";
    }
} else {
    echo "ℹ️ Dark theme already exists<br>";
}

// Create a sample neon theme
$neonTheme = $themesDir . '/neon.css';
if (!file_exists($neonTheme)) {
    $neonCSS = "/* Neon Theme for User Profiles */
body {
    background: linear-gradient(45deg, #0a0a0a, #1a0a1a) !important;
    color: #00ff00 !important;
}

.box {
    background-color: rgba(0, 0, 0, 0.8) !important;
    border: 2px solid #00ff00 !important;
    box-shadow: 0 0 20px rgba(0, 255, 0, 0.3) !important;
}

.novello-title {
    color: #00ff00 !important;
    text-shadow: 0 0 10px #00ff00 !important;
}

.has-text-grey-light {
    color: #00cc00 !important;
}

.button {
    background-color: transparent !important;
    border: 2px solid #00ff00 !important;
    color: #00ff00 !important;
    text-shadow: 0 0 5px #00ff00 !important;
    box-shadow: 0 0 10px rgba(0, 255, 0, 0.3) !important;
}

.button:hover {
    background-color: rgba(0, 255, 0, 0.1) !important;
    box-shadow: 0 0 20px rgba(0, 255, 0, 0.5) !important;
}

.button.is-success {
    border-color: #00ff00 !important;
    color: #00ff00 !important;
}

.button.is-danger {
    border-color: #ff0066 !important;
    color: #ff0066 !important;
    text-shadow: 0 0 5px #ff0066 !important;
}

.button.is-primary {
    border-color: #0066ff !important;
    color: #0066ff !important;
    text-shadow: 0 0 5px #0066ff !important;
}

.button.is-warning {
    border-color: #ffaa00 !important;
    color: #ffaa00 !important;
    text-shadow: 0 0 5px #ffaa00 !important;
}";
    
    if (file_put_contents($neonTheme, $neonCSS)) {
        echo "✅ Created neon theme<br>";
    } else {
        echo "❌ Failed to create neon theme<br>";
    }
} else {
    echo "ℹ️ Neon theme already exists<br>";
}

echo "<h2>Setup Complete!</h2>";
echo "<p>Users can now customize their profiles with:</p>";
echo "<ul>";
echo "<li><strong>Custom CSS:</strong> Direct CSS code in their profile settings</li>";
echo "<li><strong>Custom Themes:</strong> Pre-made themes (dark, neon, etc.)</li>";
echo "</ul>";
echo "<p>Available themes: default, dark, neon</p>";
echo "<br><a href='/admin'>Back to Admin</a>";
?> 