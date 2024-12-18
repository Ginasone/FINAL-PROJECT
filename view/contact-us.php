<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="/FINAL PROJECT/assets/css/style.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" href="/FINAL PROJECT/assets/css/contact.css">
        <title>Contact Us - K-pop4Life</title>
        <link rel="icon" type="image/x-icon" href="/FINAL PROJECT/assets/images/k-pop4life.png">
        <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>

    <body>
        <header>
            <?php include 'navbar_in.php'; ?>
        </header>

        <main class="contact-container">
            <div class="contact-header">
                <h1>Get in Touch</h1>
                <p>Have questions or suggestions? We'd love to hear from you!</p>
            </div>

            <div class="success-message" id="successMessage">Your message has been sent successfully!</div>
            <div class="error-message" id="errorMessage">There was an error sending your message. Please try again.</div>

            <form class="contact-form" id="contactForm">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" required>
                </div>

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required></textarea>
                </div>

                <button type="submit" class="contact-button">Send Message</button>
            </form>

            <div class="contact-info">
                <h2>Other Ways to Connect</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="icon">
                            <span class="material-symbols-outlined">email</span>
                        </div>
                        <div class="info-content">
                            <h3>Email</h3>
                            <p>support@kpop4life.com</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="icon">
                            <span class="material-symbols-outlined">schedule</span>
                        </div>
                        <div class="info-content">
                            <h3>Response Time</h3>
                            <p>Within 24 hours</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="icon">
                            <span class="material-symbols-outlined">forum</span>
                        </div>
                        <div class="info-content">
                            <h3>Social Media</h3>
                            <p>Follow us for updates</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer>
            <?php include 'footer.php'; ?>
        </footer>
    </body>
</html>