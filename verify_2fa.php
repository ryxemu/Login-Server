<?php
require_once 'vendor/autoload.php'; // Include the Google Authenticator library

// Retrieve the user's secret key from the database based on the user identifier

$google2fa = new \PragmaRX\Google2FA\Google2FA();
$isValid = $google2fa->verifyKey($storedSecret, $_POST['code']);

if ($isValid) {
    // Code is valid, authenticate the user and proceed to dashboard
    header('Location: /dashboard.php');
    exit();
} else {
    echo "Invalid verification code!";
    // Handle the error (e.g., display an error message or redirect back to the verification form)
}
