<?php
session_start();
require_once("../db.php");
$message = "";
$show_form = false;

if (!isset($_GET['token'])) {
    die("Invalid request.");
}

$token = $_GET['token'];

$stmt = $conn->prepare("SELECT pr.id, pr.admin_id, pr.expires_at, pr.used, a.fullname FROM password_resets pr JOIN admin a ON pr.admin_id = a.id WHERE pr.token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Invalid or expired token.");
}

$row = $result->fetch_assoc();

if ($row['used']) {
    die("This reset link has already been used.");
}

if (strtotime($row['expires_at']) < time()) {
    die("This reset link has expired.");
}

$show_form = true;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $message = "❌ Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $message = "❌ Password should be at least 6 characters.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update password in admin table
        $update = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
        $update->bind_param("si", $hashed_password, $row['admin_id']);

        if ($update->execute()) {
            // Mark token as used
            $mark_used = $conn->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
            $mark_used->bind_param("i", $row['id']);
            $mark_used->execute();

            $message = "✅ Password reset successful! You can now <a href='login.php'>login</a>.";
            $show_form = false;
        } else {
            $message = "❌ Could not update password. Try again.";
        }
        $update->close();
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Reset Password | TUSHAR JAMBHULE |EDIT BY TJ</title>
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
  }

  h2 {
    margin-bottom: 20px;
    font-weight: 600;
    color: #333;
    font-size: 1.5rem;
    text-align: center;
  }

  .password-wrapper {
    position: relative;
    margin-bottom: 20px;
  }

  input[type="password"], input[type="text"] {
    width: 100%;
    padding: 12px 40px 12px 15px; /* space for icon on right */
    border: 1.8px solid #ccc;
    border-radius: 5px;
    font-size: 1rem;
    box-sizing: border-box;
    transition: border-color 0.3s;
  }

  input[type="password"]:focus, input[type="text"]:focus {
    border-color: #007BFF;
    outline: none;
  }

  .toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    user-select: none;
    font-size: 1.2rem;
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
    text-align: center;
    font-weight: 600;
    font-size: 1rem;
  }

  p.message a {
    color: #007BFF;
    text-decoration: none;
  }

  p.message a:hover {
    text-decoration: underline;
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

<?php if ($show_form): ?>
<form method="POST" action="">
    <h2>Reset Password for <?php echo htmlspecialchars($row['fullname']); ?></h2>

    <div class="password-wrapper">
        <input type="password" name="password" id="password" placeholder="New Password" required>
        <span class="toggle-password" onclick="togglePassword('password', this)" title="Show/Hide Password">👁️</span>
    </div>

    <div class="password-wrapper">
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
        <span class="toggle-password" onclick="togglePassword('confirm_password', this)" title="Show/Hide Password">👁️</span>
    </div>

    <input type="submit" value="Reset Password">
    <p class="message <?php echo (strpos($message, '❌') === 0) ? 'error' : ''; ?>"><?php echo $message; ?></p>
</form>
<?php else: ?>
<div style="background:#fff; padding:30px 40px; border-radius:8px; box-shadow: 0 4px 18px rgba(0,0,0,0.1); width:320px; box-sizing:border-box; text-align:center;">
  <p class="message <?php echo (strpos($message, '❌') === 0) ? 'error' : 'success'; ?>"><?php echo $message; ?></p>
</div>
<?php endif; ?>

<script>
function togglePassword(fieldId, icon) {
    const passwordField = document.getElementById(fieldId);
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
