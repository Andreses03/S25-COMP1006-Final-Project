<?php
session_start();
require_once 'includes/db.php';

// Verifica si el usuario está autenticado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user']['id'];
$userName = $_SESSION['user']['name'];
$profileImage = $_SESSION['user']['profile_image'];

if (!isset($_GET['id'])) {
    header('Location: content.php');
    exit;
}

$postId = $_GET['id'];

// Get new post
$stmt = $pdo->prepare("SELECT * FROM content WHERE id = ? AND user_id = ?");
$stmt->execute([$postId, $userId]);
$post = $stmt->fetch();

if (!$post) {
    echo "Post not found or unauthorized.";
    exit;
}

$error = '';
$success = '';

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $body = trim($_POST['body']);
    $newImagePath = $post['image_path']; // Keep existing image by default

    if (empty($title)) {
        $error = "Title is required.";
    } else {
        // If new image is uploaded
        if (!empty($_FILES['image']['name'])) {
            $imageName = time() . '_' . basename($_FILES['image']['name']);
            $targetDir = 'uploads/';
            $newImagePath = $targetDir . $imageName;

            $imageType = strtolower(pathinfo($newImagePath, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($imageType, $allowedTypes)) {
                $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
            } elseif (!move_uploaded_file($_FILES['image']['tmp_name'], $newImagePath)) {
                $error = "Failed to upload image.";
            } else {
                // Delete old image if exists
                if (!empty($post['image_path']) && file_exists($post['image_path'])) {
                    unlink($post['image_path']);
                }
            }
        }

        if (!$error) {
            $stmt = $pdo->prepare("UPDATE content SET title = ?, body = ?, image_path = ? WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$title, $body, $newImagePath, $postId, $userId])) {
                $success = "Post updated successfully!";
                // Update $post variable to reflect changes
                $post['title'] = $title;
                $post['body'] = $body;
                $post['image_path'] = $newImagePath;
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Post - GeorgianBook</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="background-image"></div>

<div class="welcome-container">
    <h1><i class="fas fa-edit"></i> Edit Post</h1>
    <div class="button-group" style="margin-bottom: 20px;">
        <a href="content.php" class="btn secondary"><i class="fas fa-arrow-left"></i> Back to Content</a>
    </div>


    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label><strong>Title:</strong></label>
            <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
        </div>

        <div class="form-group">
            <label><strong>Message:</strong></label>
            <textarea name="body" rows="4" style="width: 100%; max-width: 500px;"><?= htmlspecialchars($post['body']) ?></textarea>
        </div>

        <div class="form-group">
            <label><strong>Current Image:</strong></label><br>
            <?php if (!empty($post['image_path']) && file_exists($post['image_path'])): ?>
                <img src="<?= htmlspecialchars($post['image_path']) ?>" style="max-width: 200px; border-radius: 10px; margin-bottom: 10px;"><br>
            <?php else: ?>
                <p>No image uploaded.</p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label><strong>New Image (optional):</strong></label>
            <input type="file" name="image">
        </div>

        <button type="submit" class="btn"><i class="fas fa-save"></i> Update Post</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
