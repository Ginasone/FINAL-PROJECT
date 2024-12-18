<?php
require_once '../../db/db-config.php';
require_once '../../actions/admin-actions.php';
require_once '../../utils/email-utils.php';


// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
    die("Access denied. Admin privileges required.");
}

// Fetch all users
$conn = getDatabaseConnection();
$usersQuery = "
    SELECT u.*, COUNT(c.content_id) as content_count 
    FROM users u 
    LEFT JOIN content c ON u.user_id = c.user_id 
    WHERE u.role = 1 
    GROUP BY u.user_id 
";
$usersResult = $conn->query($usersQuery);

// Fetch all content with user details
$contentQuery = "
    SELECT c.*, u.username, u.email, ct.type_name, kg.group_name 
    FROM content c
    JOIN users u ON c.user_id = u.user_id
    JOIN content_types ct ON c.content_type_id = ct.content_type_id
    JOIN kpop_groups kg ON c.group_id = kg.group_id
";
$contentResult = $conn->query($contentQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/style.css">
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/admin-dashboard.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <header>
        <?php include 'admin-navbar.php'; ?>
    </header>

    <main class="container">
        <h1>Admin Dashboard</h1>

        <section>
            <h2>User Management</h2>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Content Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $usersResult->fetch_assoc()): ?>
                    <tr>
                        <td><?= $user['user_id'] ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><span class="content-count"><?= $user['content_count'] ?></span></td>
                        <td>
                            <a href="/FINAL PROJECT/actions/admin-actions.php?action=email_user&user_id=<?= $user['user_id'] ?>" 
                               class="action-btn">Email</a>
                            <a href="/FINAL PROJECT/actions/admin-actions.php?action=delete_user&user_id=<?= $user['user_id'] ?>" 
                               class="action-btn delete-btn" 
                               onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <section>
            <h2>Content Management</h2>
            <table>
                <thead>
                    <tr>
                        <th>Content ID</th>
                        <th>Title</th>
                        <th>User</th>
                        <th>Group</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($content = $contentResult->fetch_assoc()): ?>
                    <tr>
                        <td><?= $content['content_id'] ?></td>
                        <td><?= htmlspecialchars($content['title']) ?></td>
                        <td><?= htmlspecialchars($content['username']) ?></td>
                        <td><?= htmlspecialchars($content['group_name']) ?></td>
                        <td><?= htmlspecialchars($content['type_name']) ?></td>
                        <td>
                            <a href="/FINAL PROJECT/actions/admin-actions.php?action=review_content&content_id=<?= $content['content_id'] ?>" 
                               class="action-btn">Review</a>
                            <a href="/FINAL PROJECT/actions/admin-actions.php?action=delete_content&content_id=<?= $content['content_id'] ?>" 
                               class="action-btn delete-btn" 
                               onclick="return confirm('Are you sure you want to delete this content?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>
</body>
</html>