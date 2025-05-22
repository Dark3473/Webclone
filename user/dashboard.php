<?php
// Start the session at the very beginning
session_start();

// Check if the user is logged in, if not, redirect to login page
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../auth/login.php');
    exit;
}

// Include the functions file which contains database interactions
require_once 'course_functions.php';

// Get the user ID from the session
$userId = $_SESSION['user_id'];

// --- Handle POST requests (Enrollment) ---
// Check if the 'enroll' form was submitted and 'course_id' is present
if (isset($_POST['enroll']) && isset($_POST['course_id'])) {
    $courseId = $_POST['course_id'];
    // Attempt to enroll the user in the specified course
    enrollInCourse($userId, $courseId);
    // Redirect back to the dashboard to prevent form resubmission on refresh
    // Add a parameter to indicate success or show the enroll tab maybe?
    header('Location: dashboard.php?tab=enroll'); // Redirect to enroll tab after enrollment
    exit; // Stop further script execution
}

// --- Handle GET requests (Course Completion) ---
// Check if the 'complete' action was requested and 'enrollment_id' is present
if (isset($_GET['complete']) && isset($_GET['enrollment_id'])) {
    $enrollmentId = $_GET['enrollment_id'];
    // Attempt to mark the specified enrollment as complete
    completeCourse($enrollmentId);
    // Redirect back to the dashboard to prevent duplication and update the view
    // Redirect to completed tab after completion
    header('Location: dashboard.php?tab=completed');
    exit; // Stop further script execution
}

// --- Fetch data needed for the dashboard display ---
// Get the user's active courses
$activeCourses = getActiveCourses($userId);
// Get the user's completed courses
$completedCourses = getCompletedCourses($userId);
// Get courses available for the user to enroll in
$availableCourses = getAvailableCourses($userId);

// --- Determine the initial active tab for the display ---
// Default tab is 'active'
$activeTab = 'active';
// Check if a specific tab was requested in the URL
if (isset($_GET['tab']) && in_array($_GET['tab'], ['active', 'completed', 'enroll'])) {
    $activeTab = $_GET['tab'];
}

