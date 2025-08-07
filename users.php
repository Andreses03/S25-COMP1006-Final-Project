<?php
require_once 'includes/db.php';

session_start();

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once 'includes/db.php'; // Your PDO connection

try {
    $stmt = $pdo->query("SELECT id, name, email, profile_image FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - GeorgianBook</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>    
        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .user-table th, .user-table td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: center;
        }

        .user-table th {
            background-color: #3498db;
            color: white;
        }

        .profile-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
        }

        .btn-action {
            padding: 6px 12px;
            font-size: 14px;
            margin: 0 5px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            color: white;
        }

        .btn-edit {
            background-color: #f1c40f;
        }

        .btn-delete {
            background-color: #e74c3c;
        }

        .manage-container {
            background-color: rgba(255,255,255,0.95);
            padding: 30px;
            border-radius: 16px;
            max-width: 1000px;
            margin: 50px auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .manage-container {
        color: #2c3e50; 
}

        .user-table td,
        .user-table th {
        color: #2c3e50; 
    }

    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="background-image"></div>

<div class="manage-container">
    <h2><i class="fas fa-users-cog"></i> Manage Users</h2>

    <?php if (count($users) > 0): ?>
        <table class="user-table">
            <tr>
                <th>ID</th>
                <th>Profile</th>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td>
                        <?php if (!empty($user['profile_image']) && file_exists($user['profile_image'])): ?>
                            <img src="<?= htmlspecialchars($user['profile_image']) ?>" class="profile-thumb" alt="Profile Image">
                        <?php else: ?>
                            <span>No Image</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <?php if ($_SESSION['user']['is_admin']): ?>
                            <a href="update.php?id=<?= $user['id'] ?>" class="btn-action btn-edit"><i class="fas fa-edit"></i> Edit</a>
                            <a href="delete.php?id=<?= $user['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this user?');">
                                <i class="fas fa-trash-alt"></i> Delete
                            </a>
                        <?php else: ?>
                            <span style="color: #999;">Restricted</span>
                        <?php endif; ?>
                    </td>

                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p style="text-align:center;">No users found.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>