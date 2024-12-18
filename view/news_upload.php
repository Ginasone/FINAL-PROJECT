<?php
session_start();
require_once '../db/db-config.php';

$conn = getDatabaseConnection();

?>


<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="/FINAL PROJECT/assets/css/style.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" href="/FINAL PROJECT/assets/css/news-upload.css">
        <title>News Upload</title>
        <link rel="icon" type="image/x-icon" href="/FINAL PROJECT/assets/images/k-pop4life.png">
        <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>

    <body>
        <header>
            <?php include 'navbar_in.php'; ?>
        </header>

        <section class="news-upload">
            <form class="form" method="post" action="../actions/news_upload_process.php">
                <h1>Upload and Share What Is Happening In the K-pop World!</h1>

                <div class="input-field">     
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" required maxlength="255">
                </div>

                <div class="input-field">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required maxlength="1000"></textarea>
                </div>

                <div class="input-field">
                    <label for="source">Source URL:</label>
                    <input type="url" id="source" name="source" required>
                </div>

                <div class="input-field">
                    <label for="group">Related K-pop Group:</label>
                    <select name="group_id" id="group" onchange="toggleGroupInput()">
                        <option value="">Select a Group (Optional)</option>
                        <?php
                        // Populate with existing groups from database
                        $groups_query = "SELECT group_id, group_name FROM kpop_groups ORDER BY group_name";
                        $groups_result = $conn->query($groups_query);
                        while ($group = $groups_result->fetch_assoc()) {
                            echo "<option value='" . $group['group_id'] . "'>" . htmlspecialchars($group['group_name']) . "</option>";
                        }
                        ?>
                        <option value="new">+ Add New Group</option>
                    </select>
                </div>

                <div id="new-group-container" style="display:none;" class="input-field">
                    <label for="new_group_name">New Group Name:</label>
                    <input type="text" id="new_group_name" name="new_group_name" maxlength="50">
                    
                    <div class="additional-group-info">
                        <label for="group_agency">Agency:</label>
                        <input type="text" id="group_agency" name="group_agency" maxlength="100">
                        
                        <label for="group_debut_date">Debut Date:</label>
                        <input type="date" id="group_debut_date" name="group_debut_date">
                        
                        <label for="group_type">Group Type:</label>
                        <select name="group_type" id="group_type">
                            <option value="Group">Group</option>
                            <option value="Soloist">Soloist</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="submit-btn">Upload News</button>
            </form>
        </section>

        <footer>
          <?php include 'footer.php'; ?>
        </footer>
        <script src="../assets/js/new-group.js"></script>
    </body>
</html>