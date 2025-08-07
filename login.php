<?php
session_start();
require_once 'includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Login successful - save full user in session
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'profile_image' => $user['profile_image'],
                    'is_admin' => $user['is_admin']
                ];

                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - GeorgianBook</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="background-image"></div>

<div class="welcome-container">
    <h1><i class="fas fa-sign-in-alt"></i> Login</h1>
    <p>Welcome back! Log in to your GeorgianBook account.</p>

    <?php if ($error): ?>
        <p class="error" style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <div style="text-align: left;">
            <label><i class="fas fa-envelope"></i> Email:</label><br>
            <input type="email" name="email" required><br><br>

            <label><i class="fas fa-lock"></i> Password:</label><br>
            <input type="password" name="password" required><br><br>
        </div>

        <button type="submit" class="btn"><i class="fas fa-sign-in-alt"></i> Login</button>
    </form>

    <p style="margin-top: 15px;">Don't have an account? <a href="register.php">Register here</a>.</p>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
