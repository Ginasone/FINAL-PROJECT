<?php
include '../db/db-config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to edit content.");
}

$userId = $_SESSION['user_id'];
$contentId = isset($_GET['content_id']) ? intval($_GET['content_id']) : 0;

// Database connection
$conn = getDatabaseConnection();

// Fetch content details
$stmt = $conn->prepare("
    SELECT c.*, ct.type_name, kg.group_name 
    FROM content c
    JOIN content_types ct ON c.content_type_id = ct.content_type_id
    JOIN kpop_groups kg ON c.group_id = kg.group_id
    WHERE c.content_id = ? AND c.user_id = ?
");
$stmt->bind_param("ii", $contentId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Content not found or you don't have permission to edit.");
}

$content = $result->fetch_assoc();

// Fetch groups and content types for dropdowns
$groupsQuery = "SELECT * FROM kpop_groups ORDER BY group_name";
$groupsResult = $conn->query($groupsQuery);

$typesQuery = "SELECT * FROM content_types ORDER BY type_name";
$typesResult = $conn->query($typesQuery);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and process form data
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $groupId = intval($_POST['group_id']);
    $contentTypeId = intval($_POST['content_type_id']);

    // Validate inputs
    $errors = [];
    if (empty($title)) {
        $errors[] = "Title cannot be empty.";
    }

    // Handle file upload if applicable
    $videoUrl = $content['video_url'];
    $sourceUrl = $content['source_url'];

    if (!empty($_FILES['video']['name'])) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/FINAL PROJECT/assets/vid/uploads/videos/';
        $uniqueFileName = uniqid() . '.mp4';
        $uploadPath = $uploadDir . $uniqueFileName;
        
        if (move_uploaded_file($_FILES['video']['tmp_name'], $uploadPath)) {
            // If a previous video exists, delete it
            if (!empty($content['video_url'])) {
                $oldVideoPath = $_SERVER['DOCUMENT_ROOT'] . $content['video_url'];
                if (file_exists($oldVideoPath)) {
                    unlink($oldVideoPath);
                }
            }
            $videoUrl = '/FINAL PROJECT/assets/vid/uploads/videos/' . $uniqueFileName;
        } else {
            $errors[] = "Failed to upload video.";
        }
    }

    if (!empty($_POST['source_url'])) {
        $sourceUrl = filter_var($_POST['source_url'], FILTER_VALIDATE_URL);
        if ($sourceUrl === false) {
            $errors[] = "Invalid source URL.";
        }
    }

    // Before the update statement, add explicit handling of potential NULL values
$title = trim($_POST['title']);
$description = trim($_POST['description']);
$groupId = intval($_POST['group_id']);
$contentTypeId = intval($_POST['content_type_id']);

// Explicitly handle video_url and source_url
$videoUrl = !empty($videoUrl) ? $videoUrl : '';
$sourceUrl = !empty($_POST['source_url']) ? filter_var($_POST['source_url'], FILTER_VALIDATE_URL) : '';

// If no errors, update content
if (empty($errors)) {
    $updateStmt = $conn->prepare("
        UPDATE content 
        SET title = ?, description = ?, group_id = ?, 
            content_type_id = ?, video_url = ?, source_url = ? 
        WHERE content_id = ?
    ");
    
    // Ensure all parameters are strings or numeric as per the type definition
    $updateStmt->bind_param("ssiissi", 
        $title, 
        $description, 
        $groupId, 
        $contentTypeId, 
        $videoUrl, 
        $sourceUrl, 
        $contentId
    );

    if ($updateStmt->execute()) {
        $_SESSION['message'] = "Content updated successfully!";
        header("Location: user-dashboard.php");
        exit();
    } else {
        $errors[] = "Failed to update content: " . $updateStmt->error;
    }
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Content</title>
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/edit.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/FINAL PROJECT/assets/images/k-pop4life.png">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
    <header>
        <?php include 'navbar_in.php'; ?>
    </header>

    <main>
        <div class="container">
            <h1>Edit Content</h1>

            <?php 
            // Display any errors
            if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" onsubmit="this.classList.add('loading')">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" 
                           value="<?= htmlspecialchars($content['title']) ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4"><?= 
                        htmlspecialchars($content['description']) 
                    ?></textarea>
                </div>

                <div class="form-group">
                    <label>K-Pop Group</label>
                    <select name="group_id" required>
                        <?php while ($group = $groupsResult->fetch_assoc()): ?>
                            <option value="<?= $group['group_id'] ?>" 
                                <?= $group['group_id'] == $content['group_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($group['group_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Content Type</label>
                    <select name="content_type_id" required>
                        <?php while ($type = $typesResult->fetch_assoc()): ?>
                            <option value="<?= $type['content_type_id'] ?>" 
                                <?= $type['content_type_id'] == $content['content_type_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type['type_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <?php if ($content['type_name'] === 'Video' || $content['type_name'] === 'Music Video' || $content['type_name'] === 'Performance'): ?>
                <div class="form-group">
                    <label>Video File</label>
                    <input type="file" name="video" accept="video/*">
                    <?php if (!empty($content['video_url'])): ?>
                        <p>Current video: <?= htmlspecialchars(basename($content['video_url'])) ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Source URL (optional)</label>
                    <input type="url" name="source_url" 
                           value="<?= htmlspecialchars($content['source_url'] ?? '') ?>">
                </div>

                <button type="submit">Update Content</button>
            </form>
        </div>
    </main>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>