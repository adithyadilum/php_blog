<?php
include 'config.php';
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows() > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // Success and store session variables
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit;
        } else {
            $message = "Incorrect password!";
        }
        $message = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login | PHP Blog</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <h2>Login</h2>
    <form method="post">
        <label>Username</label><br>
        <input type="text" name="username" required> <br><br>
        <label>Password</label><br>
        <input type="password" name="password" required> <br><br>
        <button type="submit">Login</button>
    </form>
    <p style="color: red;"> <?php echo $message ?> </p>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
</body>

</html>