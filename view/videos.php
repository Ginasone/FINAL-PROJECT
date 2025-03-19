<?php
// videos.php - Main video listing page
session_start();
require_once '../db/db-config.php';

$conn = getDatabaseConnection();

// Get filter parameters
$type_filter = isset($_GET['type']) ? intval($_GET['type']) : 0;
$group_filter = isset($_GET['group']) ? intval($_GET['group']) : 0;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build the SQL query based on filters
$sql_base = "
    SELECT c.*, 
           kg.group_name, 
           ct.type_name, 
           u.username,
           u.user_id
    FROM content c
    JOIN content_types ct ON c.content_type_id = ct.content_type_id
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN kpop_groups kg ON c.group_id = kg.group_id
    WHERE (ct.type_name = 'Music Video' OR ct.type_name = 'Performance' OR ct.type_name = 'Concert')
    AND c.video_url IS NOT NULL 
    AND c.video_url != ''
";

$params = [];
$param_types = "";

// Add type filter
if ($type_filter > 0) {
    $sql_base .= " AND c.content_type_id = ?";
    $params[] = $type_filter;
    $param_types .= "i";
}

// Add group filter
if ($group_filter > 0) {
    $sql_base .= " AND c.group_id = ?";
    $params[] = $group_filter;
    $param_types .= "i";
}

// Add search filter
if (!empty($search_query)) {
    $sql_base .= " AND (c.title LIKE ? OR kg.group_name LIKE ?)";
    $search_param = "%{$search_query}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= "ss";
}

// Add ordering
$sql_base .= " ORDER BY c.upload_date DESC";

// Prepare the statement
$stmt = $conn->prepare($sql_base);

// Bind parameters if any
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$videos = $stmt->get_result();

// Get video types for filter
$types_query = "SELECT * FROM content_types 
                WHERE type_name IN ('Music Video', 'Performance', 'Concert')
                ORDER BY type_name";
$types_result = $conn->query($types_query);

// Get groups for filter
$groups_query = "SELECT DISTINCT kg.* FROM kpop_groups kg
                 JOIN content c ON kg.group_id = c.group_id
                 JOIN content_types ct ON c.content_type_id = ct.content_type_id
                 WHERE (ct.type_name = 'Music Video' OR ct.type_name = 'Performance' OR ct.type_name = 'Concert')
                 ORDER BY kg.group_name";
