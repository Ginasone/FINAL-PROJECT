<?php
include '../db/db-config.php';
$dbConnection = getDatabaseConnection();
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    die("You must be logged in to view your dashboard.");
}

$userId = $_SESSION['user_id']; 

// Safely check the role with a default value
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : null;

// Check if the user has the correct role
if ($userRole !== 1) {
    die("You do not have permission to access this page.");
}

// Handle content deletion
if (isset($_GET['delete']) && isset($_GET['content_id'])) {
    $contentId = intval($_GET['content_id']);
    
    // Prepare delete statement
    $deleteStmt = $dbConnection->prepare("DELETE FROM content WHERE content_id = ? AND user_id = ?");
    $deleteStmt->bind_param("ii", $contentId, $userId);
    
    if ($deleteStmt->execute()) {
        $_SESSION['message'] = "Content deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting content.";
    }
    
    $deleteStmt->close();
    
    // Redirect to avoid form resubmission
    header("Location: user-dashboard.php");
    exit();
}

// Retrieve user's uploaded content
$contentQuery = "
    SELECT c.content_id, c.title, c.description, c.upload_date, 
           ct.type_name, kg.group_name, 
           c.video_url, c.source_url
    FROM content c
    JOIN content_types ct ON c.content_type_id = ct.content_type_id
    JOIN kpop_groups kg ON c.group_id = kg.group_id
    WHERE c.user_id = ?
    ORDER BY c.upload_date DESC
";

$contentStmt = $dbConnection->prepare($contentQuery);
$contentStmt->bind_param("i", $userId);
$contentStmt->execute();
$contentResult = $contentStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Dashboard</title>
        <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" href="../assets/css/user-dashboard.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
        <link rel="icon" type="image/x-icon" href="/FINAL PROJECT/assets/images/k-pop4life.png">
        <style>
            .content-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
                padding: 20px;
            }
            .content-card {
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 15px;
                background-color: #f9f9f9;
            }
            .content-actions {
                display: flex;
                justify-content: space-between;
                margin-top: 10px;
            }
            .btn {
                display: inline-block;
                padding: 5px 10px;
                text-decoration: none;
                border-radius: 4px;
            }
            .btn-edit {
                background-color: #4CAF50;
                color: white;
            }
            .btn-delete {
                background-color: #f44336;
                color: white;
            }
        </style>
    </head>
    <body>
    <header>
        <?php include 'navbar_in.php'; ?>
    </header>
    <main>
        <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>
        
        <?php
        // Display success or error messages
        if (isset($_SESSION['message'])) {
            echo "<div class='alert alert-success'>" . htmlspecialchars($_SESSION['message']) . "</div>";
            unset($_SESSION['message']);
        }
        if (isset($_SESSION['error'])) {
            echo "<div class='alert alert-danger'>" . htmlspecialchars($_SESSION['error']) . "</div>";
            unset($_SESSION['error']);
        }
        ?>
        
        <h2>Your Uploaded Content</h2>
        
        <?php if ($contentResult->num_rows > 0): ?>
            <div class="content-grid">
                <?php while ($content = $contentResult->fetch_assoc()): ?>
                    <div class="content-card">
                        <h3><?= htmlspecialchars($content['title']) ?></h3>
                        <p><strong>Type:</strong> <?= htmlspecialchars($content['type_name']) ?></p>
                        <p><strong>Group:</strong> <?= htmlspecialchars($content['group_name']) ?></p>
                        <p><strong>Description:</strong> <?= htmlspecialchars($content['description']) ?></p>
                        <p><strong>Uploaded:</strong> <?= date('F j, Y, g:i a', strtotime($content['upload_date'])) ?></p>
                        
                        <?php if (!empty($content['video_url'])): ?>
                            <p><strong>Video:</strong> <?= htmlspecialchars(basename($content['video_url'])) ?></p>
                        <?php endif; ?>
                        
                        <?php if (!empty($content['source_url'])): ?>
                            <p><strong>Source URL:</strong> <?= htmlspecialchars($content['source_url']) ?></p>
                        <?php endif; ?>
                        
                        <div class="content-actions">
                            <a href="edit-content.php?content_id=<?= $content['content_id'] ?>" class="btn btn-edit">
                                <i class="material-symbols-outlined">edit</i> Edit
                            </a>
                            <a href="user-dashboard.php?delete=1&content_id=<?= $content['content_id'] ?>" 
                               class="btn btn-delete" 
                               onclick="return confirm('Are you sure you want to delete this content?');">
                                <i class="material-symbols-outlined">delete</i> Delete
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>You haven't uploaded any content yet.</p>
        <?php endif; ?>
        
    </main>
    <footer>
        <?php include 'footer.php'; ?>
    </footer>
    </body>
</html>

<?php
// Close database connection
$contentStmt->close();
$dbConnection->close();
?>