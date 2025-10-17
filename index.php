<?php
include 'includes/header.php';
include 'config.php';

$successNotice = '';
if (isset($_GET['msg'])) {
    $status = $_GET['msg'];
    if ($status === 'created') {
        $successNotice = '<div class="max-w-xl mx-auto flex items-center gap-3 rounded-full bg-linen/90 border border-sand/70 px-5 py-3 text-sm text-charcoal shadow-soft"><span class="text-xl">🎉</span><span class="font-medium">Post created successfully</span></div>';
    } elseif ($status === 'updated') {
        $successNotice = '<div class="max-w-xl mx-auto flex items-center gap-3 rounded-full bg-linen/90 border border-sand/70 px-5 py-3 text-sm text-charcoal shadow-soft"><span class="text-xl">✨</span><span class="font-medium">Post updated successfully</span></div>';
    } elseif ($status === 'deleted') {
        $successNotice = '<div class="max-w-xl mx-auto flex items-center gap-3 rounded-full bg-linen/90 border border-sand/70 px-5 py-3 text-sm text-charcoal shadow-soft"><span class="text-xl">🗑️</span><span class="font-medium">Post deleted successfully</span></div>';
    }
}

$tagFilter = isset($_GET['tag']) ? trim($_GET['tag']) : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;
$contextMessage = '';

