<?php
$to = "uditprajapati9791@gmail.com";
$subject = "Test Email";
$message = "This is a test email sent from PHP.";
$headers = "From: noreply@reehana.com\r\n";
$headers .= "Reply-To: noreply@reehana.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

if (mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully!";
} else {
    echo "Email sending failed.";
}
?>
