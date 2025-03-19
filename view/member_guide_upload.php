<?php
// member_guide_upload.php
session_start();
include '../db/db-config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$connection = getDatabaseConnection();

// Fetch K-pop groups for dropdown
$groupsSql = "SELECT group_id, group_name FROM kpop_groups ORDER BY group_name";
$groupsResult = $connection->query($groupsSql);

// Get group types for dropdown
$groupTypes = ['Group', 'Soloist', 'Duo', 'Trio', 'Band', 'Project'];

$error_message = "";
$success_message = "";

// Check if there's an error or success message from the processing page
if (isset($_GET['error'])) {
    $error_message = urldecode($_GET['error']);
}

if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $success_message = "Member guide uploaded successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Member Guide</title>
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/style.css">
    <link rel="stylesheet" href="/FINAL PROJECT/assets/css/member-guide-upload.css">
    <link rel="icon" type="image/x-icon" href="/FINAL PROJECT/assets/images/k-pop4life.png">
</head>
<body>
    <header>
        <?php include 'navbar_in.php'; ?>
    </header>

    <main class="upload-container">
        <h1>Upload Member Guide</h1>
        
        <?php if ($error_message): ?>
            <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <form class="upload-form" method="post" action="../actions/member_guide_upload_process.php" enctype="multipart/form-data">
            <!-- Group Information Section -->
            <section class="form-section">
                <h2>Group Information</h2>
                
                <div class="form-group">
                    <label for="group_id">Select Group *</label>
                    <select id="group_id" name="group_id" required onchange="toggleNewGroup(this.value)">
                        <option value="">-- Select Existing Group --</option>
                        <?php while ($group = $groupsResult->fetch_assoc()): ?>
                            <option value="<?= $group['group_id'] ?>">
                                <?= htmlspecialchars($group['group_name']) ?>
                            </option>
                        <?php endwhile; ?>
                        <option value="new">+ Add New Group</option>
                    </select>
                </div>

                <!-- New Group Fields (hidden by default) -->
                <div id="new_group_section" style="display: none;">
                    <div class="form-group">
                        <label for="group_name">Group Name *</label>
                        <input type="text" id="group_name" name="group_name">
                    </div>

                    <div class="form-group">
                        <label for="group_type">Group Type *</label>
                        <select id="group_type" name="group_type">
                            <?php foreach ($groupTypes as $type): ?>
                                <option value="<?= $type ?>"><?= $type ?></option>
                            <?php endforeach; ?>
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

                    <div class="form-group">
                        <label for="group_image">Group Photo *</label>
                        <input type="file" id="group_image" name="group_image" accept="image/*">
                        <div class="image-preview" id="group-image-preview"></div>
                        <p class="file-requirements">
                            Accepted formats: JPG, PNG, WebP (Max size: 5MB)
                        </p>
                    </div>
                </div>
            </section>

            <!-- Guide Content Section -->
            <section class="form-section">
                <h2>Guide Content</h2>
                
                <div class="form-group">
                    <label for="guide_title">Guide Title *</label>
                    <input type="text" id="guide_title" name="guide_title" required>
                </div>

                <div class="form-group">
                    <label for="guide_content">Group Description/History *</label>
                    <textarea id="guide_content" name="guide_content" rows="6" required></textarea>
                </div>
            </section>

            <!-- Member Information Section -->
            <section class="form-section">
                <h2>Member Information</h2>
                
                <div id="members_container">
                    <!-- First member form (always shown) -->
                    <div class="member-form">
                        <h3>Member 1</h3>
                        
                        <div class="form-group">
                            <label for="stage_name_0">Stage Name *</label>
                            <input type="text" id="stage_name_0" name="members[0][stage_name]" required>
                        </div>

                        <div class="form-group">
                            <label for="birth_name_0">Birth Name *</label>
                            <input type="text" id="birth_name_0" name="members[0][birth_name]" required>
                        </div>

                        <div class="form-group">
                            <label for="birthday_0">Birthday *</label>
                            <input type="date" id="birthday_0" name="members[0][birthday]" required>
                        </div>

                        <div class="form-group">
                            <label for="nationality_0">Nationality *</label>
                            <input type="text" id="nationality_0" name="members[0][nationality]" required>
                        </div>

                        <div class="form-group">
                            <label for="position_0">Position</label>
                            <input type="text" id="position_0" name="members[0][position]">
                        </div>

                        <div class="form-group">
                            <label for="photo_0">Member Photo *</label>
                            <input type="file" id="photo_0" name="members[0][photo]" accept="image/*" required>
                            <div class="image-preview"></div>
                            <p class="file-requirements">
                                Accepted formats: JPG, PNG, WebP (Max size: 5MB)
                            </p>
                        </div>
                    </div>
                </div>
                
                <button type="button" onclick="addMemberForm()" class="add-member-btn">
                    + Add Another Member
                </button>
            </section>

            <button type="submit" class="submit-btn">Upload Member Guide</button>
        </form>
    </main>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>

    <script>
    // JavaScript for form interactions
    let memberCount = 0;

    function addMemberForm() {
        memberCount++;
        const container = document.getElementById('members_container');
        const memberForm = document.createElement('div');
        memberForm.className = 'member-form';
        memberForm.innerHTML = `
            <h3>Member ${memberCount + 1}</h3>
            
            <div class="form-group">
                <label for="stage_name_${memberCount}">Stage Name *</label>
                <input type="text" id="stage_name_${memberCount}" name="members[${memberCount}][stage_name]" required>
            </div>

            <div class="form-group">
                <label for="birth_name_${memberCount}">Birth Name *</label>
                <input type="text" id="birth_name_${memberCount}" name="members[${memberCount}][birth_name]" required>
            </div>

            <div class="form-group">
                <label for="birthday_${memberCount}">Birthday *</label>
                <input type="date" id="birthday_${memberCount}" name="members[${memberCount}][birthday]" required>
            </div>

            <div class="form-group">
                <label for="nationality_${memberCount}">Nationality *</label>
                <input type="text" id="nationality_${memberCount}" name="members[${memberCount}][nationality]" required>
            </div>

            <div class="form-group">
                <label for="position_${memberCount}">Position</label>
                <input type="text" id="position_${memberCount}" name="members[${memberCount}][position]">
            </div>

            <div class="form-group">
                <label for="photo_${memberCount}">Member Photo *</label>
                <input type="file" id="photo_${memberCount}" name="members[${memberCount}][photo]" accept="image/*" required>
                <div class="image-preview"></div>
                <p class="file-requirements">
                    Accepted formats: JPG, PNG, WebP (Max size: 5MB)
                </p>
            </div>

            <button type="button" onclick="removeMemberForm(this)" class="remove-member-btn">
                Remove Member
            </button>
        `;
        container.appendChild(memberForm);
        
        // Add image preview functionality
        setupImagePreview(memberForm.querySelector('input[type="file"]'));
    }

    function removeMemberForm(button) {
        button.closest('.member-form').remove();
    }

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

    // Image preview functionality
    function setupImagePreview(fileInput) {
        if (!fileInput) return;
        
        fileInput.addEventListener('change', function(e) {
            const file = this.files[0];
            if (!file) return;
            
            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Please upload only JPG, PNG, or WebP images.');
                this.value = '';
                return;
            }
            
            // Validate file size (5MB max)
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            if (file.size > maxSize) {
                alert('File size must be less than 5MB.');
                this.value = '';
                return;
            }
            
            // Preview image
            const previewDiv = this.nextElementSibling;
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewDiv.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 100%; max-height: 200px;">`;
            };
            
            reader.readAsDataURL(file);
        });
    }

    // Setup image previews when page loads
    document.addEventListener('DOMContentLoaded', function() {
        const fileInputs = document.querySelectorAll('input[type="file"]');
        fileInputs.forEach(setupImagePreview);
    });
    </script>
</body>
</html>