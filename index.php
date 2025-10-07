<?php
include 'includes/auth_check.php';
include 'includes/header.php';
?>
<h2> Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
<p>This is your dashboard - you'll later see your blog post here. </p>

<?php include 'includes/footer.php'; ?>