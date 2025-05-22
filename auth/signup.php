<?php
session_start();
include '../config.php';
$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = isset($_POST['full_name']) ? $_POST['full_name'] : '';
    
    // Extract name from email if full_name not provided
    if (empty($full_name)) {
        $full_name = explode('@', $email)[0];
    }
    
    // Validate input
    if (empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long";
    } else {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error_message = "Email already exists. Please use a different email or login.";
            $check_stmt->close();
        } else {
            $check_stmt->close();
            
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Get current date
            $current_date = date('Y-m-d');
            
            // Insert new user
            $insert_stmt = $conn->prepare("INSERT INTO users (full_name, email, password, date) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssss", $full_name, $email, $hashed_password, $current_date);
            
            if ($insert_stmt->execute()) {
                $success_message = "Registration successful! You can now log in.";
                
                // Optional: Auto-login after signup
                /*
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                $_SESSION['logged_in'] = true;
                
                // Redirect to dashboard or home page
                header("Location: ../index.php");
                exit();
                */
            } else {
                $error_message = "Registration failed: " . $conn->error;
            }
            
            $insert_stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - OpenEDG</title>
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
            <a href="login.php">Log in</a>
        </div>
    </header>

    <main class="auth-main">
        <div class="auth-slogan">
            <h1>Train Hard. Assess Randomly.<br> Certify Maybe.</h1>
        </div>

        <div class="auth-form-container">
            <h2>Sign up</h2>
            <div class="title-underline"></div>
            
            <?php if(!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if(!empty($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name">
                    <small class="form-text">If left blank, name will be taken from email</small>
                </div>
                <div class="form-group">
                    <label for="email">Email*</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password*</label>
                    <input type="password" id="password" name="password" required>
                    <small class="form-text">Minimum 6 characters</small>
                </div>
                <div class="form-group">
                    <label for="confirm-password">Confirm Password*</label>
                    <input type="password" id="confirm-password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn-submit">Create Account</button>
            </form>
            <p class="switch-form-link">
                Already have an account? <a href="login.php">Log in</a>
            </p>
        </div>
    </main>

</body>
</html>