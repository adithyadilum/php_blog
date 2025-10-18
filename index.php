<?php
include 'includes/header.php';
require_once __DIR__ . '/config.php';

$totalPosts = 0;
$totalUsers = 0;

$postCountResult = $conn->query('SELECT COUNT(*) AS total FROM posts');
if ($postCountResult instanceof mysqli_result) {
    $countRow = $postCountResult->fetch_assoc();
    if ($countRow) {
        $totalPosts = (int) ($countRow['total'] ?? 0);
    }
    $postCountResult->free();
}

$userCountResult = $conn->query('SELECT COUNT(*) AS total FROM users');
if ($userCountResult instanceof mysqli_result) {
    $countRow = $userCountResult->fetch_assoc();
    if ($countRow) {
        $totalUsers = (int) ($countRow['total'] ?? 0);
    }
    $userCountResult->free();
}

$postLabel = $totalPosts === 1 ? 'story' : 'stories';
$userLabel = $totalUsers === 1 ? 'member' : 'members';

$toastMessage = '';
$toastType = 'toast-info';
$toastIcon = 'info';
if (isset($_GET['msg'])) {
    $status = $_GET['msg'];
    if ($status === 'created') {
        $toastMessage = 'Post created successfully';
        $toastType = 'toast-success';
        $toastIcon = 'plus';
    } elseif ($status === 'updated') {
        $toastMessage = 'Post updated successfully';
        $toastType = 'toast-success';
        $toastIcon = 'check';
    } elseif ($status === 'deleted') {
        $toastMessage = 'Post deleted successfully';
        $toastType = 'toast-info';
        $toastIcon = 'minus';
    } elseif ($status === 'unauthorized') {
        $toastMessage = 'Unauthorized';
        $toastType = 'toast-error';
        $toastIcon = 'error';
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
    $featuredPost = null;
    $featuredLikeCount = 0;
    $featuredExcerpt = '';
}
$featuredResult = $conn->query('
    SELECT posts.*, users.username, COUNT(likes.id) AS like_count
    FROM posts
    LEFT JOIN likes ON likes.post_id = posts.id
    JOIN users ON posts.user_id = users.id
    GROUP BY posts.id
    ORDER BY like_count DESC, posts.created_at DESC
    LIMIT 1
');
ksort($availableTags);
if ($featuredResult instanceof mysqli_result) {
    $featuredRow = $featuredResult->fetch_assoc();
    if ($featuredRow) {
        $featuredPost = $featuredRow;
        $featuredLikeCount = (int) ($featuredRow['like_count'] ?? 0);

        $rawFeaturedExcerpt = trim(strip_tags($featuredRow['content'] ?? ''));
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            $featuredExcerpt = mb_strlen($rawFeaturedExcerpt) > 220 ? mb_substr($rawFeaturedExcerpt, 0, 220) . '…' : $rawFeaturedExcerpt;
        } else {
            $featuredExcerpt = strlen($rawFeaturedExcerpt) > 220 ? substr($rawFeaturedExcerpt, 0, 220) . '…' : $rawFeaturedExcerpt;
        }
    }
    $featuredResult->free();
}
$availableTags = array_values($availableTags);
?>

<section class="relative overflow-hidden px-6 py-16">
    <div class="pointer-events-none absolute inset-0 opacity-60" style="background: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.9) 0%, rgba(250,246,233,0.4) 45%, rgba(250,246,233,0) 75%);"></div>
    <div class="relative max-w-4xl mx-auto text-center space-y-6">
        <?php if ($toastMessage): ?>
            <div data-toast class="toast-notification <?php echo $toastType; ?>" role="alert">
                <span class="toast-icon">
                    <?php if ($toastIcon === 'plus'): ?>
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 5v14" stroke-linecap="round" />
                            <path d="M5 12h14" stroke-linecap="round" />
                        </svg>
                    <?php elseif ($toastIcon === 'check'): ?>
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M5 12.5L9.5 17l9-10" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    <?php elseif ($toastIcon === 'minus'): ?>
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M5 12h14" stroke-linecap="round" />
                        </svg>
                    <?php else: ?>
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 8v4" stroke-linecap="round" />
                            <circle cx="12" cy="16" r="0.75" fill="currentColor" stroke="none" />
                        </svg>
                    <?php endif; ?>
                </span>
                <div class="toast-message">
                    <?php echo htmlspecialchars($toastMessage, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            </div>
        <?php endif; ?>
        <p class="uppercase tracking-[0.45em] text-[0.65rem] text-charcoal/60">CURATED STORIES FOR MODERN CREATORS</p>
        <h1 class="font-heading text-4xl md:text-5xl text-charcoal">Ideas that bridge creativity and technology</h1>
        <p class="font-sans text-base md:text-lg text-charcoal/70 max-w-2xl mx-auto">Explore stories at the intersection of art, code, and design — where imagination meets logic, and pixels tell as many stories as paper ever did.</p>
        <a href="#stories" class="btn-major mx-auto inline-flex items-center gap-3 rounded-full px-6 py-3 text-xs md:text-sm uppercase tracking-[0.35em]">
            Drift Into Stories
            <svg aria-hidden="true" class="h-4 w-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                <path d="M12 5v14m0 0-5-5m5 5 5-5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </a>
        <?php if ($contextMessage): ?>
            <p class="text-xs uppercase tracking-[0.4em] text-charcoal/60"><?php echo $contextMessage; ?></p>
        <?php endif; ?>
    </div>
</section>

<?php if ($featuredPost): ?>
    <section class="px-6 pb-12">
        <div class="relative mx-auto max-w-6xl overflow-hidden rounded-[2.5rem] border border-charcoal/12 bg-linen/95 shadow-soft backdrop-blur">
            <div class="pointer-events-none absolute inset-0 opacity-80" style="background: radial-gradient(circle at 12% 18%, rgba(255,255,255,0.95) 0%, rgba(250,246,233,0.65) 38%, rgba(244,237,213,0.4) 70%, rgba(244,237,213,0.05) 100%);"></div>
            <div class="relative grid gap-10 p-10 md:grid-cols-[1.3fr_1fr] md:items-center">
                <div class="space-y-6">
                    <span class="inline-flex items-center gap-2 rounded-full border border-charcoal/20 bg-white/70 px-4 py-2 text-[0.65rem] uppercase tracking-[0.35em] text-charcoal/70">
                        Featured Story
                        <svg aria-hidden="true" class="h-3 w-3 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                            <path d="M12 5v14m0 0-5-5m5 5 5-5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <h2 class="font-heading text-3xl text-charcoal md:text-4xl lg:text-[2.65rem]">
                        <a href="posts/view.php?id=<?php echo (int) $featuredPost['id']; ?>" class="hover:opacity-80 transition">
                            <?php echo htmlspecialchars($featuredPost['title'] ?? 'Untitled story', ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </h2>
                    <p class="font-sans text-sm text-charcoal/70 md:text-base">
                        <?php echo htmlspecialchars($featuredExcerpt, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <div class="flex flex-wrap items-center gap-4 text-xs uppercase tracking-[0.28em] text-charcoal/60">
                        <span><?php echo htmlspecialchars($featuredPost['username'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?> · <?php echo isset($featuredPost['created_at']) ? date('M d, Y', strtotime($featuredPost['created_at'])) : ''; ?></span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-charcoal/15 bg-white/70 px-3 py-1 text-[0.65rem] text-charcoal/70">
                            <svg aria-hidden="true" class="h-3.5 w-3.5 fill-current" viewBox="0 0 24 24">
                                <path d="M11.645 20.205l-.007-.004C5.986 15.88 2.5 12.634 2.5 8.75 2.5 6.126 4.57 4 7.25 4a4.5 4.5 0 0 1 3.75 1.97A4.5 4.5 0 0 1 14.75 4c2.68 0 4.75 2.126 4.75 4.75 0 3.883-3.486 7.13-9.138 11.456l-.007.004-.005.004a.75.75 0 0 1-.894 0l-.005-.004Z" fill="currentColor" />
                            </svg>
                            <?php echo number_format($featuredLikeCount); ?> likes
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="posts/view.php?id=<?php echo (int) $featuredPost['id']; ?>" class="btn-major inline-flex items-center gap-2 rounded-full px-6 py-3 text-xs uppercase tracking-[0.3em]">
                            Read the feature
                            <svg aria-hidden="true" class="h-3.5 w-3.5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                                <path d="M5 12h14" stroke-linecap="round" />
                                <path d="M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="overflow-hidden rounded-3xl border border-charcoal/10 bg-white/50 shadow-soft">
                    <?php if (!empty($featuredPost['cover_image'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($featuredPost['cover_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($featuredPost['title'] ?? 'Featured story cover', ENT_QUOTES, 'UTF-8'); ?>" class="h-full w-full object-cover" />
                    <?php else: ?>
                        <div class="flex h-full min-h-[18rem] w-full items-center justify-center bg-gradient-to-br from-sand via-cream to-linen text-xs uppercase tracking-[0.3em] text-charcoal/40">
                            Awaiting cover
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<section id="stories" class="px-6 pb-16">
    <div class="max-w-6xl mx-auto space-y-10 bg-transparent">
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
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
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
                    <article class="story-card flex flex-col overflow-hidden rounded-xl bg-linen shadow-soft transition-all duration-300 hover:shadow-hover">
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
                                        <svg aria-hidden="true" class="h-4 w-4 fill-current" viewBox="0 0 24 24">
                                            <path d="M11.645 20.205l-.007-.004C5.986 15.88 2.5 12.634 2.5 8.75 2.5 6.126 4.57 4 7.25 4a4.5 4.5 0 0 1 3.75 1.97A4.5 4.5 0 0 1 14.75 4c2.68 0 4.75 2.126 4.75 4.75 0 3.883-3.486 7.13-9.138 11.456l-.007.004-.005.004a.75.75 0 0 1-.894 0l-.005-.004Z" fill="currentColor" />
                                        </svg>
                                        <span class="like-count"><?php echo $like_count; ?></span>
                                    </button>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-2 rounded-full border border-sand/80 bg-sand/50 px-4 py-2 text-sm font-medium text-charcoal/60">
                                        <svg aria-hidden="true" class="h-4 w-4 fill-current" viewBox="0 0 24 24">
                                            <path d="M11.645 20.205l-.007-.004C5.986 15.88 2.5 12.634 2.5 8.75 2.5 6.126 4.57 4 7.25 4a4.5 4.5 0 0 1 3.75 1.97A4.5 4.5 0 0 1 14.75 4c2.68 0 4.75 2.126 4.75 4.75 0 3.883-3.486 7.13-9.138 11.456l-.007.004-.005.004a.75.75 0 0 1-.894 0l-.005-.004Z" fill="currentColor" />
                                        </svg>
                                        <span class="like-count"><?php echo $like_count; ?></span>
                                    </span>
                                <?php endif; ?>

                                <a href="posts/view.php?id=<?php echo $postId; ?>" class="inline-flex items-center gap-2 text-xs uppercase tracking-[0.25em] text-charcoal hover:opacity-70 transition">
                                    Read story
                                    <svg aria-hidden="true" class="h-3 w-3 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>
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

<section id="about" class="px-6 pb-20">
    <div class="relative mx-auto max-w-6xl overflow-hidden rounded-[2.75rem] border border-charcoal/10 bg-linen/90 px-8 py-16 shadow-soft backdrop-blur">
        <div class="pointer-events-none absolute inset-0 opacity-[0.88]" style="background: radial-gradient(circle at 12% 18%, rgba(255,255,255,0.95) 0%, rgba(250,246,233,0.65) 38%, rgba(244,237,213,0.4) 70%, rgba(244,237,213,0.05) 100%);"></div>
        <div class="relative grid gap-12 md:grid-cols-[1.2fr_1fr] md:items-center">
            <div class="space-y-7 text-center md:text-left">
                <p class="uppercase tracking-[0.4em] text-[0.65rem] text-charcoal/55">About Paper & Pixels</p>
                <h2 class="font-heading text-4xl leading-tight text-charcoal md:text-5xl">Where craft meets curiosity</h2>
                <p class="font-sans text-base md:text-lg text-charcoal/75">Paper & Pixels celebrates the intersection of storytelling and systems thinking. From publishing experiments to process retrospectives, every feature is designed to help modern makers blend tactile craft with digital clarity.</p>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-charcoal/12 bg-white/70 px-6 py-6 text-left shadow-inner">
                        <p class="font-heading text-3xl text-charcoal"><?php echo number_format($totalPosts); ?></p>
                        <p class="mt-2 text-xs uppercase tracking-[0.28em] text-charcoal/60">Stories crafted</p>
                        <p class="mt-3 text-sm text-charcoal/70">Now home to <?php echo number_format($totalPosts); ?> published <?php echo $postLabel; ?> from designers, developers, and multidisciplinary collaborators.</p>
                    </div>
                    <div class="rounded-2xl border border-charcoal/12 bg-white/70 px-6 py-6 text-left shadow-inner">
                        <p class="font-heading text-3xl text-charcoal"><?php echo number_format($totalUsers); ?></p>
                        <p class="mt-2 text-xs uppercase tracking-[0.28em] text-charcoal/60">Contributors</p>
                        <p class="mt-3 text-sm text-charcoal/70">A studio of <?php echo number_format($totalUsers); ?> <?php echo $userLabel; ?> sharing frameworks, annotated sketches, and code-first concepts.</p>
                    </div>
                </div>
                <div class="flex flex-wrap justify-center gap-3 md:justify-start">
                    <a href="/php_blog/auth/register.php" class="btn-major inline-flex items-center gap-2 rounded-full px-6 py-3 text-xs uppercase tracking-[0.3em]">
                        Join the collective
                        <svg aria-hidden="true" class="h-3.5 w-3.5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                            <path d="M5 12h14" stroke-linecap="round" />
                            <path d="M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                    <a href="#stories" class="inline-flex items-center gap-2 rounded-full border border-charcoal/20 bg-white/70 px-6 py-3 text-xs uppercase tracking-[0.3em] text-charcoal transition hover:border-charcoal/45 hover:text-charcoal/80">
                        Browse stories
                        <svg aria-hidden="true" class="h-3.5 w-3.5 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                            <path d="M5 12h14" stroke-linecap="round" />
                            <path d="M12 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="space-y-6 rounded-3xl border border-charcoal/12 bg-white/55 p-8 text-left shadow-soft">
                <p class="text-xs uppercase tracking-[0.35em] text-charcoal/60">What you'll find inside</p>
                <ul class="space-y-6 text-sm text-charcoal/75">
                    <li class="flex gap-4">
                        <span class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-charcoal text-[0.65rem] font-semibold uppercase tracking-[0.25em] text-linen">01</span>
                        <div>
                            <p class="font-semibold uppercase tracking-[0.2em] text-[0.75rem] text-charcoal/80">Studio essays</p>
                            <p class="mt-1 leading-relaxed text-charcoal/70">Narratives that bridge analog craft, digital tooling, and the rituals that keep ideas flowing.</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-charcoal text-[0.65rem] font-semibold uppercase tracking-[0.25em] text-linen">02</span>
                        <div>
                            <p class="font-semibold uppercase tracking-[0.2em] text-[0.75rem] text-charcoal/80">Playbooks</p>
                            <p class="mt-1 leading-relaxed text-charcoal/70">Practical frameworks, checklists, and repeatable rituals to ship thoughtful work.</p>
                        </div>
                    </li>
                    <li class="flex gap-4">
                        <span class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-charcoal text-[0.65rem] font-semibold uppercase tracking-[0.25em] text-linen">03</span>
                        <div>
                            <p class="font-semibold uppercase tracking-[0.2em] text-[0.75rem] text-charcoal/80">Tools & textures</p>
                            <p class="mt-1 leading-relaxed text-charcoal/70">Curated resources—from component libraries to analog prompts—that inspire the next iteration.</p>
                        </div>
                    </li>
                </ul>
                <p class="text-xs uppercase tracking-[0.3em] text-charcoal/55">Stay curious, publish boldly, and let your ideas travel from paper to pixels.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>