<?php
include '../includes/auth_check.php';
include '../config.php';

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
        $file_name = basename($_FILES["cover_image"]["name"]);
        $target_file = $target_dir . $file_name;

        // Move uploaded file to uploads folder
        if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $target_file)) {
            $cover_image = $file_name;
        } else {
            $message = "Failed to upload image!";
        }
    }

    // Insert post into DB
    $stmt = $conn->prepare("INSERT INTO posts (user_id, title, content, tags, cover_image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $title, $content, $tags, $cover_image);

    if ($stmt->execute()) {
        header("Location: ../index.php?msg=created");
        exit;
    } else {
        $message = "Error: " . $stmt->error;
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="max-w-2xl mx-auto">
    <h2 class="text-3xl font-bold mb-6 text-gray-800">Create New Post</h2>

    <?php if (!empty($message)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg mb-6">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-white shadow-lg rounded-lg p-8 space-y-6">
        <div>
            <label class="block text-gray-700 font-semibold mb-2">Title:</label>
            <input type="text"
                name="title"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div>
            <label class="block text-gray-700 font-semibold mb-2">Content:</label>
            <textarea name="content"
                rows="10"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            <p class="text-sm text-gray-500 mt-1">You can use Markdown syntax for formatting (e.g., **bold**, *italic*, # Heading)</p>
        </div>

        <div>
            <label class="block text-gray-700 font-semibold mb-2">Tags (comma-separated):</label>
            <input type="text"
                name="tags"
                placeholder="e.g. php, web, tutorial"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div>
            <label class="block text-gray-700 font-semibold mb-2">Cover Image:</label>
            <input type="file"
                name="cover_image"
                accept="image/*"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <small class="text-gray-500 text-sm mt-1 block">Max 5MB. Supported: JPG, PNG, GIF, WEBP</small>
        </div>

        <button type="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg">
            📝 Publish Post
        </button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>