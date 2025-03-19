<?php
// video-detail.php
session_start();
require_once '../db/db-config.php';

$conn = getDatabaseConnection();
$video_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$video_id) {
    header('Location: videos.php');
    exit;
}

// Fetch the video details
$video_query = "
    SELECT c.*, 
           kg.group_name, 
           ct.type_name, 
           u.username,
           u.user_id
    FROM content c
    JOIN content_types ct ON c.content_type_id = ct.content_type_id
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN kpop_groups kg ON c.group_id = kg.group_id
    WHERE c.content_id = ?
";

$stmt = $conn->prepare($video_query);
$stmt->bind_param("i", $video_id);
$stmt->execute();
$video = $stmt->get_result()->fetch_assoc();

if (!$video) {
    header('Location: videos.php');
    exit;
}

// Fetch recommended videos - same group
$same_group_query = "
    SELECT c.*, 
           kg.group_name, 
           ct.type_name, 
           u.username
    FROM content c
    JOIN content_types ct ON c.content_type_id = ct.content_type_id
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN kpop_groups kg ON c.group_id = kg.group_id
    WHERE (ct.type_name = 'Music Video' OR ct.type_name = 'Performance' OR ct.type_name = 'Concert')
    AND c.video_url IS NOT NULL 
    AND c.video_url != ''
    AND c.content_id != ?
    AND c.group_id = ?
    ORDER BY RAND()
    LIMIT 5
";

$stmt = $conn->prepare($same_group_query);
$stmt->bind_param("ii", $video_id, $video['group_id']);
$stmt->execute();
$same_group_videos = $stmt->get_result();

// Fetch recommended videos - same type
$same_type_query = "
    SELECT c.*, 
           kg.group_name, 
           ct.type_name, 
           u.username
    FROM content c
    JOIN content_types ct ON c.content_type_id = ct.content_type_id
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN kpop_groups kg ON c.group_id = kg.group_id
    WHERE ct.content_type_id = ?
    AND c.video_url IS NOT NULL 
    AND c.video_url != ''
    AND c.content_id != ?
    AND (c.group_id != ? OR c.group_id IS NULL)
    ORDER BY RAND()
    LIMIT 5
";

$stmt = $conn->prepare($same_type_query);
$stmt->bind_param("iii", $video['content_type_id'], $video_id, $video['group_id']);
$stmt->execute();
$same_type_videos = $stmt->get_result();

