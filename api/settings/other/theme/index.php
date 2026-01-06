<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/private/config.inc.php");

if($AUTH && $_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['csrf']) && $_POST['csrf'] == $_SESSION['CSRF-TOKEN'] && !empty($_POST['theme'])) {

    $id = $my->id;
    $theme = strtolower($_POST['theme']);

    $getThemeSTMT = $db->prepare("SELECT `Theme` FROM `users` WHERE `id` = ?");
    $getThemeSTMT->bindValue(1, $id, PDO::PARAM_INT);
    $getThemeSTMT->execute();

    $gT = $getThemeSTMT->fetch(PDO::FETCH_OBJ);

    if($theme == $gT) {
        die("unchanged");
    }else{
        $updateThemeSTMT = $db->prepare("UPDATE `users` SET `Theme` = ? WHERE `id` = ?");
        $updateThemeSTMT->bindValue(1, $theme, PDO::PARAM_STR);
        $updateThemeSTMT->bindValue(2, $id, PDO::PARAM_INT);
        $updateThemeSTMT->execute();

        die("success");
    }
}
