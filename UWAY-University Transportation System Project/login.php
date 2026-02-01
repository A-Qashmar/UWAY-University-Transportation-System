<?php
session_start();
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $pass  = $_POST["password"];

    $conn = mysqli_connect("localhost", "root", "", "uway");

    $query = "SELECT * FROM User WHERE Email = '$email'";
    $result = $conn->query($query);

    if ($result && mysqli_num_rows($result) == 1) {

        $row = mysqli_fetch_array($result);

        if ($row["Password"] == $pass) {

            $_SESSION["user_id"] = $row["UserID"];
            $_SESSION["role"]    = $row["Role"];

            if ($row["Role"] == "Admin") {
                header("Location: admin.php");
            } elseif ($row["Role"] == "Driver") {
                header("Location: driver.php");
            } else {
                header("Location: student.php");
            }
            exit;
        }
    }

    $error = "Invalid email or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UWAY Login</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="login-page">

<div class="login-card">

    <div class="login-logo">
        <img src="uway-logo.png" alt="UWAY Logo">
        <div class="login-logo-title">UWAY</div>
    </div>

    <div class="login-subtitle">University Transportation System</div>

    <?php
    if ($error != "") {
        echo "<div class='error' style='text-align:center;margin-bottom:10px;'>$error</div>";
    }
    ?>

    <form action="login.php" method="post">
        <p>
            <label>Email</label>
            <input class="login-input" type="text" name="email" required>
        </p>

        <p>
            <label>Password</label>
            <input class="login-input" type="password" name="password" required>
        </p>

        <input class="login-btn" type="submit" value="Login">
    </form>
</div>

</body>
</html>
