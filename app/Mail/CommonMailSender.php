<?php

namespace App\Mail;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CommonMailSender {

	public function sendMail($from, $to, $subject, $message, $isMultipleRecipient = false, $ccEmail = [], $bccEmail = []) {

		// try
		// {
		// 	Mail::html($message, function ($m) use ($from, $to, $subject, $isMultipleRecipient, $ccEmail, $bccEmail) {
		// 		$m->from("noreply@reehana.com", $from["name"]);
		// 		$m->subject($subject);

		// 		if ($isMultipleRecipient) {
		// 			foreach ($to as $value) {
		// 				$m->to($value["email"], $value["name"]);
		// 			}
		// 		} else {
		// 			$m->to($to["email"], $to["name"]);
		// 		}

		// 		if ($ccEmail && isset($ccEmail["email"])) {
		// 			$bcc = explode(",", $ccEmail["email"]);
		// 			if (is_array($bcc)) {
		// 				foreach ($bcc as $val) {
		// 					$m->bcc(trim($val));
		// 				}
		// 			}
		// 		}

		// 		if ($bccEmail && isset($bccEmail["email"])) {
		// 			$m->bcc($bccEmail["email"]);
		// 		}
		// 	});

		// 	$status = true;
		// 	$responseMessage = "Mail sent successfully";
		// } catch (\Exception $e) {
		// 	$status = false;
		// 	$responseMessage = $e->getMessage();
		// }

		// DB::table("mail_log")->insert([
		// 	"email" => $isMultipleRecipient ? implode(",", array_column($to, "email")) : $to["email"],
		// 	"response" => $responseMessage,
		// 	"created_at" => now(),
		// ]);

		return [
			"status" => true,
			"message" => "Mail sent successfully"
		];
	}

}
