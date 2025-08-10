<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | TUSHAR JAMBHULE |EDIT BY TJ</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f2f2f2;
        margin: 0;
        padding: 0;
    }
    .dashboard {
        max-width: 600px;
        margin: 100px auto;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        text-align: center;
    }
    h1 {
        color: #2c3e50;
    }
    a.logout {
        display: inline-block;
        margin-top: 20px;
        padding: 10px 20px;
        background-color: #e74c3c;
        color: white;
        text-decoration: none;
        border-radius: 4px;
    }
    a.logout:hover {
        background-color: #c0392b;
    }
</style>
</head>
<body>

<div class="dashboard">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</h1>
    <p>You are now in the protected admin dashboard.</p>
    <a class="logout" href="logout.php">Logout</a>
</div>

</body>
</html>
