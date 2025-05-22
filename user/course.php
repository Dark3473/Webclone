<?php
include('../config.php'); // Đảm bảo đường dẫn này đúng
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../auth/login.php'); // Đảm bảo đường dẫn này đúng
    exit;
}

$user_id = $_SESSION['user_id']; // Lấy user_id từ session

// --- Xác định Module ID ---
$module_id = null;

// *** SỬA ĐỔI ĐIỀU KIỆN KIỂM TRA TẠI ĐÂY ***
if (isset($_GET['module_id']) && filter_var($_GET['module_id'], FILTER_VALIDATE_INT) !== false) {
    $module_id = (int)$_GET['module_id'];
} elseif (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $course_id_from_url = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT module_id FROM edubase.modules WHERE course_id = ? ORDER BY module_order ASC LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $course_id_from_url);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $module_id = $row['module_id'];
        } else {
             echo "Lỗi: Không tìm thấy module nào cho khóa học này.";
             // exit;
        }
        $stmt->close();
    } else {
        echo "Lỗi chuẩn bị câu lệnh lấy module đầu tiên: " . $conn->error;
        // exit;
    }
} else {
    $stmt = $conn->prepare("SELECT course_id FROM edubase.course_management WHERE user_id = ? AND `Trạng thái học` IN ('active', 'completed') ORDER BY enrollment_date DESC LIMIT 1");
     if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $course_id_from_enrollment = $row['course_id'];
            $stmt_mod = $conn->prepare("SELECT module_id FROM edubase.modules WHERE course_id = ? ORDER BY module_order ASC LIMIT 1");
            if ($stmt_mod) {
                 $stmt_mod->bind_param("i", $course_id_from_enrollment);
                 $stmt_mod->execute();
                 $result_mod = $stmt_mod->get_result();
                 if ($row_mod = $result_mod->fetch_assoc()) {
                     $module_id = $row_mod['module_id'];
                 }
                 $stmt_mod->close();
            } else {
                 echo "Lỗi chuẩn bị câu lệnh lấy module đầu tiên từ enrollment: " . $conn->error;
            }
        }
        $stmt->close();
    } else {
         echo "Lỗi chuẩn bị câu lệnh lấy khóa học đã đăng ký: " . $conn->error;
    }
}

// Kiểm tra lại module_id sau tất cả các bước
if ($module_id === null) {
    echo "Lỗi: Không thể xác định module để hiển thị.";
    exit;
}

// --- Lấy thông tin Course và Module hiện tại ---
$current_course_id = null;
$module_name = "Unknown Module";
$course_name = "Unknown Course";

$sql_module = "SELECT module_name, course_id FROM edubase.modules WHERE module_id = ?";
$stmt_module = $conn->prepare($sql_module);
if ($stmt_module) {
    $stmt_module->bind_param("i", $module_id);
    $stmt_module->execute();
    $result_module = $stmt_module->get_result();
    if ($row_module = $result_module->fetch_assoc()) {
        $module_name = $row_module['module_name'];
        $current_course_id = $row_module['course_id'];
    } else {
         echo "Lỗi: Module ID không hợp lệ.";
         $conn->close();
         exit;
    }
    $stmt_module->close();
} else {
    echo "Lỗi chuẩn bị câu lệnh lấy chi tiết module: " . $conn->error;
    $conn->close();
    exit;
}

// Lấy tên Khóa học
if ($current_course_id) {
    $sql_course = "SELECT course_name FROM edubase.courses WHERE course_id = ?";
    $stmt_course = $conn->prepare($sql_course);
    if ($stmt_course) {
        $stmt_course->bind_param("i", $current_course_id);
        $stmt_course->execute();
        $result_course = $stmt_course->get_result();
        if ($row_course = $result_course->fetch_assoc()) {
            $course_name = $row_course['course_name'];
        }
        $stmt_course->close();
    } else {
        echo "Lỗi chuẩn bị câu lệnh lấy tên khóa học: " . $conn->error;
    }
}

// --- Lấy danh sách Modules cho sidebar ---
$all_modules = [];
if ($current_course_id) {
    $sql_all_modules = "SELECT module_id, module_name FROM edubase.modules WHERE course_id = ? ORDER BY module_order ASC";
    $stmt_all_modules = $conn->prepare($sql_all_modules);
    if ($stmt_all_modules) {
        $stmt_all_modules->bind_param("i", $current_course_id);
        $stmt_all_modules->execute();
        $result_all_modules = $stmt_all_modules->get_result();
        while ($row = $result_all_modules->fetch_assoc()) {
            // Bạn có thể thêm thông tin trạng thái hoàn thành module ở đây nếu cần
            $all_modules[] = $row;
        }
        $stmt_all_modules->close();
    } else {
        echo "Lỗi chuẩn bị câu lệnh lấy danh sách modules: " . $conn->error;
    }
}

