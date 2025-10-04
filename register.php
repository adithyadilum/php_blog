<?php
include 'config.php';
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    //check if usrename or email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "Username or email already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?,?,?)");
        $stmt->bind_param("sss", $username, $email, $password);
        if ($stmt->execute()) {
            $message = "Registration successful! You can now log in.";
        } else {
            $message = "Error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register | PHP Blog</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <h2>User Registration</h2>
    <form method="POST">
        <label>Username:</label><br>
        <input type="text" name="username" required> <br><br>
        <label>Email:</label><br>
        <input type="email" name="email" required> <br><br>
        <label>Password:</label><br>
        <input type="password" name="password" required> <br><br>
        <button type="submit">Register</button>
    </form>
    <p style="color:red"><?php echo $message ?></p>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</body>

</html>