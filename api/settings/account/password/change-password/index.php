<?php
if($AUTH && $_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['csrf']) && $_POST['csrf'] == $_SESSION['CSRF-TOKEN'] && !empty($_POST['currentPassword']) && !empty($_POST['newPassword']) && !empty($_POST['newPasswordConfirm'])) {

    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $newPasswordConfirm = $_POST['newPasswordConfirm'];

    if (!password_verify($currentPassword, $myU->password)) {
			
        $errorMessage = 'The current password you entered is invalid, please try again.';
        $_SESSION['JumpToTab'] = 'password';
        
    } else if ($newPasswordConfirm != $newPassword) {
        
        $errorMessage = 'Your confirm new password does not match your new password, please try again.';
        $_SESSION['JumpToTab'] = 'password';
        
    } else if (strlen($newPassword) < 8 || !preg_match('~[0-9]~', $newPassword)) {
        
        $errorMessage = 'Invalid password. A password should have at least 8 characters with a number.';
        $_SESSION['JumpToTab'] = 'password';
        
    } else {
        
        $CurrentEncrypted = $myU->password;
        $NewEncrypted = password_hash($newPassword, PASSWORD_DEFAULT);
        $OneDay = time() + 86400;
        $key = md5(microtime() + $myU->id);
        
        $log = $db->prepare("INSERT INTO UserPasswordChanges (UserID, PreviousPassword, NewPassword, RevertCode, Expire, IP) VALUES(?, ?, ?, ?, ?, ?)");
        $log->bindValue(1, $myU->id, PDO::PARAM_INT);
        $log->bindValue(2, $CurrentEncrypted, PDO::PARAM_STR);
        $log->bindValue(3, $NewEncrypted, PDO::PARAM_STR);
        $log->bindValue(4, $key, PDO::PARAM_STR);
        $log->bindValue(5, $OneDay, PDO::PARAM_INT);
        $log->bindValue(6, $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
        $log->execute();
        
        $change = $db->prepare("UPDATE users SET password = ? WHERE id = ".$myU->id."");
        $change->bindValue(1, $NewEncrypted, PDO::PARAM_STR);
        $change->execute();
        
        $InsertUserActionLog = $db->prepare("INSERT INTO useractionlog (UserID, Action, TimeLog, IP) VALUES(".$myU->id.", ?, ".time().", ?)");
        $InsertUserActionLog->bindValue(1, 'Changed their account password', PDO::PARAM_STR);
        $InsertUserActionLog->bindValue(2, $appIP, PDO::PARAM_STR);
        $InsertUserActionLog->execute();
        
        $cache->delete($myU->id);
        $cache->delete($myU->username.'_Profile');
        $successMessage = 'You have successfully changed your password. For security reasons, you have been signed out.';
        $_SESSION['JumpToTab'] = 'password';
        
        die("success");
        
    }
}