// Đặt encoding UTF-8
$conn->set_charset("utf8mb4");

// --- Lấy danh sách Topics cho Module này ---
$topics = [];
$sql_topics = "SELECT topic_id, topic_name FROM edubase.topics WHERE module_id = ? ORDER BY topic_order ASC";
$stmt_topics = $conn->prepare($sql_topics);
if ($stmt_topics) {
    $stmt_topics->bind_param("i", $module_id);
    $stmt_topics->execute();
    $result_topics = $stmt_topics->get_result();
    while ($row = $result_topics->fetch_assoc()) {
        $topics[] = $row;
    }
    $stmt_topics->close();
} else {
    echo "Lỗi chuẩn bị câu lệnh lấy topics: " . $conn->error;
}

// --- *** THÊM BƯỚC TIỀN XỬ LÝ TOPICS *** ---
foreach ($topics as $index => &$topic) { // Sử dụng tham chiếu '&' để sửa trực tiếp mảng
    // 1. Xác định icon và loại topic
    $topic['icon_class'] = 'fa-book-open'; // Mặc định
    $topic['is_test'] = false;
    if (stripos($topic['topic_name'], 'test') !== false ||
        stripos($topic['topic_name'], 'quiz') !== false ||
        stripos($topic['topic_name'], 'kiểm tra') !== false) {
        $topic['icon_class'] = 'fa-tasks';
        $topic['is_test'] = true;
    } elseif (stripos($topic['topic_name'], 'learning') !== false) {
        $topic['icon_class'] = 'fa-pencil-alt';
    } elseif (stripos($topic['topic_name'], 'page') !== false) {
        $topic['icon_class'] = 'fa-file-alt';
    }

    // 2. Xác định có phải lesson đầu tiên không (để mở mặc định)
    $topic['is_first'] = ($index === 0);

    // 3. (Tùy chọn) Lấy trạng thái hoàn thành topic ở đây nếu cần
    // $topic['is_completed'] = check_topic_completion($user_id, $topic['topic_id']); // Ví dụ
}
unset($topic); // Hủy tham chiếu sau vòng lặp

$conn->close(); // Đóng kết nối Database

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module: <?php echo htmlspecialchars($module_name); ?> - <?php echo htmlspecialchars($course_name); ?></title>
    <link rel="stylesheet" href="../index.css"> <link rel="stylesheet" href="dash.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<header class="dashboard-header">
    <div class="logo">
        <a href="../index.php"> <img src="/edubeclone/assets/images/logo1.png" alt="OpenClone Logo"></a>
    </div>
    <nav class="center-nav">
        <a href="../index.php">Home</a>
        <a href="dashboard.php" class="active">Learning</a>
    </nav>
    <div class="user-actions">
        <a href="#" title="Messages"><i class="fas fa-comment-alt"></i></a>
        <div class="profile-dropdown-container">
            <a href="#" title="Profile" class="user-profile-icon" id="profile-toggle"><i class="fas fa-user-circle"></i></a>
            <div class="profile-dropdown" id="profile-menu">
                <a href="account_setting.php"><i class="fas fa-cog"></i> Account Settings</a>
                <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
            </div>
        </div>
    </div>
</header>

