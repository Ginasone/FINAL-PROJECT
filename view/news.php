

<!-- news.php -->
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/style.css">
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/news.css">
    <title>K-pop News</title>
    <link rel="icon" type="image/x-icon" href="/FINAL PROJECT/assets/images/k-pop4life.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <header>
        <?php include 'navbar_in.php'; ?>
    </header>

    <main class="news-container">
        <h1>Latest K-pop News</h1>
        
        <div class="news-grid">
            <?php
            require_once '../db/db-config.php';
            $conn = getDatabaseConnection();

            $news_query = "
                SELECT 
                    c.content_id, 
                    c.title, 
                    LEFT(c.description, 300) as preview,
                    c.description,
                    c.upload_date,
                    c.source_url,
                    u.username,
                    kg.group_name,
                    nv.verification_id IS NOT NULL as is_verified
                FROM content c
                JOIN users u ON c.user_id = u.user_id
                LEFT JOIN kpop_groups kg ON c.group_id = kg.group_id
                LEFT JOIN news_verification nv ON c.content_id = nv.content_id
                WHERE c.content_type_id = (
                    SELECT content_type_id FROM content_types WHERE type_name = 'News'
                )
                ORDER BY c.upload_date DESC
            ";

            $result = $conn->query($news_query);

            while ($news = $result->fetch_assoc()): 
            ?>
                <article class="news-item">
                    <div class="news-header">
                        <h2><?= htmlspecialchars($news['title']) ?></h2>
                        <div class="news-meta">
                            <span>By <?= htmlspecialchars($news['username']) ?></span>
                            <span><?= date('F j, Y', strtotime($news['upload_date'])) ?></span>
                            <?php if ($news['is_verified']): ?>
                                <span class="verified-badge">Verified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="news-preview">
                        <?= htmlspecialchars($news['preview']) ?>...
                    </div>
                    
                    <div class="news-actions">
                        <a href="news-article.php?id=<?= $news['content_id'] ?>" 
                           class="read-more-link">Read Full Article</a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    </main>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>
</body>
</html>