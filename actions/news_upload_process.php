<?php
session_start();
require_once '../db/db-config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// Establish database connection
$conn = getDatabaseConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Begin transaction
        $conn->begin_transaction();

        // Dynamically fetch the content_type_id for 'News'
        $type_query = "SELECT content_type_id FROM content_types WHERE type_name = 'News'";
        $type_result = $conn->query($type_query);
        
        // Check if the query was successful and a result was found
        if (!$type_result || $type_result->num_rows === 0) {
            throw new Exception("News content type not found in database");
        }
        
        $type_row = $type_result->fetch_assoc();
        $content_type_id = $type_row['content_type_id'];

        // Sanitize inputs
        $title = htmlspecialchars(trim($_POST['title']));
        $description = htmlspecialchars(trim($_POST['description']));
        $source = htmlspecialchars(trim($_POST['source']));
        
        // Determine group handling
        $group_id = null;
        
        // Check if a new group is being added
        if (!empty($_POST['new_group_name'])) {
            // Insert new group
            $new_group_name = htmlspecialchars(trim($_POST['new_group_name']));
            $agency = !empty($_POST['group_agency']) ? htmlspecialchars(trim($_POST['group_agency'])) : null;
            $debut_date = !empty($_POST['group_debut_date']) ? $_POST['group_debut_date'] : null;
            $group_type = !empty($_POST['group_type']) ? $_POST['group_type'] : 'Group';

            $group_insert_sql = "INSERT INTO kpop_groups (group_name, agency, debut_date, group_type) 
                                 VALUES (?, ?, ?, ?)";
            $group_stmt = $conn->prepare($group_insert_sql);
            $group_stmt->bind_param("ssss", $new_group_name, $agency, $debut_date, $group_type);
            $group_stmt->execute();
            
            $group_id = $conn->insert_id;
        } elseif (!empty($_POST['group_id'])) {
            // Use existing group
            $group_id = intval($_POST['group_id']);
        }
        
        // Insert content
        $content_sql = "INSERT INTO content (user_id, group_id, content_type_id, title, description, source_url) 
                        VALUES (?, ?, ?, ?, ?, ?)";
        
        $content_stmt = $conn->prepare($content_sql);
        $content_stmt->bind_param("iissss", 
            $_SESSION['user_id'], 
            $group_id, 
            $content_type_id, 
            $title, 
            $description, 
            $source
        );
        
        $content_stmt->execute();
        $content_id = $conn->insert_id;
        
        // Optional: Insert into news_verification
        $verification_sql = "INSERT INTO news_verification (content_id, source_url, verified_by_user_id) 
                             VALUES (?, ?, ?)";
        $verification_stmt = $conn->prepare($verification_sql);
        $verification_stmt->bind_param("isi", 
            $content_id, 
            $source, 
            $_SESSION['user_id']
        );
        $verification_stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Redirect to news page with success message
        header("Location: news.php?status=success");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction in case of error
        $conn->rollback();
        
        // Log error or display error message
        error_log("News upload error: " . $e->getMessage());
        header("Location: news_upload.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}
?>