<?php
	class mailHandler {
		public static function sendMail($to, $subject, $body) {
			$apiKey = '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';
			$url = 'https://api.brevo.com/v3/smtp/email';

			$data = [
				'sender' => [
					'name' => 'Bricktaria',
					'email' => 'noreply@bricktaria.com'
				],
				'to' => [
					[
						'email' => $to
					]
				],
				'subject' => $subject,
				'htmlContent' => $body
			];

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'accept: application/json',
				'api-key: ' . $apiKey,
				'content-type: application/json'
			]);

			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			
			if (curl_errno($ch)) {
				curl_close($ch);
				return false;
			}

			curl_close($ch);

			if ($httpCode >= 200 && $httpCode < 300) {
				return true;
			} else {
				// Optionally log the error response
				// error_log("Brevo API Error: " . $response);
				return false;
			}
		}
	}
?>
