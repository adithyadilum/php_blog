<?php
include '../includes/config.php';
if (!isset($_GET['post_id']) || !is_numeric($_GET['post_id'])) exit('0');
$post_id = (int)$_GET['post_id'];
$res = $conn->query("SELECT COUNT(*) AS total FROM likes WHERE post_id=$post_id");
$row = $res->fetch_assoc();
echo $row['total'] ?? 0;
