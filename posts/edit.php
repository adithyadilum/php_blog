<?php
include '../includes/auth_check.php';
include '../config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$post_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';
$isAdmin = ($user_role === 'admin');

// Get post owner
$stmt = $conn->prepare("SELECT user_id FROM posts WHERE id=?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->bind_result($owner_id);
if (!$stmt->fetch()) {
    $stmt->close();
    header("Location: ../index.php");
    exit;
}
$stmt->close();

// Access control
if (!$isAdmin && $user_id != $owner_id) {
    die("Unauthorized: You don't have permission to edit this post.");
}

// Fetch post for editing
if ($isAdmin) {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
} else {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $post_id, $user_id);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Post not found or you don't have permission to edit it.";
    include '../includes/footer.php';
    exit;
}

$post = $result->fetch_assoc();
$stmt->close();
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $tags = trim($_POST['tags']);

    // Handle new cover image if uploaded
    $cover_image = $post['cover_image'];
    if (!empty($_FILES['cover_image']['name'])) {
        $target_dir = "../uploads/";
        $file_name = basename($_FILES["cover_image"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file)) {
            $cover_image = $file_name;
        } else {
            $message = "Failed to upload image!";
        }
    }

    if ($isAdmin) {
        $update = $conn->prepare("UPDATE posts SET title=?, content=?, tags=?, cover_image=?, updated_at=NOW() WHERE id=?");
        $update->bind_param("ssssi", $title, $content, $tags, $cover_image, $post_id);
    } else {
        $update = $conn->prepare("UPDATE posts SET title=?, content=?, tags=?, cover_image=?, updated_at=NOW() WHERE id=? AND user_id=?");
        $update->bind_param("ssssii", $title, $content, $tags, $cover_image, $post_id, $user_id);
    }

    if ($update->execute()) {
        header("Location: ../index.php?msg=updated");
        exit;
    } else {
        $message = "Error updating post: " . $update->error;
    }
    $update->close();
}

$page_extra_head = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplemde@1.11.2/dist/simplemde.min.css">';
$page_extra_scripts = '<script src="https://cdn.jsdelivr.net/npm/simplemde@1.11.2/dist/simplemde.min.js"></script>';

include '../includes/header.php';
?>

<div class="max-w-2xl mx-auto">
    <h2 class="text-3xl font-bold mb-6 text-gray-800">Edit Post</h2>

    <?php if (!empty($message)): ?>
        <div class="<?php echo strpos($message, 'Error') === 0 ? 'bg-red-100 border border-red-400 text-red-700' : 'bg-green-100 border border-green-400 text-green-700'; ?> px-6 py-4 rounded-lg mb-6">
            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-white shadow-lg rounded-lg p-8 space-y-6">
        <div>
            <label class="block text-gray-700 font-semibold mb-2">Title:</label>
            <input type="text"
                name="title"
                required
                value="<?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div>
            <label class="block text-gray-700 font-semibold mb-2">Content:</label>
            <textarea name="content"
                rows="10"
                required
                data-markdown-editor="true"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            <p class="text-sm text-gray-500 mt-1">Use Markdown for formatting (e.g., **bold**, *italic*, # Heading)</p>
        </div>

        <div>
            <label class="block text-gray-700 font-semibold mb-2">Tags (comma-separated):</label>
            <input type="text"
                name="tags"
                value="<?php echo htmlspecialchars($post['tags'], ENT_QUOTES, 'UTF-8'); ?>"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div>
            <label class="block text-gray-700 font-semibold mb-2">Cover Image:</label>
            <?php if (!empty($post['cover_image'])): ?>
                <img src="../uploads/<?php echo htmlspecialchars($post['cover_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="Cover image" class="w-40 h-24 object-cover rounded mb-3">
            <?php endif; ?>
            <input type="file"
                name="cover_image"
                accept="image/*"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <small class="text-gray-500 text-sm mt-1 block">Uploading a new image will replace the existing one.</small>
        </div>

        <div class="flex justify-between items-center">
            <a href="../index.php" class="text-sm text-gray-500 hover:text-primary">← Cancel</a>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg">
                💾 Save Changes
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>