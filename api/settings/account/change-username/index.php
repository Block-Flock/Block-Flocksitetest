<?php
if ($AUTH && $_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['csrf']) && $_POST['csrf'] == $_SESSION['CSRF-TOKEN'] && !empty($_POST['newUsername']) && !empty($_POST['newUsernameConfirm']) && !empty($_POST['password'])) {

    $appname = $_POST['newUsername'];
    $appnameConfirm = $_POST['newUsernameConfirm'];
    $password = $_POST['password'];
    $check = new Check();

    if ($my->Currency < 500) {
        die("insufficent-balance");
    } else if ($my->username == $appname) {
        die("unchanged");
    } else if (strlen($appname) < 3 || strlen($appname) > 20 || !preg_match('/^[A-Za-z0-9]*(?:_[A-Za-z0-9]+)*$/', $appname)) {
        die("invalid-username");
    } else if (obfuscateIfProfane($appname) == 1) {
        die("bad-username");
    } else {

        // TODO: Consolidate queries into a (stored procedure/mega query)

        $CountUsername = $db->prepare("SELECT COUNT(*) FROM `users` WHERE `username` = ?");
        $CountUsername->bindValue(1, $appname, PDO::PARAM_STR);
        $CountUsername->execute();
        $CountUsername = $CountUsername->fetchColumn();

        if ($CountUsername > 0) {
            die("username-unavailable");
        } else {

            $DeductCurrencySTMT = $db->prepare("UPDATE `users` SET `username` = ?, `Currency` = `Currency` - 500 WHERE `id` = " . $my->id . "");
            $DeductCurrencySTMT->bindValue(1, $appname, PDO::PARAM_STR);
            $DeductCurrencySTMT->execute();

            if ($DeductCurrencySTMT->rowCount() > 0) {

                $LogUserActionSTMT = $db->prepare("INSERT INTO `useractionlogs` (`UserID`, `Action`, `TimeLog`, `IP`) VALUES(" . $my->id . ", ?, " . time() . ", ?)");
                $LogUserActionSTMT->bindValue(1, 'Changed their account username from \'' . $my->username . '\' to \'' . $appname . '\'', PDO::PARAM_STR);
                $LogUserActionSTMT->bindValue(2, $appIP, PDO::PARAM_STR);
                $LogUserActionSTMT->execute();

            }

            die("success");
        }
    }
}








