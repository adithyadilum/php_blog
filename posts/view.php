<?php
include '../includes/header.php';
include '../config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>Invalid post ID.</p>";
    include '../includes/footer.php';
    exit;
}

$post_id = (int)$_GET['id'];

// Fetch post details with author information
$stmt = $conn->prepare(
    "SELECT posts.*, users.username
    FROM posts
    JOIN users ON posts.user_id = users.id
    WHERE posts.id = ?"
);

$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>Post not found.</p>";
    include '../includes/footer.php';
    exit;
}

$post = $result->fetch_assoc();
?>

<div class="single-post">
    <?php if (!empty($post['cover_image'])): ?>
        <img src="../uploads/<?php echo htmlspecialchars($post['cover_image']); ?>" alt="" class="cover-image">
    <?php endif; ?>
    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
    <p class="meta">
        By <strong><?php echo htmlspecialchars($post['username']); ?></strong>
        on <?php echo date("M d, Y h:i A", strtotime($post['created_at'])); ?>
    </p>
    <?php if (!empty($post['tags'])): ?>
        <p class="tags">Tags: <?php echo htmlspecialchars($post['tags']); ?></p>
    <?php endif; ?>
    <div class="content">
        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
    </div>
    <p><a href="../index.php">Back to home</a></p>
</div>
<?php include '../includes/footer.php'; ?>