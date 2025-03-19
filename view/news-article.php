<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/style.css">
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/news-article.css">
    <title>Article - K-pop News</title>
    <link rel="icon" type="image/x-icon" href="/FINAL PROJECT/assets/images/k-pop4life.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <header>
        <?php include 'navbar_in.php'; ?>
    </header>

    <main class="article-container">
        <?php
        require_once '../db/db-config.php';
        $conn = getDatabaseConnection();

        $article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$article_id) {
            header('Location: news.php');
            exit;
        }

        $article_query = "
            SELECT 
                c.*, 
                u.username,
                kg.group_name,
                nv.verification_id IS NOT NULL as is_verified
            FROM content c
            JOIN users u ON c.user_id = u.user_id
            LEFT JOIN kpop_groups kg ON c.group_id = kg.group_id
            LEFT JOIN news_verification nv ON c.content_id = nv.content_id
            WHERE c.content_id = ?
        ";

        $stmt = $conn->prepare($article_query);
        $stmt->bind_param("i", $article_id);
        $stmt->execute();
        $article = $stmt->get_result()->fetch_assoc();

        if (!$article) {
            header('Location: news.php');
            exit;
        }
        ?>

        <article class="full-article">
            <header class="article-header">
                <h1><?= htmlspecialchars($article['title']) ?></h1>
                <div class="article-meta">
                    <span class="author">By <?= htmlspecialchars($article['username']) ?></span>
                    <span class="date"><?= date('F j, Y', strtotime($article['upload_date'])) ?></span>
                    <?php if ($article['is_verified']): ?>
                        <span class="verified-badge">Verified</span>
                    <?php endif; ?>
                    <?php if ($article['group_name']): ?>
                        <span class="group-tag"><?= htmlspecialchars($article['group_name']) ?></span>
                    <?php endif; ?>
                </div>
            </header>

            <div class="article-content">
                <?= nl2br(htmlspecialchars($article['description'])) ?>
            </div>

            <?php if ($article['source_url']): ?>
                <footer class="article-footer">
                    <div class="source-link">
                        <strong>Source:</strong>
                        <a href="<?= htmlspecialchars($article['source_url']) ?>" 
                           target="_blank" rel="noopener noreferrer">
                            <?= htmlspecialchars($article['source_url']) ?>
                        </a>
                    </div>
                </footer>
            <?php endif; ?>
        </article>
    </main>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>
</body>
</html>