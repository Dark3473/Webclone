<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../auth/login.php');
    exit;
}

// Database connection
require_once '../config.php';

// Get page_id, topic_id, and module_id from URL parameters
$page_id = isset($_GET['page_id']) ? (int)$_GET['page_id'] : null;
$topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : null;
$module_id = isset($_GET['module_id']) ? (int)$_GET['module_id'] : null;

// Determine course_id based on passed parameters

// Cập nhật trạng thái học khi truy cập trang
if ($page_id && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $today = date("Y-m-d");

    // Kiểm tra nếu đã có record
    $stmt = $conn->prepare("SELECT * FROM edubase.lesson_management WHERE user_id=? AND page_id=?");
    $stmt->bind_param("ii", $user_id, $page_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Cập nhật trạng thái
        $stmt = $conn->prepare("UPDATE edubase.lesson_management SET `trạng thái học`='đã hoàn thành', last_accessed=? WHERE user_id=? AND page_id=?");
        $stmt->bind_param("sii", $today, $user_id, $page_id);
        $stmt->execute();
    } else {
        // Tạo mới
        $stmt = $conn->prepare("INSERT INTO edubase.lesson_management (user_id, page_id, `trạng thái học`, last_accessed) VALUES (?, ?, 'đã hoàn thành', ?)");
        $stmt->bind_param("iis", $user_id, $page_id, $today);
        $stmt->execute();
    }
}

