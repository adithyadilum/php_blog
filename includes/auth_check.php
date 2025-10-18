<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    $documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? __DIR__ . '/..'));
    $projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    $relativeRoot = trim(str_replace($documentRoot, '', $projectRoot), '/');
    $loginPath = '/' . ($relativeRoot !== '' ? $relativeRoot . '/' : '') . 'auth/login.php';

    header("Location: {$loginPath}");
    exit;
}