// Increment view count if needed
// You would need to add a views column to the content table
// $update_views = "UPDATE content SET views = views + 1 WHERE content_id = ?";
// $stmt = $conn->prepare($update_views);
// $stmt->bind_param("i", $video_id);
// $stmt->execute();
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/style.css">
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/video-detail.css">
    <title><?= htmlspecialchars($video['title']) ?> - K-pop Videos</title>
    <link rel="icon" type="image/x-icon" href="/FINAL PROJECT/assets/images/k-pop4life.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <header>
        <?php include 'navbar_in.php'; ?>
    </header>

    <main class="video-container">
        <div class="content-wrapper">
            <!-- Main Video Section -->
            <div class="primary-content">
                <div class="video-player">
                    <video controls autoplay id="main-video">
                        <source src="<?= htmlspecialchars($video['video_url']) ?>" type="video/mp4">
                    </video>
                </div>
                
                <div class="video-details">
                    <h1><?= htmlspecialchars($video['title']) ?></h1>
                    
                    <div class="video-meta">
                        <div class="meta-left">
                            <span class="upload-date"><?= date('F j, Y', strtotime($video['upload_date'])) ?></span>
                            <?php if ($video['group_name']): ?>
                                <a href="videos.php?group=<?= $video['group_id'] ?>" class="group-tag">
                                    <?= htmlspecialchars($video['group_name']) ?>
                                </a>
                            <?php endif; ?>
                            <a href="videos.php?type=<?= $video['content_type_id'] ?>" class="type-tag">
                                <?= htmlspecialchars($video['type_name']) ?>
                            </a>
                        </div>
                        
                        <div class="meta-right">
                            <div class="uploader-info">
                                <span>Uploaded by: </span>
                                <a href="user-profile.php?id=<?= $video['user_id'] ?>"><?= htmlspecialchars($video['username']) ?></a>
                            </div>
                            
                            <div class="video-actions">
                                <a href="<?= htmlspecialchars($video['video_url']) ?>" download class="action-btn download-btn">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                
                                <button type="button" class="action-btn share-btn" onclick="shareVideo()">
                                    <i class="fas fa-share"></i> Share
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($video['description'])): ?>
                        <div class="video-description">
                            <h3>Description</h3>
                            <p><?= nl2br(htmlspecialchars($video['description'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recommended Videos Section -->
            <div class="recommended-content">
                <h2>More Videos</h2>
                
                <?php if ($same_group_videos->num_rows > 0): ?>
                    <div class="recommendation-section">
                        <h3>More from <?= htmlspecialchars($video['group_name']) ?></h3>
                        <div class="recommended-videos">
                            <?php while ($rec_video = $same_group_videos->fetch_assoc()): ?>
                                <div class="recommended-video">
                                    <a href="video-detail.php?id=<?= $rec_video['content_id'] ?>" class="video-thumb">
                                        <div class="thumb-container">
                                            <video class="thumb-video">
                                                <source src="<?= htmlspecialchars($rec_video['video_url']) ?>" type="video/mp4">
                                            </video>
                                            <div class="play-icon"><i class="fas fa-play"></i></div>
                                        </div>
                                    </a>
                                    <div class="rec-video-info">
                                        <h4><a href="video-detail.php?id=<?= $rec_video['content_id'] ?>"><?= htmlspecialchars($rec_video['title']) ?></a></h4>
                                        <span class="rec-video-type"><?= htmlspecialchars($rec_video['type_name']) ?></span>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($same_type_videos->num_rows > 0): ?>
                    <div class="recommendation-section">
                        <h3>More <?= htmlspecialchars($video['type_name']) ?> Videos</h3>
                        <div class="recommended-videos">
                            <?php while ($rec_video = $same_type_videos->fetch_assoc()): ?>
                                <div class="recommended-video">
                                    <a href="video-detail.php?id=<?= $rec_video['content_id'] ?>" class="video-thumb">
                                        <div class="thumb-container">
                                            <video class="thumb-video">
                                                <source src="<?= htmlspecialchars($rec_video['video_url']) ?>" type="video/mp4">
                                            </video>
                                            <div class="play-icon"><i class="fas fa-play"></i></div>
                                        </div>
                                    </a>
                                    <div class="rec-video-info">
                                        <h4><a href="video-detail.php?id=<?= $rec_video['content_id'] ?>"><?= htmlspecialchars($rec_video['title']) ?></a></h4>
                                        <?php if ($rec_video['group_name']): ?>
                                            <a href="videos.php?group=<?= $rec_video['group_id'] ?>" class="rec-group-link">
                                                <?= htmlspecialchars($rec_video['group_name']) ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="back-to-videos">
                    <a href="videos.php" class="back-link">Back to all videos</a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>

    <div id="share-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeShareModal()">&times;</span>
            <h3>Share This Video</h3>
            <div class="share-options">
                <div class="share-link">
                    <label for="video-url">Video Link:</label>
                    <input type="text" id="video-url" value="<?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ?>" readonly>
                    <button onclick="copyVideoLink()">Copy</button>
                </div>
                <div class="share-buttons">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>" target="_blank" class="share-button facebook">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>&text=<?= urlencode("Check out this video: " . $video['title']) ?>" target="_blank" class="share-button twitter">
                        <i class="fab fa-twitter"></i> Twitter
                    </a>
                    <a href="mailto:?subject=<?= urlencode("Check out this video: " . $video['title']) ?>&body=<?= urlencode("I found this video and thought you might like it: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>" class="share-button email">
                        <i class="fas fa-envelope"></i> Email
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Load video thumbnails for recommendations
    document.addEventListener('DOMContentLoaded', function() {
        const thumbnails = document.querySelectorAll('.thumb-video');
        thumbnails.forEach(video => {
            // Set the current time to the middle of the video for thumbnail
            video.addEventListener('loadeddata', function() {
                video.currentTime = Math.floor(video.duration / 2);
            });
            
            // Prevent playing when clicking the thumbnail
            video.addEventListener('click', function(e) {
                e.preventDefault();
                return false;
            });
        });
    });

    // Share functionality
    function shareVideo() {
        document.getElementById('share-modal').style.display = 'block';
    }

    function closeShareModal() {
        document.getElementById('share-modal').style.display = 'none';
    }

    function copyVideoLink() {
        const copyText = document.getElementById("video-url");
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices
        document.execCommand("copy");
        
        // Show copy success message
        const button = copyText.nextElementSibling;
        const originalText = button.innerText;
        button.innerText = "Copied!";
        setTimeout(() => {
            button.innerText = originalText;
        }, 2000);
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('share-modal');
        if (event.target === modal) {
            modal.style.display = "none";
        }
    }
    </script>
</body>
</html>