<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/db.php';

$userId = $_SESSION['user']['id'];
$userName = $_SESSION['user']['name'];
$profileImage = $_SESSION['user']['profile_image'];

$contentError = '';
$contentSuccess = '';

if (isset($_GET['success'])) {
    $contentSuccess = "Content posted successfully!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $body  = trim($_POST['body']);
    $imagePath = '';

    if (empty($title)) {
        $contentError = "Title is required.";
    } else {
        // Handle image
        if (!empty($_FILES['image']['name'])) {
            $imageName = time() . '_' . basename($_FILES['image']['name']);
            $targetDir = 'uploads/';
            $imagePath = $targetDir . $imageName;

            $imageType = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($imageType, $allowedTypes)) {
                $contentError = "Only JPG, JPEG, PNG, and GIF files are allowed.";
            } elseif (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                $contentError = "Failed to upload image.";
            }
        }

        if (!$contentError) {
            $stmt = $pdo->prepare("INSERT INTO content (user_id, title, body, image_path) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$userId, $title, $body, $imagePath])) {
            header("Location: content.php?success=1");
            exit;
        } else {
        $contentError = "Something went wrong while saving your content.";
            }
        }
    }
}

// Fetch users posts
$stmt = $pdo->prepare("
    SELECT content.*, users.name, users.profile_image
    FROM content
    JOIN users ON content.user_id = users.id
    ORDER BY content.created_at DESC
");
$stmt->execute();
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Content - GeorgianBook</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="background-image"></div>

<div class="welcome-container">
    <h1><i class="fas fa-file-alt"></i> Manage Content</h1>
    <p class="welcome-text">Welcome, <?= htmlspecialchars($userName) ?>. Here you can manage your posts or shared content.</p>

    <div class="button-group">
        <a href="dashboard.php" class="btn secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <div class="content-box" style="margin-top: 30px; text-align: center;">
        <?php if ($contentError): ?>
            <p class="error" style="color: red;"><?= htmlspecialchars($contentError) ?></p>
        <?php elseif ($contentSuccess): ?>
            <p class="success" style="color: green;"><?= htmlspecialchars($contentSuccess) ?></p>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label><strong>Title:</strong></label>
                <input type="text" name="title" required>
            </div>

            <div class="form-group">
                <label><strong>Message:</strong></label>
                <textarea name="body" rows="4" style="width: 100%; max-width: 500px;"></textarea>
            </div>

            <div class="form-group">
                <label><strong>Image (optional):</strong></label>
                <input type="file" name="image">
            </div>

            <button type="submit" class="btn"><i class="fas fa-plus-circle"></i> Publish</button>
        </form>
    </div>
</div>

<!-- Show posts -->
<h2 style="color: white; text-align:center; margin-top: 50px;"><i class="fas fa-list"></i>  Posts</h2>

<?php if ($posts): ?>
    <?php foreach ($posts as $post): ?>
        <div style="background: #fff; color: #333; padding: 20px; margin: 20px auto; max-width: 600px; border-radius: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0);">
            <div style="display: flex; align-items: center; margin-bottom: 10px;">
            <?php if (!empty($post['profile_image']) && file_exists($post['profile_image'])): ?>
                <img src="<?= htmlspecialchars($post['profile_image']) ?>" alt="Author" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
            <?php endif; ?>
            <div>
                <strong><?= htmlspecialchars($post['name']) ?></strong>
                <small style="color: gray;"><i class="far fa-clock"></i> <?= $post['created_at'] ?></small>
            </div>
        </div>

            <h3><?= htmlspecialchars($post['title']) ?></h3>
            <p><?= nl2br(htmlspecialchars($post['body'])) ?></p>

            <?php if (
                $post['user_id'] == $_SESSION['user']['id'] ||
                (!empty($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin'])
            ): ?>
                <div style="margin-top: 10px;">
                    <a href="edit.php?id=<?= $post['id'] ?>" class="btn btn-edit"><i class="fas fa-edit"></i> Edit</a>
                    <a href="delete_post.php?id=<?= $post['id'] ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this post?');">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </div>
            <?php endif; ?>



            <?php if (!empty($post['image_path']) && file_exists($post['image_path'])): ?>
                <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Post Image" style="max-width: 100%; margin-top: 15px; border-radius: 8px;">
            <?php endif; ?>

        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p style="color: black; text-align: center;">No posts yet.</p>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
</body>
</html>
