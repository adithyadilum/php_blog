<?php
include '../includes/auth_check.php';
include '../includes/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$post_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';
$isAdmin = ($user_role === 'admin');

// Get post owner
$stmt = $conn->prepare("SELECT user_id FROM posts WHERE id=?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->bind_result($owner_id);
if (!$stmt->fetch()) {
    $stmt->close();
    header("Location: ../index.php");
    exit;
}
$stmt->close();

// Access control
if (!$isAdmin && $user_id != $owner_id) {
    die("Unauthorized: You don't have permission to delete this post.");
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
    header("Location: ../index.php?msg=deleted");
    exit;
} else {
    echo "Error: You are not authorized to delete this post or it does not exist.";
}
$stmt->close();
