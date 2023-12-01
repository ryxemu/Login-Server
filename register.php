<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
</head>
<body>
    <h2>User Registration</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username"><br>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password"><br><br>
        <input type="submit" value="Register">
    </form>

    <?php
    $servername = "localhost";
    $username = "your_mysql_username";
    $password = "your_mysql_password";
    $dbname = "your_database_name";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username'];
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            echo "Username and password are required.";
        } else {
            // Generate salt and hash the password using bcrypt
            $salt = password_hash($username, PASSWORD_DEFAULT);
            $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['salt' => $salt]);

            // Prepare and execute SQL query to insert user into the database
            $stmt = $conn->prepare("INSERT INTO users (username, password, salt) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed_password, $salt);

            if ($stmt->execute()) {
                echo "Registration successful";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    $conn->close();
    ?>
</body>
</html>
