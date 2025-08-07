<?php
session_start();
require_once 'includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Handle profile image upload
        $profileImagePath = '';
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
                // Insert user into database
                // Count existing users to determine if this is the first user
                $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                $userCount = $stmt->fetchColumn();

                // If no users exist, make this user an admin
                $isAdmin = ($userCount == 0) ? 1 : 0;

                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, profile_image, is_admin) VALUES (?, ?, ?, ?, ?)");
                $stmt->bindParam(1, $name);
                $stmt->bindParam(2, $email);
                $stmt->bindParam(3, $hashedPassword);
                $stmt->bindParam(4, $profileImagePath);
                $stmt->bindParam(5, $isAdmin);


                if ($stmt->execute()) {
                    // Get the last inserted user ID
                    $userId = $pdo->lastInsertId();

                    // Retrieve the full user data
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $user = $stmt->fetch();

                    // Auto-login the user
                    $_SESSION['user'] = [
                        'id'    => $user['id'],
                        'name'  => $user['name'],
                        'email' => $user['email'],
                        'profile_image' => $user['profile_image'],
                        'is_admin' => $user['is_admin']
                    ];

                    // Redirect to dashboard
                    header('Location: dashboard.php');
                    exit;
                }

                else {
                    $error = "An error occurred during registration.";
                }
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) {
                    $error = "This email is already registered.";
                } else {
                    $error = "Database error: " . $e->getMessage();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration - GeorgianBook</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="background-image"></div>

    <div class="welcome-container">
        <h1><i class="fas fa-user-plus"></i> Register</h1>

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php elseif ($success): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>Profile Image:</label>
                <input type="file" name="profile_image">
            </div>

            <button type="submit" class="btn"><i class="fas fa-user-check"></i> Register</button>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
