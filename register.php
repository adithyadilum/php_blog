<?php
include 'includes/config.php';
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
            unset($_SESSION['guest_access']);
            $message = "Registration successful! You can now log in.";
        } else {
            $message = "Error: " . $stmt->error;
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<section class="px-6 py-16 flex min-h-[calc(100vh-8rem)] items-center">
    <div class="mx-auto flex w-full max-w-5xl flex-col-reverse gap-10 md:flex-row md:items-center">
        <div class="md:w-1/2">
            <div class="rounded-3xl border border-charcoal/10 bg-linen/70 p-8 shadow-soft backdrop-blur">
                <h2 class="font-heading text-2xl text-charcoal text-center mb-6">Create your account</h2>

                <?php if (!empty($message)): ?>
                    <?php
                    $isSuccess = stripos($message, 'successful') !== false;
                    $alertClasses = $isSuccess
                        ? 'border-green-200 bg-green-50 text-green-700'
                        : 'border-red-200 bg-red-50 text-red-600';
                    ?>
                    <div class="mb-4 rounded-2xl border <?php echo $alertClasses; ?> px-4 py-3 text-sm" role="alert">
                        <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-5">
                    <div class="space-y-2">
                        <label class="text-xs uppercase tracking-[0.3em] text-charcoal/70">Username</label>
                        <input type="text" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>" class="w-full rounded-xl border border-charcoal/20 bg-white/70 px-4 py-3 text-charcoal placeholder:text-charcoal/40 focus:border-charcoal/50 focus:bg-white focus:outline-none focus:ring-0 transition" required>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs uppercase tracking-[0.3em] text-charcoal/70">Email</label>
                        <input type="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>" class="w-full rounded-xl border border-charcoal/20 bg-white/70 px-4 py-3 text-charcoal placeholder:text-charcoal/40 focus:border-charcoal/50 focus:bg-white focus:outline-none focus:ring-0 transition" required>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs uppercase tracking-[0.3em] text-charcoal/70">Password</label>
                        <input type="password" name="password" class="w-full rounded-xl border border-charcoal/20 bg-white/70 px-4 py-3 text-charcoal placeholder:text-charcoal/40 focus:border-charcoal/50 focus:bg-white focus:outline-none focus:ring-0 transition" required>
                    </div>

                    <button type="submit" class="btn-major w-full rounded-full px-6 py-3 text-sm font-semibold uppercase tracking-[0.3em]">Register</button>
                </form>

                <div class="mt-5 flex justify-center">
                    <a href="index.php?guest=1" class="inline-flex items-center gap-2 rounded-full border border-charcoal/20 bg-white/70 px-5 py-2 text-[0.65rem] font-semibold uppercase tracking-[0.3em] text-charcoal transition hover:border-charcoal/50 hover:text-charcoal/80">
                        <svg aria-hidden="true" class="h-3.5 w-3.5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                            <path d="M5 12h14" stroke-linecap="round" />
                            <path d="M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Explore as guest
                    </a>
                </div>

                <p class="mt-6 text-center text-sm text-charcoal/70">
                    Already part of the studio?
                    <a href="login.php" class="font-medium text-charcoal hover:opacity-70 underline-offset-4 hover:underline">Sign in</a>
                </p>
            </div>
        </div>

        <div class="md:w-1/2 space-y-4 text-center md:text-left">
            <p class="uppercase tracking-[0.4em] text-xs text-charcoal/60">Join the collective</p>
            <h1 class="font-heading text-4xl text-charcoal">Start telling stories with us</h1>
            <p class="font-sans text-base text-charcoal/70">Create your Paper & Pixels account to publish bold ideas, collaborate with peers, and collect inspiration that bridges analog warmth with digital clarity.</p>
            <ul class="space-y-3 text-sm text-charcoal/70">
                <li class="inline-flex items-center gap-2"><span class="text-charcoal">★</span> Publish with a minimalist editor built for focus.</li>
                <li class="inline-flex items-center gap-2"><span class="text-charcoal">★</span> Save drafts and revisit them across devices.</li>
                <li class="inline-flex items-center gap-2"><span class="text-charcoal">★</span> Curate your own gallery of favorite stories.</li>
            </ul>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>