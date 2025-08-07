<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}


require 'includes/db.php';

// Redirect if ID is not provided
if (!isset($_GET['id'])) {
    die('User ID not provided.');
}

$id = $_GET['id'];

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die('User not found.');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $newImage = $_FILES['profile_image'] ?? null;

    // Validate input
    if (empty($name) || empty($email)) {
        echo "Please fill in all fields.";
        exit;
    }

    // Handle image upload if a new image is provided
    $imagePath = $user['profile_image'];
    if ($newImage && $newImage['tmp_name']) {
        $targetDir = "uploads/";
        $imagePath = $targetDir . basename($newImage["name"]);
        move_uploaded_file($newImage["tmp_name"], $imagePath);
    }

    // Update user in the database
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, profile_image = ? WHERE id = ?");
    $stmt->execute([$name, $email, $imagePath, $id]);

    header("Location: users.php");
    exit;
}
$error = '';
$success = '';

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    $error = "User not found.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $profileImagePath = $user['profile_image'];

    if (!empty($_FILES['profile_image']['name'])) {
        $imageName = time() . '_' . basename($_FILES['profile_image']['name']);
        $targetDir = 'uploads/';
        $profileImagePath = $targetDir . $imageName;
        $imageType = strtolower(pathinfo($profileImagePath, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($imageType, $allowedTypes)) {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        } elseif (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $profileImagePath)) {
            $error = "Failed to upload image.";
        }
    }

    if (!$error) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, profile_image = ? WHERE id = ?");
            $stmt->execute([$name, $email, $profileImagePath, $id]);
            $success = "User updated successfully!";
            header("Location: users.php");
            exit;
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="background-image"></div>

<div class="welcome-container">
    <h2><i class="fas fa-user-edit"></i> Edit User</h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Name:</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>

        <label>Profile Image:</label><br>
        <?php if ($user['profile_image']): ?>
            <img src="<?= htmlspecialchars($user['profile_image']) ?>" width="100" style="border-radius: 8px;"><br>
        <?php endif; ?>
        <input type="file" name="profile_image"><br><br>

        <button type="submit" class="btn"><i class="fas fa-save"></i> Save Changes</button>
        <a href="users.php" class="btn secondary"><i class="fas fa-arrow-left"></i> Cancel</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>