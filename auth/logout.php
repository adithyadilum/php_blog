<?php
session_start();
// Unset all of the session variables

session_unset();
session_destroy();

setcookie(session_name(), '', time() - 3600, '/');

$documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? __DIR__ . '/..'));
$projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$relativeRoot = trim(str_replace($documentRoot, '', $projectRoot), '/');
$loginPath = '/' . ($relativeRoot !== '' ? $relativeRoot . '/' : '') . 'auth/login.php';

header("Location: {$loginPath}");
exit;
