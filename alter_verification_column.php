<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/private/config.inc.php');

$alterUsers = $db->prepare("
    ALTER TABLE `users`
    CHANGE `emailVerifyCode` `emailVerifyCode` VARCHAR(255) NULL DEFAULT NULL;
");
$alterUsers->execute();

echo "Column `emailVerifyCode` in table `users` has been successfully altered.";
?>