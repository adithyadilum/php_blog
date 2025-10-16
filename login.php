<?php
include 'config.php';
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // Success and store session variables
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            header("Location: index.php");
            exit;
        } else {
            $message = "Incorrect password!";
        }
    } else {
        $message = "User not found!";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md mt-8">
    <h2 class="text-3xl font-bold mb-6 text-center text-gray-800">Login</h2>

    <?php if (!empty($message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
            <input type="text"
                name="username"
                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                required
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" />
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password"
                name="password"
                required
                class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" />
        </div>

        <button type="submit"
            class="w-full bg-primary text-white py-2 rounded-md hover:bg-blue-600 transition duration-200 font-semibold">
            Sign In
        </button>
    </form>

    <p class="text-center text-sm text-gray-600 mt-4">
        Don't have an account?
        <a href="register.php" class="text-primary hover:underline">Register here</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>