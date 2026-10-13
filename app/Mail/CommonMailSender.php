<?php

namespace App\Mail;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\DB;

class CommonMailSender
{

	public function sendMail($from, $to, $subject, $message, $isMultipleRecipient = false, $ccEmail = [], $bccEmail = [])
	{


// 		$mail = new PHPMailer(true); // Passing `true` enables exceptions
// 		try {

// 			$mail->CharSet = 'UTF-8';
// 			$mail->Encoding = 'quoted-printable';
// 			/*
// 				             *  Server settings
// 			*/

// 			// $mail->SMTPDebug = 0; // Enable verbose debug output
// 			$mail->SMTPDebug = 0; // Enable verbose debug output
// 			//$mail->isSMTP(); // Set mailer to use SMTP
// 			$mail->Host = 'localhost'; // Specify main and backup SMTP servers
// 			// $mail->SMTPAuth = False; // Enable SMTP authentication
// 			$mail->isSMTP();
// 			$mail->Host       = 'smtp.gmail.com';
// 			$mail->SMTPAuth   = true;
// 			$mail->Username   = 'bhumi.qf@gmail.com';
// 			$mail->Password   = 'lrmnvwvlwudgswoi';
// 			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
// 			$mail->Port       = 587;

// 			/*
// 				             * Recipients
// 			*/
// 			// $mail->setFrom('noreply@reehana.com', $from['name']);
// 			if ($isMultipleRecipient) {
// 				foreach ($to as $value) {
// 					$mail->addAddress($value['email'], $value['name']); // Add a recipient, Name is optional
// 				}
// 			} else {
// 				$mail->addAddress($to['email'], $to['name']); // Add a recipient, Name is optional
// 			}
// 			//$mail->addReplyTo('your-email@gmail.com', 'Mailer');
// 			//$mail->addCC('his-her-email@gmail.com');
// 			//$mail->addBCC('his-her-email@gmail.com');
// 			// if ($ccEmail && isset($ccEmail['email'])) {
// 			// 	//$mail->addCC($ccEmail['email']);

// 			// 	$bcc = explode(",", $ccEmail['email']);
// 			// 	if (is_array($bcc)) {
// 			// 		foreach ($bcc as $val) {
// 			// 			$mail->addBCC($val);
// 			// 		}
// 			// 	}
// 			// }
// 			// if ($bccEmail && isset($bccEmail['email'])) {
// 			// 	$mail->addBCC($bccEmail['email']);
// 			// }

// 			/*
// 				             * Attachments (optional)
// 			*/
// 			// $mail->addAttachment('/var/tmp/file.tar.gz');// Add attachments
// 			// $mail->addAttachment('/tmp/image.jpg', 'new.jpg');// Optional name
// 			/*
// 	             * Content
// */
// 			//$message = 'test';
// 			$mail->isHTML(true); // Set email format to HTML
// 			$mail->Subject = $subject;
// 			$mail->Body = $message; // message
// 			if ($mail->send()) {
// 				$status = true;
// 				$message = "Mail sent successfully";
// 			} else {
// 				$status = false;
// 				$message = "Mail not sent! Something went wrong";
// 			}
// 		} catch (Exception $e) {
// 			$status = true;
// 			$message = $e->errorMessage();
// 		}

// 		DB::table('mail_log')->insert([
// 			'email' => $isMultipleRecipient ? implode(',', array_column($to, 'email')) : $to['email'],
// 			'response' => $message,
// 			'created_at' => now(),
// 			// 'updated_at' => now(),
// 		]);
// 		//	dd($message);
// 		return [
// 			'status' => $status,
// 			'message' => $message,
// 		];

		return [
			'status' => true,
			'message' => "Mail sent successfully",
		];
	}
}
