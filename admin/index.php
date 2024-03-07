<?php
define('admin', true);
session_start();
// Bao gồm tập tin cấu hình, tập tin này chứa các cài đặt bạn có thể thay đổi.
include '../config.php';
// Bao gồm các hàm và kết nối đến cơ sở dữ liệu bằng PDO MySQL
include '../functions.php';
// kết nối MySQL database
$pdo = pdo_connect_mysql();
// Nếu người dùng chưa đăng nhập, chuyển hướng họ đến trang đăng nhập
if (!isset($_SESSION['account_loggedin'])) {
    header('Location: ../index.php?page=myaccount');
    exit;
}
// Nếu người dùng không phải là admin, chuyển hướng họ trở lại trang chính của trang mua sắm
$stmt = $pdo->prepare('SELECT * FROM tai_khoan WHERE id = ?');
$stmt->execute([ $_SESSION['id_taikhoan'] ]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$account || $account['admin'] != 1) {
    header('Location: ../index.php');
    exit;
}
// Trang mặc định được thiết lập là trang chủ (home.php), vì vậy khi khách truy cập trang web, đó sẽ là trang họ nhìn thấy.
$page = isset($_GET['page']) && file_exists($_GET['page'] . '.php') ? $_GET['page'] : 'orders';
if (isset($_GET['page']) && $_GET['page'] == 'logout') {
    session_destroy();
    header('Location: ../index.php');
    exit;
}
// Xuất biến lỗi (nếu có)
$error = '';
// Bao gồm trang được yêu cầu
include $page . '.php';
?>
