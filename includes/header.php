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

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>

    <!-- Custom Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream: '#FAF6E9', // Primary background  
                        sand: '#ECE8D9', // Card background 
                        linen: '#FFFDF6', // Header and section backgrounds  
                        charcoal: '#494949', // Text and icon color  
                    },
                    fontFamily: {
                        heading: ['"GT Super Display Light"', 'serif'],
                        sans: ['Inter', 'sans-serif'],
                    },
                    boxShadow: {
                        soft: '0 4px 12px rgba(0, 0, 0, 0.04)',
                        hover: '0 6px 20px rgba(0, 0, 0, 0.08)',
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

<body class="bg-gray-50 text-gray-800">
    <header class="bg-linen sticky top-0 z-10">
        <?php include 'navbar.php'; ?>
    </header>

    <main class="max-w-6xl mx-auto p-6">