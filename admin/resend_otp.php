<?php
require_once("../db.php");
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if (!isset($_SESSION['signup_data'])) {
    header("Location: signup.php");
    exit;
}

// Generate new OTP and expiry
$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['otp_expiry'] = time() + 60; // 60 seconds

// Send email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'Your-Email_Address'; // Your email address
    // Use an App Password if 2FA is enabled
    // Go to Google account in security and Generate an App Password from your Google Account settings
    $mail->Password = 'Email-App-Password';// Your App Password of the email account do not use your email password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('Your-Email_Address', 'My Website OTP , EDIT BY TJ');
    $mail->addAddress($_SESSION['signup_data']['email'], $_SESSION['signup_data']['fullname']);
    $mail->isHTML(true);
    $mail->Subject = 'Your New OTP Code';
    $mail->Body = "<h3>Your new OTP is: <b>$otp</b></h3><p>It will expire in 60 seconds.</p>";

    $mail->send();
    header("Location: verify_otp.php");
    exit;
} catch (Exception $e) {
    echo "❌ Could not send OTP. Mailer Error: {$mail->ErrorInfo}";
}
?>
