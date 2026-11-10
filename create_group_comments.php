<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/init.inc.php');

try {
    $query = "CREATE TABLE IF NOT EXISTS `group_comments` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user` int(11) NOT NULL,
        `item` int(11) NOT NULL,
        `comment` text NOT NULL,
        `timecommented` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `moderation` tinyint(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $db->exec($query);
    echo "Table 'group_comments' created successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>
