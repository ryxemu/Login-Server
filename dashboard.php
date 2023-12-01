<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /register.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome to the Dashboard!</h1>
    <p>You are logged in.</p>
    <p>User ID: <?php echo $_SESSION['user_id']; ?></p>

</body>
</html>
