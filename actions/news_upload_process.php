<?php
// news_upload_process.php
session_start();
require_once '../db/db-config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDatabaseConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Begin transaction
        $conn->begin_transaction();

        // Get content type ID for News
        $type_query = "SELECT content_type_id FROM content_types WHERE type_name = 'News'";
        $type_result = $conn->query($type_query);
        
        if (!$type_result || $type_result->num_rows === 0) {
            throw new Exception("News content type not found in database");
        }
        
        $type_row = $type_result->fetch_assoc();
        $content_type_id = $type_row['content_type_id'];

        // Sanitize inputs
        $title = htmlspecialchars(trim($_POST['title']));
        $preview = htmlspecialchars(trim($_POST['preview']));
        $full_content = htmlspecialchars(trim($_POST['full_content']));
        $source = htmlspecialchars(trim($_POST['source']));
        
        // Handle group_id
        $group_id = !empty($_POST['group_id']) ? intval($_POST['group_id']) : null;
        
        // Insert content
        $content_sql = "INSERT INTO content (
            user_id, 
            group_id, 
            content_type_id, 
            title, 
            description,
            preview_text, 
            source_url
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $content_stmt = $conn->prepare($content_sql);
        $content_stmt->bind_param("iiissss", 
            $_SESSION['user_id'], 
            $group_id, 
            $content_type_id, 
            $title, 
            $full_content,
            $preview, 
            $source
        );
        
        $content_stmt->execute();
        $content_id = $conn->insert_id;
        
        // Optional: Insert into news_verification if needed
        $verification_sql = "INSERT INTO news_verification (
            content_id, 
            source_url, 
            verified_by_user_id
        ) VALUES (?, ?, ?)";
        
        $verification_stmt = $conn->prepare($verification_sql);
        $verification_stmt->bind_param("isi", 
            $content_id, 
            $source, 
            $_SESSION['user_id']
        );
        $verification_stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Redirect with success message
        header("Location: ../view/news.php?status=success");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("News upload error: " . $e->getMessage());
        header("Location: ../view/news_upload.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}
?>