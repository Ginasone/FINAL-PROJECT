<?php
session_start();
include '../db/db-config.php'; 
$dbConnection = getDatabaseConnection();

$username = "";
$password = "";

$username_error = "";
$password_error = "";

$error = false;
$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username)){
        $username_error = "Username is required";
        $error = true;
    } 
    if (empty($password)) {
        $password_error = "Password is required";
        $error = true;
    }
    if (!$error) {
        try {
            $dbConnection = getDatabaseConnection();

            // Query to fetch user details
            $query = "SELECT user_id, username, password, role FROM users WHERE username = ?";
            $stmt = $dbConnection->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Detailed password verification debugging
                echo "Entered Password: " . $password . "<br>";
                echo "Stored Hash: " . $user['password'] . "<br>";
                echo "Hash Length: " . strlen($user['password']) . "<br>";

                // Verify password with more detailed checking
                $password_check = password_verify($password, $user['password']);
                
                echo "Password Verification Result: " . ($password_check ? 'TRUE' : 'FALSE') . "<br>";

                if ($password_check) {
                    // Regenerate session and set session variables
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    // Debug session
                    echo "Session User ID: " . $_SESSION['user_id'] . "<br>";
                    echo "Session Username: " . $_SESSION['username'] . "<br>";
                    echo "Session Role: " . $_SESSION['role'] . "<br>";

                    // Redirect based on role
                    if ($user['role'] == 2) {
                        $admin_path = "../view/admin/admin-dashboard.php";
                        if (file_exists($admin_path)) {
                            header("Location: " . $admin_path);
                            exit;
                        } else {
                            die("Admin dashboard file not found: " . $admin_path);
                        }
                    } else {
                        $user_path = "../view/user-dashboard.php";
                        if (file_exists($user_path)) {
                            header("Location: " . $user_path);
                            exit;
                        } else {
                            die("User dashboard file not found: " . $user_path);
                        }
                    }
                } else {
                    $password_error = "Invalid password.";
                }
            } else {
                $username_error = "Invalid username or password.";
            }
            $stmt->close();
            $dbConnection->close();
        } catch (Exception $e) {
            die("Error: " . htmlspecialchars($e->getMessage()));
        }
    }
}
?>