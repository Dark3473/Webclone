document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const tabs = document.querySelectorAll('.tab');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Hide all content sections
            const contentSections = document.querySelectorAll('.content-section');
            contentSections.forEach(section => {
                section.classList.remove('active');
                section.style.display = 'none'; // Explicitly hide sections
            });
            
            // Show the target content section
            const targetId = this.getAttribute('data-target');
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.classList.add('active');
                targetSection.style.display = 'grid'; // Use grid display for the course grid
            }
        });
    });

    // Function to show tab content by ID (keeping it global for onclick handlers)
    window.showTab = function(tabId) {
        // Find the tab that controls this content
        const tab = document.querySelector(`.tab[data-target="${tabId}"]`);
        if (tab) {
            tab.click();
        }
    };

    // Course enrollment modal functionality
    const modal = document.getElementById('enrollment-modal');
    const closeModalBtn = document.querySelector('.close-modal');
    const confirmEnrollBtn = document.getElementById('confirm-enrollment');
    const cancelEnrollBtn = document.getElementById('cancel-enrollment');
    let currentCourseId = null;
    let currentCourseName = null;

    // Add click event to all course cards
    const courseCards = document.querySelectorAll('.course-card');
    courseCards.forEach(card => {
        card.addEventListener('click', function() {
            currentCourseId = this.getAttribute('data-course-id');
            currentCourseName = this.querySelector('.course-title-short').textContent;
            
            // Update modal message with course name
            document.getElementById('modal-message').textContent = `Would you like to enroll in "${currentCourseName}"?`;
            
            // Check if user is logged in
            if (!userIsLoggedIn) {
                window.location.href = 'auth/login.php';
                return;
            }
            
            modal.style.display = 'block';
        });
    });

    // Close modal when clicking the X
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }

    // Close modal when clicking cancel
    if (cancelEnrollBtn) {
        cancelEnrollBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }

    // Confirm enrollment
    if (confirmEnrollBtn) {
        confirmEnrollBtn.addEventListener('click', function() {
            if (!currentCourseId) return;
            
            // Show loading state
            confirmEnrollBtn.textContent = 'Processing...';
            confirmEnrollBtn.disabled = true;
            
            // Send enrollment request via AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'enroll.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (this.status === 200) {
                    let response;
                    try {
                        response = JSON.parse(this.responseText);
                    } catch (e) {
                        console.error('Invalid JSON response:', this.responseText);
                        response = {
                            success: false,
                            message: 'An unexpected error occurred. Please try again.'
                        };
                    }
                    
                    const modalMessage = document.getElementById('modal-message');
                    
                    modalMessage.textContent = response.message;
                    
                    if (response.success) {
                        // Change modal buttons to show only a "Go to Dashboard" button
                        document.querySelector('.modal-buttons').innerHTML = 
                            '<button onclick="window.location.href=\'user/dashboard.php\'" class="btn btn-primary">Go to Dashboard</button>';
                    } else {
                        // Just show an OK button to close the modal
                        document.querySelector('.modal-buttons').innerHTML = 
                            '<button onclick="document.getElementById(\'enrollment-modal\').style.display=\'none\'" class="btn">OK</button>';
                    }
                } else {
                    const modalMessage = document.getElementById('modal-message');
                    modalMessage.textContent = 'Failed to communicate with the server. Please try again later.';
                    document.querySelector('.modal-buttons').innerHTML = 
                        '<button onclick="document.getElementById(\'enrollment-modal\').style.display=\'none\'" class="btn">OK</button>';
                }
            };
            
            xhr.onerror = function() {
                const modalMessage = document.getElementById('modal-message');
                modalMessage.textContent = 'Network error occurred. Please check your connection and try again.';
                document.querySelector('.modal-buttons').innerHTML = 
                    '<button onclick="document.getElementById(\'enrollment-modal\').style.display=\'none\'" class="btn">OK</button>';
            };
            
            xhr.send('course_id=' + currentCourseId);
        });
    }

    // Close modal when clicking outside of it
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Show the initial active tab content on page load
    const initialActiveTab = document.querySelector('.tab.active');
    if (initialActiveTab) {
        const initialTargetId = initialActiveTab.getAttribute('data-target');
        const initialContent = document.getElementById(initialTargetId);
        if (initialContent) {
            // Show the initially active content
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
                section.style.display = 'none';
            });
            initialContent.classList.add('active');
            initialContent.style.display = 'grid';
        }
    }
});