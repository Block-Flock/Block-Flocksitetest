<?php
// ✅ SECURITY: Define environment state. Set to false in production!
define('ENV_IS_PRODUCTION', false); // <-- CHANGE TO TRUE ON LIVE SERVER

// ✅ Start session securely (before any output)
if (session_status() === PHP_SESSION_NONE) {
    // ✅ SECURITY: Use strict session cookie parameters
    session_set_cookie_params([
        'lifetime' => 86400, // 1 day
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
        'secure' => (!ENV_IS_PRODUCTION) ? false : true, // Force HTTPS in production
        'httponly' => true, // Prevent JS access to session cookie
        'samesite' => 'Strict' // Mitigate CSRF
    ]);
    session_start();
    
    // ✅ SECURITY: Regenerate session ID periodically to prevent fixation
    $regenerateInterval = 300; // 5 minutes
    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    } elseif (time() - $_SESSION['CREATED'] > $regenerateInterval) {
        session_regenerate_id(true);
        $_SESSION['CREATED'] = time();
    }
    
    // ✅ SECURITY: Generate a fresh CSRF token once per session
    if (empty($_SESSION['CSRF-TOKEN'])) {
        $_SESSION['CSRF-TOKEN'] = bin2hex(random_bytes(32));
    }
}

// ✅ SECURITY: Safe error reporting for production vs development
if (ENV_IS_PRODUCTION) {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    // Log errors to a secure file instead
    ini_set('log_errors', 1);
    ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/private/logs/php_errors.log');
} else {
    // Development - show errors
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// ✅ SECURITY: Include config files safely
$configPath = $_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php';
$functionsPath = $_SERVER['DOCUMENT_ROOT'] . '/private/functions.inc.php';

if (file_exists($configPath) && file_exists($functionsPath)) {
    require_once($configPath);
    require_once($functionsPath);
} else {
    // Log and die with a generic message in production
    error_log("Critical configuration files missing at: " . $configPath);
    http_response_code(500);
    die('Service temporarily unavailable.');
}

// Verbose performance / debug helpers (enable with ?debug=1 or configuration.debug == 1)
$__perf_enabled = (isset($_GET['debug']) && $_GET['debug'] === '1') || (isset($configure) && isset($configure->debug) && $configure->debug == 1);
$__perf_marks = array();
function perf_mark($name) {
    global $__perf_marks;
    $__perf_marks[$name] = microtime(true);
}
function perf_report_html() {
    global $__perf_marks, $__perf_enabled;
    if (!$__perf_enabled) return '';
    $output = "\n<!-- Server-Side Performance Marks:\n";
    $prev = null;
    foreach ($__perf_marks as $name => $time) {
        $output .= sprintf("  %s: %.4fs", $name, $time);
        if ($prev !== null) {
            $output .= sprintf(" (+%.4fs)", $time - $prev);
        }
        $output .= "\n";
        $prev = $time;
    }
    $output .= "-->\n";
    return $output;
}

// mark that PHP includes finished
perf_mark('php_includes');

// ✅ SECURITY: Initialize admin state without a hardcoded "fail" value
if (!isset($_SESSION['ADMIN_KEY'])) {
    $_SESSION['ADMIN_KEY'] = null; // Use null or false, not a string "fail"
}

// Maintenance check
try {
    if (isset($db)) {
        $configureQ = $db->prepare("SELECT * FROM `configuration`");
        $configureQ->execute();
        $configure = $configureQ->fetch(PDO::FETCH_OBJ);
    } else {
        // Handle missing DB connection gracefully
        $configure = (object)['maintenance' => 0];
        error_log("Database connection not available for maintenance check.");
    }
} catch (PDOException $e) {
    // Log the error, but don't crash the page for a non-critical check
    error_log("Maintenance check query failed: " . $e->getMessage());
    $configure = (object)['maintenance' => 0];
}

if (isset($configure->maintenance) && $configure->maintenance == 1) {
    if (!isset($_SESSION['allowed_Maintenance']) || !$_SESSION['allowed_Maintenance']) {
        header('Location: /offline/');
        exit;
    }
}
?>