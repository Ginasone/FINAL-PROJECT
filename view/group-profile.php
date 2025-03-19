<?php
// group-profile.php
session_start();
require_once '../db/db-config.php';

$connection = getDatabaseConnection();
$group_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$group_id) {
    header('Location: member_guides.php');
    exit;
}

// Fetch group details
$group_query = "SELECT * FROM kpop_groups WHERE group_id = ?";
$stmt = $connection->prepare($group_query);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$group_result = $stmt->get_result();

if ($group_result->num_rows === 0) {
    header('Location: member_guides.php');
    exit;
}

$group = $group_result->fetch_assoc();

// Fetch member details
$members_query = "SELECT * FROM group_members WHERE group_id = ? ORDER BY stage_name";
$stmt = $connection->prepare($members_query);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$members_result = $stmt->get_result();
$members = [];
while ($member = $members_result->fetch_assoc()) {
    $members[] = $member;
}

// Fetch group guide content
$guide_query = "
    SELECT c.* FROM content c
    WHERE c.group_id = ? 
    AND c.content_type_id = (
        SELECT content_type_id FROM content_types 
        WHERE type_name = 'Member Guide'
    )
    ORDER BY c.upload_date DESC
    LIMIT 1
";
$stmt = $connection->prepare($guide_query);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$guide_result = $stmt->get_result();
$guide = $guide_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($group['group_name']) ?> Profile - K-pop4Life</title>
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/style.css">
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/group-profile.css">
    <link rel="icon" type="image/x-icon" href="/FINAL PROJECT/assets/images/k-pop4life.png">
</head>
<body>
    <header>
        <?php include 'navbar_in.php'; ?>
    </header>

    <main class="profile-container">
        <article class="group-profile">
            <!-- Group Header -->
            <header class="group-header">
                <?php if (!empty($group['group_image_url'])): ?>
                    <div class="group-image">
                        <img src="<?= htmlspecialchars($group['group_image_url']) ?>" 
                             alt="<?= htmlspecialchars($group['group_name']) ?>" 
                             class="group-profile-image">
                    </div>
                <?php endif; ?>
                
                <h1><?= htmlspecialchars($group['group_name']) ?></h1>
                
                <div class="group-details">
                    <div class="detail-item">
                        <strong>Debut</strong>
                        <span><?= date('F j, Y', strtotime($group['debut_date'])) ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <strong>Agency</strong>
                        <span><?= htmlspecialchars($group['agency']) ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <strong>Group Type</strong>
                        <span><?= htmlspecialchars($group['group_type']) ?></span>
                    </div>
                    
                    <?php if (!empty($group['fandom'])): ?>
                        <div class="detail-item">
                            <strong>Fandom</strong>
                            <span><?= htmlspecialchars($group['fandom']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Group Description -->
            <?php if ($guide): ?>
                <section class="group-description">
                    <h2>About <?= htmlspecialchars($group['group_name']) ?></h2>
                    <div class="description-content">
                        <?= nl2br(htmlspecialchars($guide['description'])) ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Members Section -->
            <section class="members-section">
                <h2>Members</h2>
                
                <div class="members-grid">
                    <?php foreach ($members as $member): ?>
                        <div class="member-card">
                            <?php if (!empty($member['member_image_url'])): ?>
                                <div class="member-image">
                                    <img src="<?= htmlspecialchars($member['member_image_url']) ?>" 
                                         alt="<?= htmlspecialchars($member['stage_name']) ?>" 
                                         class="member-profile-image">
                                </div>
                            <?php endif; ?>
                            
                            <h3><?= htmlspecialchars($member['stage_name']) ?></h3>
                            
                            <div class="member-info">
                                <p><strong>Birth Name:</strong> <?= htmlspecialchars($member['real_name']) ?></p>
                                <p><strong>Birthday:</strong> <?= date('F j, Y', strtotime($member['birth_date'])) ?></p>
                                <p><strong>Nationality:</strong> <?= htmlspecialchars($member['nationality']) ?></p>
                                
                                <?php if (!empty($member['position'])): ?>
                                    <p><strong>Position:</strong> <?= htmlspecialchars($member['position']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
            
            <!-- Back to Guides Link -->
            <div class="back-link">
                <a href="member_guides.php" class="back-button">
                    &larr; Back to Group Profiles
                </a>
            </div>
        </article>
    </main>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>
</body>
</html>