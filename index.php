<?php
include 'includes/header.php';
include 'config.php';

$tagFilter = isset($_GET['tag']) ? trim($_GET['tag']) : null;

if ($tagFilter) {
    $stmt = $conn->prepare("
        SELECT posts.*, users.username
        FROM posts
        JOIN users ON posts.user_id = users.id
        WHERE posts.tags LIKE ?
        ORDER BY posts.created_at DESC
    ");
    $likeParam = '%' . $tagFilter . '%';
    $stmt->bind_param("s", $likeParam);
    $stmt->execute();
    $result = $stmt->get_result();
    echo "<h3>Showing posts tagged with: <em>$tagFilter</em></h3>";
} else {
    $result = $conn->query("
        SELECT posts.*, users.username 
        FROM posts 
        JOIN users ON posts.user_id = users.id 
        ORDER BY posts.created_at DESC
    ");
}

?>

<h2>Latest Blog Posts</h2>

<div class="posts">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="post-card">
                <?php if (!empty($row['cover_image'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($row['cover_image']); ?>" alt="Cover Image">
                <?php endif; ?>

                <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                <p><small>By <?php echo htmlspecialchars($row['username']); ?> on <?php echo date("M d, Y", strtotime($row['created_at'])); ?></small></p>

                <?php if (!empty($row['tags'])): ?>
                    <p class="tags">
                        Tags:
                        <?php
                        $tags = explode(',', $row['tags']);
                        foreach ($tags as $tag) {
                            $tag = trim($tag);
                            echo "<a href='index.php?tag=" . urlencode($tag) . "'>$tag</a> ";
                        }
                        ?>
                    </p>
                <?php endif; ?>

                <p>
                    <?php echo nl2br(substr(htmlspecialchars($row['content']), 0, 150)); ?>...
                    <a href="posts/view.php?id=<?php echo $row['id']; ?>">Read more</a>
                </p>

                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']): ?>
                    <p>
                        <a href="posts/edit.php?id=<?php echo $row['id']; ?>">Edit</a> |
                        <a href="posts/delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                    </p>
                <?php endif; ?>

            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No posts yet. <a href="posts/create.php">Create your first one</a>!</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>