<?php
session_start();

require_once 'vendor/autoload.php'; // Include the Google Authenticator library

// Your database connection details
$servername = 'your_server';
$username = 'your_username';
$password = 'your_password';
$dbname = 'your_database';

// Establish database connection (replace with your database details)
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Placeholder function for database query to save user information
function saveUser($conn, $email, $passwordHash)
{
    $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $passwordHash);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle user registration
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate and hash the password
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Save user details to the database
    saveUser($conn, $email, $passwordHash);

    // Generate and display 2FA setup
    $google2fa = new \PragmaRX\Google2FA\Google2FA();
    $secret = $google2fa->generateSecretKey();
    $_SESSION['user_email'] = $email; // Store the email in the session for later use

    $qrCodeUrl = $google2fa->getQRCodeInline(
        'YourAppName',
        $email,
        $secret
    );
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register and Setup 2FA</title>
</head>
<body>
    <?php if (isset($qrCodeUrl)): ?>
        <h1>Register and Enable Two-Factor Authentication (2FA)</h1>
        <p>Scan the QR code below with Google Authenticator:</p>
        <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code">

        <form action="verify_2fa.php" method="post">
            <label for="code">Enter verification code:</label>
            <input type="text" id="code" name="code" required>
            <input type="submit" value="Verify">
        </form>
    <?php else: ?>
        <h1>Registration Successful</h1>
        <p>Your account has been registered. You can now log in.</p>
    <?php endif; ?>
</body>
</html>
