<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once($_SERVER['DOCUMENT_ROOT'] . '/private/header.inc.php');

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Testing Group View SQL Query...<br>";

try {
    // This is the query from line 372 of group/view/index.php
    $sql = 'SELECT group_comments.*, users.* FROM group_comments JOIN users WHERE group_comments.user = users.id AND group_comments.id = ?';
    echo "Query: " . htmlspecialchars($sql) . "<br>";
    
    $stmt = $db->prepare($sql);
    // We need a valid comment ID. Let's find one first.
    $check = $db->query("SELECT id FROM group_comments LIMIT 1");
    if ($check->rowCount() > 0) {
        $id = $check->fetchColumn();
        $stmt->bindValue(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        echo "Query executed successfully.<br>";
    } else {
        echo "No group comments found to test with, but prepare didn't crash.<br>";
    }

} catch (PDOException $e) {
    echo "<strong>SQL Error Caught:</strong> " . $e->getMessage() . "<br>";
}

echo "Testing Asset Paths...<br>";
$securePath = $_SERVER['DOCUMENT_ROOT'] . '/JGFJFSHFSAH/core/rendering/group/';
$publicPath = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/group/';

echo "Secure Path exists? " . (file_exists($securePath) ? "Yes" : "No") . "<br>";
echo "Public Path exists? " . (file_exists($publicPath) ? "Yes" : "No") . "<br>";

?>