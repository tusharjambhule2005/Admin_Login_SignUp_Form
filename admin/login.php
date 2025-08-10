<?php
session_start();
require_once("../db.php"); // Include DB connection
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, fullname, password FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['admin_id'] = $row['id'];
            $_SESSION['admin_name'] = $row['fullname'];
            header("Location: home.php");
            exit;
        } else {
            $message = "❌ Invalid password.";
        }
    } else {
        $message = "❌ No account found with that email.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login Page | TUSHAR JAMBHULE |EDIT BY TJ</title>
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
.login-container input[type="password"],
.login-container input[type="email"] {
  width: 100%;
  padding: 10px;
  margin: 8px 0;
  border: 1px solid black;
  border-radius: 25px;
  box-sizing: border-box;
  outline: none;
}

.login-container input[type="text"]:focus,
.login-container input[type="password"]:focus,
.login-container input[type="email"]:focus {
  border: 2px solid black;
  outline: none;
}


.login-container input[type="submit"] {
  width: 100%;
  padding: 10px;
  margin-top: 10px;
  background-color: #4285F4;
  color: white;
  border: 1px solid black;
  border-radius: 25px;
  cursor: pointer;
}

.login-container input[type="submit"]:hover {
  background-color: #357ae8;
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

.forgot-password {
  text-align: right;
  margin: 5px 0 10px;
}

.forgot-password a {
  font-size: 12px;
  color: #4285F4;
  text-decoration: none;
}

.forgot-password a:hover {
  text-decoration: underline;
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

.signup-button {
  margin-top: 20px;
}

.signup-button button {
  width: 100%;
  padding: 10px;
  border: 1px solid black;
  border-radius: 25px;
  background-color: #4CAF50;
  color: white;
  font-weight: bold;
  cursor: pointer;
  font-size: 14px;
}

.signup-button button:hover {
  background-color: #45a049;
}


</style>
</head>
<body>
<div class="login-container">

    <?php if (!empty($message)): ?>
        <div class="message <?php echo (strpos($message, '✅') === 0) ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <h2>Login</h2>
    <form action="" method="POST">
        <input type="email" name="email" placeholder="Email" required>

        <div class="password-container">
            <input type="password" id="password" name="password" placeholder="Password" required>
            <span class="toggle-password" onclick="togglePassword()">👁️</span>
        </div>

<div class="forgot-password">
    <a href="forgot_password.php">Forgot password?</a>
</div>


        <input type="submit" value="Login">

        <div class="separator">OR</div>

        <div class="social-buttons">
            <button type="button" class="social-button google-btn">
                <img src="https://img.icons8.com/color/48/000000/google-logo.png" alt="Google icon">
                Continue with Google
            </button>
            <button type="button" class="social-button facebook-btn">
                <img src="https://img.icons8.com/ios-filled/50/ffffff/facebook-new.png" alt="Facebook icon">
                Continue with Facebook
            </button>
        </div>

<div class="signup-button">
    <button type="button" onclick="window.location.href='signup.php'">Sign Up</button>
</div>

    </form>
</div>

<script>
function togglePassword() {
    const passwordField = document.getElementById("password");
    const icon = document.querySelector(".toggle-password");
    if (passwordField.type === "password") {
        passwordField.type = "text";
        icon.textContent = "🙈";
    } else {
        passwordField.type = "password";
        icon.textContent = "👁️";
    }
}
</script>
</body>
</html>
