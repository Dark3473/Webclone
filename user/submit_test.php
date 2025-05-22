<?php
include('../config.php'); // Đường dẫn tới file config của bạn
session_start();

// Kiểm tra đăng nhập và phương thức POST
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Có thể chuyển hướng về login hoặc trang lỗi
    header('Location: ../auth/login.php');
    exit;
}

// Lấy dữ liệu từ form
if (!isset($_POST['module_id']) || !filter_var($_POST['module_id'], FILTER_VALIDATE_INT) || !isset($_POST['answers']) || !is_array($_POST['answers'])) {
     // Dữ liệu gửi lên không hợp lệ
     // Chuyển hướng về trang trước hoặc trang lỗi
     header('Location: ' . $_SERVER['HTTP_REFERER'] ?? 'course.php'); // Quay lại trang trước nếu có
     exit;
}

$module_id = (int)$_POST['module_id'];
$user_answers = $_POST['answers']; // Mảng [Cau_hoi_id => 'A'/'B'/'C'/'D']
$user_id = $_SESSION['user_id'];

$conn->set_charset("utf8mb4");

// --- Lấy đáp án đúng từ Database ---
$correct_answers_map = [];
$sql_correct = "SELECT `Cau hoi_id`, `correct_answer` FROM `edubase`.`câu hỏi module` WHERE `module_id` = ?";
$stmt_correct = $conn->prepare($sql_correct);

if ($stmt_correct) {
    $stmt_correct->bind_param("i", $module_id);
    $stmt_correct->execute();
    $result_correct = $stmt_correct->get_result();
    while ($row = $result_correct->fetch_assoc()) {
        // Chỉ lấy những câu hỏi có trong bài làm của user gửi lên
        if (array_key_exists($row['Cau hoi_id'], $user_answers)) {
             $correct_answers_map[$row['Cau hoi_id']] = $row['correct_answer'];
        }
    }
    $stmt_correct->close();
} else {
    die("Lỗi truy vấn đáp án đúng: " . $conn->error);
}
// --- Tính điểm ---
$total_questions_in_submission = count($user_answers); // Số câu user đã trả lời và gửi lên
$correct_count = 0;

foreach ($user_answers as $question_id => $user_answer) {
    // Kiểm tra xem câu hỏi này có đáp án đúng trong map không và so sánh
    if (isset($correct_answers_map[$question_id]) && $correct_answers_map[$question_id] === $user_answer) {
        $correct_count++;
    }
}

// Tính điểm theo thang 100 hoặc thang 10 tùy bạn
// Ví dụ thang 100:
$score = ($total_questions_in_submission > 0) ? round(($correct_count / $total_questions_in_submission) * 100, 2) : 0;
// Ví dụ thang 10:
// $score = ($total_questions_in_submission > 0) ? round(($correct_count / $total_questions_in_submission) * 10, 2) : 0;


// --- Lưu kết quả vào bảng test_module ---
$sql_insert_result = "INSERT INTO edubase.test_module (user_id, module_id, score, attempt_date) VALUES (?, ?, ?, NOW())";
$stmt_insert = $conn->prepare($sql_insert_result);

if ($stmt_insert) {
    $stmt_insert->bind_param("iid", $user_id, $module_id, $score); // "iid" nghĩa là integer, integer, double (cho score)
    $stmt_insert->execute();

    if ($stmt_insert->affected_rows > 0) {
        // Lưu thành công, có thể lưu điểm vào session để hiển thị ở trang kết quả
        $_SESSION['last_test_score'] = $score;
        $_SESSION['last_test_module_id'] = $module_id; // Lưu module_id để biết kết quả của module nào
        $_SESSION['last_test_correct_count'] = $correct_count;
        $_SESSION['last_test_total_questions'] = $total_questions_in_submission;

        // Chuyển hướng đến trang hiển thị kết quả (ví dụ: test_result.php)
        header('Location: test_result.php');
        exit;
    } else {
        // Xử lý lỗi không lưu được
        echo "Lỗi: Không thể lưu kết quả bài kiểm tra.";
         // Log lỗi $stmt_insert->error;
    }
    $stmt_insert->close();
} else {
    die("Lỗi chuẩn bị câu lệnh lưu kết quả: " . $conn->error);
}

$conn->close();

?>