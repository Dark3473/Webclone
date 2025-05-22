<?php
session_start();
include '../config.php';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error_message = "Email and password are required";
    } else {
        // Prepare SQL statement using mysqli prepared statements
        $stmt = $conn->prepare("SELECT user_id, full_name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Check if user exists
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // For testing with plain text passwords (not recommended for production)
            if ($password === $user['password']) {
                // Password is correct, create session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                
                // Redirect to dashboard or home page
                header("Location: ../user/dashboard.php");
                exit();
            } 
            // For hashed passwords (recommended)
            else if (password_verify($password, $user['password'])) {
                // Password is correct, create session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['logged_in'] = true;
                
                // Redirect to dashboard or home page
                header("Location: ../user/dashboard.php");
                exit();
            }
            else {
                // Password is incorrect
                $error_message = "Invalid email or password";
            }
        } else {
            // User not found
            $error_message = "Invalid email or password";
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - OpenEDG</title>
    <link rel="stylesheet" href="auth.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
     <header class="auth-header">
        <div class="logo">
            <a href="../index.php">
                <img src="/edubeclone/assets/images/logo1.png" alt="OpenClone">
            </a>
        </div>
        <div class="auth-link">
            <a href="signup.php">Sign up</a>
        </div>
    </header>

     <main class="auth-main">
        <div class="auth-slogan">
             <h1>Train Hard. Assess Randomly.<br> Certify Maybe.</h1>
        </div>

        <div class="auth-form-container">
            <h2>Log in</h2>
            <div class="title-underline"></div>
            
            <?php if(!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="email">Email*</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password*</label>
                    <input type="password" id="password" name="password" required>
                </div>
                 <div class="form-options">
                     <a href="fogot_pass.php" class="forgot-password">Forgot password?</a>
                 </div>
                <button type="submit" class="btn-submit">Log In</button>
            </form>
            <p class="switch-form-link">
                Don't have an account? <a href="signup.php">Sign up</a>
            </p>
        </div>
    </main>

</body>
</html>