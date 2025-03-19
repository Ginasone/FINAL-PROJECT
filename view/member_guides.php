<!-- member_guides.php -->
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/style.css">
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/member-guides.css">
    <title>K-pop Group Profiles</title>
    <link rel="icon" type="image/x-icon" href="/FINAL PROJECT/assets/images/k-pop4life.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <header>
        <?php include 'navbar_in.php'; ?>
    </header>

    <main class="guides-container">
        <h1>K-pop Group Profiles</h1>
        
        <div class="groups-grid">
            <?php
            require_once '../db/db-config.php';
            $conn = getDatabaseConnection();

            $groups_query = "
                SELECT 
                    kg.*,
                    GROUP_CONCAT(gm.stage_name ORDER BY gm.stage_name) as member_names,
                    COUNT(gm.member_id) as member_count
                FROM kpop_groups kg
                LEFT JOIN group_members gm ON kg.group_id = gm.group_id
                WHERE kg.group_id IN (
                    SELECT DISTINCT group_id 
                    FROM content 
                    WHERE content_type_id = (
                        SELECT content_type_id FROM content_types WHERE type_name = 'Member Guide'
                    )
                )
                GROUP BY kg.group_id
                ORDER BY kg.group_name
            ";

            $result = $conn->query($groups_query);

            while ($group = $result->fetch_assoc()): 
                $members = explode(',', $group['member_names']);
            ?>
                <div class="group-card">
                    <h2><?= htmlspecialchars($group['group_name']) ?></h2>
                    
                    <div class="group-preview">
                        <p><strong>Number of Members:</strong> <?= $group['member_count'] ?></p>
                        <div class="members-list">
                            <strong>Members:</strong>
                            <ul>
                                <?php foreach ($members as $member): ?>
                                    <li><?= htmlspecialchars($member) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    
                    <a href="group-profile.php?id=<?= $group['group_id'] ?>" 
                       class="view-profile-btn">
                        View Full Profile
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>
</body>
</html>

