<?php
// ✅ Always start session first, before any HTML
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link href="https://db.onlinewebfonts.com/c/685863138475bd81c7e5068082244fc5?family=GT+Super+Display+Light" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#facc15',
                        dark: '#111827',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['GT Super Display Light', 'sans-serif'],
                    },
                },
            },
        };
    </script>
</head>

<body class="bg-gray-50 text-gray-800">
    <header class="bg-white shadow-md sticky top-0 z-10">
        <?php include 'navbar.php'; ?>
    </header>

    <main class="max-w-6xl mx-auto p-6">