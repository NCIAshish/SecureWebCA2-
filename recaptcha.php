<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $secretKey = "6Lcnn5EqAAAAAD-u44c0KzaEZt3EDJj7vmzAGNz_"; // Replace with your Secret Key
    $responseKey = $_POST['g-recaptcha-response'];
    $userIP = $_SERVER['REMOTE_ADDR'];

    $url = "https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$responseKey}&remoteip={$userIP}";

    $response = file_get_contents($url);
    $responseKeys = json_decode($response, true);

    if (intval($responseKeys["success"]) !== 1) {
        echo "Please complete the reCAPTCHA correctly.";
    } else {
        echo "Verification successful! You're a human!";
    }
} else {
    echo "Invalid request.";
}
?>