<nav class="bg-white shadow-sm sticky top-0 z-10">
    <div class="max-w-6xl mx-auto px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <!-- Left: Logo -->
        <div class="flex justify-between items-center">
            <a href="/php_blog/index.php" class="text-2xl font-heading text-primary">📝 PHP Blog</a>
        </div>

        <!-- Center: Search bar -->
        <form action="/php_blog/index.php" method="GET" class="flex items-center gap-2 w-full md:w-1/2">
            <input
                type="text"
                name="search"
                placeholder="Search posts..."
                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                class="flex-grow px-3 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary">
            <button
                type="submit"
                class="bg-primary text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">
                Search
            </button>
        </form>

        <!-- Right: Navigation links -->
        <div class="flex flex-wrap justify-center md:justify-end gap-4 text-gray-700">
            <a href="/php_blog/index.php" class="hover:text-primary font-medium">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/php_blog/posts/create.php" class="hover:text-primary font-medium">New Post</a>
                <a href="/php_blog/logout.php" class="hover:text-primary font-medium">Logout</a>
            <?php else: ?>
                <a href="/php_blog/login.php" class="hover:text-primary font-medium">Login</a>
                <a href="/php_blog/register.php" class="hover:text-primary font-medium">Register</a>
            <?php endif; ?>
        </div>

    </div>
</nav>