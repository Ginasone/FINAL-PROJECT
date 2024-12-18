<?php
session_start();
include '../db/db-config.php';

$connection = getDatabaseConnection();

// Fetch K-pop groups for dropdown
$groupsSql = "SELECT group_id, group_name FROM kpop_groups ORDER BY group_name";
$groupsResult = $connection->query($groupsSql);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Start a transaction to ensure data consistency
    $connection->begin_transaction();

    try {
        // Check if it's a new group
        $group_id = $_POST['group_id'];
        if ($group_id == 'new') {
            // Insert new group
            $new_group_name = $_POST['new_group_name'];
            $debut_date = $_POST['debut_date'];
            $agency = $_POST['agency'];
            $group_type = $_POST['group_type'];
            $fandom = $_POST['fandom'] ?? null;

            $groupInsertSql = "INSERT INTO kpop_groups 
                               (group_name, debut_date, agency, group_type, fandom) 
                               VALUES (?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($groupInsertSql);
            $stmt->bind_param("ssssi", $new_group_name, $debut_date, $agency, $group_type, $fandom);
            $stmt->execute();
            
            // Get the newly inserted group ID
            $group_id = $connection->insert_id;
        }

        // Insert group members if provided
        if (!empty($_POST['stage_names'])) {
            $memberInsertSql = "INSERT INTO group_members 
                                (group_id, stage_name, real_name, birth_date, nationality, position) 
                                VALUES (?, ?, ?, ?, ?, ?)";
            $memberStmt = $connection->prepare($memberInsertSql);

            foreach ($_POST['stage_names'] as $index => $stage_name) {
                if (!empty($stage_name)) {
                    $real_name = $_POST['real_names'][$index];
                    $birth_date = $_POST['birth_dates'][$index];
                    $nationality = $_POST['nationalities'][$index];
                    $position = $_POST['positions'][$index];

                    $memberStmt->bind_param("isssss", 
                        $group_id, 
                        $stage_name, 
                        $real_name, 
                        $birth_date, 
                        $nationality, 
                        $position
                    );
                    $memberStmt->execute();
                }
            }
        }

        // Get Member Guide content type ID
        $contentTypeSql = "SELECT content_type_id FROM content_types WHERE type_name = 'Member Guide'";
        $contentTypeResult = $connection->query($contentTypeSql);
        $contentType = $contentTypeResult->fetch_assoc();

        // Insert member guide
        $title = $_POST['title'];
        $description = $_POST['description'];
        $video_url = $_POST['video_url'] ?? null;

        $insertSql = "INSERT INTO content 
                      (user_id, group_id, content_type_id, title, description, video_url) 
                      VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $connection->prepare($insertSql);
        $stmt->bind_param("iiiiss", 
            $_SESSION['user_id'], 
            $group_id, 
            $contentType['content_type_id'], 
            $title, 
            $description, 
            $video_url
        );

        if ($stmt->execute()) {
            $connection->commit();
            $successMessage = "Group, Members, and Member Guide uploaded successfully!";
        } else {
            throw new Exception("Error uploading content: " . $stmt->error);
        }
    } catch (Exception $e) {
        $connection->rollback();
        $errorMessage = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Member Guide</title>
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/member-guide-upload.css">
    <link rel="icon" type="image/x-icon" href="/FINAL PROJECT/assets/images/k-pop4life.png">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
    <header>
        <?php include 'navbar_in.php'; ?>
    </header>

    <main class="container">
        <h1>Upload Member Guide</h1>

        <?php if (isset($successMessage)): ?>
            <div class="success-message"><?php echo $successMessage; ?></div>
        <?php endif; ?>

        <?php if (isset($errorMessage)): ?>
            <div class="error-message"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <form method="POST" class="upload-form">
            <div class="form-group">
                <label for="group_id">K-pop Group</label>
                <select id="group_id" name="group_id" required onchange="toggleNewGroupSection()">
                    <?php while ($group = $groupsResult->fetch_assoc()): ?>
                        <option value="<?php echo $group['group_id']; ?>">
                            <?php echo htmlspecialchars($group['group_name']); ?>
                        </option>
                    <?php endwhile; ?>
                    <option value="new">+ Create New Group</option>
                </select>
            </div>

            <div id="newGroupSection" style="display:none;" class="new-group-section">
                <h3>New Group Details</h3>
                <div class="form-group">
                    <label>Group Name</label>
                    <input type="text" name="new_group_name">
                </div>
                <div class="form-group">
                    <label>Debut Date</label>
                    <input type="date" name="debut_date">
                </div>
                <div class="form-group">
                    <label>Agency</label>
                    <input type="text" name="agency">
                </div>
                <div class="form-group">
                    <label>Group Type</label>
                    <select name="group_type">
                        <option value="Group">Group</option>
                        <option value="Soloist">Soloist</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="title">Guide Title</label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="description">Guide Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>

            <div class="form-group">
                <button type="button" onclick="addMemberInput()" class="btn btn-secondary">
                    Add Group Member
                </button>
            </div>

            <div id="memberContainer"></div>

            <div class="form-group">
                <label for="video_url">Optional Video URL</label>
                <input type="url" id="video_url" name="video_url">
            </div>

            <div class="form-group">
                <label>Fandom (Optional)</label>
                <input type="text" name="fandom">
            </div>

            <button type="submit" class="btn btn-primary">Upload Member Guide</button>
        </form>
    </main>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>
    <script src="../assets/js/groups.js"></script>
</body>
</html>
<?php 
closeDatabaseConnection($connection);
?>