$course_id = null;
if ($page_id) {
    $stmt = $conn->prepare("
        SELECT t.module_id, m.course_id
        FROM edubase.pages p
        JOIN edubase.topics t ON p.topic_id = t.topic_id
        JOIN edubase.modules m ON t.module_id = m.module_id
        WHERE p.page_id = ?
    ");
    $stmt->bind_param("i", $page_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $module_id = $row['module_id'];
        $course_id = $row['course_id'];
    }
} elseif ($topic_id) {
    $stmt = $conn->prepare("
        SELECT t.module_id, m.course_id
        FROM edubase.topics t
        JOIN edubase.modules m ON t.module_id = m.module_id
        WHERE t.topic_id = ?
    ");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $module_id = $row['module_id'];
        $course_id = $row['course_id'];
    }
} elseif ($module_id) {
    $stmt = $conn->prepare("
        SELECT course_id
        FROM edubase.modules
        WHERE module_id = ?
    ");
    $stmt->bind_param("i", $module_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $course_id = $row['course_id'];
    }
} else {
    // If no parameters, get the first course and its first module
    $stmt = $conn->prepare("SELECT course_id FROM edubase.courses ORDER BY date DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $course_id = $row['course_id'];
        
        // Get first module
        $stmt = $conn->prepare("
            SELECT module_id 
            FROM edubase.modules 
            WHERE course_id = ? 
            ORDER BY module_order ASC LIMIT 1
        ");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $module_id = $row['module_id'];
        }
    }
}

// Get current page content if page_id is set
$current_page = null;
if ($page_id) {
    $stmt = $conn->prepare("
        SELECT p.*, t.topic_name, t.topic_id
        FROM edubase.pages p
        JOIN edubase.topics t ON p.topic_id = t.topic_id
        WHERE p.page_id = ?
    ");
    $stmt->bind_param("i", $page_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $current_page = $result->fetch_assoc();
        $topic_id = $current_page['topic_id'];
    } else {
        // If page not found, reset to just the topic
        $page_id = null;
    }
}

// If no page but topic is set, get first page from that topic
if (!$page_id && $topic_id) {
    $stmt = $conn->prepare("
        SELECT page_id 
        FROM edubase.pages 
        WHERE topic_id = ? 
        ORDER BY page_order ASC 
        LIMIT 1
    ");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $page_id = $row['page_id'];
        header('Location: view.php?page_id=' . $page_id);
        exit;
    }
}

// Get current topic info
$current_topic = null;
if ($topic_id) {
    $stmt = $conn->prepare("
        SELECT * 
        FROM edubase.topics 
        WHERE topic_id = ?
    ");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $current_topic = $result->fetch_assoc();
    }
}

// Load course navigation data - this will power our navigation menu for prev/next - need to keep this for page navigation
function getCourseNavigation($conn, $course_id) {
    $navigation = [];
    
    // Get course details
    $stmt = $conn->prepare("SELECT * FROM edubase.courses WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $course = $result->fetch_assoc();
        $navigation['course'] = $course;
        
        // Get all modules for this course
        $stmt = $conn->prepare("
            SELECT * 
            FROM edubase.modules 
            WHERE course_id = ? 
            ORDER BY module_order ASC
        ");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $modules_result = $stmt->get_result();
        
        $navigation['modules'] = [];
        while ($module = $modules_result->fetch_assoc()) {
            $module_data = $module;
            $module_data['topics'] = [];
            
            // Get all topics for this module
            $stmt = $conn->prepare("
                SELECT * 
                FROM edubase.topics 
                WHERE module_id = ? 
                ORDER BY topic_order ASC
            ");
            $stmt->bind_param("i", $module['module_id']);
            $stmt->execute();
            $topics_result = $stmt->get_result();
            
            while ($topic = $topics_result->fetch_assoc()) {
                $topic_data = $topic;
                $topic_data['pages'] = [];
                
                // Get all pages for this topic
                $stmt = $conn->prepare("
                    SELECT * 
                    FROM edubase.pages 
                    WHERE topic_id = ? 
                    ORDER BY page_order ASC
                ");
                $stmt->bind_param("i", $topic['topic_id']);
                $stmt->execute();
                $pages_result = $stmt->get_result();
                
                while ($page = $pages_result->fetch_assoc()) {
                    $topic_data['pages'][] = $page;
                }
                
                $module_data['topics'][] = $topic_data;
            }
            
            $navigation['modules'][] = $module_data;
        }
    }
    
    return $navigation;
}

// Get navigation data if we have a course_id
$navigation = null;
if ($course_id) {
    $navigation = getCourseNavigation($conn, $course_id);
}

// Find prev/next page for navigation
$prev_page = null;
$next_page = null;

if ($page_id && $navigation) {
    $found = false;
    $prev_found = false;
    
    // Flatten the navigation structure to get a linear sequence of pages
    $all_pages = [];
    foreach ($navigation['modules'] as $module) {
        foreach ($module['topics'] as $topic) {
            foreach ($topic['pages'] as $page) {
                $all_pages[] = $page;
            }
        }
    }
    
    // Find current page position
    $current_index = -1;
    foreach ($all_pages as $index => $page) {
        if ($page['page_id'] == $page_id) {
            $current_index = $index;
            break;
        }
    }
    
    // Get prev/next
    if ($current_index > 0) {
        $prev_page = $all_pages[$current_index - 1];
    }
    
    if ($current_index < count($all_pages) - 1) {
        $next_page = $all_pages[$current_index + 1];
    }
}

// Get page content if we have a page_id
$content = '';
if ($page_id) {
    $stmt = $conn->prepare("SELECT content FROM edubase.pages WHERE page_id = ?");
    $stmt->bind_param("i", $page_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $content = $row['content'];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($current_page) ? htmlspecialchars($current_page['topic_name']) : 'Learning Platform'; ?></title>
    <link rel="stylesheet" href="../index.css">
    <link rel="stylesheet" href="dash.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="lesson-header">
        <div class="logo">
            <a href="../index.php">
                <img src="/edubeclone/assets/images/logo1.png" alt="OpenClone">
            </a>
        </div>
        <div class="lesson-title-container">
            <span class="lesson-title-header">
                <?php echo isset($current_page) ? htmlspecialchars($current_page['topic_name']) : 
                      (isset($current_topic) ? htmlspecialchars($current_topic['topic_name']) : 'Topic'); ?>
            </span>
        </div>
        <div class="lesson-actions">
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

    <div class="lesson-container">
        <main class="content-container">
            <article class="lesson-content">
                <?php 
                if (empty($content)) {
                    echo "<div class='no-content-message'>
                            <h2>No content available</h2>
                            <p>This topic doesn't have any content yet.</p>
                          </div>";
                } else {
                    echo nl2br($content);
                }
                ?>
                
                <?php if ($page_id): ?>
                <div class="page-navigation">
                    <?php if ($prev_page): ?>
                    <a href="view.php?page_id=<?php echo $prev_page['page_id']; ?>" class="nav-button prev-button">
                        <i class="fas fa-arrow-left"></i> Previous
                    </a>
                    <?php else: ?>
                    <span class="nav-button disabled">
                        <i class="fas fa-arrow-left"></i> Previous
                    </span>
                    <?php endif; ?>
                    
                    <?php if ($next_page): ?>
                    <a href="view.php?page_id=<?php echo $next_page['page_id']; ?>" class="nav-button next-button">
                        Next <i class="fas fa-arrow-right"></i>
                    </a>
                    <?php else: ?>
                    <span class="nav-button disabled">
                        Next <i class="fas fa-arrow-right"></i>
                    </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </article>
        </main>
    </div>
    <script src="profile.js"></script>
    <script>
        // Simplified JavaScript - only keep the profile dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Profile dropdown functionality
            const profileToggle = document.getElementById('profile-toggle');
            const profileMenu = document.getElementById('profile-menu');
            
            profileToggle.addEventListener('click', function(e) {
                e.preventDefault();
                profileMenu.classList.toggle('active');
            });
            
            document.addEventListener('click', function(e) {
                if (!profileToggle.contains(e.target) && !profileMenu.contains(e.target)) {
                    profileMenu.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>