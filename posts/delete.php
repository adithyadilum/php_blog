<?php
include '../includes/auth_check.php';
include '../config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$post_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify ownership before deletion
$stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    header("Location: ../index.php?msg=deleted");
    exit;
} else {
    echo "Error: You are not authorized to delete this post or it does not exist.";
}
