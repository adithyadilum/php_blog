<?php
include 'includes/header.php';
include 'config.php';

$tagFilter = isset($_GET['tag']) ? trim($_GET['tag']) : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;

if ($tagFilter) {
    $stmt = $conn->prepare("
        SELECT posts.*, users.username
        FROM posts
        JOIN users ON posts.user_id = users.id
        WHERE posts.tags LIKE ?
        ORDER BY posts.created_at DESC
    ");
    $param = '%' . $tagFilter . '%';
    $stmt->bind_param("s", $param);
    $stmt->execute();
    $result = $stmt->get_result();
    echo "<h3>Showing posts tagged with: <em>$tagFilter</em></h3>";
} elseif ($search) {
    $stmt = $conn->prepare("
        SELECT posts.*, users.username
        FROM posts
        JOIN users ON posts.user_id = users.id
        WHERE posts.title LIKE ? OR posts.content LIKE ?
        ORDER BY posts.created_at DESC
    ");
    $param = '%' . $search . '%';
    $stmt->bind_param("ss", $param, $param);
    $stmt->execute();
    $result = $stmt->get_result();
    echo "<h3>Search results for: <em>$search</em></h3>";
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
            <div class="bg-white shadow rounded-lg p-6 mb-6 hover:shadow-lg transition">
                <?php if (!empty($row['cover_image'])): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($row['cover_image']); ?>" class="rounded mb-4">
                <?php endif; ?>
                <h2 class="text-2xl font-semibold mb-2 text-primary">
                    <a href="/php_blog/posts/view.php?id=<?php echo $row['id']; ?>">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </a>
                </h2>
                <p class="text-gray-600 text-sm mb-3">
                    by <strong><?php echo htmlspecialchars($row['username']); ?></strong>
                    • <?php echo date("M d, Y", strtotime($row['created_at'])); ?>
                </p>

                <div class="flex justify-between items-center">
                    <div class="flex gap-2">
                        <?php if (!empty($row['tags'])): ?>
                            <?php foreach (explode(',', $row['tags']) as $tag): ?>
                                <a href="?tag=<?php echo urlencode(trim($tag)); ?>" class="text-xs bg-gray-200 px-2 py-1 rounded hover:bg-primary hover:text-white transition"><?php echo trim($tag); ?></a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <span class="text-sm text-gray-500">❤️ <?php echo $row['likes_count'] ?? 0; ?> likes</span>
                </div>
                <p class="text-gray-700 mb-4"><?php echo substr(strip_tags($row['content']), 0, 200); ?>... <a href="posts/view.php?id=<?php echo $row['id']; ?>">Read more</a></p>

                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']): ?>
                    <p>
                        <a href="posts/edit.php?id=<?php echo $row['id']; ?>">Edit</a> |
                        <a href="posts/delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                    </p>
                <?php endif; ?>

            </div>
</div>
<?php endwhile; ?>
<?php else: ?>
    <p>No posts yet. <a href="posts/create.php">Create your first one</a>!</p>
<?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>