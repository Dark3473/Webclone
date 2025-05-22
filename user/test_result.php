<?php
session_start();

// Kiểm tra xem có thông tin kết quả trong session không
if (!isset($_SESSION['last_test_score']) || !isset($_SESSION['last_test_module_id'])) {
    // Nếu không có, chuyển về trang dashboard hoặc trang course_view
    // Lấy module_id từ session nếu có để quay về đúng module
    $redirect_url = isset($_SESSION['last_test_module_id']) ? 'course_view.php?module_id=' . $_SESSION['last_test_module_id'] : 'dashboard.php';
    header('Location: ' . $redirect_url);
    exit;
}

// Lấy thông tin từ session
$score = $_SESSION['last_test_score'];
$module_id = $_SESSION['last_test_module_id'];
$correct_count = $_SESSION['last_test_correct_count'] ?? 'N/A'; // Lấy số câu đúng nếu có
$total_questions = $_SESSION['last_test_total_questions'] ?? 'N/A'; // Lấy tổng số câu nếu có

// Xóa thông tin khỏi session sau khi đã lấy để tránh hiển thị lại khi refresh
unset($_SESSION['last_test_score']);
unset($_SESSION['last_test_module_id']);
unset($_SESSION['last_test_correct_count']);
unset($_SESSION['last_test_total_questions']);

// (Tùy chọn) Lấy tên module từ DB để hiển thị thân thiện hơn
include('../config.php'); // Cần kết nối lại DB nếu muốn lấy thêm thông tin
$module_name = "Module"; // Mặc định
if (isset($module_id) && $conn) {
     $conn->set_charset("utf8mb4");
     $stmt = $conn->prepare("SELECT module_name FROM edubase.modules WHERE module_id = ?");
     if($stmt) {
         $stmt->bind_param("i", $module_id);
         $stmt->execute();
         $result = $stmt->get_result();
         if ($row = $result->fetch_assoc()) {
             $module_name = $row['module_name'];
         }
         $stmt->close();
     }
     $conn->close();
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả Kiểm tra</title>
    <link rel="stylesheet" href="../index.css"> <?php // CSS chung ?>
    <link rel="stylesheet" href="dash.css"> <?php // CSS dashboard ?>
     <style>
        .result-container { padding: 30px; max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); text-align: center; }
        .result-container h1 { color: #333; margin-bottom: 15px; }
        .result-score { font-size: 2.5em; font-weight: bold; color: #007bff; margin: 20px 0; }
        .result-details { margin-bottom: 25px; color: #555; }
        .result-actions a {
             display: inline-block; padding: 10px 20px; margin: 5px; border-radius: 5px; text-decoration: none; transition: background-color 0.3s ease;
        }
        .btn-review { background-color: #ffc107; color: #333; } /* Nút xem lại (nếu có chức năng) */
        .btn-back-module { background-color: #6c757d; color: white; }
        .btn-dashboard { background-color: #28a745; color: white; }
        .btn-review:hover { background-color: #e0a800; }
        .btn-back-module:hover { background-color: #5a6268; }
        .btn-dashboard:hover { background-color: #218838; }
    </style>
</head>
<body>
     <?php // Include header dashboard nếu cần ?>
    <div class="result-container">
        <h1>Kết quả bài kiểm tra</h1>
        <h2><?php echo htmlspecialchars($module_name); ?></h2>

        <p class="result-details">Bạn đã trả lời đúng <?php echo htmlspecialchars($correct_count); ?> / <?php echo htmlspecialchars($total_questions); ?> câu hỏi.</p>

        <div class="result-score">
            Điểm của bạn: <?php echo htmlspecialchars($score); ?>%
        </div>

        <div class="result-actions">
            <?php // <a href="review_test.php?module_id=<?php echo $module_id; >" class="btn-review">Xem lại bài làm</a> // Nếu bạn có chức năng xem lại ?>
            <a href="course_view.php?module_id=<?php echo $module_id; ?>" class="btn-back-module">Quay lại Module</a>
            <a href="dashboard.php" class="btn-dashboard">Về trang Dashboard</a>
        </div>
    </div>
     <?php // Include footer dashboard nếu cần ?>
</body>
</html>