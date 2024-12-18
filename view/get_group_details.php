<?php
include '../db/db-config.php';

$connection = getDatabaseConnection();

// Validate and sanitize group ID
$group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;

if ($group_id <= 0) {
    echo "Invalid group ID";
    exit;
}

// Fetch group details
$groupSql = "SELECT * FROM kpop_groups WHERE group_id = ?";
$groupStmt = $connection->prepare($groupSql);
$groupStmt->bind_param("i", $group_id);
$groupStmt->execute();
$groupResult = $groupStmt->get_result();
$group = $groupResult->fetch_assoc();

// Fetch group members
$membersSql = "SELECT * FROM group_members WHERE group_id = ?";
$membersStmt = $connection->prepare($membersSql);
$membersStmt->bind_param("i", $group_id);
$membersStmt->execute();
$membersResult = $membersStmt->get_result();
$members = [];
while ($member = $membersResult->fetch_assoc()) {
    $members[] = $member;
}

// Fetch recent guides for this group
$guidesSql = "SELECT * FROM content 
              WHERE group_id = ? AND content_type_id = (
                  SELECT content_type_id FROM content_types WHERE type_name = 'Member Guide'
              ) 
              ORDER BY upload_date DESC 
              LIMIT 5";
$guidesStmt = $connection->prepare($guidesSql);
$guidesStmt->bind_param("i", $group_id);
$guidesStmt->execute();
$guidesResult = $guidesStmt->get_result();
$guides = [];
while ($guide = $guidesResult->fetch_assoc()) {
    $guides[] = $guide;
}
?>

<div class="group-details">
    <h2><?php echo htmlspecialchars($group['group_name']); ?></h2>
    <p><strong>Debut Date:</strong> <?php echo date('F d, Y', strtotime($group['debut_date'])); ?></p>
    <p><strong>Agency:</strong> <?php echo htmlspecialchars($group['agency']); ?></p>
    <p><strong>Group Type:</strong> <?php echo htmlspecialchars($group['group_type']); ?></p>
    <?php if (!empty($group['fandom'])): ?>
        <p><strong>Fandom Name:</strong> <?php echo htmlspecialchars($group['fandom']); ?></p>
    <?php endif; ?>

    <h3>Group Members</h3>
    <div class="member-grid">
        <?php foreach ($members as $member): ?>
            <div class="member-card-modal">
                <h4><?php echo htmlspecialchars($member['stage_name']); ?></h4>
                <p><strong>Real Name:</strong> <?php echo htmlspecialchars($member['real_name']); ?></p>
                <p><strong>Birth Date:</strong> <?php echo date('F d, Y', strtotime($member['birth_date'])); ?></p>
                <p><strong>Nationality:</strong> <?php echo htmlspecialchars($member['nationality']); ?></p>
                <?php if (!empty($member['position'])): ?>
                    <p><strong>Position:</strong> <?php echo htmlspecialchars($member['position']); ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (!empty($guides)): ?>
        <h3>Recent Member Guides</h3>
        <div class="guides-list">
            <?php foreach ($guides as $guide): ?>
                <div class="guide-item">
                    <h4><?php echo htmlspecialchars($guide['title']); ?></h4>
                    <p><?php echo htmlspecialchars($guide['description']); ?></p>
                    <?php if (!empty($guide['video_url'])): ?>
                        <a href="<?php echo htmlspecialchars($guide['video_url']); ?>" target="_blank">Watch Video</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php
closeDatabaseConnection($connection);
?>