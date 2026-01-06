<?php
	class mailHandler {
		public static function sendMail($message, $altmessage, $to, $title, $username) {
			include_once $_SERVER['DOCUMENT_ROOT'].'/mail/PHPMailerAutoload.php';
			$mail = new PHPMailer;
			$mail->isSMTP();
			$mail->Host = 'smtp.gmail.com';
			$mail->Port = 465;
			$mail->SMTPSecure = 'ssl';
			$mail->SMTPAuth = true;
			$mail->Username = '';
			$mail->Password = '';
			$mail->From = '';
			$mail->FromName = 'Bricktaria';
			$mail->addAddress($to, $username);
			$mail->addReplyTo('support@bricktaria.com', 'Bricktaria');
			$mail->WordWrap = 50;
			$mail->isHTML(true);
			$mail->Subject = $title;
			$mail->Body    = $message;
			$mail->AltBody = $altmessage;
			$mail->send();
		}
	}
?>
