<?php
// Set secure session cookie parameters
$cookieParams = session_get_cookie_params();
session_set_cookie_params([
    'lifetime' => $cookieParams['lifetime'],
    'path' => $cookieParams['path'],
    'domain' => $cookieParams['domain'],
    'secure' => true, // Ensure the cookie is sent over HTTPS
    'httponly' => true, // Prevent JavaScript access to session cookie
    'samesite' => 'Strict' // Prevent CSRF attacks
]);

session_start();

// Set session timeout duration (e.g., 30 minutes)
$timeout_duration = 1800;

// Check if the user is logged in and if the session has timed out
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    // Last request was more than 30 minutes ago
    session_unset();     // Unset $_SESSION variable for the run-time
    session_destroy();   // Destroy session data in storage
    header("Location: index.php"); // Redirect to login page
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity time stamp

include 'conn.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

if (isset($_SESSION['user_id'])) {
    // auto redirect to crud/index.php if user is admin
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT roles FROM users WHERE id='$user_id'";
    $query = mysqli_query($conn, $sql);
    $data = mysqli_fetch_array($query);

    if ($data['roles'] == 'admin') {
        header("Location: crud/index.php");
    } else {
        header("Location: crud/user.php");
    }
    exit();
}





// Define maximum login attempts and lockout time
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOCKOUT_TIME', 300); // 15 minutes

// Check if the user is locked out
if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
    if (time() - $_SESSION['last_login_attempt'] < LOCKOUT_TIME) {
        echo "<script>alert('Too many login attempts. Please try again later.');</script>";
        exit;
    } else {
        // Reset login attempts after lockout time has passed
        $_SESSION['login_attempts'] = 0;
    }
}

// Increment login attempts on each login attempt
if (isset($_POST['login'])) {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
    }
    $_SESSION['login_attempts']++;
    $_SESSION['last_login_attempt'] = time();
}




if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $_SESSION['email'] = $email;

        $sql = "SELECT * FROM users WHERE email='$email'";
        $query = mysqli_query($conn, $sql);
        $data = mysqli_fetch_array($query);

        if ($data && password_verify($password, $data['password'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            $otp = rand(100000, 999999);
            $otp_expiry = date("Y-m-d H:i:s", strtotime("+3 minute"));
            $subject = "Your OTP for Login";
            $message = "Your OTP is: $otp";

            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'alab8438@gmail.com'; //host email 
            $mail->Password = 'Secret Code'; // app password of your host email
            $mail->Port = 587;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->isHTML(true);
            $mail->setFrom('alab8438@gmail.com', 'Computer Laboratory Management System'); //Sender's Email & Name
            $mail->addAddress($email, $name); //Receiver's Email and Name
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->send();

            $sql1 = "UPDATE users SET otp='$otp', otp_expiry='$otp_expiry' WHERE id=" . $data['id'];
            $query1 = mysqli_query($conn, $sql1);

            $_SESSION['temp_user'] = ['id' => $data['id'], 'otp' => $otp];
            header("Location: otp_verification.php");
            exit();
        } 
        else {
            ?>
            <script>
                alert("Invalid Email or Password. Please try again.");
                function navigateToPage() {
                    window.location.href = 'index.php';
                }
                window.onload = function() {
                    navigateToPage();
                }
            </script>
            <?php
        }
        
    } 
    else {
        ?>
        <script>
            alert("Invalid Captcha. Please try again.");
            function navigateToPage() {
                window.location.href = 'index.php';
            }
            window.onload = function() {
                navigateToPage();
            }
        </script>
        <?php
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        function enablesubmitbtn() {
            document.getElementById("submit").disabled = false;
        }
    </script>
    <title></title>
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
            width: 440px;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.8); 
        }
        input[type=text], input[type=password] {
            width: 300px;
            height: 20px;
            padding: 10px;
        }
        label {
            font-size: 20px;
            font-weight: bold;
        }
        form {
            margin-left: 50px;
        }
        a {
            text-decoration: none;
            font-weight: bold;
            font-size: 21px;
            color: blue;
        }
        a:hover {
            cursor: pointer;
            color: purple;
        }
        input[type=submit] {
            width: 70px;
            background-color: blue;
            border: 1px solid blue;
            color: white;
            font-weight: bold;
            padding: 7px;
            margin-left: 130px;
        }
        input[type=submit]:hover {
            background-color: purple;
            cursor: pointer;
            border: 1px solid purple;
        }
    </style>
</head>
<body>
    <div id="container">
        <form method="post" action="index.php">
            <label for="email">Email</label><br>
            <input type="text" name="email" placeholder="Enter Your Email" required><br><br>
            <label for="password">Password:</label><br>
            <input type="password" name="password" placeholder="Enter Your Password" required><br><br>
            <!-- Google reCAPTCHA block -->
            <div class="g-recaptcha" data-sitekey="6Lcnn5EqAAAAAPPAlTqjznykTMTrj44vj5ZVxsXM" data-callback="enablesubmitbtn"></div>
            <input type="submit" id="submit" disabled="disabled" name="login" value="Login"><br><br>
            <label>Don't have an account? </label><a href="registration.php">Sign Up</a>
        </form>
    </div>
</body>
</html>
