<?php
require_once("../db.php");
session_start();
$message = "";

if (!isset($_SESSION['otp']) || !isset($_SESSION['signup_data'])) {
    header("Location: signup.php");
    exit;
}

$remaining_time = max(0, $_SESSION['otp_expiry'] - time());
$redirect_after_success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['otp'])) {
    if (time() > $_SESSION['otp_expiry']) {
        $message = "❌ OTP expired! Please request a new one.";
    } else {
        $user_otp = trim($_POST['otp']);
        if ($user_otp == $_SESSION['otp']) {
            // OTP correct → insert into DB
            $data = $_SESSION['signup_data'];
            $stmt = $conn->prepare("INSERT INTO admin (fullname, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $data['fullname'], $data['email'], $data['phone'], $data['password']);
            
            if ($stmt->execute()) {
                unset($_SESSION['otp'], $_SESSION['signup_data'], $_SESSION['otp_expiry']);
                $message = "✅ Registration successful! Redirecting to login...";
                $redirect_after_success = true; // Flag for JS redirect
            } else {
                $message = "❌ Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "❌ Invalid OTP!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Verify OTP | TUSHAR JAMBHULE |EDIT BY TJ</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: linear-gradient(to right, #4facfe, #00f2fe);
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
    }
    .container {
        background: white;
        padding: 25px 30px;
        border-radius: 10px;
        box-shadow: 0px 4px 20px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 400px;
        text-align: center;
    }
    h2 {
        margin-bottom: 15px;
        color: #333;
    }
    p.success {
        background: #d4edda;
        color: #155724;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    p.error {
        background: #f8d7da;
        color: #721c24;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 15px;
    }
    #timer {
        font-weight: bold;
        color: #ff5722;
    }
    input[type="text"] {
        width: 100%;
        padding: 10px;
        margin: 8px 0;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 16px;
        text-align: center;
    }
    button {
        background-color: #4facfe;
        color: white;
        padding: 10px 18px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        margin-top: 8px;
        transition: background 0.3s ease;
    }
    button:hover {
        background-color: #00c6fb;
    }
    .resend-btn {
        background-color: #ff9800;
    }
    .resend-btn:hover {
        background-color: #fb8c00;
    }
</style>
</head>
<body>
<div class="container">

    <?php if (!empty($message)): ?>
        <p class="<?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
            <?php echo $message; ?>
        </p>
    <?php endif; ?>
    
    <?php if (!$redirect_after_success): ?>
        <h2>Verify OTP</h2>
        <p>OTP will expire in: <span id="timer"><?php echo $remaining_time; ?></span> seconds</p>

        <form method="POST">
            <input type="text" name="otp" placeholder="Enter OTP" required>
            <button type="submit">Verify</button>
        </form>

        <form method="POST" action="resend_otp.php" id="resendForm" style="display:none;">
            <button type="submit" class="resend-btn">Resend OTP</button>
        </form>
    <?php endif; ?>

</div>

<script>
<?php if ($redirect_after_success): ?>
    setTimeout(function() {
        window.location.href = "login.php?registered=1";
    }, 3000); // Redirect after 3 seconds
<?php else: ?>
    let timeLeft = <?php echo $remaining_time; ?>;
    const timerElement = document.getElementById("timer");
    const resendForm = document.getElementById("resendForm");

    const countdown = setInterval(() => {
        if (timeLeft <= 0) {
            clearInterval(countdown);
            timerElement.textContent = "Expired";
            resendForm.style.display = "block";
        } else {
            timerElement.textContent = timeLeft;
            timeLeft--;
        }
    }, 1000);
<?php endif; ?>
</script>
</body>
</html>
