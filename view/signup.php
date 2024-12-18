<?php include '../actions/signup-action.php'; ?>
<!DOCTYPE html>
<html>
    <head>
        <title>K-pop4Life</title>
        <meta name = "viewport" content = "width=device-width, initial-scale = 1">
        <link rel = "stylesheet" href = "../assets/css/style.css?v=<?php echo time(); ?>">
        <link rel = "stylesheet" href = "../assets/css/signup-login-style.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" rel="stylesheet">
        <link rel="icon" type="image/x-icon" href="/FINAL PROJECT/assets/images/k-pop4life.png">
    </head>

    <body>
        <header>
            <?php include 'navbar_guest.php'; ?>
        </header>

        <section class="signup-login">
            <form class="form" method="post">
                <h1>Signup</h1>

                <div class = "input-field">     
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required value="<?= $username ?>">
                </div>
                <span class="text-danger"><?= $username_error ?></span>

                <div class = "input-field">
                    <label for="country">Country:</label>
                    <select id="country" name="country" required value="<?= $country ?>">
                        <option value="argentina">Argentina</option>
                        <option value="brazil">Brazil</option>
                        <option value="china">China</option>
                        <option value="denmark">Denmark</option>
                        <option value="egypt">Egypt</option>
                        <option value="france">France</option>
                        <option value="ghana">Ghana</option>
                        <option value="hungary">Hungary</option>
                    </select>
                </div>

                <div class = "input-field">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required value="<?= $email ?>">
                </div>
                <span class="text-danger"><?= $email_error ?></span>

                <div class = "input-field">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <span class="text-danger"><?= $password_error ?></span>

                <div class = "input-field">
                    <label for="password">Confirm Password:</label>
                    <input type="password" id="password-confirm" name="confirm_password" required>
                </div>
                <span class="text-danger"><?= $confirm_password_error ?></span>

                <button type = "submit" class = "button">Sign Up</button>

                <div class="switch-login-signup">
                    <p>Already have an account? <a href="login.php"> Log in here</a></p>
                </div>
            </form>
        </section>

        <footer>
            <?php include 'footer.php'; ?>
        </footer>
        <script src="../assets/js/signup-validation.js"></script>
    </body>
</html>