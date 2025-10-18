<?php
include '../includes/header.php';
include '../includes/config.php';
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

<section class="px-6 py-16">
    <div class="mx-auto flex max-w-5xl flex-col gap-10">
        <div class="space-y-6 text-center">
            <?php if (!empty($post['cover_image'])): ?>
                <div class="overflow-hidden rounded-3xl">
                    <img src="../uploads/<?php echo htmlspecialchars($post['cover_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="h-96 w-full object-cover" />
                </div>
            <?php endif; ?>

            <div class="space-y-4">
                <p class="uppercase tracking-[0.4em] text-xs text-charcoal/60">Paper & Pixels Feature</p>
                <h1 class="font-heading text-4xl md:text-5xl text-charcoal"><?php echo htmlspecialchars($post['title']); ?></h1>
                <p class="text-sm uppercase tracking-[0.32em] text-charcoal/60">
                    by <span class="font-semibold text-charcoal"><?php echo htmlspecialchars($post['username']); ?></span>
                    · <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                </p>

                <?php if (!empty($post['tags'])): ?>
                    <div class="mt-3 flex flex-wrap justify-center gap-2">
                        <?php foreach (array_map('trim', explode(',', $post['tags'])) as $tag):
                            if ($tag === '') {
                                continue;
                            }
                        ?>
                            <a href="../index.php?tag=<?php echo urlencode($tag); ?>" class="text-xs uppercase tracking-[0.25em] rounded-full border border-charcoal/15 bg-linen/70 px-4 py-2 text-charcoal/80 transition hover:border-charcoal/40 hover:text-charcoal">
                                #<?php echo htmlspecialchars(ltrim($tag, '#')); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <article class="post-body prose prose-slate md:prose-lg font-text prose-h1:text-4xl prose-h2:text-3xl prose-h3:text-2xl prose-headings:font-heading prose-headings:text-charcoal prose-p:text-charcoal prose-strong:text-charcoal prose-a:text-charcoal hover:prose-a:text-charcoal/80 prose-blockquote:border-l-4 prose-blockquote:border-charcoal/20 prose-blockquote:bg-linen/30 prose-blockquote:text-charcoal prose-blockquote:pl-6 prose-blockquote:py-4 mx-auto w-full">
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

        <?php
        $isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id'];
        $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        ?>

        <div class="mt-10 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button type="button"
                        class="like-btn inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium transition <?php echo $userLiked ? 'bg-charcoal text-linen border-charcoal' : 'bg-sand/60 text-charcoal border-sand/80 hover:bg-charcoal hover:text-linen'; ?>"
                        data-post-id="<?php echo $post_id; ?>"
                        onclick="toggleLike(<?php echo $post_id; ?>)">
                        <svg aria-hidden="true" class="h-4 w-4 fill-current" viewBox="0 0 24 24">
                            <path d="M11.645 20.205l-.007-.004C5.986 15.88 2.5 12.634 2.5 8.75 2.5 6.126 4.57 4 7.25 4a4.5 4.5 0 0 1 3.75 1.97A4.5 4.5 0 0 1 14.75 4c2.68 0 4.75 2.126 4.75 4.75 0 3.883-3.486 7.13-9.138 11.456l-.007.004-.005.004a.75.75 0 0 1-.894 0l-.005-.004Z" fill="currentColor" />
                        </svg>
                        <span class="like-count"><?php echo $totalLikes; ?></span>
                        <span class="sr-only"><?php echo $userLiked ? 'Unlike this story' : 'Like this story'; ?></span>
                    </button>
                <?php else: ?>
                    <span class="inline-flex items-center gap-2 rounded-full border border-sand/80 bg-sand/50 px-4 py-2 text-sm font-medium text-charcoal/60">
                        <svg aria-hidden="true" class="h-4 w-4 fill-current" viewBox="0 0 24 24">
                            <path d="M11.645 20.205l-.007-.004C5.986 15.88 2.5 12.634 2.5 8.75 2.5 6.126 4.57 4 7.25 4a4.5 4.5 0 0 1 3.75 1.97A4.5 4.5 0 0 1 14.75 4c2.68 0 4.75 2.126 4.75 4.75 0 3.883-3.486 7.13-9.138 11.456l-.007.004-.005.004a.75.75 0 0 1-.894 0l-.005-.004Z" fill="currentColor" />
                        </svg>
                        <span class="like-count"><?php echo $totalLikes; ?></span>
                    </span>
                <?php endif; ?>

                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="../login.php" class="text-xs uppercase tracking-[0.25em] text-charcoal/70 hover:text-charcoal">Login to like</a>
                <?php endif; ?>
            </div>

            <div class="flex flex-wrap items-center gap-4 text-xs uppercase tracking-[0.25em]">
                <a href="../index.php" class="text-charcoal hover:underline">Back to home</a>
                <?php if ($isOwner || $isAdmin): ?>
                    <a href="edit.php?id=<?php echo $post_id; ?>" class="text-charcoal/80 hover:text-charcoal transition">Edit story</a>
                    <a href="delete.php?id=<?php echo $post_id; ?>" class="text-red-500 hover:text-red-600 transition" onclick="return confirm('Are you sure you want to delete this post?');">Delete story</a>
                <?php endif; ?>
            </div>
        </div>

        <?php
        // Handle new comment
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
            if (!isset($_SESSION['user_id'])) {
                echo "<p class='text-center text-sm text-red-500'>Please <a class=\"underline\" href='../login.php'>login</a> to comment.</p>";
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

        <div class="rounded-3xl border border-charcoal/10 bg-white/40 px-6 py-8">
            <div class="flex flex-col gap-6">
                <h3 class="text-center font-heading text-2xl text-charcoal">Join the discussion</h3>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="post" class="space-y-4">
                        <label class="sr-only" for="comment">Write your comment</label>
                        <textarea id="comment" name="comment" rows="4" placeholder="Share your thoughts..." required data-autoresize="true" class="w-full rounded-2xl border border-charcoal/15 bg-white/70 px-4 py-3 text-charcoal placeholder:text-charcoal/40 focus:border-charcoal/40 focus:bg-white focus:outline-none focus:ring-0 transition"></textarea>
                        <button type="submit" class="btn-major inline-flex items-center justify-center rounded-full px-6 py-3 text-xs font-semibold uppercase tracking-[0.3em]">Post Comment</button>
                    </form>
                <?php else: ?>
                    <p class="text-center text-sm uppercase tracking-[0.25em] text-charcoal/70">Please <a href="../login.php" class="text-charcoal hover:underline">login</a> to comment.</p>
                <?php endif; ?>

                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs uppercase tracking-[0.3em] text-charcoal/70">Comments</h4>
                        <span class="rounded-full bg-charcoal/10 px-3 py-1 text-xs uppercase tracking-[0.2em] text-charcoal/70"><?php echo $result_comments ? $result_comments->num_rows : 0; ?> total</span>
                    </div>

                    <div class="space-y-4">
                        <?php if ($result_comments && $result_comments->num_rows > 0): ?>
                            <?php while ($c = $result_comments->fetch_assoc()): ?>
                                <div class="rounded-2xl border border-charcoal/10 bg-white/40 px-5 py-4">
                                    <p class="text-xs uppercase tracking-[0.25em] text-charcoal/60 mb-2">
                                        <strong class="text-charcoal"><?php echo htmlspecialchars($c['username']); ?></strong>
                                        <span class="text-charcoal/50"> · <?php echo date('M d, Y H:i', strtotime($c['created_at'])); ?></span>
                                    </p>
                                    <p class="text-sm text-charcoal/80 leading-relaxed"><?php echo nl2br(htmlspecialchars($c['content'])); ?></p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-center text-sm text-charcoal/60">No comments yet. Be the first to start a thread.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>