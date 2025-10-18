<?php
// Always start session first, before any HTML
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>PHP Blog</title>
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

<body class="min-h-screen bg-[radial-gradient(circle_at_10%_20%,_rgba(255,255,255,0.95)_0%,_rgba(250,246,233,0.75)_45%,_rgba(244,237,213,0.6)_90%)] text-charcoal font-sans antialiased scroll-smooth">
    <header class="sticky top-0 z-50 border-b border-cream/50 bg-[radial-gradient(circle_at_10%_20%,_rgba(255,255,255,0.95)_0%,_rgba(250,246,233,0.75)_45%,_rgba(244,237,213,0.6)_90%)] backdrop-blur-sm shadow-soft">
        <?php include 'navbar.php'; ?>
    </header>

    <main class="flex-1">