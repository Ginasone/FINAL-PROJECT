<?php
require_once '../db/db-config.php';

$conn = getDatabaseConnection();
// Fetch news content
$news_query = "
    SELECT 
        c.content_id, 
        c.title, 
        c.description, 
        c.upload_date, 
        c.source_url,
        u.username,
        kg.group_name,
        nv.verification_id IS NOT NULL AS is_verified
    FROM 
        content c
    JOIN 
        users u ON c.user_id = u.user_id
    LEFT JOIN 
        kpop_groups kg ON c.group_id = kg.group_id
    LEFT JOIN 
        news_verification nv ON c.content_id = nv.content_id
    WHERE 
        c.content_type_id = (SELECT content_type_id FROM content_types WHERE type_name = 'News')
    ORDER BY 
        c.upload_date DESC
    LIMIT 50  
";

$result = $conn->query($news_query);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/news.css">
    <title>K-pop News</title>
    <link rel="icon" type="image/x-icon" href="/FINAL PROJECT/assets/images/k-pop4life.png">
    <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <header>
        <?php include 'navbar_in.php'; ?>
    </header>

    <main class="news-container">
        <h1>Latest K-pop News</h1>
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert success">News uploaded successfully!</div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <div class="news-grid">
                <?php while ($news = $result->fetch_assoc()): ?>
                    <div class="news-item" onclick="toggleNewsDetails(<?php echo $news['content_id']; ?>)">
                        <div class="news-item-header">
                            <div class="news-item-title">
                                <?php echo htmlspecialchars($news['title']); ?>
                                <span class="read-more" id="read-more-<?php echo $news['content_id']; ?>">Read More</span>
                            </div>
                            <div class="news-item-meta">
                                <span><?php echo htmlspecialchars($news['username']); ?></span>
                                <span style="margin: 0 10px;">|</span>
                                <span><?php echo date('F j, Y', strtotime($news['upload_date'])); ?></span>
                                <?php if ($news['group_name']): ?>
                                    <span style="margin: 0 10px;">|</span>
                                    <span><?php echo htmlspecialchars($news['group_name']); ?></span>
                                <?php endif; ?>
                                <?php if ($news['is_verified']): ?>
                                    <span class="verified-badge">Verified</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div id="news-details-<?php echo $news['content_id']; ?>" class="news-item-details">
                            <p><?php echo htmlspecialchars($news['description']); ?></p>
                            <?php if ($news['source_url']): ?>
                                <a href="<?php echo htmlspecialchars($news['source_url']); ?>" 
                                   target="_blank" 
                                   class="source-link"
                                   onclick="event.stopPropagation();">
                                    View Original Source
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No news articles found.</p>
        <?php endif; ?>
    </main>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>
    <script src="../assets/js/news.js"></script>
</body>
</html>