<?php
include '../includes/auth_check.php';
include '../config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$post_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch post owned by this user
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Post not found or you don't have permission to edit it.";
    include '../includes/footer.php';
    exit;
}

$post = $result->fetch_assoc();
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

    $update = $conn->prepare("UPDATE posts SET title=?, content=?, tags=?, cover_image=?, updated_at=NOW() WHERE id=? AND user_id=?");
    $update->bind_param("ssssii", $title, $content, $tags, $cover_image, $post_id, $user_id);

    if ($update->execute()) {
        $message = "Post updated successfully!";
        // Refresh post data
        $post['title'] = $title;
        $post['content'] = $content;
        $post['tags'] = $tags;
        $post['cover_image'] = $cover_image;
    } else {
        $message = "Error updating post: " . $update->error;
    }
}
?>

<?php include '../includes/header.php'; ?>

<h2>Edit Post</h2>

<p style="color:green;"><?php echo $message; ?></p>

<form method="POST" enctype="multipart/form-data">
    <label>Title:</label><br>
    <input type="text" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required><br><br>

    <label>Content:</label><br>
    <textarea name="content" rows="8" cols="60" required><?php echo htmlspecialchars($post['content']); ?></textarea><br><br>

    <label>Tags:</label><br>
    <input type="text" name="tags" value="<?php echo htmlspecialchars($post['tags']); ?>"><br><br>

    <label>Cover Image:</label><br>
    <?php if (!empty($post['cover_image'])): ?>
        <img src="../uploads/<?php echo htmlspecialchars($post['cover_image']); ?>" width="150"><br>
    <?php endif; ?>
    <input type="file" name="cover_image"><br><br>

    <button type="submit">Update Post</button>
</form>

<p><a href="../index.php">← Back to Home</a></p>

<?php include '../includes/footer.php'; ?>