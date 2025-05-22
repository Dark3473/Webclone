<?php
// index.php
include('config.php');
session_start();
// Fetch courses from the database
$sql_courses = "SELECT c.*, u.full_name as teacher_name
                FROM courses c
                LEFT JOIN users u ON c.teacher_id = u.user_id
                ORDER BY c.date DESC";
$result_courses = $conn->query($sql_courses);
// Kiểm tra xem người dùng đã đăng nhập chưa
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Lấy tên người dùng nếu có (giả sử bạn lưu vào 'username' khi đăng nhập)
$username = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Khách'; // Tên mặc định nếu chưa đăng nhập hoặc không có username
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clone Courses & Certifications</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.php">
                <img src="assets/images/logo1.png" alt="OpenClone">
            </a>
        </div>
        <?php if ($is_logged_in): ?>
            <div class="auth-buttons"> <a href="user/dashboard.php" class="btn btn-dashboard">Dashboard</a>
        <?php else: ?>
            <div class="auth-buttons">
                <a href="auth/login.php" class="btn btn-login">Log in</a> <a href="auth/signup.php" class="btn btn-signup">Sign up</a> </div>
        <?php endif; ?>
    </header>

    <section class="intro-section">
        <div class="intro-content">
            <h1>Bắt đầu hành trình chinh phục công nghệ!</h1>
            <p>Tại đây, bạn có thể tìm thấy các khóa học đa dạng về Python, Phát triển Web (HTML, CSS, JS),... và các chứng chỉ chuyên nghiệp sắp ra mắt. Hãy khám phá ngay!</p>
            <a href="#content-start" class="btn btn-primary intro-button">Khám phá</a>
        </div>
    </section>

    <nav class="filter-bar">
        <div class="tabs">
            <button class="tab active" data-target="courses-content">Courses</button>
            <button class="tab" data-target="certifications-content">Certifications</button>
        </div>
    </nav>

    <div id="content-start"></div>

    <main class="course-grid content-section active" id="courses-content">
        <?php
        // Check if there are courses
        if ($result_courses && $result_courses->num_rows > 0) {
            $cardStyles = ['card-html', 'card-css', 'card-python-dark', 'card-python-blue', 'card-js', 'card-python-advanced'];
            $styleCount = count($cardStyles);
            $i = 0;

            while($course = $result_courses->fetch_assoc()) {
                $currentStyle = $cardStyles[$i % $styleCount];
                $i++;
                $level = 'beginner'; // Logic xác định level như cũ
                if (stripos($course['course_name'], 'advanced') !== false || stripos($course['description'], 'advanced') !== false) {
                    $level = 'advanced';
                } elseif (stripos($course['course_name'], 'intermediate') !== false || stripos($course['description'], 'intermediate') !== false) {
                    $level = 'intermediate';
                }
        ?>
        <article class="course-card <?php echo $currentStyle; ?>" data-course-id="<?php echo $course['course_id']; ?>">
            <header class="card-header">
                <div>
                    <div class="course-title-short"><?php echo htmlspecialchars($course['course_name']); ?></div>
                    <?php if (!empty($course['teacher_name'])): ?>
                    <div class="course-provider"><?php echo htmlspecialchars($course['teacher_name']); ?></div>
                    <?php else: ?>
                    <div class="course-provider">INSTITUTE</div>
                    <?php endif; ?>
                </div>
                <div class="course-icon">
                    <?php if (stripos($course['course_name'], 'python') !== false): ?>
                    Python<span>io</span>
                    <?php elseif (stripos($course['course_name'], 'html') !== false): ?>
                    HTML<span>5</span>
                     <?php elseif (stripos($course['course_name'], 'css') !== false): ?>
                    CSS<span>3</span>
                    <?php elseif (stripos($course['course_name'], 'javascript') !== false): ?>
                    JS<span></span>
                    <?php endif; ?>
                </div>
            </header>
            <section class="card-body">
                <?php echo htmlspecialchars($course['description']); ?>
            </section>
            <footer class="card-footer">
                <span class="level-tag <?php echo $level; ?>"><?php echo strtoupper($level); ?></span>
                Created: <?php echo date('d-m-Y', strtotime($course['date'])); ?>
            </footer>
        </article>
        <?php
            } // end while
        } else {
            echo '<div class="no-courses" style="grid-column: 1 / -1;">No courses available at the moment.</div>'; // Thêm style để chiếm hết hàng
        }
        ?>
    </main>

    <section class="course-grid content-section" id="certifications-content">
        <?php
            // Đây là nơi bạn đặt đoạn mã của mình (đã bỏ dấu {} thừa)
            // Cách 2: Chỉ hiển thị một thông báo chung
            echo '<div class="no-courses" style="grid-column: 1 / -1;">Certifications are coming soon! Stay tuned.</div>';
        ?>
    </section>


    <section class="feature-section">
        <div class="feature-container">
             <div class="feature-content">
                 <h2>Nâng Tầm Kỹ Năng Với Chứng Chỉ Chuyên Nghiệp</h2>
                 <p>Các khóa học của chúng tôi không chỉ cung cấp kiến thức nền tảng mà còn chuẩn bị cho bạn các kỳ thi chứng chỉ quốc tế được công nhận bởi CLone Testing Service. Hãy khẳng định năng lực và mở rộng cơ hội sự nghiệp!</p>
                 <a href="#certifications-content" class="btn btn-feature" onclick="showTab('certifications-content')">Khám phá các Chứng chỉ</a>
             </div>
             <div class="feature-image">
                 <img src="assets/images/certificate.png" alt="Chứng chỉ chuyên nghiệp">
             </div>
        </div>
    </section>

    <!-- Enrollment Modal -->
    <div id="enrollment-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Enroll in Course</h2>
            <p id="modal-message">Would you like to enroll in this course?</p>
            <div class="modal-buttons">
                <button id="confirm-enrollment" class="btn btn-primary">Yes, Enroll</button>
                <button id="cancel-enrollment" class="btn">Cancel</button>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <div class="footer-left">
                <div class="footer-brand">
                    <a href="/">
                        <img src="assets/images/underlogo.png" alt="Clone Interactive">
                    </a>
                    Edube Clone&trade;
                </div>
                <p>Brought to you by OpenClone: Massive Clone Dev Group</p>
                <p>Train Hard. Assess Randomly. Certify Maybe.</p>
                <p class="copyright">2025 &copy; All Rights Reserved</p>
            </div>
            <div class="footer-right">
                <nav class="footer-nav-top">
                    <a href="#">About</a>
                    <a href="#">Contact</a>
                    <a href="#">Privacy Policy</a>
                </nav>
                <hr class="footer-divider">
                <nav class="footer-nav-bottom">
                    <a href="#">Sandbox</a>
                    <a href="#">Store</a>
                </nav>
            </div>
        </div>
    </footer>
    
    <script>
        
        const userIsLoggedIn = <?php echo isset($_SESSION['logged_in']) && $_SESSION['logged_in'] ? 'true' : 'false'; ?>;
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html>