$stmt = null;
if ($tagFilter) {
    $stmt = $conn->prepare('
        SELECT posts.*, users.username
        FROM posts
        JOIN users ON posts.user_id = users.id
        WHERE posts.tags LIKE ?
        ORDER BY posts.created_at DESC
    ');
    $param = '%' . $tagFilter . '%';
    $stmt->bind_param('s', $param);
    $stmt->execute();
    $result = $stmt->get_result();
    $contextMessage = 'Filtering by #' . htmlspecialchars($tagFilter, ENT_QUOTES, 'UTF-8');
} elseif ($search) {
    $stmt = $conn->prepare('
        SELECT posts.*, users.username
        FROM posts
        JOIN users ON posts.user_id = users.id
        WHERE posts.title LIKE ? OR posts.content LIKE ?
        ORDER BY posts.created_at DESC
    ');
    $param = '%' . $search . '%';
    $stmt->bind_param('ss', $param, $param);
    $stmt->execute();
    $result = $stmt->get_result();
    $contextMessage = 'Search results for "' . htmlspecialchars($search, ENT_QUOTES, 'UTF-8') . '"';
} else {
    $result = $conn->query('
        SELECT posts.*, users.username
        FROM posts
        JOIN users ON posts.user_id = users.id
        ORDER BY posts.created_at DESC
    ');
}

$posts = [];
if ($result instanceof mysqli_result) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    $result->free();
}
if ($stmt instanceof mysqli_stmt) {
    $stmt->close();
}

$availableTags = [];
foreach ($posts as $post) {
    if (!empty($post['tags'])) {
        $pieces = array_map('trim', explode(',', $post['tags']));
        foreach ($pieces as $tag) {
            if ($tag === '') {
                continue;
            }
            $availableTags[strtolower($tag)] = $tag;
        }
    }
}
ksort($availableTags);
$availableTags = array_values($availableTags);
?>

<section class="relative overflow-hidden px-6 py-16">
    <div class="pointer-events-none absolute inset-0 opacity-60" style="background: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.9) 0%, rgba(250,246,233,0.4) 45%, rgba(250,246,233,0) 75%);"></div>
    <div class="relative max-w-4xl mx-auto text-center space-y-6">
        <?php if ($successNotice): ?>
            <?php echo $successNotice; ?>
        <?php endif; ?>
        <p class="uppercase tracking-[0.45em] text-[0.65rem] text-charcoal/60">CURATED STORIES FOR MODERN CREATORS</p>
        <h1 class="font-heading text-4xl md:text-5xl text-charcoal">Ideas that bridge creativity and technology</h1>
        <p class="font-sans text-base md:text-lg text-charcoal/70 max-w-2xl mx-auto">Explore stories at the intersection of art, code, and design — where imagination meets logic, and pixels tell as many stories as paper ever did.</p>
        <a href="#stories" class="mx-auto inline-flex items-center gap-3 rounded-full border border-charcoal/20 bg-linen px-6 py-3 text-xs md:text-sm uppercase tracking-[0.35em] text-charcoal hover:border-charcoal/40 hover:bg-charcoal hover:text-linen transition">
            Drift Into Stories
            <span aria-hidden="true" class="text-base">↓</span>
        </a>
        <?php if ($contextMessage): ?>
            <p class="text-xs uppercase tracking-[0.4em] text-charcoal/60"><?php echo $contextMessage; ?></p>
        <?php endif; ?>
    </div>
</section>

<section id="stories" class="px-6 pb-16">
    <div class="max-w-6xl mx-auto space-y-10 bg-linen/60 backdrop-blur-sm rounded-3xl px-6 py-10 shadow-soft">
        <div class="flex flex-col gap-6">
            <div class="flex flex-wrap justify-center md:justify-start gap-3">
                <?php
                $isAllActive = empty($tagFilter) && empty($search);
                $pillBase = 'px-4 py-2 rounded-full border text-xs uppercase tracking-[0.28em] transition';
                ?>
                <a href="/php_blog/index.php" class="<?php echo $pillBase . ' ' . ($isAllActive ? 'bg-charcoal text-linen border-charcoal' : 'bg-linen border-sand/70 text-charcoal hover:border-charcoal/50 hover:text-charcoal/80'); ?>">All</a>
                <?php foreach ($availableTags as $tag):
                    $isActive = $tagFilter && strcasecmp($tagFilter, $tag) === 0;
                    $href = '/php_blog/index.php?tag=' . urlencode($tag);
                    $classes = $pillBase . ' ' . ($isActive ? 'bg-charcoal text-linen border-charcoal' : 'bg-linen border-sand/70 text-charcoal hover:border-charcoal/50 hover:text-charcoal/80');
                ?>
                    <a href="<?php echo $href; ?>" class="<?php echo $classes; ?>">#<?php echo htmlspecialchars(ltrim($tag, '#'), ENT_QUOTES, 'UTF-8'); ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!empty($posts)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($posts as $post):
                    $postId = (int) $post['id'];

                    $like_stmt = $conn->prepare('SELECT COUNT(*) as count FROM likes WHERE post_id = ?');
                    $like_stmt->bind_param('i', $postId);
                    $like_stmt->execute();
                    $like_result = $like_stmt->get_result();
                    $like_row = $like_result->fetch_assoc();
                    $like_count = isset($like_row['count']) ? (int) $like_row['count'] : 0;
                    $like_result->free();
                    $like_stmt->close();

                    $user_liked = false;
                    if (isset($_SESSION['user_id'])) {
                        $check_stmt = $conn->prepare('SELECT id FROM likes WHERE post_id = ? AND user_id = ?');
                        $check_stmt->bind_param('ii', $postId, $_SESSION['user_id']);
                        $check_stmt->execute();
                        $check_stmt->store_result();
                        $user_liked = $check_stmt->num_rows > 0;
                        $check_stmt->free_result();
                        $check_stmt->close();
                    }

                    $rawExcerpt = trim(strip_tags($post['content']));
                    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                        $excerpt = mb_strlen($rawExcerpt) > 180 ? mb_substr($rawExcerpt, 0, 180) . '…' : $rawExcerpt;
                    } else {
                        $excerpt = strlen($rawExcerpt) > 180 ? substr($rawExcerpt, 0, 180) . '…' : $rawExcerpt;
                    }
                    $excerpt = htmlspecialchars($excerpt, ENT_QUOTES, 'UTF-8');

                    $tagPills = [];
                    if (!empty($post['tags'])) {
                        foreach (array_map('trim', explode(',', $post['tags'])) as $tag) {
                            if ($tag === '') {
                                continue;
                            }
                            $tagPills[] = $tag;
                        }
                    }
                ?>
                    <article class="flex flex-col overflow-hidden rounded-2xl bg-linen shadow-soft transition-all duration-300 hover:shadow-hover">
                        <?php if (!empty($post['cover_image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($post['cover_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>" class="h-56 w-full object-cover" />
                        <?php else: ?>
                            <div class="h-56 w-full bg-gradient-to-br from-sand via-cream to-linen flex items-center justify-center text-xs uppercase tracking-[0.3em] text-charcoal/40">Awaiting cover</div>
                        <?php endif; ?>

                        <div class="flex flex-1 flex-col px-6 pb-6 pt-6">
                            <div class="text-xs uppercase tracking-[0.3em] text-charcoal/50">
                                <?php echo htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                            </div>
                            <h2 class="mt-4 font-heading text-2xl text-charcoal">
                                <a href="posts/view.php?id=<?php echo $postId; ?>" class="hover:opacity-80 transition">
                                    <?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </h2>
                            <p class="mt-4 text-sm md:text-base text-[#6b6b6b] leading-relaxed line-clamp-3">
                                <?php echo $excerpt; ?>
                            </p>

                            <?php if (!empty($tagPills)): ?>
                                <div class="mt-5 flex flex-wrap gap-2">
                                    <?php foreach ($tagPills as $tag): ?>
                                        <a href="/php_blog/index.php?tag=<?php echo urlencode($tag); ?>" class="text-xs bg-sand text-charcoal rounded-full px-3 py-1 tracking-[0.15em] uppercase">
                                            #<?php echo htmlspecialchars(ltrim($tag, '#'), ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="mt-auto pt-6 flex items-center justify-between border-t border-sand/70">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <button class="like-btn inline-flex items-center gap-2 rounded-full border border-sand/80 px-4 py-2 text-sm font-medium transition <?php echo $user_liked ? 'bg-charcoal text-linen border-charcoal' : 'bg-sand/60 text-charcoal hover:bg-charcoal hover:text-linen'; ?>" data-post-id="<?php echo $postId; ?>" onclick="toggleLike(<?php echo $postId; ?>)">
                                        <span>❤</span>
                                        <span class="like-count"><?php echo $like_count; ?></span>
                                    </button>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-2 rounded-full border border-sand/80 bg-sand/50 px-4 py-2 text-sm font-medium text-charcoal/60">
                                        <span>❤</span>
                                        <span class="like-count"><?php echo $like_count; ?></span>
                                    </span>
                                <?php endif; ?>

                                <div class="flex items-center gap-3">
                                    <?php
                                    $isOwner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id'];
                                    $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
                                    if ($isOwner || $isAdmin):
                                    ?>
                                        <a href="posts/edit.php?id=<?php echo $postId; ?>" class="text-xs uppercase tracking-[0.25em] text-charcoal/80 hover:text-charcoal transition">Edit</a>
                                        <a href="posts/delete.php?id=<?php echo $postId; ?>" class="text-xs uppercase tracking-[0.25em] text-charcoal/80 hover:text-red-500 transition" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
                                    <?php endif; ?>
                                    <a href="posts/view.php?id=<?php echo $postId; ?>" class="inline-flex items-center gap-2 text-xs uppercase tracking-[0.25em] text-charcoal hover:opacity-70 transition">
                                        Read story
                                        <span aria-hidden="true">→</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="rounded-2xl bg-linen px-8 py-12 text-center text-charcoal/70 shadow-soft">
                <p class="font-heading text-2xl">No stories yet</p>
                <p class="mt-3 text-sm md:text-base">Start the first chapter by <a href="/php_blog/posts/create.php" class="underline underline-offset-4">writing a story</a>.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<section id="about" class="px-8 py-16">
    <div class="max-w-4xl mx-auto rounded-3xl bg-linen/60 px-8 py-12 text-center backdrop-blur-sm shadow-soft space-y-4">
        <h2 class="font-heading text-3xl text-charcoal">About Paper & Pixels</h2>
        <p class="font-sans text-base md:text-lg text-charcoal/70">Paper & Pixels is a space for thinkers, makers, and dreamers who find beauty in both the tactile and the digital. We write about design, creativity, and the subtle ways technology shapes how we create and connect. Whether it’s code that feels like poetry or visuals that spark emotion, our stories celebrate the craft behind every pixel and page.</p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>