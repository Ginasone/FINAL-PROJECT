<?php
session_start();
require_once '../db/db-config.php';

// Establish database connection
$conn = getDatabaseConnection();

?>


<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="/FINAL PROJECT/assets/css/style.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" href="/FINAL PROJECT/assets/css/video-upload.css">
        <title>Video Upload</title>
        <link rel="icon" type="image/x-icon" href="/FINAL PROJECT/assets/images/k-pop4life.png">
        <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>

    <body>
        <header>
            <?php include 'navbar_in.php'; ?>
        </header>

        <main>
            <div class="container">
                <h1>Upload Video</h1>
                <form action="../actions/video_upload_process.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="form-group">
                        <label for="title">Video Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>

                    <div class="form-group">
                        <label>K-Pop Group</label>
                        <div class="group-selection">
                            <select id="existing_group" name="existing_group">
                                <option value="">Select Existing Group</option>
                                <?php
                                require_once '../db/db-config.php';
                                
                                // Get database connection
                                $conn = getDatabaseConnection();

                                // Retrieve groups
                                $sql = "SELECT group_id, group_name FROM kpop_groups ORDER BY group_name";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='" . $row['group_id'] . "'>" . 
                                             htmlspecialchars($row['group_name']) . "</option>";
                                    }
                                }

                                // Close result and connection
                                $result->free();
                                closeDatabaseConnection($conn);
                                ?>
                                <option value="new">+ Add New Group</option>
                            </select>

                            <div id="new_group_fields" style="display:none;">
                                <input type="text" id="new_group_name" name="new_group_name" placeholder="New Group Name">
                                
                                <div class="new-group-details">
                                    <input type="date" id="debut_date" name="debut_date" placeholder="Debut Date">
                                    
                                    <input type="text" id="agency" name="agency" placeholder="Agency">
                                    
                                    <select name="group_type">
                                        <option value="Group">Group</option>
                                        <option value="Soloist">Soloist</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="content_type">Content Type</label>
                        <select id="content_type" name="content_type" required>
                            <?php
                            // Reopen connection
                            $conn = getDatabaseConnection();

                            // Retrieve content types
                            $sql = "SELECT content_type_id, type_name FROM content_types";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . $row['content_type_id'] . "'>" . 
                                         htmlspecialchars($row['type_name']) . "</option>";
                                }
                            } else {
                                echo "<option>No content types found</option>";
                            }

                            // Close result and connection
                            $result->free();
                            closeDatabaseConnection($conn);
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="video">Upload Video</label>
                        <input type="file" id="video" name="video" accept=".mp4,.avi,.mov,.wmv" required>
                    </div>

                    <button type="submit">Upload Video</button>
                </form>
            </div>
        </main>

        <footer>
            <?php include 'footer.php'; ?>
        </footer>

        <script src="../assets/js/new-group-video.js"></script>
    </body>
</html>