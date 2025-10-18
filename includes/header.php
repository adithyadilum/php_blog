<?php
// Always start session first, before any HTML
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['guest'])) {
    $_SESSION['guest_access'] = true;
}

$currentScript = $_SERVER['SCRIPT_NAME'] ?? '';
$currentPage = basename($currentScript);
$isAuthPage = in_array($currentPage, ['login.php', 'register.php'], true);
$isLoggedIn = !empty($_SESSION['user_id']);

if (!$isLoggedIn && !$isAuthPage) {
    $_SESSION['guest_access'] = true;
} elseif ($isLoggedIn && !empty($_SESSION['guest_access'])) {
    unset($_SESSION['guest_access']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Paper & Pixels</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Google Fonts: Inter and GT Super Display Light -->
    <?php if (!empty($page_extra_head)) {
        echo $page_extra_head;
    } ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link href="https://db.onlinewebfonts.com/c/685863138475bd81c7e5068082244fc5?family=GT+Super+Display+Light" rel="stylesheet" type="text/css" />
    <link href="https://db.onlinewebfonts.com/c/014bb250446c9521bf247d2c6266d23c?family=GT+Super+Text+Book" rel="stylesheet" type="text/css" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.tailwindcss.com?plugins=typography,line-clamp"></script>

    <link rel="stylesheet" href="/php_blog/assets/css/style.css" />

    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream: '#FAF6E9', // Primary background
                        sand: '#ECE8D9', // Header and section backgrounds
                        linen: '#FFFDF6', // Card background
                        charcoal: '#494949', // Text and icon color
                    },
                    fontFamily: {
                        heading: ['"GT Super Display Light"', 'serif'],
                        sans: ['Inter', 'sans-serif'],
                        text: ['"GT Super Text Book"', 'serif'],
                    },
                    boxShadow: {
                        soft: '0 4px 18px rgba(73, 73, 73, 0.08)',
                        hover: '0 10px 28px rgba(73, 73, 73, 0.12)',
                        inner: 'inset 0 1px 0 rgba(255,255,255,0.3)',
                    },
                    borderRadius: {
                        xl: '1rem',
                        '2xl': '1.5rem',
                    },
                },
            },
        }
    </script>
</head>

<body class="min-h-screen bg-[radial-gradient(circle_at_10%_20%,_rgba(255,255,255,0.95)_0%,_rgba(250,246,233,0.75)_45%,_rgba(244,237,213,0.6)_90%)] text-charcoal font-sans antialiased scroll-smooth flex flex-col">

    <?php if (!$isLoggedIn && !$isAuthPage): ?>
        <div class="fixed bottom-4 left-4 right-4 z-50 flex justify-center md:bottom-6 md:left-auto md:right-6 md:max-w-sm md:justify-end">
            <div class="flex w-full flex-col gap-3 rounded-3xl border border-charcoal/12 bg-linen/95 px-6 py-5 text-sm text-charcoal/80 shadow-soft backdrop-blur">
                <div>
                    <p class="text-[0.7rem] font-semibold uppercase tracking-[0.28em] text-charcoal/60">Guest preview</p>
                    <p class="mt-1 text-sm leading-relaxed text-charcoal/80">You're viewing Paper & Pixels with limited tools. Sign in to publish posts, save drafts, and show appreciation with likes.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="/php_blog/login.php" class="btn-major inline-flex flex-1 items-center justify-center rounded-full px-4 py-2 text-[0.75rem] font-semibold">Log in</a>
                    <a href="/php_blog/register.php" class="inline-flex flex-1 items-center justify-center rounded-full border border-charcoal/20 bg-transparent px-4 py-2 text-[0.75rem] font-semibold text-charcoal transition hover:border-charcoal/60">Create account</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <header class="sticky top-0 z-50 border-b border-cream/50 bg-[radial-gradient(circle_at_10%_20%,_rgba(255,255,255,0.95)_0%,_rgba(250,246,233,0.75)_45%,_rgba(244,237,213,0.6)_90%)] backdrop-blur-sm shadow-soft">
        <?php include 'navbar.php'; ?>
    </header>

    <main class="flex-1">