<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/FINAL PROJECT/db/db-config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/FINAL PROJECT/utils/email-utils.php';


function isAdmin($userId) {
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        return $user['role'] == 2; // Admin role is 2
    }
    return false;
}

// Ensure only admins can perform actions
if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
    die("Access denied. Admin privileges required.");
}

if (isset($_GET['action'])) {
    $conn = getDatabaseConnection();

    switch ($_GET['action']) {
        case 'email_user':
            $userId = intval($_GET['user_id']);
            $stmt = $conn->prepare("SELECT email, username FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                $subject = "Content Review Request";
                $body = "
                <p>Dear " . htmlspecialchars($user['username']) . ",</p>
                <p>An administrator would like to review your content. Please check your dashboard for more information.</p>
                <br>
                <p>Best regards,<br>K-Pop Content Admin Team</p>
                ";
                
                sendAdminEmail($user['email'], $subject, $body);
                $_SESSION['message'] = "Email sent to user successfully.";
            }
            break;

        case 'delete_content':
            $contentId = intval($_GET['content_id']);
            
            // First, fetch content details for notification
            $stmt = $conn->prepare("
                SELECT c.title, u.email, c.video_url 
                FROM content c 
                JOIN users u ON c.user_id = u.user_id 
                WHERE c.content_id = ?
            ");
            $stmt->bind_param("i", $contentId);
            $stmt->execute();
            $result = $stmt->get_result();
            $content = $result->fetch_assoc();

            // Delete video file if exists
            if (!empty($content['video_url'])) {
                $videoPath = $_SERVER['DOCUMENT_ROOT'] . $content['video_url'];
                if (file_exists($videoPath)) {
                    unlink($videoPath);
                }
            }

            // Delete from database
            $deleteStmt = $conn->prepare("DELETE FROM content WHERE content_id = ?");
            $deleteStmt->bind_param("i", $contentId);
            $deleteStmt->execute();

            // Notify user
            notifyUserContentDeleted($content['email'], $content['title']);
            
            $_SESSION['message'] = "Content deleted successfully.";
            break;

        case 'delete_user':
            $userId = intval($_GET['user_id']);
            
            // Fetch user email for notification
            $stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            // Delete user's content videos
            $videoStmt = $conn->prepare("SELECT video_url FROM content WHERE user_id = ?");
            $videoStmt->bind_param("i", $userId);
            $videoStmt->execute();
            $videoResult = $videoStmt->get_result();

            while ($video = $videoResult->fetch_assoc()) {
                if (!empty($video['video_url'])) {
                    $videoPath = $_SERVER['DOCUMENT_ROOT'] . $video['video_url'];
                    if (file_exists($videoPath)) {
                        unlink($videoPath);
                    }
                }
            }

            // Delete user's content
            $conn->query("DELETE FROM content WHERE user_id = $userId");
            
            // Delete user
            $deleteStmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $deleteStmt->bind_param("i", $userId);
            $deleteStmt->execute();

            // Notify user
            notifyUserAccountDeleted($user['email']);
            
            $_SESSION['message'] = "User and all associated content deleted successfully.";
            break;
    }

    // Redirect back to admin dashboard
    header("Location: ../view/admin/admin-dashboard.php");
    exit();
}
?>