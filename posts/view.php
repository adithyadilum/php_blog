<?php
include '../includes/header.php';
include '../config.php';
include '../includes/Parsedown.php';
$Parsedown = new Parsedown();

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

    <article class="prose lg:prose-lg mx-auto">
        <?php echo $Parsedown->text($post['content']); ?>
    </article>

    <?php
    // count likes
    $count = $conn->prepare("SELECT COUNT(*) AS total FROM likes WHERE post_id=?");
    $count->bind_param("i", $post_id);
    $count->execute();
    $resultCount = $count->get_result();
    $countRow = $resultCount->fetch_assoc();
    $totalLikes = isset($countRow['total']) ? (int) $countRow['total'] : 0;
    $resultCount->free();
    $count->close();

    // check if already liked
    $userLiked = false;
    if (isset($_SESSION['user_id'])) {
        $likeCheck = $conn->prepare("SELECT id FROM likes WHERE post_id=? AND user_id=?");
        $likeCheck->bind_param("ii", $post_id, $_SESSION['user_id']);
        $likeCheck->execute();
        $likeCheck->store_result();
        $userLiked = $likeCheck->num_rows > 0;
        $likeCheck->free_result();
        $likeCheck->close();
    }
    ?>
    <div class="likes mt-6 flex items-center gap-4">
        <?php if (isset($_SESSION['user_id'])): ?>
            <button type="button"
                id="like-btn"
                data-post="<?php echo $post_id; ?>"
                class="inline-flex items-center gap-2 px-4 py-2 border-2 rounded-full text-sm font-semibold transition-all duration-300 <?php echo $userLiked ? 'bg-red-50 border-red-400 text-red-600' : 'bg-gray-100 border-gray-300 text-gray-700 hover:bg-red-50 hover:border-red-400'; ?>">
                <span class="text-lg">❤️</span>
                <span class="like-label"><?php echo $userLiked ? 'Unlike' : 'Like'; ?></span>
            </button>
        <?php else: ?>
            <p><a href="../login.php" class="text-primary hover:underline">Login to like this post</a></p>
        <?php endif; ?>

        <p id="like-count" class="text-gray-600">Likes: <span><?php echo $totalLikes; ?></span></p>
    </div>
    <p><a href="../index.php">Back to home</a></p>
</div>
<hr>
<h3>Comments</h3>
<?php
// Handle new commnent
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<p>Please <a href='../login.php'>login</a> to comment.</p>";
    } else {
        $comment = trim($_POST['comment']);
        $user_id = $_SESSION['user_id'];

        if (!empty($comment)) {
            $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $post_id, $user_id, $comment);
            $stmt->execute();
        }
    }
}
// Fetch comments
$comments = $conn->prepare("
  SELECT comments.*, users.username 
  FROM comments 
  JOIN users ON comments.user_id = users.id
  WHERE comments.post_id = ?
  ORDER BY comments.created_at DESC
");
$comments->bind_param("i", $post_id);
$comments->execute();
$result_comments = $comments->get_result();
?>

<?php if (isset($_SESSION['user_id'])): ?>
    <form method="post">
        <textarea name="comment" rows="3" cols="70" placeholder="Write your comment..." required></textarea><br>
        <button type="submit">Post Comment</button>
    </form>
<?php else: ?>
    <p><a href="../login.php">Login to post a comment</a></p>
<?php endif; ?>

<div class="comments">
    <?php if ($result_comments && $result_comments->num_rows > 0): ?>
        <div class="mt-10">
            <h3 class="text-xl font-semibold mb-4">Comments</h3>
            <?php while ($c = $result_comments->fetch_assoc()): ?>
                <div class="bg-gray-100 rounded-lg p-4 mb-3">
                    <p class="text-sm text-gray-600 mb-1">
                        <strong class="text-primary"><?php echo htmlspecialchars($c['username']); ?></strong>
                        <span class="text-gray-500">— <?php echo date("M d, Y H:i", strtotime($c['created_at'])); ?></span>
                    </p>
                    <p><?php echo nl2br(htmlspecialchars($c['content'])); ?></p>
                </div>
            <?php endwhile; ?>
        </div>

    <?php else: ?>
        <p>No comments yet.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>