<div class="dashboard-container">
    <main class="dashboard-content" id="course-view-content">

        <div class="course-view-header">
            <h1><?php echo htmlspecialchars($course_name); ?></h1>
            <div class="progress-section">
                 <div class="progress-tracker">
                    <label for="activity-progress">Course Activity Tracker: ...% covered</label>
                    <progress id="activity-progress" value="0" max="100"></progress> <a href="#" class="details-link">Details <i class="fas fa-chevron-down"></i></a>
                </div>
                <div class="progress-tracker">
                    <label for="completion-progress">Course Completion Progress: ...% accomplished</label>
                    <progress id="completion-progress" value="0" max="100" class="completion"></progress> <a href="#" class="details-link">Details <i class="fas fa-chevron-down"></i></a>
                </div>
            </div>
        </div>

        <div class="course-view-main">
            <nav class="module-list-sidebar">
                <h4>Course Modules</h4>
                <ul>
                    <?php if (!empty($all_modules)): ?>
                        <?php foreach ($all_modules as $mod): ?>
                            <li>
                                <a href="?module_id=<?php echo $mod['module_id']; ?>"
                                   class="<?php echo ($mod['module_id'] == $module_id) ? 'active' : ''; ?>">
                                    <i class="fas fa-check-circle completion-icon"></i> <?php /* Thêm class completed nếu module đã hoàn thành */ ?>
                                    <?php echo htmlspecialchars($mod['module_name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No modules found for this course.</li>
                    <?php endif; ?>
                </ul>
            </nav>

            <section class="module-content-display">
                <h2><?php echo htmlspecialchars($module_name); ?></h2>
                <div class="lesson-list">
                    <?php if (!empty($topics)): ?>
                        <?php foreach ($topics as $topic): ?>
                            <?php // Giờ không cần khối PHP tính toán ở đây nữa ?>
                            <div class="lesson-item <?php echo $topic['is_first'] ? 'open' : ''; // Sử dụng giá trị đã chuẩn bị ?>">
                                <div class="lesson-header" data-topic-id="<?php echo $topic['topic_id']; ?>">
                                    <span class="lesson-info">
                                        <i class="fas <?php echo $topic['icon_class']; // Sử dụng giá trị đã chuẩn bị ?> lesson-icon"></i>
                                        <?php echo htmlspecialchars($topic['topic_name']); ?>
                                        <i class="fas fa-check-circle completion-icon-inline <?php // echo $topic['is_completed'] ? 'completed' : ''; ?>"></i>
                                    </span>
                                    <button class="toggle-lesson">
                                        <i class="fas <?php echo $topic['is_first'] ? 'fa-chevron-up' : 'fa-chevron-down'; // Sử dụng giá trị đã chuẩn bị ?>"></i>
                                    </button>
                                </div>
                                <div class="lesson-body" <?php /* CSS sẽ xử lý việc ẩn hiện dựa vào class 'open' */ ?>>
                                    <?php if ($topic['is_test']): // Sử dụng giá trị đã chuẩn bị ?>
                                        <a href="take_test.php?module_id=<?php echo $module_id; ?>&topic_id=<?php echo $topic['topic_id']; ?>" class="btn-action btn-start-test">
                                            Bắt đầu Kiểm tra
                                        </a>
                                    <?php else: ?>
                                        <a href="view.php?topic_id=<?php echo $topic['topic_id']; ?>&module_id=<?php echo $module_id; ?>" class="btn-action btn-resume">
                                             Xem Bài học
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Không tìm thấy bài học nào cho module này.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- Xử lý đóng/mở các lesson item ---
    const lessonList = document.querySelector('.lesson-list');
    if (lessonList) {
        lessonList.addEventListener('click', function(event) {
            const header = event.target.closest('.lesson-header');
            if (header) {
                const lessonItem = header.closest('.lesson-item');
                const icon = header.querySelector('.toggle-lesson i');

                if (lessonItem && icon) {
                    const wasOpen = lessonItem.classList.contains('open');

                    // (Tùy chọn) Đóng tất cả các item khác trước khi mở item mới
                    // lessonList.querySelectorAll('.lesson-item.open').forEach(openItem => {
                    //     if (openItem !== lessonItem) {
                    //         openItem.classList.remove('open');
                    //         const otherIcon = openItem.querySelector('.toggle-lesson i');
                    //         if (otherIcon) {
                    //             otherIcon.classList.remove('fa-chevron-up');
                    //             otherIcon.classList.add('fa-chevron-down');
                    //         }
                    //     }
                    // });

                    // Toggle item hiện tại
                    lessonItem.classList.toggle('open', !wasOpen);
                    icon.classList.toggle('fa-chevron-up', !wasOpen);
                    icon.classList.toggle('fa-chevron-down', wasOpen);
                }
            }
        });
    }

    // --- JavaScript cho Profile Dropdown ---
    const profileToggle = document.getElementById('profile-toggle');
    const profileMenu = document.getElementById('profile-menu');
    if(profileToggle && profileMenu) {
        profileToggle.addEventListener('click', function(event) {
            event.preventDefault();
            profileMenu.style.display = profileMenu.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', function(event) {
            if (!profileToggle.contains(event.target) && !profileMenu.contains(event.target)) {
                profileMenu.style.display = 'none';
            }
        });
    }
});
</script>
</body>
</html>