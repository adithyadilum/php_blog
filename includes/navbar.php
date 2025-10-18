<nav class="px-4 py-3 md:px-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between gap-4 md:grid md:grid-cols-[auto_minmax(0,1fr)_auto] md:items-center md:gap-6">
            <a href="/php_blog/index.php" class="font-heading text-xl md:text-xl tracking-[0.35em] text-charcoal uppercase">Paper & Pixels</a>

            <form action="/php_blog/index.php" method="GET" role="search" class="hidden md:flex w-full max-w-md md:justify-self-center" data-desktop-search>
                <label class="sr-only" for="desktop-global-search">Search Paper & Pixels</label>
                <div class="flex w-full items-center gap-3 rounded-full border border-charcoal/10 bg-linen/80 px-5 py-2 shadow-soft transition-all duration-300 focus-within:border-charcoal/40 focus-within:shadow-hover focus-within:ring-2 focus-within:ring-charcoal/15 focus-within:translate-y-[-2px]">
                    <svg class="h-5 w-5 text-charcoal/50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.65 6.65a7.5 7.5 0 0 0 10.02 10.02Z" />
                    </svg>
                    <input id="desktop-global-search" type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Search Paper & Pixels" class="flex-1 bg-transparent text-sm md:text-base text-charcoal placeholder:text-charcoal/40 focus:outline-none" />
                    <button type="submit" class="sr-only">Search</button>
                </div>
            </form>

            <div class="flex items-center gap-3 md:hidden">
                <button type="button" class="rounded-full border border-charcoal/15 bg-linen/70 p-2 text-charcoal/80 transition hover:border-charcoal/50 hover:text-charcoal" data-nav-search-toggle aria-controls="mobile-search" aria-expanded="false" aria-label="Toggle search">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="6" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                </button>
                <button type="button" class="rounded-full border border-charcoal/15 bg-linen/70 p-2 text-charcoal/80 transition hover:border-charcoal/50 hover:text-charcoal" data-nav-menu-toggle aria-controls="mobile-menu" aria-expanded="false" aria-label="Toggle navigation">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="4" y1="7" x2="20" y2="7" />
                        <line x1="4" y1="12" x2="20" y2="12" />
                        <line x1="4" y1="17" x2="20" y2="17" />
                    </svg>
                </button>
            </div>

            <div class="hidden md:flex items-center gap-6 md:justify-self-end">
                <div class="flex items-center gap-3 text-sm">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="/php_blog/posts/create.php" class="px-4 py-2 rounded-full border border-charcoal/30 text-charcoal/80 transition hover:border-charcoal/60 hover:text-charcoal">Write a Story</a>
                        <a href="/php_blog/logout.php" class="px-4 py-2 rounded-full bg-charcoal text-linen transition hover:bg-opacity-80">Logout</a>
                    <?php else: ?>
                        <a href="/php_blog/login.php" class="px-4 py-2 rounded-full border border-charcoal/30 text-charcoal/80 transition hover:border-charcoal/60 hover:text-charcoal">Login</a>
                        <a href="/php_blog/register.php" class="px-4 py-2 rounded-full bg-charcoal text-linen transition hover:bg-opacity-80">Join the Journal</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div id="mobile-search" class="mt-3 hidden" data-mobile-search>
            <form action="/php_blog/index.php" method="GET" role="search" class="w-full">
                <label class="sr-only" for="mobile-global-search">Search Paper & Pixels</label>
                <div class="flex items-center gap-3 rounded-2xl border border-charcoal/10 bg-linen/80 px-5 py-3 shadow-soft transition-all duration-300 focus-within:border-charcoal/40 focus-within:shadow-hover focus-within:ring-2 focus-within:ring-charcoal/15">
                    <svg class="h-5 w-5 text-charcoal/50" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.65 6.65a7.5 7.5 0 0 0 10.02 10.02Z" />
                    </svg>
                    <input id="mobile-global-search" type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Search Paper & Pixels" class="flex-1 bg-transparent text-base text-charcoal placeholder:text-charcoal/40 focus:outline-none" />
                    <button type="submit" class="sr-only">Search</button>
                </div>
            </form>
        </div>

        <div id="mobile-menu" class="mt-3 hidden space-y-4" data-mobile-menu>
            <div class="flex flex-col gap-3 text-sm">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/php_blog/posts/create.php" class="rounded-full border border-charcoal/30 px-4 py-3 text-center text-charcoal/80 transition hover:border-charcoal/60 hover:text-charcoal">Write a Story</a>
                    <a href="/php_blog/logout.php" class="rounded-full bg-charcoal px-4 py-3 text-center text-linen transition hover:bg-opacity-80">Logout</a>
                <?php else: ?>
                    <a href="/php_blog/login.php" class="rounded-full border border-charcoal/30 px-4 py-3 text-center text-charcoal/80 transition hover:border-charcoal/60 hover:text-charcoal">Login</a>
                    <a href="/php_blog/register.php" class="rounded-full bg-charcoal px-4 py-3 text-center text-linen transition hover:bg-opacity-80">Join the Journal</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>