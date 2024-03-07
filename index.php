<?php
define('shoppingcart', true);
session_start();
// Bao gồm tệp cấu hình, bao gồm các cài đặt bạn có thể thay đổi.
include 'config.php';
// Bao gồm các hàm và kết nối đến cơ sở dữ liệu sử dụng PDO MySQL
include 'functions.php';
// Kết nối đến cơ sở dữ liệu MySQL
$pdo = pdo_connect_mysql();
// Trang được thiết lập mặc định là trang chính (home.php), vì vậy khi người truy cập ghé thăm, đó sẽ là trang họ thấy.
$page = isset($_GET['page']) && file_exists($_GET['page'] . '.php') ? $_GET['page'] : 'home';
// Biến lỗi
$error = '';
// Bao gồm trang được yêu cầu
include $page . '.php';
?>
