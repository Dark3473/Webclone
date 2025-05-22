document.addEventListener('DOMContentLoaded', function() {
    console.log("Profile JS loaded and DOM ready.");

    // --- Xử lý Profile Dropdown ---
    const profileToggle = document.getElementById('profile-toggle');
    const profileMenu = document.getElementById('profile-menu');

    if (profileToggle && profileMenu) {
        profileToggle.addEventListener('click', function(event) {
            event.preventDefault(); // Ngăn link mặc định
            profileMenu.classList.toggle('show'); // Hiện/ẩn dropdown
            console.log("Profile menu toggled.");
        });

        // Đóng dropdown nếu click ra ngoài
        document.addEventListener('click', function(event) {
            if (!profileToggle.contains(event.target) && !profileMenu.contains(event.target)) {
                if (profileMenu.classList.contains('show')) {
                    profileMenu.classList.remove('show');
                    console.log("Profile menu closed due to outside click.");
                }
            }
        });
        console.log("Profile dropdown handler initialized.");
    } else {
        console.error("Profile toggle button or menu element not found!");
    }

    // === XỬ LÝ CHUYỂN ĐỔI NỘI DUNG CHÍNH (HEADER NAV) ===
    // (Learning / Certification / Achievements)
    const navLinks = document.querySelectorAll('.center-nav a[id^="nav-"]'); // Lấy các link có id bắt đầu bằng nav-
    const contentDivs = document.querySelectorAll('#main-content-area > div[id^="content-"]'); // Lấy các div nội dung

    // Hàm để hiển thị nội dung tương ứng và cập nhật trạng thái active
    function showContent(targetId) {
        console.log(`Attempting to show content: ${targetId}`);
        
        // Kiểm tra targetId hợp lệ
        if (!targetId || typeof targetId !== 'string' || !targetId.startsWith('content-')) {
            console.error('Invalid targetId provided:', targetId);
            return;
        }
        
        // Ẩn tất cả các div nội dung
        contentDivs.forEach(div => {
            if (div) {
                div.style.display = 'none';
            }
        });

        // Hiển thị div nội dung mục tiêu
        const targetContent = document.getElementById(targetId);
        if (targetContent) {
            targetContent.style.display = 'block'; // Hoặc 'flex' nếu bạn dùng flexbox
            console.log(`Content ${targetId} is now visible.`);
        } else {
            console.error(`Content element not found for ID: ${targetId}`);
        }

        // Cập nhật trạng thái active cho link điều hướng
        const linkId = 'nav-' + targetId.split('-')[1]; // Suy ra id của link từ id nội dung (content-learning -> nav-learning)
        navLinks.forEach(link => {
            if (link) {
                link.classList.remove('active');
                if (link.id === linkId) {
                    link.classList.add('active');
                    console.log(`Navigation link ${linkId} is now active.`);
                }
            }
        });
    }

    // Gắn sự kiện click cho các link điều hướng
    if (navLinks.length > 0 && contentDivs.length > 0) {
        navLinks.forEach(link => {
            if (link && link.id && link.id.startsWith('nav-')) {
                link.addEventListener('click', function(event) {
                    event.preventDefault(); // Ngăn hành động mặc định của link

                    // Xác định id của nội dung cần hiển thị dựa trên id của link được click
                    const targetContentId = 'content-' + this.id.split('-')[1];
                    showContent(targetContentId);
                    
                    // Đóng dropdown profile nếu đang mở
                    if (profileMenu && profileMenu.classList.contains('show')) {
                        profileMenu.classList.remove('show');
                        console.log("Profile menu closed due to main tab switch.");
                    }
                });
            }
        });
        
        // Thiết lập trạng thái ban đầu khi tải trang
        const initiallyActiveLink = document.querySelector('.center-nav a.active[id^="nav-"]');
        let initialContentId = 'content-learning'; // Mặc định là learning

        if (initiallyActiveLink) {
            const initialSuffix = initiallyActiveLink.id.substring('nav-'.length);
            initialContentId = 'content-' + initialSuffix;
        } else {
            // Nếu không có link nào active sẵn trong HTML, đặt active cho link learning
            const learningLink = document.getElementById('nav-learning');
            if (learningLink) {
                learningLink.classList.add('active');
            }
            console.warn("No initial active link found in HTML, defaulting to 'nav-learning'.");
        }
        
        // Hiển thị nội dung ban đầu tương ứng
        showContent(initialContentId);
        console.log(`Main content switcher initialized. Initial content: ${initialContentId}`);
    } else {
        console.warn("Main navigation links or content divs not found. Tab switching disabled.");
    }

    // --- Xử lý chuyển đổi nội dung Settings (Cho trang account_setting.php) ---
    const sidebarLinks = document.querySelectorAll('.dashboard-sidebar nav li a');
    const settingsContents = document.querySelectorAll('.settings-content-area');

    if (sidebarLinks.length > 0 && settingsContents.length > 0) {
        console.log("Settings navigation found - initializing functionality.");
        
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function(event) {
                event.preventDefault(); // Ngăn link mặc định

                // Bỏ active ở tất cả link và nội dung
                sidebarLinks.forEach(l => l.classList.remove('active'));
                settingsContents.forEach(c => c.classList.remove('active')); 

                // Thêm active vào link được click
                this.classList.add('active');

                // Tìm và hiện nội dung tương ứng
                try {
                    const targetId = this.getAttribute('href').substring(1) + '-content'; 
                    const targetContent = document.getElementById(targetId);
                    if (targetContent) {
                        targetContent.classList.add('active'); // Hiện nội dung mục tiêu
                        console.log(`Settings content ${targetId} is now active.`);
                    } else {
                        // Nếu chưa có nội dung, hiện tạm nội dung profile
                        const defaultContent = document.getElementById('my-profile-content');
                        if (defaultContent) {
                            defaultContent.classList.add('active');
                            console.warn('Content for ID:', targetId, 'not found. Showing default content.');
                        }
                    }
                } catch (e) {
                    // Xử lý nếu href không hợp lệ hoặc không tìm thấy ID
                    const defaultContent = document.getElementById('my-profile-content');
                    if (defaultContent) {
                        defaultContent.classList.add('active');
                    }
                    console.error('Error finding content:', e);
                }

                // Đóng dropdown profile nếu đang mở
                if (profileMenu && profileMenu.classList.contains('show')) {
                    profileMenu.classList.remove('show');
                }
            });
        });

        // Kích hoạt nội dung mặc định (My Profile) khi tải trang
        const initialActiveContent = document.querySelector('.settings-content-area.active');
        if (!initialActiveContent) {
            // Nếu chưa có active content nào trong HTML, đặt mặc định
            const profileContent = document.getElementById('my-profile-content');
            if (profileContent) {
                profileContent.classList.add('active');
                console.log("Default settings content 'my-profile-content' activated.");
            }
        }
    } else {
        console.log("Settings navigation not found on this page - skipping initialization.");
    }

}); // Kết thúc DOMContentLoaded