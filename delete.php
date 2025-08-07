<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

require 'includes/db.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    die('User ID not provided.');
}

$id = $_GET['id'];

// Fetch the user to get image path
$stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die('User not found.');
}

// Delete the image if it exists
if (!empty($user['profile_image']) && file_exists($user['profile_image'])) {
    unlink($user['profile_image']);
}

// Delete the user from the database
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

// Redirect back to users list
header("Location: users.php");
exit;
