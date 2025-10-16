<?php
include 'config.php';
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'user';

    //check if usrename or email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $message = "Username or email already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $username, $email, $password, $role);
        if ($stmt->execute()) {
            $message = "Registration successful! You can now log in.";
        } else {
            $message = "Error: " . $stmt->error;
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold mb-6 text-center">Register</h2>
    <form method="POST">
        <label class="block mb-2 text-sm font-medium">Username</label>
        <input type="text" name="username" class="w-full border rounded px-3 py-2 mb-4 focus:outline-none focus:ring-2 focus:ring-primary" required>

        <label class="block mb-2 text-sm font-medium">Email</label>
        <input type="email" name="email" class="w-full border rounded px-3 py-2 mb-4 focus:outline-none focus:ring-2 focus:ring-primary" required>

        <label class="block mb-2 text-sm font-medium">Password</label>
        <input type="password" name="password" class="w-full border rounded px-3 py-2 mb-6 focus:outline-none focus:ring-2 focus:ring-primary" required>

        <button type="submit" class="w-full bg-primary text-white py-2 rounded hover:bg-blue-600 transition">Register</button>
    </form>
</div>


<p style="color:red;"><?php echo $message ?? ''; ?></p>

<?php include 'includes/footer.php'; ?>