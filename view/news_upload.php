<?php
// news_upload.php
session_start();
include '../db/db-config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = getDatabaseConnection();

// Fetch groups for dropdown
$groups_query = "SELECT group_id, group_name FROM kpop_groups ORDER BY group_name";
$groups_result = $conn->query($groups_query);

// Check for error/success messages
$error_message = "";
$success_message = "";

if (isset($_GET['error'])) {
    $error_message = urldecode($_GET['error']);
}

if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $success_message = "News article uploaded successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/news-upload.css?v=<?php echo time(); ?>">
    <title>Upload News Article</title>
    <link rel="icon" type="image/x-icon" href="/FINAL PROJECT/assets/images/k-pop4life.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
    /* Inline styles as a fallback */
    .upload-container {
        max-width: 900px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .upload-form {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        padding: 2rem;
    }

    .form-section {
        margin-bottom: 2.5rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid #eee;
    }

    .form-section h2 {
        color: #6c5ce7;
        font-size: 1.6rem;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #eee;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .form-group input, 
    .form-group textarea, 
    .form-group select {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
    }

    .field-description {
        margin-top: 0.5rem;
        font-size: 0.85rem;
        color: #666;
        font-style: italic;
    }

    .submit-btn {
        background-color: #6c5ce7;
        color: white;
        padding: 1rem 2rem;
        font-size: 1.1rem;
        font-weight: 600;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        width: 100%;
    }
    </style>
</head>
<body>
    <header>
        <?php include 'navbar_in.php'; ?>
    </header>

    <main class="upload-container">
        <h1>Upload News Article</h1>
        
        <?php if ($error_message): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <form class="upload-form" action="../actions/news_upload_process.php" method="post">
            <!-- Article Content Section -->
            <section class="form-section">
                <h2>Article Content</h2>
                
                <div class="form-group">
                    <label for="title">Article Title *</label>
                    <input type="text" id="title" name="title" required>
                </div>

                <div class="form-group">
                    <label for="preview">Article Preview/Summary *</label>
                    <textarea id="preview" name="preview" required rows="3" 
                              placeholder="A brief preview that will appear in the news listing (max 300 characters)"></textarea>
                    <p class="field-description">This text will appear in the news listing page. Keep it concise.</p>
                </div>

                <div class="form-group">
                    <label for="full_content">Full Article Content *</label>
                    <textarea id="full_content" name="full_content" required rows="8"
                              placeholder="The complete article content"></textarea>
                    <p class="field-description">The complete article text that will be shown when readers click "Read Full Article".</p>
                </div>

                <div class="form-group">
                    <label for="source">Source URL *</label>
                    <input type="url" id="source" name="source" required 
                           placeholder="https://example.com/news-article">
                    <p class="field-description">Link to the original source of this news.</p>
                </div>
            </section>

            <!-- Related Group Section -->
            <section class="form-section">
                <h2>Group Information</h2>
                
                <div class="form-group">
                    <label for="group_id">Related K-pop Group (Optional)</label>
                    <select id="group_id" name="group_id" onchange="toggleNewGroup(this.value)">
                        <option value="">-- Select a Group --</option>
                        <?php while ($group = $groups_result->fetch_assoc()): ?>
                            <option value="<?= $group['group_id'] ?>"><?= htmlspecialchars($group['group_name']) ?></option>
                        <?php endwhile; ?>
                        <option value="new">+ Add New Group</option>
                    </select>
                    <p class="field-description">If this news is about a specific K-pop group, please select it here.</p>
                </div>

                <!-- New Group Fields (hidden by default) -->
                <div id="new_group_section" style="display: none;">
                    <div class="form-group">
                        <label for="group_name">Group Name *</label>
                        <input type="text" id="group_name" name="group_name">
                    </div>

                    <div class="group-details">
                        <div class="form-group">
                            <label for="group_type">Group Type *</label>
                            <select id="group_type" name="group_type">
                                <option value="Group">Group</option>
                                <option value="Soloist">Soloist</option>
                                <option value="Duo">Duo</option>
                                <option value="Trio">Trio</option>
                                <option value="Band">Band</option>
                                <option value="Project">Project</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="debut_date">Debut Date *</label>
                            <input type="date" id="debut_date" name="debut_date">
                        </div>

                        <div class="form-group">
                            <label for="agency">Agency *</label>
                            <input type="text" id="agency" name="agency">
                        </div>

                        <div class="form-group">
                            <label for="fandom">Fandom Name</label>
                            <input type="text" id="fandom" name="fandom">
                        </div>
                    </div>
                </div>
            </section>

            <button type="submit" class="submit-btn">Publish Article</button>
        </form>
    </main>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>

    <script>
    function toggleNewGroup(value) {
        const newGroupSection = document.getElementById('new_group_section');
        const requiredFields = newGroupSection.querySelectorAll('input');
        
        if (value === 'new') {
            newGroupSection.style.display = 'block';
            // Make fields required
            requiredFields.forEach(field => {
                if (field.id !== 'fandom') {  // Fandom is optional
                    field.setAttribute('required', 'required');
                }
            });
        } else {
            newGroupSection.style.display = 'none';
            // Remove required attribute
            requiredFields.forEach(field => {
                field.removeAttribute('required');
            });
        }
    }
    </script>
</body>
</html>