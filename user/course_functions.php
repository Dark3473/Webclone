<?php
include('../config.php'); // Adjust path as needed to your config file

/**
 * Get all active courses for a user
 * @param int $userId User ID
 * @return array Array of active courses
 */
function getActiveCourses($userId) {
    global $conn;
    
    $sql = "SELECT c.*, cm.enrollment_date, cm.id as enrollment_id 
            FROM courses c
            JOIN course_management cm ON c.course_id = cm.course_id
            WHERE cm.user_id = ? AND cm.`Trạng thái học` = 'active'
            ORDER BY cm.enrollment_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    
    return $courses;
}

/**
 * Get all completed courses for a user
 * @param int $userId User ID
 * @return array Array of completed courses
 */
function getCompletedCourses($userId) {
    global $conn;
    
    $sql = "SELECT c.*, cm.enrollment_date, cm.certificate_issued, cm.certificate_date, cm.id as enrollment_id 
            FROM courses c
            JOIN course_management cm ON c.course_id = cm.course_id
            WHERE cm.user_id = ? AND cm.`Trạng thái học` = 'completed'
            ORDER BY cm.enrollment_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    
    return $courses;
}

/**
 * Get all available courses for enrollment that the user hasn't enrolled in yet
 * @param int $userId User ID
 * @return array Array of available courses
 */
function getAvailableCourses($userId) {
    global $conn;
    
    $sql = "SELECT c.*
            FROM courses c
            WHERE c.course_id NOT IN (
                SELECT cm.course_id
                FROM course_management cm
                WHERE cm.user_id = ?
            )
            ORDER BY c.date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    
    return $courses;
}

/**
 * Update the course status when a user completes a course
 * @param int $enrollmentId The enrollment ID from course_management table
 * @return bool True if update was successful
 */
function completeCourse($enrollmentId) {
    global $conn;
    
    $sql = "UPDATE course_management 
            SET `Trạng thái học` = 'completed', 
                certificate_issued = 1,
                certificate_date = CURRENT_DATE()
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $enrollmentId);
    $result = $stmt->execute();
    
    return $result;
}

/**
 * Enroll a user in a course
 * @param int $userId User ID
 * @param int $courseId Course ID
 * @return bool True if enrollment was successful
 */
function enrollInCourse($userId, $courseId) {
    global $conn;
    
    $sql = "INSERT INTO course_management 
            (user_id, course_id, enrollment_date, `Trạng thái học`, certificate_issued)
            VALUES (?, ?, CURRENT_DATE(), 'active', 0)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $courseId);
    $result = $stmt->execute();
    
    return $result;
}

/**
 * Get course progress information
 * @param int $userId User ID
 * @param int $courseId Course ID
 * @return array Progress information
 */
function getCourseProgress($userId, $courseId) {
    global $conn;
    
    // Get total pages for the course
    $sqlTotalPages = "SELECT COUNT(p.page_id) as total_pages
                     FROM pages p
                     JOIN topics t ON p.topic_id = t.topic_id
                     JOIN modules m ON t.module_id = m.module_id
                     WHERE m.course_id = ?";
    
    $stmtTotal = $conn->prepare($sqlTotalPages);
    $stmtTotal->bind_param("i", $courseId);
    $stmtTotal->execute();
    $resultTotal = $stmtTotal->get_result()->fetch_assoc();
    $totalPages = $resultTotal['total_pages'] ?? 0;
    
    // Get completed pages
    $sqlCompleted = "SELECT COUNT(lm.id) as completed_pages
                    FROM lesson_management lm
                    JOIN pages p ON lm.page_id = p.page_id
                    JOIN topics t ON p.topic_id = t.topic_id
                    JOIN modules m ON t.module_id = m.module_id
                    WHERE m.course_id = ? AND lm.`trạng thái học` = 'đã hoàn thành'";
    
    $stmtCompleted = $conn->prepare($sqlCompleted);
    $stmtCompleted->bind_param("i", $courseId);
    $stmtCompleted->execute();
    $resultCompleted = $stmtCompleted->get_result()->fetch_assoc();
    $completedPages = $resultCompleted['completed_pages'] ?? 0;
    
    $progressPercent = ($totalPages > 0) ? ($completedPages / $totalPages) * 100 : 0;
    
    return [
        'total_pages' => $totalPages,
        'completed_pages' => $completedPages,
        'progress_percent' => $progressPercent
    ];
}
?>