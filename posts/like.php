<?php
include '../includes/auth_check.php';
require_once __DIR__ . '/../config.php';

if (!isset($_GET['post_id']) || !is_numeric($_GET['post_id'])) {
    exit('Invalid post ID.');
}

$post_id = (int) $_GET['post_id'];
$user_id = $_SESSION['user_id'];

// Check if the user has already liked the post
$check = $conn->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
$check->bind_param("ii", $post_id, $user_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // unlike the post
    $stmt = $conn->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $post_id, $user_id);
    if ($stmt->execute()) {
        echo 'unliked';
    } else {
        echo 'error';
    }
} else {
    // like the post
    $stmt = $conn->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $post_id, $user_id);
    if ($stmt->execute()) {
        echo 'liked';
    } else {
        echo 'error';
    }
}
