<nav class="shadow-sm sticky top-0 z-10">
    <div class="max-w-6xl mx-auto px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <!-- Left: Logo -->
        <div class="flex justify-between items-center">
            <a href="/php_blog/index.php" class="text-2xl font-heading text-primary">📝 PHP Blog</a>
        </div>

        <!-- Center: Search bar -->
        <form action="/php_blog/index.php" method="GET" role="search" class="w-full md:w-1/2">
            <div class="flex items-center gap-3 bg-gray-100 border border-transparent hover:border-gray-300 focus-within:bg-white focus-within:border-gray-400 rounded-full px-4 py-2 transition shadow-sm">
                <svg class="w-5 h-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="6" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input
                    type="text"
                    name="search"
                    placeholder="Search posts"
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                    class="flex-1 bg-transparent text-sm md:text-base text-gray-700 placeholder:text-gray-500 focus:outline-none" />
                <button type="submit" class="sr-only">Submit search</button>
            </div>
        </form>

        <!-- Right: Navigation links -->
        <div class="flex flex-wrap justify-center md:justify-end gap-4 text-gray-700">
            <a href="/php_blog/index.php" class="hover:text-primary font-medium">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/php_blog/posts/create.php" class="hover:text-primary font-medium">Write</a>
                <a href="/php_blog/logout.php" class="hover:text-primary font-medium">Logout</a>
            <?php else: ?>
                <a href="/php_blog/login.php" class="hover:text-primary font-medium">Login</a>
                <a href="/php_blog/register.php" class="hover:text-primary font-medium">Register</a>
            <?php endif; ?>
        </div>

    </div>
</nav>