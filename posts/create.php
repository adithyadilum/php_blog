<?php
include '../includes/auth_check.php';
require_once __DIR__ . '/../config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $tags = trim($_POST['tags']);
    $user_id = $_SESSION['user_id'];

    // Handle cover image upload
    $cover_image = null;
    if (!empty($_FILES['cover_image']['name'])) {
        $target_dir = "../uploads/";
        $originalName = basename($_FILES['cover_image']['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $uniqueName = uniqid('cover_', true);
        $file_name = $extension ? $uniqueName . '.' . $extension : $uniqueName;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_file)) {
            $cover_image = $file_name;
        } else {
            $message = "Failed to upload image!";
        }
    }

    // Insert post into DB
    $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content, tags, cover_image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $title, $content, $tags, $cover_image);

    if ($stmt->execute()) {
        $newPostId = $conn->insert_id;
        header("Location: view.php?id=" . $newPostId);
        exit;
    } else {
        $message = "Error: " . $stmt->error;
    }
}

$page_extra_head = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplemde@1.11.2/dist/simplemde.min.css">';
$page_extra_scripts = '<script src="https://cdn.jsdelivr.net/npm/simplemde@1.11.2/dist/simplemde.min.js"></script>';

include '../includes/header.php';
?>

<section class="px-6 py-16">
    <form method="POST" enctype="multipart/form-data" class="mx-auto flex max-w-5xl flex-col gap-12">
        <div class="text-center space-y-6">
            <p class="uppercase tracking-[0.4em] text-xs text-charcoal/60">Start a new chapter</p>

            <?php if (!empty($message)): ?>
                <div data-toast class="toast-notification toast-error" role="alert">
                    <span class="toast-icon">
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M15 9L9 15" stroke-linecap="round" />
                            <path d="M9 9l6 6" stroke-linecap="round" />
                        </svg>
                    </span>
                    <div class="toast-message">
                        <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>
            <?php endif; ?>

            <input type="text"
                name="title"
                required
                value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                placeholder="Enter your story headline"
                class="w-full bg-transparent font-heading text-4xl text-charcoal placeholder:text-charcoal/30 focus:outline-none focus:ring-0 text-center" />

            <p class="text-sm uppercase tracking-[0.32em] text-charcoal/60">
                by <span class="font-semibold text-charcoal"><?php echo htmlspecialchars($_SESSION['username'] ?? 'You', ENT_QUOTES, 'UTF-8'); ?></span>
                · <?php echo date('M d, Y'); ?>
            </p>

            <div class="flex flex-wrap justify-center gap-2">
                <input type="text"
                    name="tags"
                    placeholder="Comma-separated tags (e.g. design, code, creative)"
                    value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                    class="w-full max-w-xl rounded-full border border-charcoal/15 bg-white/60 px-5 py-3 text-xs uppercase tracking-[0.25em] text-charcoal placeholder:text-charcoal/30 focus:border-charcoal/40 focus:outline-none transition" />
            </div>
        </div>

        <div class="flex flex-col items-center gap-4">
            <label class="text-xs uppercase tracking-[0.3em] text-charcoal/60">Cover image</label>
            <div class="relative flex w-full max-w-3xl flex-col items-center justify-center gap-4 rounded-3xl border border-dashed border-charcoal/15 bg-white/60 px-6 py-10 text-center">
                <span class="text-sm uppercase tracking-[0.25em] text-charcoal/60">Drag & drop or upload a cover image</span>
                <input type="file"
                    name="cover_image"
                    accept="image/*"
                    class="w-full max-w-sm cursor-pointer rounded-full border border-charcoal/15 bg-linen px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-charcoal transition hover:border-charcoal/40 hover:bg-white file:mr-3 file:rounded-full file:border-0 file:bg-charcoal file:px-4 file:py-2 file:text-xs file:font-semibold file:uppercase file:tracking-[0.25em] file:text-linen file:hover:bg-opacity-80" />
                <p class="text-xs uppercase tracking-[0.25em] text-charcoal/45">Recommended 1600×900 · JPG, PNG, GIF, WEBP · Max 5MB</p>
            </div>
        </div>

        <div>
            <label for="content" class="sr-only">Content</label>
            <textarea id="content"
                name="content"
                rows="14"
                required
                data-autoresize="true"
                data-markdown-editor="true"
                placeholder="Write your story in Markdown..."
                class="w-full rounded-3xl bg-transparent px-6 py-5 text-charcoal placeholder:text-charcoal/35 focus:outline-none focus:ring-0 transition"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
            <p class="mt-3 text-center text-xs uppercase tracking-[0.25em] text-charcoal/50">Use Markdown for structure — headings, quotes, lists, and links.</p>
        </div>

        <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
            <a href="../index.php" class="text-xs uppercase tracking-[0.3em] text-charcoal/60 hover:text-charcoal">Cancel</a>
            <button type="submit"
                class="btn-major inline-flex items-center gap-3 rounded-full px-6 py-3 text-xs font-semibold uppercase tracking-[0.3em]">
                <svg aria-hidden="true" class="h-4 w-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
                    <path d="M12 19V5m0 0-4 4m4-4 4 4" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M5 19h14" stroke-linecap="round" />
                </svg>
                Publish post
            </button>
        </div>
    </form>
</section>

<?php include '../includes/footer.php'; ?>