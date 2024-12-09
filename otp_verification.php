<?php
session_start();

include 'conn.php';
if (!isset($_SESSION['temp_user'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_otp = filter_input(INPUT_POST, 'otp', FILTER_SANITIZE_NUMBER_INT);
    $stored_otp = $_SESSION['temp_user']['otp'];
    $user_id = $_SESSION['temp_user']['id'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE id=? AND otp=?");
    $stmt->bind_param("is", $user_id, $user_otp);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        $otp_expiry = strtotime($data['otp_expiry']);
        if ($otp_expiry >= time()) {
            $_SESSION['user_id'] = $data['id'];
            unset($_SESSION['temp_user']);
            header("Location: index.php");
            exit();
        } else {
            ?>
            <script>
                alert("OTP has expired. Please try again.");
                function navigateToPage() {
                    window.location.href = 'index.php';
                }
                window.onload = function() {
                    navigateToPage();
                }
            </script>
            <?php 
        }
    } else {
        echo "<script> alert('Invalid OTP. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Two-Step Verification</title>
    <style type="text/css">
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        #container {
            border: 1px solid black;
            width: 400px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        form {
            margin-top: 20px;
        }
        p {
            margin-top: 10px;
        }
        h1 {
            margin-top: 0;
        }
        input[type=number] {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
        }
        button {
            background-color: orange;
            border: 1px solid orange;
            width: 100%;
            padding: 10px;
            margin-top: 20px;
        }
        button:hover {
            cursor: pointer;
            opacity: .9;
        }
    </style>
</head>
<body>
    <div id="container">
        <h1>Two-Step Verification</h1>
        <p>Enter the 6 Digit OTP Code that has been sent <br> to your email address.</p>
        <form method="post" action="otp_verification.php">
            <label style="font-weight: bold; font-size: 18px;" for="otp">Enter OTP Code:</label><br>
            <input type="number" name="otp" pattern="\d{6}" placeholder="Six-Digit OTP" required><br><br>
            <button type="submit">Verify OTP</button>
        </form>
    </div>
</body>
</html>
