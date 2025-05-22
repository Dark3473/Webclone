<?php
include('../config.php'); // Đảm bảo kết nối $conn thành công

$error_message = '';
$success_message = '';
$email_input = ''; // Lưu lại email đã nhập ban đầu
$email_to_reset = ''; // Email hợp lệ để reset
$show_reset_form = false; // Biến trạng thái: false = hiện form nhập email, true = hiện form reset pass

// --- Xử lý Form 1: Kiểm tra Email ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['check_email_submit'])) {
    $email_input = trim($_POST['email']);

    if (empty($email_input)) {
        $error_message = "Vui lòng nhập địa chỉ email.";
    } elseif (!filter_var($email_input, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Định dạng email không hợp lệ.";
    } else {
        // Sử dụng Prepared Statements
        $sql_check = "SELECT user_id FROM users WHERE email = ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("s", $email_input);
            if ($stmt_check->execute()) {
                $stmt_check->store_result();
                if ($stmt_check->num_rows > 0) {
                    // Email tồn tại -> Chuẩn bị hiển thị form reset
                    $show_reset_form = true;
                    $email_to_reset = $email_input; // Lưu email để dùng trong form reset
                } else {
                    $error_message = "Email không tồn tại trong hệ thống.";
                }
            } else {
                $error_message = "Lỗi truy vấn kiểm tra email.";
            }
            $stmt_check->close();
        } else {
            $error_message = "Lỗi chuẩn bị truy vấn kiểm tra email.";
        }
    }
}

// --- Xử lý Form 2: Cập nhật Mật khẩu mới ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['reset_password_submit'])) {
    $email_to_reset = $_POST['email_for_reset'] ?? ''; // Lấy email từ hidden input
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $show_reset_form = true; // Giữ hiển thị form reset nếu có lỗi

    if (empty($email_to_reset)) {
        $error_message = "Lỗi: Không xác định được email cần reset.";
        $show_reset_form = false; // Quay lại bước nhập email nếu không có email
    } elseif (empty($new_password) || empty($confirm_password)) {
        $error_message = "Vui lòng nhập cả mật khẩu mới và xác nhận mật khẩu.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Mật khẩu mới và xác nhận mật khẩu không khớp.";
    } elseif (strlen($new_password) < 6) { // Thêm kiểm tra độ dài tối thiểu (ví dụ)
         $error_message = "Mật khẩu mới phải có ít nhất 6 ký tự.";
    } else {
        // Mật khẩu hợp lệ -> Hash và cập nhật DB
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $sql_update = "UPDATE users SET password = ? WHERE email = ?";
        if ($stmt_update = $conn->prepare($sql_update)) {
            $stmt_update->bind_param("ss", $hashed_password, $email_to_reset);
            if ($stmt_update->execute()) {
                if ($stmt_update->affected_rows > 0) {
                    $success_message = "Mật khẩu đã được cập nhật thành công!";
                    $show_reset_form = false; // Không cần hiện form reset nữa
                } else {
                     $error_message = "Không thể cập nhật mật khẩu. Email có thể không chính xác hoặc không có thay đổi.";
                     // Vẫn giữ $show_reset_form = true; để người dùng thử lại nếu muốn
                }
            } else {
                $error_message = "Lỗi cập nhật mật khẩu.";
            }
            $stmt_update->close();
        } else {
             $error_message = "Lỗi chuẩn bị truy vấn cập nhật mật khẩu.";
        }
    }
}
// $conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - OpenEDG Clone</title>
    <link rel="stylesheet" href="auth.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
    <header class="auth-header">
        <div class="logo">
            <a href="../index.php"><img src="/edubeclone/assets/images/logo1.png" alt="OpenClone"></a>
        </div>
        <div class="auth-link">
            <a href="login.php">Log in</a>
            <span style="margin: 0 5px; color: #ccc;">|</span>
            <a href="signup.php">Sign up</a>
        </div>
    </header>

    <main class="auth-main">
        <div class="auth-slogan">
            <h1>Train Hard. Assess Randomly.<br> Certify Maybe.</h1>
        </div>

        <div class="auth-form-container">
            <h2><?php echo $show_reset_form ? 'Đặt lại mật khẩu' : 'Forgot your password?'; ?></h2>
            <div class="title-underline"></div>

            <?php
            // Hiển thị thông báo lỗi/thành công chung
            if (!empty($error_message)) {
                echo "<div class='message error-message'>" . htmlspecialchars($error_message) . "</div>";
            }
            if (!empty($success_message)) {
                 echo "<div class='message success-message'>" . htmlspecialchars($success_message) . "</div>";
            }
            ?>

            <?php if ($show_reset_form) : ?>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="reset-password-form">
                    <input type="hidden" name="email_for_reset" value="<?php echo htmlspecialchars($email_to_reset); ?>">

                    <div class="form-group">
                        <label for="new_password">Mật khẩu mới*</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Nhập mật khẩu mới" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu mới*</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" required>
                    </div>
                    <button type="submit" name="reset_password_submit" class="btn-submit btn-reset">Cập nhật mật khẩu</button>
                </form>
            <?php elseif (empty($success_message)) : // Chỉ hiện form email nếu chưa thành công ?>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="email">Email*</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email address" required value="<?php echo htmlspecialchars($email_input); ?>">
                    </div>
                    <button type="submit" name="check_email_submit" class="btn-submit btn-reset">Kiểm tra Email</button>
                 </form>
            <?php endif; ?>

            <p class="back-link">
                <a href="login.php">&larr; Back to Log in</a>
            </p>
        </div>
    </main>
</body>
</html>