<?php
include('config.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'You need to log in first']);
    exit;
}

// Check if course_id is provided
if (!isset($_POST['course_id']) || empty($_POST['course_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid course']);
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = $_POST['course_id'];

// Check if the user is already enrolled in this course
$check_sql = "SELECT * FROM course_management 
              WHERE user_id = ? AND course_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $user_id, $course_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You are already enrolled in this course']);
    exit;
}

// Enroll the user in the course
$enroll_sql = "INSERT INTO course_management (user_id, course_id, enrollment_date, `Trạng thái học`) 
               VALUES (?, ?, CURDATE(), 'active')";
$enroll_stmt = $conn->prepare($enroll_sql);
$enroll_stmt->bind_param("ii", $user_id, $course_id);

if ($enroll_stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'You have successfully enrolled in this course']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error enrolling in course: ' . $conn->error]);
}

$check_stmt->close();
$enroll_stmt->close();
$conn->close();
?>