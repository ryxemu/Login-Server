<?php
session_start();

if (isset($_SESSION['access_token'])) {
    header('Location: /dashboard.php');
    exit();
}

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $client_id = 'YOUR_GOOGLE_CLIENT_ID';
    $client_secret = 'YOUR_GOOGLE_CLIENT_SECRET';
    $redirect_uri = 'http://yourdomain.com/google-callback.php'; // Replace with your registered callback URI

    $token_url = 'https://oauth2.googleapis.com/token';
    $params = [
        'code' => $code,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code'
    ];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $token_url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true);
    if (isset($data['access_token'])) {
        $access_token = $data['access_token'];

        // Get user info from Google
        $info_url = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $access_token;
        $user_info = json_decode(file_get_contents($info_url), true);

        // Connect to SQLite database
        $db = new SQLite3('test.db');
        if (!$db) {
            die("Database connection failed: " . $db->lastErrorMsg());
        }

        // Check if user already exists in the database
        $query = "SELECT * FROM users WHERE google_id = '" . $user_info['id'] . "'";
        $result = $db->query($query);
        
        if ($result->fetchArray(SQLITE3_ASSOC)) {
            $_SESSION['user_id'] = $user_info['id'];
            header('Location: /dashboard.php');
            exit();
        } else {
            // Insert new user into the database
            $insertQuery = "INSERT INTO users (google_id, email, name) VALUES ('" . $user_info['id'] . "', '" . $user_info['email'] . "', '" . $user_info['name'] . "')";
            $result = $db->exec($insertQuery);
            if (!$result) {
                echo "Error: " . $db->lastErrorMsg();
                exit();
            }
            $_SESSION['user_id'] = $user_info['id'];
            header('Location: /dashboard.php');
            exit();
        }

        $db->close();
    } else {
        echo 'Error while fetching access token.';
    }
} else {
    $client_id = 'YOUR_GOOGLE_CLIENT_ID';
    $redirect_uri = 'http://yourdomain.com/google-callback.php'; // Replace with your registered callback URI

    $auth_url = 'https://accounts.google.com/o/oauth2/auth?client_id=' . $client_id . '&redirect_uri=' . $redirect_uri . '&response_type=code&scope=email profile';
    header('Location: ' . $auth_url);
    exit();
}
