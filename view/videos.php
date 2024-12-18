<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="/FINAL PROJECT/assets/css/style.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" href="/FINAL PROJECT/assets/css/videos.css">
        <title>Videos</title>
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
                <h1>K-Pop Videos</h1>

                <?php
                require_once '../db/db-config.php';

                // Check if download is requested
                if (isset($_GET['download']) && isset($_GET['content_id'])) {
                    $content_id = intval($_GET['content_id']);
                    
                    // Get database connection
                    $conn = getDatabaseConnection();

                    // Prepare SQL to get video file path
                    $stmt = $conn->prepare("SELECT video_url, title FROM content WHERE content_id = ?");
                    $stmt->bind_param("i", $content_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $video = $result->fetch_assoc();
                        $file_path = $_SERVER['DOCUMENT_ROOT'] . $video['video_url'];

                        // Check if file exists
                        if (file_exists($file_path)) {
                            // Prepare file for download
                            header('Content-Description: File Transfer');
                            header('Content-Type: application/octet-stream');
                            header('Content-Disposition: attachment; filename="' . basename($video['title']) . '.mp4"');
                            header('Expires: 0');
                            header('Cache-Control: must-revalidate');
                            header('Pragma: public');
                            header('Content-Length: ' . filesize($file_path));
                            
                            // Clear output buffer
                            ob_clean();
                            flush();
                            
                            // Read and output file
                            readfile($file_path);
                            exit;
                        } else {
                            echo "File not found.";
                        }

                        $stmt->close();
                    }
                    
                    closeDatabaseConnection($conn);
                    exit;
                }

                // Get database connection
                $conn = getDatabaseConnection();

                // Retrieve videos with group and content type information
                // Filter for only Music Video, Performance, and Concert types
                $sql = "
                    SELECT c.*, 
                           kg.group_name, 
                           ct.type_name, 
                           u.username 
                    FROM content c
                    JOIN kpop_groups kg ON c.group_id = kg.group_id
                    JOIN content_types ct ON c.content_type_id = ct.content_type_id
                    JOIN users u ON c.user_id = u.user_id
                    WHERE ct.type_name IN ('Music Video', 'Performance', 'Concert')
                    AND c.video_url IS NOT NULL 
                    AND c.video_url != ''
                    ORDER BY c.upload_date DESC
                ";

                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    echo "<div class='video-grid'>";
                    while ($video = $result->fetch_assoc()) {
                        echo "<div class='video-card'>";
                        echo "<video controls>";
                        echo "<source src='" . htmlspecialchars($video['video_url']) . "' type='video/mp4'>";
                        echo "Your browser does not support the video tag.";
                        echo "</video>";
                        echo "<div class='video-info'>";
                        echo "<h3>" . htmlspecialchars($video['title']) . "</h3>";
                        echo "<p>Group: " . htmlspecialchars($video['group_name']) . "</p>";
                        echo "<p>Type: " . htmlspecialchars($video['type_name']) . "</p>";
                        echo "<p>Uploaded by: " . htmlspecialchars($video['username']) . "</p>";
                        echo "<p>Description: " . htmlspecialchars($video['description']) . "</p>";
                        
                        // Add download button
                        echo "<a href='videos.php?download=1&content_id=" . $video['content_id'] . "' class='download-btn'>
                                <i class='material-symbols-outlined'>download</i> Download
                              </a>";
                        
                        echo "</div>";
                        echo "</div>";
                    }
                    echo "</div>";
                } else {
                    echo "<p>No videos have been uploaded yet.</p>";
                }

                // Close result and connection
                $result->free();
                closeDatabaseConnection($conn);
                ?>
            </div>
        </main>

        <footer>
            <?php include 'footer.php'; ?>
        </footer>
    </body>
</html>