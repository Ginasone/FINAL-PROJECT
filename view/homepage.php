<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="/FINAL PROJECT/assets/css/style.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" href="/FINAL PROJECT/assets/css/home.css?v=<?php echo time(); ?>">
        <title>K-pop4Life</title>
        <link rel="icon" type="image/x-icon" href="/FINAL PROJECT/assets/images/k-pop4life.png">
        <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    </head>
    <body>
        <header>
            <?php include 'navbar_guest.php'; ?>
        </header>

        <!-- Hero Section -->
        <section class="hero">
            <div class="content">
                <h1>Unleash Your Inner K-pop Fan Today!</h1>
                <p>Dive into the vibrant world of K-pop where you can share, discover, and connect with fellow fans. Join us to stay updated on the latest news, videos, and performances from your favorite artists.</p>
                <div class="cta-buttons">
                    <a href="/FINAL PROJECT/view/signup.php" class="cta-primary">Join!</a>
                    <a href="about.php" class="cta-secondary">Learn More!</a>
                </div>
            </div>
            <img src="/FINAL PROJECT/assets/images/unleash.jpeg" alt="kpop">
        </section>

        <!-- Connect Section -->
        <section class="connect">
            <p class="section-label">Connect</p>
            <div class="content">
                <h1>Your Ultimate K-pop Community Awaits You</h1>
                <p>K-pop4Life is your one-stop destination for all things K-pop. Share videos, news, and connect with fans worldwide!</p>
                <div class="feature-list">
                    <div class="feature">
                        <h2>Share Videos</h2>
                        <p>Upload and enjoy music videos, performances, and concerts from your favorite artists.</p>
                    </div>
                    <div class="feature">
                        <h2>Stay Informed</h2>
                        <p>Get the latest news with reliable sources to keep you updated.</p>
                    </div>
                </div>
                <div class="cta-buttons">
                    <a href="/FINAL PROJECT/view/signup.php" class="cta-primary">Join!</a>
                    <a href="#explore" class="cta-secondary">Explore ></a>
                </div>
            </div>
            <img src="/FINAL PROJECT/assets/images/community.jpeg" alt="Connect">
        </section>

        <!-- Explore Section -->
        <section id="explore" class="explore">
            <p class="section-label">Explore</p>
            <div class="content">
                <h1>Discover Our Exciting K-pop Features</h1>
                <p>Dive into a world of K-pop with our extensive features. From the latest videos to in-depth news and member guides, we have everything you need.</p>
                
                <div class="features-grid">
                    <div class="feature">
                        <h2>Engaging Videos for Every K-pop Fan</h2>
                        <p>Watch the most captivating K-pop performances and music videos.</p>
                    </div>
                    <div class="feature">
                        <h2>Stay Updated with the Latest News</h2>
                        <p>Get timely updates on your favorite K-pop artists.</p>
                    </div>
                    <div class="feature">
                        <h2>Comprehensive Member Guides for Fans</h2>
                        <p>Learn everything about your favorite K-pop idols.</p>
                    </div>
                </div>
                
                <div class="cta-buttons">
                    <a href="/FINAL PROJECT/view/about.php" class="cta-primary">Learn More!</a>
                    <a href="/FINAL PROJECT/view/signup.php" class="cta-secondary">Sign Up</a>
                </div>
            </div>
        </section>

        <!-- Blog Section -->
        <section class="blog">
            <p class="section-label">Blog</p>
            <div class="content">
                <h1>Latest K-pop Trends</h1>
                <p>Stay updated with K-pop's hottest news and trends.</p>
                <a href="/FINAL PROJECT/view/news.php" class="cta-primary">View all</a>
            </div>
            <img src="/FINAL PROJECT/assets/images/snsd.jpeg" alt="kpop">
        </section>

        <!-- Join Section -->
        <section class="join">
            <div class="content">
                <h1>Join the K-pop4Life Family</h1>
                <p>Become a part of our vibrant K-pop community where fans connect, share, and celebrate their favorite artists together. Sign up today to access exclusive content, news, and connect with fellow fans from around the world!</p>
                <div class="cta-buttons">
                    <a href="/FINAL PROJECT/view/signup.php" class="cta-primary">Sign Up</a>
                    <a href="/FINAL PROJECT/view/about.php" class="cta-secondary">Learn More!</a>
                </div>
            </div>
        </section>

        <footer>
            <?php include 'footer.php'; ?>
        </footer>

        <script>
            // Smooth scroll functionality
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    if (targetId === "#") return;
                    
                    const target = document.querySelector(targetId);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        </script>
    </body>
</html>