<?php
session_start();
require_once '../db/db-config.php';

// Get database connection
$conn = getDatabaseConnection();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $title = $conn->real_escape_string(trim($_POST['title']));
    $description = $conn->real_escape_string(trim($_POST['description']));
    $content_type_id = intval($_POST['content_type']);
    
    // Check user is logged in
    if (!isset($_SESSION['user_id'])) {
        die("You must be logged in to upload content.");
    }
    $user_id = intval($_SESSION['user_id']);

    // Determine group_id
    $group_id = null;

    // Check if it's a new group or existing group
    if (isset($_POST['existing_group']) && $_POST['existing_group'] === 'new') {
        // Create new group
        $new_group_name = $conn->real_escape_string(trim($_POST['new_group_name']));
        $debut_date = $conn->real_escape_string(trim($_POST['debut_date']));
        $agency = $conn->real_escape_string(trim($_POST['agency']));
        $group_type = $conn->real_escape_string(trim($_POST['group_type']));

        // Prepare and execute insert for new group
        $group_sql = "INSERT INTO kpop_groups (group_name, debut_date, agency, group_type) 
                      VALUES (?, ?, ?, ?)";
        
        $group_stmt = $conn->prepare($group_sql);
        $group_stmt->bind_param("ssss", 
            $new_group_name, 
            $debut_date, 
            $agency, 
            $group_type
        );

        if ($group_stmt->execute()) {
            // Get the ID of the newly inserted group
            $group_id = $group_stmt->insert_id;
            $group_stmt->close();
        } else {
            die("Error creating new group: " . $group_stmt->error);
        }
    } else {
        // Use existing group
        $group_id = intval($_POST['existing_group']);
    }

    
    // Handle file upload
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        $allowed = ['mp4', 'avi', 'mov', 'wmv'];
        $filename = $_FILES['video']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        // Validate file type
        if (!in_array(strtolower($filetype), $allowed)) {
            die("Error: Invalid file type. Allowed types: " . implode(', ', $allowed));
        }

        // Create unique filename
        $new_filename = uniqid() . '.' . $filetype;
        $upload_dir = '/FINAL PROJECT/assets/vid/uploads/videos/';
        $full_upload_path = $_SERVER['DOCUMENT_ROOT'] . $upload_dir;
        $full_file_path = $full_upload_path . $new_filename;

        // Create directory if it doesn't exist
        if (!is_dir($full_upload_path)) {
            mkdir($full_upload_path, 0755, true);
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES['video']['tmp_name'], $full_file_path)) {
            // Construct full video URL
            $video_url = $conn->real_escape_string($upload_dir . $new_filename);

            // Prepare SQL statement for content
            $sql = "INSERT INTO content 
                    (user_id, group_id, content_type_id, title, description, video_url) 
                    VALUES 
                    (?, ?, ?, ?, ?, ?)";
            
            // Prepare and bind
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissss", 
                $user_id, 
                $group_id, 
                $content_type_id, 
                $title, 
                $description, 
                $video_url
            );

            // Execute statement
            if ($stmt->execute()) {
                // Close statement and connection
                $stmt->close();
                closeDatabaseConnection($conn);

                // Redirect to videos page with success message
                header("Location: videos.php?upload=success");
                exit();
            } else {
                die("ERROR: Could not execute upload. " . $stmt->error);
            }
        } else {
            die("Error uploading file.");
        }
    } else {
        die("No file uploaded or upload error occurred.");
    }
}

// Close connection if not already closed
closeDatabaseConnection($conn);
?>