<?php include '../actions/login-action.php';?>

<!DOCTYPE html>
<html>
    <head>
        <title>K-pop4Life</title>
        <meta name = "viewport" content = "width=device-width, initial-scale = 1">
        <link rel = "stylesheet" href = "../assets/css/style.css?v=<?php echo time(); ?>">
        <link rel = "stylesheet" href = "../assets/css/signup-login-style.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
    </head>

    <body>
        <header>
            <?php include 'navbar_guest.php'; ?>
        </header>

        <section class="signup-login">
            <form class="form" method="post" action="../actions/login-action.php">
                <h1>Login</h1>
                
                <div class = "input-field">     
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <span class="text-danger"><?= $username_error ?></span>

                <div class = "input-field">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <span class="text-danger"><?= $password_error ?></span>


                <button type = "submit" class = "button">Log In</button>
                
                <div class="switch-login-signup">
                    <p>Don't have an account? <a href="signup.php"> Sign up here</a></p>
                </div>
            </form>
        </section>

        <footer>
            <?php include 'footer.php'; ?>
        </footer>
        
        <script src="../assets/js/login-validation.js"></script>
    </body>
</html>