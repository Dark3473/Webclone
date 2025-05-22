<?php
include('../config.php'); // Đường dẫn tới file config của bạn
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Lấy module_id từ URL và kiểm tra
if (!isset($_GET['module_id']) || !filter_var($_GET['module_id'], FILTER_VALIDATE_INT)) {
    die("Lỗi: Module ID không hợp lệ.");
}
$module_id = (int)$_GET['module_id'];
$user_id = $_SESSION['user_id']; // Lấy user_id từ session

$conn->set_charset("utf8mb4");

// --- Lấy thông tin Module ---
$module_name = "Bài kiểm tra không xác định";
$stmt_module = $conn->prepare("SELECT module_name FROM edubase.modules WHERE module_id = ?");
if ($stmt_module) {
    $stmt_module->bind_param("i", $module_id);
    $stmt_module->execute();
    $result_module = $stmt_module->get_result();
    if ($row_module = $result_module->fetch_assoc()) {
        $module_name = $row_module['module_name'];
    } else {
        die("Lỗi: Không tìm thấy module với ID này.");
    }
    $stmt_module->close();
} else {
    die("Lỗi truy vấn thông tin module: " . $conn->error);
}


// --- Lấy câu hỏi từ bảng cau_hoi_module ---
$questions = [];
$sql_questions = "SELECT `Cau hoi_id`, `question_text`, `answer_A`, `answer_B`, `answer_C`, `answer_D`
                  FROM `edubase`.`câu hỏi module`
                  WHERE `module_id` = ?
                  ORDER BY RAND()"; // ORDER BY RAND() để xáo trộn câu hỏi (tùy chọn)
$stmt_questions = $conn->prepare($sql_questions);

if ($stmt_questions) {
    $stmt_questions->bind_param("i", $module_id);
    $stmt_questions->execute();
    $result_questions = $stmt_questions->get_result();
    while ($row = $result_questions->fetch_assoc()) {
        $questions[] = $row;
    }
    $stmt_questions->close();
} else {
    die("Lỗi truy vấn câu hỏi: " . $conn->error);
}

$conn->close();

// Kiểm tra xem có câu hỏi nào không
if (empty($questions)) {
    // Có thể chuyển hướng về trang course_view hoặc hiển thị thông báo
     echo "<!DOCTYPE html><html lang='vi'><head><meta charset='UTF-8'><title>Không có câu hỏi</title><link rel='stylesheet' href='../index.css'></head><body>";
     echo "<div style='padding: 20px; text-align: center;'>";
     echo "<h2>Không có câu hỏi nào cho bài kiểm tra này.</h2>";
     echo "<p><a href='course.php?module_id=" . $module_id . "'>Quay lại Module</a></p>";
     echo "</div></body></html>";
     exit;
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm tra Module: <?php echo htmlspecialchars($module_name); ?></title>
    <link rel="stylesheet" href="../index.css"> <?php // Đường dẫn CSS chung ?>
    <link rel="stylesheet" href="dash.css"> <?php // Đường dẫn CSS dashboard ?>
    <style>
        .test-container { padding: 20px; max-width: 800px; margin: 20px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .question-item { margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .question-item p { font-weight: bold; margin-bottom: 10px; }
        .question-item label { display: block; margin-bottom: 8px; cursor: pointer; }
        .question-item input[type="radio"] { margin-right: 10px; }
        .submit-button {
            background-color: #007bff; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; transition: background-color 0.3s ease; display: block; margin: 20px auto 0;
         }
        .submit-button:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <?php // Bạn có thể include header của trang dashboard ở đây nếu muốn ?>
    <div class="test-container">
        <h1>Bài kiểm tra: <?php echo htmlspecialchars($module_name); ?></h1>
        <hr>

        <form action="submit_test.php" method="post" id="test-form">
            <?php foreach ($questions as $index => $q): ?>
                <div class="question-item">
                    <p>Câu <?php echo $index + 1; ?>: <?php echo nl2br(htmlspecialchars($q['question_text'])); ?></p>
                    <?php
                        $options = ['A', 'B', 'C', 'D'];
                        // Xáo trộn thứ tự các đáp án (tùy chọn)
                        // shuffle($options);
                    ?>
                    <?php foreach ($options as $option): ?>
                        <?php if (!empty($q['answer_' . $option])): // Chỉ hiển thị nếu đáp án tồn tại ?>
                            <label>
                                <input type="radio" name="answers[<?php echo $q['Cau_hoi_id']; ?>]" value="<?php echo $option; ?>" required>
                                <?php echo $option; ?>. <?php echo htmlspecialchars($q['answer_' . $option]); ?>
                            </label>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <input type="hidden" name="module_id" value="<?php echo $module_id; ?>">
            <button type="submit" class="submit-button">Nộp bài</button>
        </form>
    </div>

     <?php // Bạn có thể include footer của trang dashboard ở đây nếu muốn ?>

     <script>
        // Có thể thêm Javascript để kiểm tra xem tất cả câu hỏi đã được trả lời chưa trước khi submit
        document.getElementById('test-form').addEventListener('submit', function(event) {
            const questionsCount = <?php echo count($questions); ?>;
            const answeredQuestions = document.querySelectorAll('input[type="radio"]:checked').length;

            if (answeredQuestions < questionsCount) {
                 if (!confirm('Bạn chưa trả lời hết tất cả các câu hỏi. Bạn có chắc chắn muốn nộp bài?')) {
                      event.preventDefault(); // Ngăn không cho form submit
                 }
            }
            // Thêm hiệu ứng loading khi submit nếu cần
        });
     </script>
</body>
</html>