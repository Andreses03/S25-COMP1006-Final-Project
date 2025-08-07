<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to GeorgianBook</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="background-image"></div>

<div class="welcome-container">
    <h1><i class="fas fa-users"></i> GeorgianBook</h1>
    <p>Connect, share and explore with other students from Georgian College.</p>

    <div class="button-group">
        <a href="register.php" class="btn"><i class="fas fa-user-plus"></i> Register</a>
        <a href="login.php" class="btn secondary"><i class="fas fa-sign-in-alt"></i> Login</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
