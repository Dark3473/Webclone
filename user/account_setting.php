<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - OpenEDG</title>
    <link rel="stylesheet" href="../index.css">
    <link rel="stylesheet" href="dash.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="dashboard-header">
        <div class="logo">
            <a href="../index.php">
                <img src="/edubeclone/assets/images/logo1.png" alt="OpenClone">
            </a>
        </div>
        <nav class="center-nav">
            <a href="../index.php">Home</a>
            <a href="dashboard.php">Learning</a>
        </nav>
        <div class="user-actions">
            <a href="#" title="Messages"><i class="fas fa-comment-alt"></i></a>
            <div class="profile-dropdown-container"> <a href="#" title="Profile" class="user-profile-icon" id="profile-toggle"><i class="fas fa-user-circle"></i></a>
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
                    <li><a href="#" class="active">My Profile</a></li> <li><a href="#">Email Addresses</a></li>
                    <li><a href="#">Password</a></li>
                    <li><a href="#">Timezone</a></li>
                    <li><a href="#">Contact Preferences</a></li>
                    <li><a href="#">Pearson VUE Web Account</a></li>
                    <li><a href="#" class="delete-account-link">Delete Account</a></li>
                </ul>
            </nav>
        </aside>

        <main class="dashboard-content">
            <h1>Account Settings</h1>

            <div id="my-profile-content" class="settings-content-area active">

                <section class="settings-section">
                    <div class="section-header">
                        <h3>Personal Details</h3>
                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i> Edit</button>
                    </div>
                    <div class="details-content">
                        <dl>
                            <dt>First Name</dt>
                            <dd> </dd>
                            <dt>Last Name</dt>
                            <dd> </dd>
                            <dt>Date of Birth</dt>
                            <dd> </dd>
                        </dl>
                    </div>
                </section>

                <section class="settings-section">
                     <div class="section-header">
                        <h3>Other Information</h3>
                        <button class="btn-edit"><i class="fas fa-pencil-alt"></i> Edit</button>
                    </div>
                    <div class="details-content other-info">
                        <dl class="nickname-dl">
                           <dt>Nickname</dt>
                           <dd>avsELUmx</dd> </dl>
                        <div class="avatar-section">
                            <div class="avatar-placeholder">
                                <i class="fas fa-user"></i> </div>
                            </div>
                    </div>
                </section>

            </div></main>
    </div>

    <script src="profile.js"></script>
</body>
</html>