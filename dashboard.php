<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userName = $_SESSION['user']['name'];
$profileImage = $_SESSION['user']['profile_image'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - GeorgianBook</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin: 20px auto 10px;
            display: block;
            border: 3px solid #3498db;
        }
        .welcome-text {
            font-size: 20px;
            color: #333;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="background-image"></div>

<div class="welcome-container">
    <?php if (!empty($profileImage) && file_exists($profileImage)): ?>
        <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profile Image" class="profile-img">
    <?php else: ?>
        <p style="text-align:center; color:#999;">No profile image available.</p>
    <?php endif; ?>

    <h1><i class="fas fa-user"></i> Welcome, <?= htmlspecialchars($userName) ?>!</h1>
    <p class="welcome-text">This is your personal dashboard where you'll manage your account and content.</p>

    <div class="button-group">
        <a href="users.php" class="btn"><i class="fas fa-users"></i> Manage Users</a>
        <a href="content.php" class="btn secondary"><i class="fas fa-file-alt"></i> Manage Content</a>
        <a href="logout.php" class="btn" style="background-color: #e74c3c;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
