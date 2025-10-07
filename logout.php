<?php
session_start();
// Unset all of the session variables

session_unset();
session_destroy();

setcookie(session_name(), '', time() - 3600, '/');

header("Location: login.php");
exit;
