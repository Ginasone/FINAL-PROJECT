<?php 
session_start();
require_once '../db/db-config.php';
include '../functions/member_guide_search.php';

$connection = getDatabaseConnection();

// Handle search if a search term is submitted
$search = isset($_GET['search']) ? $_GET['search'] : '';
$guides = searchMemberGuides($connection, $search);
$members = searchMembers($connection, $search);

// Function to get group members for a specific group
function getGroupMembers($connection, $group_id) {
    $sql = "SELECT * FROM group_members WHERE group_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }
    
    return $members;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Guides</title>
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/member-guides.css">
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/group.css">
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/guide.css">
    <link rel="icon" type="image/x-icon" href="/FINAL PROJECT/assets/images/k-pop4life.png">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body>
    <header>
        <?php include 'navbar_in.php'; ?>
    </header>

    <main class="container">
        <h1>Member Guides</h1>
        
        <div class="search-container">
            <form method="GET" action="">
                <input type="text" 
                       name="search" 
                       id="searchInput" 
                       placeholder="Search member guides, groups, members..." 
                       class="search-input" 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <?php if (!empty($search)): ?>
            <section class="members-section">
                <h2>Members</h2>
                <?php if (!empty($members)): ?>
                    <div class="members-grid">
                        <?php foreach ($members as $member): ?>
                            <div class="member-card">
                                <h3><?php echo htmlspecialchars($member['stage_name']); ?></h3>
                                <p><strong>Group:</strong> 
                                    <?php if (isset($member['group_id'])): ?>
                                        <a href="/FINAL PROJECT/actions/get_group_details.php?group_id=<?php echo $member['group_id']; ?>"
                                           onclick="showGroupDetails(<?php echo $member['group_id']; ?>); return false;">
                                            <?php echo htmlspecialchars($member['group_name']); ?>
                                        </a>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($member['group_name']); ?>
                                    <?php endif; ?>
                                </p>
                                <p><strong>Real Name:</strong> <?php echo htmlspecialchars($member['real_name']); ?></p>
                                <p><strong>Nationality:</strong> <?php echo htmlspecialchars($member['nationality']); ?></p>
                                <?php if (!empty($member['position'])): ?>
                                    <p><strong>Position:</strong> <?php echo htmlspecialchars($member['position']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No members found.</p>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <div id="guidesContainer">
            <?php 
            $groupedGuides = [];
            foreach ($guides as $guide) {
                $groupedGuides[$guide['group_name']][] = $guide;
            }
            
            if (!empty($groupedGuides)): 
                foreach ($groupedGuides as $groupName => $groupGuides): 
                    $groupId = isset($groupGuides[0]['group_id']) ? $groupGuides[0]['group_id'] : null;
            ?>
                <section class="group-section">
                    <h2>
                        <?php if ($groupId): ?>
                            <a href="#"
                               onclick="showGroupDetails(<?php echo $groupId; ?>); return false;" 
                               class="group-link">
                                <?php echo htmlspecialchars($groupName); ?>
                            </a>
                        <?php else: ?>
                            <?php echo htmlspecialchars($groupName); ?>
                        <?php endif; ?>
                    </h2>
                    <div class="guides-grid">
                        <?php foreach ($groupGuides as $guide): ?>
                            <div class="guide-card" onclick="showGuideDetails(<?php echo $guide['content_id']; ?>)">
                                <h3><?php echo htmlspecialchars($guide['title']); ?></h3>
                                <p><?php echo htmlspecialchars($guide['description']); ?></p>
                                <?php if (!empty($guide['video_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($guide['video_url']); ?>" 
                                       class="video-link"
                                       target="_blank" 
                                       onclick="event.stopPropagation();">
                                        Watch Video
                                    </a>
                                <?php endif; ?>
                                <a href="#"
                                   onclick="showGuideDetails(<?php echo $guide['content_id']; ?>); return false;"
                                   class="details-link">
                                    View Details
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php 
                endforeach; 
            else: 
                if (!empty($search)): 
            ?>
                <p>No member guides found.</p>
            <?php 
                endif; 
            endif; 
            ?>
        </div>

        <!-- Modal for Group Details -->
            <div id="groupModal" class="modal">
                <div class="modal-content">
                    <span class="close-btn" onclick="closeModal('groupModal')">&times;</span>
                    <div id="groupModalContent"></div>
                </div>
            </div>

            <!-- Modal for Guide Details -->
            <div id="guideModal" class="modal">
                <div class="modal-content">
                    <span class="close-btn" onclick="closeModal('guideModal')">&times;</span>
                    <div id="guideModalContent"></div>
                </div>
            </div>
    </main>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>
    <script>
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
            }
        }
    </script>
    <script src="/FINAL PROJECT/assets/js/showing-details.js"></script>
</body>
</html>
<?php 
closeDatabaseConnection($connection);
?>