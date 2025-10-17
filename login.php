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

<section class="px-6 py-16">
    <div class="mx-auto flex max-w-5xl flex-col gap-10 md:flex-row md:items-center">
        <div class="md:w-1/2 space-y-4 text-center md:text-left">
            <p class="uppercase tracking-[0.4em] text-xs text-charcoal/60">Welcome back</p>
            <h1 class="font-heading text-4xl text-charcoal">Unlock your creative dashboard</h1>
            <p class="font-sans text-base text-charcoal/70">Sign in to Paper & Pixels to continue crafting stories that blend artistry with technology. Your projects, drafts, and bookmarked inspirations are waiting.</p>
        </div>

        <div class="md:w-1/2">
            <div class="rounded-3xl border border-charcoal/10 bg-linen/70 p-8 shadow-soft backdrop-blur">
                <h2 class="font-heading text-2xl text-charcoal text-center mb-6">Log in</h2>

                <?php if (!empty($message)): ?>
                    <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600" role="alert">
                        <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form method="post" class="space-y-5">
                    <div class="space-y-2">
                        <label class="text-xs uppercase tracking-[0.3em] text-charcoal/70">Username</label>
                        <input type="text"
                            name="username"
                            value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                            required
                            class="w-full rounded-xl border border-charcoal/20 bg-white/70 px-4 py-3 text-charcoal placeholder:text-charcoal/40 focus:border-charcoal/50 focus:bg-white focus:outline-none focus:ring-0 transition" />
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs uppercase tracking-[0.3em] text-charcoal/70">Password</label>
                        <input type="password"
                            name="password"
                            required
                            class="w-full rounded-xl border border-charcoal/20 bg-white/70 px-4 py-3 text-charcoal placeholder:text-charcoal/40 focus:border-charcoal/50 focus:bg-white focus:outline-none focus:ring-0 transition" />
                    </div>

                    <button type="submit"
                        class="w-full rounded-full bg-charcoal px-6 py-3 text-sm font-semibold uppercase tracking-[0.3em] text-linen transition hover:bg-opacity-80">
                        Sign In
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-charcoal/70">
                    New here?
                    <a href="register.php" class="font-medium text-charcoal hover:opacity-70 underline-offset-4 hover:underline">Create an account</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>