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
    <?php
    // count likes
    $count = $conn->prepare("SELECT COUNT(*) AS total FROM likes WHERE post_id=?");
    $count->bind_param("i", $post_id);
    $count->execute();
    $resultCount = $count->get_result();
    $totalLikes = $resultCount->fetch_assoc()['total'] ?? 0;

    // check if already liked
    $userLiked = false;
    if (isset($_SESSION['user_id'])) {
        $likeCheck = $conn->prepare("SELECT id FROM likes WHERE post_id=? AND user_id=?");
        $likeCheck->bind_param("ii", $post_id, $_SESSION['user_id']);
        $likeCheck->execute();
        $likeCheck->store_result();
        $userLiked = $likeCheck->num_rows > 0;
    }
    ?>
    <div class="likes">
        <form method="GET" action="like.php">
            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
            <?php if (isset($_SESSION['user_id'])): ?>
                <button type="button" id="like-btn" data-post="<?php echo $post_id; ?>">
                    <?php echo $userLiked ? '❤️ Unlike' : '🤍 Like'; ?>
                </button>
            <?php else: ?>
                <p><a href="../login.php">Login to like this post</a></p>
            <?php endif; ?>
        </form>
        <p id="like-count">Likes: <?php echo $totalLikes; ?></p>
    </div>
    <p><a href="../index.php">Back to home</a></p>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const likeBtn = document.getElementById('like-btn');
        if (!likeBtn) return;

        likeBtn.addEventListener('click', () => {
            const postId = likeBtn.getAttribute('data-post');
            fetch(`like.php?post_id=${postId}`)
                .then(res => res.text())
                .then(data => {
                    if (data === "liked") {
                        likeBtn.textContent = "❤️ Unlike";
                    } else {
                        likeBtn.textContent = "🤍 Like";
                    }
                    // Refresh like count
                    fetch(`../api/like_count.php?post_id=${postId}`)
                        .then(r => r.text())
                        .then(count => {
                            document.getElementById('like-count').textContent = "Likes: " + count;
                        });
                });
        });
    });
</script>

<?php include '../includes/footer.php'; ?>