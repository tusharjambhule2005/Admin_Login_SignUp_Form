<?php
session_start();
require_once("../db.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    
    $stmt = $conn->prepare("SELECT id, fullname FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $admin_id = $row['id'];
        $fullname = $row['fullname'];
        
        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', time() + 900); // 15 mins expiry
        
        // Save token in DB
        $insert = $conn->prepare("INSERT INTO password_resets (admin_id, token, expires_at) VALUES (?, ?, ?)");
        $insert->bind_param("iss", $admin_id, $token, $expires_at);
        if ($insert->execute()) {
            // Send reset email
            $reset_link = "http://localhost/Admin_Login_SignUp_Form/admin/reset_password.php?token=$token"; // Adjust URL as needed means your server URL

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'Your-Email_Address'; // Your email address
                // Use an App Password if 2FA is enabled
                // Go to Google account in security and Generate an App Password from your Google Account settings
                $mail->Password = 'Email-App-Password'; // Your App Password of the email account do not use your email password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('Your-Email_Address', 'My Website');// Adjust sender email
                $mail->addAddress($email, $fullname);
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = "<p>Hello $fullname,</p>
                    <p>Click the link below to reset your password (valid for 15 minutes):</p>
                    <a href='$reset_link'>$reset_link</a>";

                $mail->send();

                $message = "✅ A password reset link has been sent to your email.";
            } catch (Exception $e) {
                $message = "❌ Could not send email. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $message = "❌ Could not generate reset link. Please try again later.";
        }
        $insert->close();
    } else {
        $message = "❌ Email not found.";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Forgot Password | TUSHAR JAMBHULE |EDIT BY TJ</title>
<style>
  body {
    background: #f7f9fc;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
  }

  form {
    background: #fff;
    padding: 30px 40px;
    border-radius: 8px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.1);
    width: 320px;
    box-sizing: border-box;
    text-align: center;
  }

  input[type="email"] {
    width: 100%;
    padding: 12px 15px;
    margin: 10px 0 20px 0;
    border: 1.8px solid #ccc;
    border-radius: 5px;
    font-size: 1rem;
    box-sizing: border-box;
    transition: border-color 0.3s;
  }

  input[type="email"]:focus {
    border-color: #007BFF;
    outline: none;
  }

  input[type="submit"] {
    width: 100%;
    background: #007BFF;
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
    border: none;
    border-radius: 5px;
    padding: 12px;
    cursor: pointer;
    transition: background 0.3s;
  }

  input[type="submit"]:hover {
    background: #0056b3;
  }

  p.message {
    margin-top: 15px;
    font-weight: 600;
    font-size: 1rem;
  }

  p.message.success {
    color: #28a745;
  }

  p.message.error {
    color: #dc3545;
  }
</style>
</head>
<body>

<form method="POST" action="">
  <h2>Forgot Password</h2>
  <input type="email" name="email" placeholder="Enter your registered email" required>
  <input type="submit" value="Send Reset Link">
  <p class="message <?php echo (strpos($message, '❌') === 0) ? 'error' : 'success'; ?>"><?php echo $message; ?></p>
</form>

</body>
</html>
