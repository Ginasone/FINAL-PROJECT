<?php
include '../db/db-config.php';

$connection = getDatabaseConnection();

// Validate and sanitize content ID
$content_id = isset($_GET['content_id']) ? intval($_GET['content_id']) : 0;

if ($content_id <= 0) {
    echo "Invalid content ID";
    exit;
}

// Fetch guide details with group information
$guideSql = "SELECT c.*, kg.group_name, kg.agency, ct.type_name,
                    (SELECT GROUP_CONCAT(stage_name) 
                     FROM group_members gm 
                     WHERE gm.group_id = c.group_id) as group_members
             FROM content c
             JOIN kpop_groups kg ON c.group_id = kg.group_id
             JOIN content_types ct ON c.content_type_id = ct.content_type_id
             WHERE c.content_id = ? AND ct.type_name = 'Member Guide'";
$guideStmt = $connection->prepare($guideSql);
$guideStmt->bind_param("i", $content_id);
$guideStmt->execute();
$guideResult = $guideStmt->get_result();

if ($guideResult->num_rows === 0) {
    echo "Guide not found";
    exit;
}

$guide = $guideResult->fetch_assoc();
?>

<div class="guide-details">
    <h2><?php echo htmlspecialchars($guide['title']); ?></h2>
    <p><strong>Group:</strong> <?php echo htmlspecialchars($guide['group_name']); ?></p>
    <p><strong>Agency:</strong> <?php echo htmlspecialchars($guide['agency']); ?></p>
    
    <?php if (!empty($guide['group_members'])): ?>
        <p><strong>Group Members:</strong> <?php echo htmlspecialchars($guide['group_members']); ?></p>
    <?php endif; ?>

    <div class="guide-description">
        <h3>Description</h3>
        <p><?php echo htmlspecialchars($guide['description']); ?></p>
    </div>

    <?php if (!empty($guide['video_url'])): ?>
        <div class="guide-video">
            <h3>Related Video</h3>
            <a href="<?php echo htmlspecialchars($guide['video_url']); ?>" target="_blank">
                Watch Video
            </a>
        </div>
    <?php endif; ?>

    <div class="guide-metadata">
        <p><strong>Uploaded:</strong> <?php echo date('F d, Y', strtotime($guide['upload_date'])); ?></p>
    </div>
</div>
<?php
closeDatabaseConnection($connection);
?>