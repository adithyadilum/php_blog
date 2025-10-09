<?php
include 'includes/header.php';
include 'config.php';

// Fetch all posts (newest first)
$query = "SELECT posts.*, users.username 
          FROM posts 
          JOIN users ON posts.user_id = users.id 
          ORDER BY posts.created_at DESC";
$result = $conn->query($query);
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
                    <p class="tags">Tags: <?php echo htmlspecialchars($row['tags']); ?></p>
                <?php endif; ?>

                <p>
                    <?php echo nl2br(substr(htmlspecialchars($row['content']), 0, 150)); ?>...
                    <a href="posts/view.php?id=<?php echo $row['id']; ?>">Read more</a>
                </p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No posts yet. <a href="posts/create.php">Create your first one</a>!</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>