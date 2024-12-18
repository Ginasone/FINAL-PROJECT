

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="/FINAL PROJECT/assets/css/kpop.css">
        <title>K-pop4Life</title>
        <title>Favicon Demo</title>
        <link rel="icon" type="image/x-icon" href="">
    </head>
    <body>
        <div class="vid-container">
            <video id="myVideo" playsinline autoplay>
                <?php
                    $videoFile = "../assets/vid/2023 READY TO BE  K-POP YEAR END MASHUP (200+ SONGS).mp4";
                ?>
                <source src="<?php echo htmlspecialchars($videoFile); ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <h1 id="first">Click for a present!</h1>
            <h1 id="second">Click the screen to enter your magical kpop world!</h1>
        </div>
        <script src="/FINAL PROJECT/assets/js/javascript.js"></script>
    </body>
</html>