// End of the main PHP logic block
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - OpenEDG</title>
    <link rel="stylesheet" href="../index.css">
    <link rel="stylesheet" href="dash.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="dashboard-header">
        <div class="logo">
            <a href="../index.php"> <img src="/edubeclone/assets/images/logo1.png" alt="OpenClone"> </a>
        </div>
        <nav class="center-nav">
            <a href="../index.php">Home</a>
            <a href="#" id="nav-learning" class="active">Learning</a>
            <a href="#" id="nav-certification">Certification</a>
            <a href="#" id="nav-achievements">Achievements</a>
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
        <aside class="dashboard-sidebar">
            <nav>
                <ul>
                    <li><a href="#" class="active">Study</a></li>
                    </ul>
            </nav>
        </aside>

        <main class="dashboard-content" id="main-content-area">
            <div id="content-learning" style="display: block;">
                <h1>Study</h1>
                <nav class="content-tabs">
                    <button type="button" class="tab-link <?php echo $activeTab == 'active' ? 'active' : ''; ?>" data-tab="active">Active</button>
                    <button type="button" class="tab-link <?php echo $activeTab == 'completed' ? 'active' : ''; ?>" data-tab="completed">Completed</button>
                    <button type="button" class="tab-link <?php echo $activeTab == 'enroll' ? 'active' : ''; ?>" data-tab="enroll">Enroll</button>
                </nav>

                <div class="course-list" id="active-courses" style="display: <?php echo $activeTab == 'active' ? 'block' : 'none'; ?>">
                    <?php if (empty($activeCourses)): // Check if the array of active courses is empty ?>
                        <div class="no-courses">
                            <p>You don't have any active courses. Enroll in a course to get started!</p>
                        </div>
                    <?php else: // If there are active courses, loop through them ?>
                        <?php foreach ($activeCourses as $course): ?>
                            <?php
                                // Get progress for this specific course and user within the loop
                                // It's efficient to get progress here as it's needed per course item
                                $progress = getCourseProgress($userId, $course['course_id']);
                            ?>
                            <div class="course-list-item">
                                <div class="course-thumbnail">
                                    <div class="mini-card card-css">
                                        <div class="mini-card-header">
                                            <?php echo htmlspecialchars($course['course_name']); // Display course name ?>
                                            <div class="mini-card-provider">INSTITUTE</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="course-details">
                                    <h2><?php echo htmlspecialchars($course['course_name']); // Display course name ?></h2>
                                    <div class="details-grid">
                                        <p><strong>Enrolled:</strong> <?php echo date('M d, Y', strtotime($course['enrollment_date'])); // Display enrollment date ?></p>
                                        <p><strong>Status:</strong> Active</p>
                                        <p><strong>Progress:</strong></p>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $progress['progress_percent']; ?>%"></div>
                                    </div>
                                    <p class="course-status">
                                        <?php echo $progress['completed_pages']; ?> of <?php echo $progress['total_pages']; ?> pages completed
                                    </p>
                                    <div class="course-actions">
                                        <button class="btn-action">Details</button>
                                        <button class="btn-action">FAQ</button>
                                        <a href="course.php?id=<?php echo $course['course_id']; ?>">
                                            <button class="btn-action">Dashboard</button>
                                        </a>
                                        <a href="course.php?id=<?php echo $course['course_id']; ?>&resume=1">
                                            <button class="btn-action btn-resume">Resume</button>
                                        </a>
                                        <?php if ($progress['progress_percent'] == 100): ?>
                                            <a href="dashboard.php?complete=1&enrollment_id=<?php echo $course['enrollment_id']; ?>">
                                                <button class="btn-action btn-complete">Mark Complete</button>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; // End of active courses loop ?>
                    <?php endif; // End of active courses check ?>
                </div>

                <div class="course-list" id="completed-courses" style="display: <?php echo $activeTab == 'completed' ? 'block' : 'none'; ?>">
                    <?php if (empty($completedCourses)): // Check if the array of completed courses is empty ?>
                        <div class="no-courses">
                            <p>You haven't completed any courses yet.</p>
                        </div>
                    <?php else: // If there are completed courses, loop through them ?>
                        <?php foreach ($completedCourses as $course): ?>
                            <div class="course-list-item">
                                <div class="course-thumbnail">
                                    <div class="mini-card card-css">
                                        <div class="mini-card-header">
                                            <?php echo htmlspecialchars($course['course_name']); // Display course name ?>
                                            <div class="mini-card-provider">INSTITUTE</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="course-details">
                                    <h2><?php echo htmlspecialchars($course['course_name']); // Display course name ?></h2>
                                    <div class="details-grid">
                                        <p><strong>Completed:</strong> <?php echo date('M d, Y', strtotime($course['certificate_date'] ?? $course['enrollment_date'])); ?></p>
                                        <p><strong>Status:</strong> Completed</p>
                                        <?php if ($course['certificate_issued']): ?>
                                            <p><strong>Certificate:</strong> Issued on <?php echo date('M d, Y', strtotime($course['certificate_date'])); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="course-actions">
                                        <button class="btn-action">Details</button>
                                        <button class="btn-action">FAQ</button>
                                        <a href="course.php?id=<?php echo $course['course_id']; ?>">
                                            <button class="btn-action">View Course</button>
                                        </a>
                                        <?php if ($course['certificate_issued']): ?>
                                            <a href="certificate.php?id=<?php echo $course['course_id']; ?>">
                                                <button class="btn-action">View Certificate</button>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; // End of completed courses loop ?>
                    <?php endif; // End of completed courses check ?>
                </div>

                <div class="course-list" id="enroll-courses" style="display: <?php echo $activeTab == 'enroll' ? 'block' : 'none'; ?>">
                    <?php if (empty($availableCourses)): // Check if the array of available courses is empty ?>
                        <div class="no-courses">
                            <p>There are no more courses available for enrollment at this time.</p>
                        </div>
                    <?php else: // If there are available courses, loop through them ?>
                        <?php foreach ($availableCourses as $course): ?>
                            <div class="course-list-item">
                                <div class="course-thumbnail">
                                    <div class="mini-card card-css">
                                        <div class="mini-card-header">
                                            <?php echo htmlspecialchars($course['course_name']); // Display course name ?>
                                            <div class="mini-card-provider">INSTITUTE</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="course-details">
                                    <h2><?php echo htmlspecialchars($course['course_name']); // Display course name ?></h2>
                                    <div class="details-grid">
                                        <p><strong>Added:</strong> <?php echo date('M d, Y', strtotime($course['date'])); // Display added date ?></p>
                                        <p><strong>Mode:</strong> Self-Study</p>
                                        <p><strong>Description:</strong> <?php echo htmlspecialchars(substr($course['description'], 0, 100)); ?>...</p>
                                    </div>
                                    <div class="course-actions">
                                        <button class="btn-action">Details</button>
                                        <form method="post" action="dashboard.php" style="display:inline;">
                                            <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                            <button type="submit" name="enroll" class="btn-action btn-enroll">Enroll Now</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; // End of available courses loop ?>
                    <?php endif; // End of available courses check ?>
                </div>
            </div>

            <div id="content-certification" style="display: none;">
                <div class="coming-soon-content">
                    <h1>Certifications</h1>
                    <p>Certifications are coming soon! Stay tuned.</p>
                </div>
            </div>

            <div id="content-achievements" style="display: none;">
                <div class="coming-soon-content">
                    <h1>Achievements</h1>
                    <p>Achievements are coming soon! Stay tuned.</p>
                </div>
            </div>
        </main>
    </div>

    <script src="profile.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Tab Switching Logic (Active, Completed, Enroll) ---
            const tabLinks = document.querySelectorAll('.content-tabs .tab-link');
            const courseLists = document.querySelectorAll('.dashboard-content .course-list'); // Select course lists within the main content area

            tabLinks.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove 'active' class from all tabs
                    tabLinks.forEach(t => t.classList.remove('active'));

                    // Add 'active' class to clicked tab
                    this.classList.add('active');

                    const tabName = this.getAttribute('data-tab');

                    // Hide all course lists within the main content area
                    courseLists.forEach(list => {
                        list.style.display = 'none';
                    });

                    // Show the selected course list
                    // Ensure the ID matches the structure (e.g., 'active-courses')
                    const targetListId = tabName + '-courses';
                    const targetList = document.getElementById(targetListId);
                    if (targetList) {
                         targetList.style.display = 'block';
                    }

                    // Update URL without refreshing the page for better user experience
                    history.pushState({}, '', `dashboard.php?tab=${tabName}`);
                });
            });

             // --- Main Navigation Logic (Learning, Certification, Achievements) ---
            const navLinks = document.querySelectorAll('.center-nav a');
            // Select content sections based on their IDs starting with 'content-'
            const contentSections = document.querySelectorAll('[id^="content-"]');

            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Prevent default link behavior (navigating away)
                    e.preventDefault();

                    // Determine the target content section ID based on the nav link ID
                    const targetId = this.id.replace('nav-', 'content-');

                    // Hide all content sections
                    contentSections.forEach(section => {
                        section.style.display = 'none';
                    });

                    // Show the target content section
                    const targetSection = document.getElementById(targetId);
                     if (targetSection) {
                        targetSection.style.display = 'block';
                    }

                    // Update active state for main navigation links
                    navLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');

                    // Optional: If switching *from* learning, you might want to reset the tab view
                    // or hide the content-tabs nav if it's only for learning.
                    // For now, we just toggle the main sections.

                    // Note: Switching main sections via JS doesn't update URL unless you add pushState here too
                    // history.pushState({}, '', `dashboard.php?section=${targetId.replace('content-', '')}`);
                    // However, the PHP only handles the 'tab' query param for the 'learning' section,
                    // so adding this might require more PHP logic to handle other sections on page load.
                    // Keeping it simple for now based on the original code's scope.
                });
            });

             // --- Initial State Setup ---
            // This part ensures the correct main section is shown on page load
            // It mirrors the PHP $activeTab logic but for the main nav sections
            // (Currently, PHP only influences the 'Learning' tabs, main nav is JS controlled after load)
            // Let's add logic to set the initial main section based on the URL or default
            // if a section parameter were added. Since it's not in the original PHP,
            // we'll just ensure the 'Learning' section is shown by default if no specific logic dictates otherwise.
            // The `style="display: block;"` on `#content-learning` and `display: none;` on others
            // already handles the initial state based on the HTML structure. The JS then takes over.
            // We can add a check to activate the correct *nav* link based on the initially displayed content.
             const initiallyActiveContent = document.querySelector('.dashboard-content > div[style*="display: block"]');
             if(initiallyActiveContent) {
                 const navId = 'nav-' + initiallyActiveContent.id.replace('content-', '');
                 const activeNavLink = document.getElementById(navId);
                 if(activeNavLink) {
                     navLinks.forEach(l => l.classList.remove('active'));
                     activeNavLink.classList.add('active');
                 }
             }


        });
    </script>
</body>
</html>