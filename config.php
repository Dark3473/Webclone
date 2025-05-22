<?php
// config.php
$servername = "localhost";
$username   = "root";
$password   = ""; // Mặc định XAMPP không có password cho root
$dbname     = "edubase";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>