<nav class="navbar">
    <h2><a href="/php_blog/index.php">PHP Blog</a></h2>
    <ul>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="/php_blog/index.php">Home</a></li>
            <li><a href="/php_blog/posts/create.php">New Post</a></li>
            <li><a href="/php_blog/logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
        <?php else: ?>
            <li><a href="/php_blog/login.php">Login</a></li>
            <li><a href="/php_blog/register.php">Register</a></li>
        <?php endif; ?>
    </ul>
</nav>