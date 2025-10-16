<?php
include 'includes/header.php';
include 'config.php';

// Show success messages
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'created') {
        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg mb-6 flex items-center gap-2">
                <span class="text-2xl">🎉</span>
                <span class="font-medium">Post created successfully!</span>
              </div>';
    } elseif ($_GET['msg'] == 'updated') {
        echo '<div class="bg-blue-100 border border-blue-400 text-blue-700 px-6 py-4 rounded-lg mb-6 flex items-center gap-2">
                <span class="text-2xl">✅</span>
                <span class="font-medium">Post updated successfully!</span>
              </div>';
    } elseif ($_GET['msg'] == 'deleted') {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg mb-6 flex items-center gap-2">
                <span class="text-2xl">🗑️</span>
                <span class="font-medium">Post deleted successfully!</span>
              </div>';
    }
}

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

<h2 class="text-2xl font-heading">Latest Blog Posts</h2>

<div class="posts">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()):
            // Get like count for this post
            $like_stmt = $conn->prepare("SELECT COUNT(*) as count FROM likes WHERE post_id = ?");
            $like_stmt->bind_param("i", $row['id']);
            $like_stmt->execute();
            $like_result = $like_stmt->get_result();
            $like_count = $like_result->fetch_assoc()['count'];

            // Check if current user liked this post
            $user_liked = false;
            if (isset($_SESSION['user_id'])) {
                $check_stmt = $conn->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
                $check_stmt->bind_param("ii", $row['id'], $_SESSION['user_id']);
                $check_stmt->execute();
                $user_liked = $check_stmt->get_result()->num_rows > 0;
            }
        ?>
            <div class="post-card bg-white shadow rounded-lg p-6 mb-6 hover:shadow-lg transition">
                <?php if (!empty($row['cover_image'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($row['cover_image']); ?>" class="rounded mb-4 w-full" alt="<?php echo htmlspecialchars($row['title']); ?>">
                <?php endif; ?>

                <h2 class="text-2xl font-semibold mb-2 text-primary">
                    <a href="posts/view.php?id=<?php echo $row['id']; ?>" class="hover:underline">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </a>
                </h2>

                <p class="text-gray-600 text-sm mb-3">
                    by <strong><?php echo htmlspecialchars($row['username']); ?></strong>
                    • <?php echo date("M d, Y", strtotime($row['created_at'])); ?>
                </p>

                <?php if (!empty($row['tags'])): ?>
                    <div class="flex gap-2 mb-3">
                        <?php foreach (explode(',', $row['tags']) as $tag): ?>
                            <a href="?tag=<?php echo urlencode(trim($tag)); ?>"
                                class="text-xs bg-gray-200 px-2 py-1 rounded hover:bg-primary hover:text-white transition">
                                <?php echo htmlspecialchars(trim($tag)); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <p class="text-gray-700 mb-4">
                    <?php echo substr(strip_tags($row['content']), 0, 200); ?>...
                    <a href="posts/view.php?id=<?php echo $row['id']; ?>" class="text-primary hover:underline">Read more</a>
                </p>

                <!-- Post Actions -->
                <div class="flex items-center gap-4 pt-3 mt-3 border-t border-gray-200">
                    <!-- Like Button -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button class="like-btn inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-red-50 border-2 border-gray-300 hover:border-red-400 rounded-full text-sm font-semibold transition-all duration-300 hover:scale-105 <?php echo $user_liked ? 'bg-red-50 border-red-400 text-red-600' : 'text-gray-700'; ?>"
                            data-post-id="<?php echo $row['id']; ?>"
                            onclick="toggleLike(<?php echo $row['id']; ?>)">
                            <span class="text-lg">❤️</span>
                            <span class="like-count"><?php echo $like_count; ?></span>
                        </button>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 border-2 border-gray-300 rounded-full text-sm font-semibold text-gray-600 opacity-70">
                            <span class="text-lg">❤️</span>
                            <span class="like-count"><?php echo $like_count; ?></span>
                        </span>
                    <?php endif; ?>

                    <!-- Edit/Delete Buttons -->
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']): ?>
                        <a href="posts/edit.php?id=<?php echo $row['id']; ?>"
                            class="inline-flex items-center gap-1 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors duration-200">
                            ✏️ Edit
                        </a>
                        <a href="posts/delete.php?id=<?php echo $row['id']; ?>"
                            class="inline-flex items-center gap-1 px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-medium transition-colors duration-200"
                            onclick="return confirm('Are you sure you want to delete this post?');">
                            🗑️ Delete
                        </a>
                    <?php endif; ?>
                </div>
            </div>
</div>
<?php endwhile; ?>
<?php else: ?>
    <p>No posts yet. <a href="posts/create.php">Create your first one</a>!</p>
<?php endif; ?>
</div>

<script>
    function toggleLike(postId) {
        const likeBtn = document.querySelector(`.like-btn[data-post-id="${postId}"]`);
        const likeCount = likeBtn.querySelector('.like-count');

        // Disable button during request
        likeBtn.disabled = true;
        likeBtn.style.opacity = '0.6';

        fetch('api/toggle_like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update like count
                    likeCount.textContent = data.count;

                    // Toggle Tailwind classes
                    if (data.action === 'liked') {
                        // Add liked styling
                        likeBtn.classList.add('bg-red-50', 'border-red-400', 'text-red-600');
                        likeBtn.classList.remove('bg-gray-100', 'border-gray-300', 'text-gray-700');
                    } else {
                        // Remove liked styling
                        likeBtn.classList.remove('bg-red-50', 'border-red-400', 'text-red-600');
                        likeBtn.classList.add('bg-gray-100', 'border-gray-300', 'text-gray-700');
                    }
                } else {
                    alert(data.message || 'Failed to update like');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            })
            .finally(() => {
                // Re-enable button
                likeBtn.disabled = false;
                likeBtn.style.opacity = '1';
            });
    }
</script>

<?php include 'includes/footer.php'; ?>