$groups_result = $conn->query($groups_query);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/videos.css?v=<?php echo time(); ?>">
    <title>K-pop Videos</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/k-pop4life.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Inline fallback styles -->
    <style>
    .search-filter-section {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .search-form {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: center;
    }

    .search-bar {
        flex: 1;
        min-width: 250px;
        display: flex;
        border-radius: 50px;
        overflow: hidden;
        border: 1px solid #ddd;
    }

    .search-bar input {
        flex: 1;
        padding: 0.8rem 1.2rem;
        border: none;
        outline: none;
        font-size: 1rem;
    }

    .search-bar button {
        background: #6c5ce7;
        color: white;
        border: none;
        padding: 0.8rem 1.2rem;
        cursor: pointer;
        font-size: 1rem;
    }

    .filters {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .filter-group {
        min-width: 200px;
    }

    .filter-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #4a4a4a;
    }

    .filter-group select {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        background-color: white;
        font-size: 1rem;
    }
    
    .videos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .video-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    </style>
</head>
<body>
    <header>
        <?php include 'navbar_in.php'; ?>
    </header>

    <main class="videos-container">
        <!-- Search and Filter Section -->
        <div class="search-filter-section">
            <form method="GET" action="" class="search-form">
                <div class="search-bar">
                    <input type="text" name="search" placeholder="Search videos..." value="<?= htmlspecialchars($search_query) ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </div>
                
                <div class="filters">
                    <div class="filter-group">
                        <label for="type">Video Type:</label>
                        <select name="type" id="type" onchange="this.form.submit()">
                            <option value="0">All Types</option>
                            <?php while ($type = $types_result->fetch_assoc()): ?>
                                <option value="<?= $type['content_type_id'] ?>" <?= $type_filter == $type['content_type_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($type['type_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="group">Group/Artist:</label>
                        <select name="group" id="group" onchange="this.form.submit()">
                            <option value="0">All Groups</option>
                            <?php while ($group = $groups_result->fetch_assoc()): ?>
                                <option value="<?= $group['group_id'] ?>" <?= $group_filter == $group['group_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($group['group_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- Active Filters Display -->
        <?php if ($type_filter > 0 || $group_filter > 0 || !empty($search_query)): ?>
            <div class="active-filters">
                <span>Active Filters:</span>
                <?php if ($type_filter > 0): 
                    $types_result->data_seek(0);
                    while ($type = $types_result->fetch_assoc()) {
                        if ($type['content_type_id'] == $type_filter) {
                            echo '<div class="filter-tag">' . htmlspecialchars($type['type_name']) . 
                                 '<a href="?type=0&group=' . $group_filter . '&search=' . urlencode($search_query) . '">×</a></div>';
                        }
                    }
                endif; ?>
                
                <?php if ($group_filter > 0): 
                    $groups_result->data_seek(0);
                    while ($group = $groups_result->fetch_assoc()) {
                        if ($group['group_id'] == $group_filter) {
                            echo '<div class="filter-tag">' . htmlspecialchars($group['group_name']) . 
                                 '<a href="?type=' . $type_filter . '&group=0&search=' . urlencode($search_query) . '">×</a></div>';
                        }
                    }
                endif; ?>
                
                <?php if (!empty($search_query)): ?>
                    <div class="filter-tag">Search: "<?= htmlspecialchars($search_query) ?>"
                        <a href="?type=<?= $type_filter ?>&group=<?= $group_filter ?>">×</a>
                    </div>
                <?php endif; ?>
                
                <a href="videos.php" class="clear-all-filters">Clear All</a>
            </div>
        <?php endif; ?>

        <!-- Videos Grid -->
        <div class="videos-grid">
            <?php if ($videos->num_rows > 0): ?>
                <?php while ($video = $videos->fetch_assoc()): ?>
                    <div class="video-card">
                        <a href="video-detail.php?id=<?= $video['content_id'] ?>" class="video-thumbnail">
                            <div class="thumbnail-container">
                                <video class="thumbnail-video">
                                    <source src="<?= htmlspecialchars($video['video_url']) ?>" type="video/mp4">
                                </video>
                                <div class="play-icon"><i class="fas fa-play"></i></div>
                            </div>
                        </a>
                        <div class="video-info">
                            <h3><a href="video-detail.php?id=<?= $video['content_id'] ?>"><?= htmlspecialchars($video['title']) ?></a></h3>
                            <div class="video-meta">
                                <?php if ($video['group_name']): ?>
                                    <a href="?group=<?= $video['group_id'] ?>" class="group-link"><?= htmlspecialchars($video['group_name']) ?></a>
                                <?php endif; ?>
                                <span class="video-type"><?= htmlspecialchars($video['type_name']) ?></span>
                            </div>
                            <div class="video-uploader">
                                <span>Uploaded by: <?= htmlspecialchars($video['username']) ?></span>
                                <span class="upload-date"><?= date('M j, Y', strtotime($video['upload_date'])) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-videos-message">
                    <h3>No videos found</h3>
                    <p>Try adjusting your filters or search criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>

    <script>
    // Load video thumbnails
    document.addEventListener('DOMContentLoaded', function() {
        const thumbnails = document.querySelectorAll('.thumbnail-video');
        thumbnails.forEach(video => {
            // Set the current time to the middle of the video for thumbnail
            video.addEventListener('loadeddata', function() {
                if (this.duration) {
                    this.currentTime = Math.floor(this.duration / 2);
                }
            });
            
            // Prevent playing when clicking the thumbnail
            video.addEventListener('click', function(e) {
                e.preventDefault();
                return false;
            });
        });
    });
    </script>
</body>
</html>