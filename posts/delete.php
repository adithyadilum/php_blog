<?php
include '../includes/auth_check.php';
require_once __DIR__ . '/../config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$post_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';
$isAdmin = ($user_role === 'admin');

// Get post owner
$stmt = $conn->prepare("SELECT user_id, cover_image FROM posts WHERE id=?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->bind_result($owner_id, $cover_image);
if (!$stmt->fetch()) {
    $stmt->close();
    header("Location: ../index.php");
    exit;
}
$stmt->close();

// Access control
if (!$isAdmin && $user_id != $owner_id) {
    $_SESSION['flash_toast'] = [
        'message' => 'Unauthorized',
        'type' => 'toast-error',
        'icon' => 'error',
    ];
    header("Location: ../index.php");
    exit;
}

// Perform deletion
if ($isAdmin) {
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
} else {
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $post_id, $user_id);
}

if ($stmt->execute() && $stmt->affected_rows > 0) {
    if (!empty($cover_image)) {
        $imagePath = dirname(__DIR__) . '/uploads/' . basename($cover_image);
        if (is_file($imagePath)) {
            @unlink($imagePath);
        }
    }
    header("Location: ../index.php?msg=deleted");
    exit;
} else {
    $_SESSION['flash_toast'] = [
        'message' => 'Unauthorized',
        'type' => 'toast-error',
        'icon' => 'error',
    ];
    header("Location: ../index.php");
    exit;
}
$stmt->close();
