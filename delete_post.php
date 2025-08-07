<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $postId = $_GET['id'];
    $userId = $_SESSION['user']['id'];

    // Ensure user owns the post
    $stmt = $pdo->prepare("SELECT * FROM content WHERE id = ? AND user_id = ?");
    $stmt->execute([$postId, $userId]);
    $post = $stmt->fetch();

    if ($post) {
        // Delete image file if exists
        if (!empty($post['image_path']) && file_exists($post['image_path'])) {
            unlink($post['image_path']);
        }

        // Delete from DB
        $stmt = $pdo->prepare("DELETE FROM content WHERE id = ? AND user_id = ?");
        $stmt->execute([$postId, $userId]);
    }
}

header("Location: content.php");
exit;
