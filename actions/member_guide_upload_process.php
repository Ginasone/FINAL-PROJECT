<?php
// member_guide_upload_process.php
session_start();
require_once '../db/db-config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../view/login.php");
    exit();
}

// Get database connection
$conn = getDatabaseConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Start transaction to ensure data consistency
        $conn->begin_transaction();
        
        // Setup upload directories
        $upload_base_dir = $_SERVER['DOCUMENT_ROOT'] . '/FINAL PROJECT/assets/images/uploads/';
        $group_upload_dir = $upload_base_dir . 'groups/';
        $member_upload_dir = $upload_base_dir . 'members/';
        
        // Create directories if they don't exist
        foreach ([$upload_base_dir, $group_upload_dir, $member_upload_dir] as $dir) {
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    throw new Exception("Failed to create upload directory: $dir");
                }
            }
        }
        
        // Process group information
        if ($_POST['group_id'] === 'new') {
            // Handle new group creation
            
            // Validate required fields
            $required_fields = ['group_name', 'group_type', 'debut_date', 'agency'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("$field is required for new group");
                }
            }
            
            // Process group image if uploaded
            $group_image_url = null;
            if (isset($_FILES['group_image']) && $_FILES['group_image']['error'] === 0) {
                $group_image_url = processImageUpload($_FILES['group_image'], $group_upload_dir, 'group');
            } else {
                throw new Exception("Group image is required for new group");
            }
            
            // Sanitize inputs
            $group_name = htmlspecialchars(trim($_POST['group_name']));
            $group_type = htmlspecialchars(trim($_POST['group_type']));
            $debut_date = $_POST['debut_date'];
            $agency = htmlspecialchars(trim($_POST['agency']));
            $fandom = !empty($_POST['fandom']) ? htmlspecialchars(trim($_POST['fandom'])) : null;
            
            // Insert new group
            $stmt = $conn->prepare("
                INSERT INTO kpop_groups (
                    group_name, group_type, debut_date, agency, fandom, group_image_url
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->bind_param("ssssss", 
                $group_name, 
                $group_type, 
                $debut_date, 
                $agency, 
                $fandom, 
                $group_image_url
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create new group: " . $stmt->error);
            }
            
            // Get the new group ID
            $group_id = $conn->insert_id;
        } else {
            // Use existing group
            $group_id = intval($_POST['group_id']);
            
            // Verify group exists
            $check_stmt = $conn->prepare("SELECT group_id FROM kpop_groups WHERE group_id = ?");
            $check_stmt->bind_param("i", $group_id);
            $check_stmt->execute();
            
            if ($check_stmt->get_result()->num_rows === 0) {
                throw new Exception("Selected group does not exist");
            }
        }
        
        // Process guide content
        $guide_title = htmlspecialchars(trim($_POST['guide_title']));
        $guide_content = htmlspecialchars(trim($_POST['guide_content']));
        
        // Get content type ID for Member Guide
        $type_query = "SELECT content_type_id FROM content_types WHERE type_name = 'Member Guide'";
        $type_result = $conn->query($type_query);
        
        if (!$type_result || $type_result->num_rows === 0) {
            throw new Exception("Member Guide content type not found");
        }
        
        $content_type_id = $type_result->fetch_assoc()['content_type_id'];
        
        // Insert guide content
        $content_stmt = $conn->prepare("
            INSERT INTO content (
                user_id, group_id, content_type_id, title, description
            ) VALUES (?, ?, ?, ?, ?)
        ");
        
        $content_stmt->bind_param("iiiss", 
            $_SESSION['user_id'], 
            $group_id, 
            $content_type_id, 
            $guide_title, 
            $guide_content
        );
        
        if (!$content_stmt->execute()) {
            throw new Exception("Failed to create guide content: " . $content_stmt->error);
        }
        
        $content_id = $conn->insert_id;
        
        // Process members
        if (!isset($_POST['members']) || !is_array($_POST['members']) || count($_POST['members']) === 0) {
            throw new Exception("At least one member is required");
        }
        
        foreach ($_POST['members'] as $index => $member) {
            // Validate required fields
            $required_member_fields = ['stage_name', 'birth_name', 'birthday', 'nationality'];
            foreach ($required_member_fields as $field) {
                if (empty($member[$field])) {
                    throw new Exception("$field is required for member #" . ($index + 1));
                }
            }
            
            // Process member image
            $member_image_url = null;
            if (isset($_FILES['members']['name'][$index]['photo']) && 
                $_FILES['members']['error'][$index]['photo'] === 0) {
                
                // Create a file array structure similar to a single file upload
                $file = [
                    'name' => $_FILES['members']['name'][$index]['photo'],
                    'type' => $_FILES['members']['type'][$index]['photo'],
                    'tmp_name' => $_FILES['members']['tmp_name'][$index]['photo'],
                    'error' => $_FILES['members']['error'][$index]['photo'],
                    'size' => $_FILES['members']['size'][$index]['photo']
                ];
                
                $member_image_url = processImageUpload($file, $member_upload_dir, 'member');
            } else {
                throw new Exception("Photo is required for member #" . ($index + 1));
            }
            
            // Sanitize member inputs
            $stage_name = htmlspecialchars(trim($member['stage_name']));
            $birth_name = htmlspecialchars(trim($member['birth_name']));
            $birthday = $member['birthday'];
            $nationality = htmlspecialchars(trim($member['nationality']));
            $position = !empty($member['position']) ? htmlspecialchars(trim($member['position'])) : null;
            
            // Insert member
            $member_stmt = $conn->prepare("
                INSERT INTO group_members (
                    group_id, stage_name, real_name, birth_date, nationality, position, member_image_url
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $member_stmt->bind_param("issssss", 
                $group_id, 
                $stage_name, 
                $birth_name, 
                $birthday, 
                $nationality, 
                $position, 
                $member_image_url
            );
            
            if (!$member_stmt->execute()) {
                throw new Exception("Failed to add member: " . $member_stmt->error);
            }
        }
        
        // All successful, commit the transaction
        $conn->commit();
        
        // Redirect to success page
        header("Location: ../view/member_guides.php?status=success");
        exit();
        
    } catch (Exception $e) {
        // An error occurred, rollback changes
        $conn->rollback();
        
        // Log the error
        error_log("Member guide upload error: " . $e->getMessage());
        
        // Redirect with error message
        header("Location: ../view/member_guide_upload.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}

/**
 * Process image upload
 *
 * @param array $file The file data from $_FILES
 * @param string $upload_dir Directory to upload to
 * @param string $prefix Prefix for the filename
 * @return string The URL path to the uploaded image
 * @throws Exception If upload fails
 */
function processImageUpload($file, $upload_dir, $prefix = 'image') {
    // Check if file is actually uploaded
    if ($file['error'] !== 0) {
        throw new Exception("File upload failed with error code: " . $file['error']);
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception("Invalid file type. Only JPG, PNG, and WebP are allowed.");
    }
    
    // Validate file size
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        throw new Exception("File size must be less than 5MB.");
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = $prefix . '_' . uniqid() . '.' . $extension;
    $upload_path = $upload_dir . $new_filename;
    
    // Move the uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception("Failed to save uploaded file.");
    }
    
    // Return the relative URL path
    return '/FINAL PROJECT/assets/images/uploads/' . ($prefix === 'group' ? 'groups/' : 'members/') . $new_filename;
}