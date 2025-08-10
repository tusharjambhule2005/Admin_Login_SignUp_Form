<?php
require_once("../db.php");
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Composer autoload

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $message = "❌ Passwords do not match!";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "❌ This email is already registered.";
        } else {
            // Store form data in session
            $_SESSION['signup_data'] = [
                'fullname' => $fullname,
                'email' => $email,
                'phone' => $phone,
                'password' => password_hash($password, PASSWORD_DEFAULT)
            ];

            // Generate OTP and expiry
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_expiry'] = time() + 60; // Expires in 60 seconds

            // Send email via PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'Your-Email_Address'; // Your email address
                // Use an App Password if 2FA is enabled
                // Go to Google account in security and Generate an App Password from your Google Account settings  
                $mail->Password = 'Email-App-Password'; // ❌ Do not hardcode in real projects
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('Your-Email_Address', 'My Website OTP');// Adjust sender email
                $mail->addAddress($email, $fullname);

                $mail->isHTML(true);
                $mail->Subject = 'Your OTP Code';
                $mail->Body = "<h3>Your OTP is: <b>$otp</b></h3><p>It will expire in 60 seconds.</p>";

                $mail->send();

                header("Location: verify_otp.php");
                exit;
            } catch (Exception $e) {
                $message = "❌ Could not send OTP. Mailer Error: {$mail->ErrorInfo}";
            }
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title> Admin Signup Page | TUSHAR JAMBHULE |EDIT BY TJ</title>
<style>
  body {
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
    background: #f2f2f2;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .login-container {
    width: 100%;
    max-width: 320px;
    padding: 30px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    text-align: center;
  }

@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap');

.login-container h2 {
  margin-bottom: 24px;
  font-size: 32px;
  font-family: 'Playfair Display', serif;
  font-weight: 700;
  color: #2c3e50;
  letter-spacing: 1px;
  text-transform: uppercase;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
  border-bottom: 2px solid #3498db;
  display: inline-block;
  padding-bottom: 6px;
}


  .login-container input[type="text"],
  .login-container input[type="email"],
  .login-container input[type="password"],
  .login-container input[type="tel"] {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    border: 1px solid black;
    border-radius: 25px;
    box-sizing: border-box;
    outline: none;
  }

  .login-container input[type="text"]:focus,
  .login-container input[type="email"]:focus,
  .login-container input[type="password"]:focus,
  .login-container input[type="tel"]:focus {
    border: 2px solid black;
    outline: none;
  }
  .login-container input[type="submit"] {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    background-color: #4CAF50;
    color: white;
    border: 1px solid black;
    border-radius: 25px;
    cursor: pointer;
  }

  .login-container input[type="submit"]:hover {
    background-color: #45a049;
  }

  .password-container {
    position: relative;
  }

  .toggle-password {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 14px;
    color: #666;
  }

  .separator {
    margin: 20px 0;
    display: flex;
    align-items: center;
    text-align: center;
  }

  .separator::before,
  .separator::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #ccc;
  }

  .separator:not(:empty)::before {
    margin-right: .75em;
  }

  .separator:not(:empty)::after {
    margin-left: .75em;
  }

  .social-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .social-button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    font-size: 14px;
  }

  .google-btn {
    background-color: #fff;
    color: #444;
    border: 1px solid #ccc;
  }

  .facebook-btn {
    background-color: #3b5998;
    color: white;
  }

  .social-button img {
    width: 20px;
    height: 20px;
  }

  .login-redirect {
  margin-top: 15px;
}

.login-redirect button {
  width: 100%;
  padding: 10px;
  background-color: #3498db;
  color: white;
  border: 1px solid black;
  border-radius: 25px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.3s ease;
}

.login-redirect button:hover {
  background-color: #2980b9;
}

  .message {
    margin-bottom: 15px;
    font-weight: bold;
  }
  .success { color: green; }
  .error { color: red; }
</style>
</head>
<body>
<div class="login-container">

    <?php if (!empty($message)): ?>
        <div class="message <?php echo (strpos($message, '✅') === 0) ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
        <h2>Sign Up</h2>
<form action="" method="POST">
  <input type="text" name="fullname" placeholder="Full Name" required>
  <input type="email" name="email" placeholder="Email" required>
        <input type="tel" name="phone" placeholder="Phone Number" required>

  <div class="password-container">
    <input type="password" id="password" name="password" placeholder="Password" required>
    <span class="toggle-password" onclick="togglePassword()">👁️</span>
  </div>

  <div class="password-container">
    <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm Password" required>
    <span class="toggle-password" onclick="toggleConfirmPassword()">👁️</span>
  </div>

  <input type="submit" value="Sign Up">

        <div class="separator">OR</div>

      <div class="social-buttons">
        <button type="button" class="social-button google-btn">
          <img src="https://img.icons8.com/color/48/000000/google-logo.png" alt="Google icon">
          Sign up with Google
        </button>
        <button type="button" class="social-button facebook-btn">
          <img src="https://img.icons8.com/ios-filled/50/ffffff/facebook-new.png" alt="Facebook icon">
          Sign up with Facebook
        </button>
      </div>

<div class="login-redirect">
    <button type="button" onclick="window.location.href='login.php'">Log In</button>
</div>

</form>
</div>
<script>
function togglePassword() {
    const field = document.getElementById("password");
    const icon = field.nextElementSibling;
    if (field.type === "password") { field.type = "text"; icon.textContent = "🙈"; }
    else { field.type = "password"; icon.textContent = "👁️"; }
}
function toggleConfirmPassword() {
    const field = document.getElementById("confirm-password");
    const icon = field.nextElementSibling;
    if (field.type === "password") { field.type = "text"; icon.textContent = "🙈"; }
    else { field.type = "password"; icon.textContent = "👁️"; }
}
</script>
</body>
</html>
