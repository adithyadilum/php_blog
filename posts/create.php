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
        $message = "Post created successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }
}
?>

<?php include '../includes/header.php'; ?>
<h2>Create New Post</h2>

<form method="POST" enctype="multipart/form-data">
    <label>Title:</label><br>
    <input type="text" name="title" required><br><br>

    <label>Content:</label><br>
    <textarea name="content" rows="8" cols="60" required></textarea><br><br>
    <p><small>You can use Markdown syntax for formatting (e.g., **bold**, *italic*, # Heading)</small></p>

    <label>Tags (comma-separated):</label><br>
    <input type="text" name="tags"><br><br>

    <label>Cover Image:</label><br>
    <input type="file" name="cover_image"><br><br>

    <button type="submit">Publish</button>
</form>

<p style="color:green;"><?php echo $message; ?></p>

<?php include '../includes/footer.php'; ?>