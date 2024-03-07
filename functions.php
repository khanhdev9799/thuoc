<?php
// Function kết nối đến cơ sở dữ liệu MySQL
function pdo_connect_mysql() {
    try {
        // Kết nối đến cơ sở dữ liệu MySQL bằng PDO...
    	return new PDO('mysql:host=' . db_host . ';dbname=' . db_name . ';charset=utf8', db_user, db_pass);
    } catch (PDOException $exception) {
    	// Không thể kết nối đến cơ sở dữ liệu MySQL, nếu lỗi này xảy ra, hãy đảm bảo rằng bạn kiểm tra cài đặt db của mình có đúng không!
    	exit('Failed to connect to database!');
    }
}

// Function để lấy sản phẩm từ giỏ hàng theo ID và chuỗi tùy chọn
function &get_cart_product($id, $options) {
    $p = null;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as &$product) {
            if ($product['id'] == $id && $product['options'] == $options) {
                $p = &$product;
                return $p;
            }
        }
    }
    return $p;
}

// Function gửi email chi tiết đơn hàng
function send_order_details_email($email, $products, $ho, $ten, $dia_chi, $quan_huyen, $thanh_pho, $sdt, $quoc_gia, $subtotal, $order_id) {
    if (mail_enabled != 'true') {
        return;
    }
	$subject = 'Chi Tiết Đơn Hàng';
	$headers = 'From: ' . mail_from . "\r\n" . 'Reply-To: ' . mail_from . "\r\n" . 'Return-Path: ' . mail_from . "\r\n" . 'X-Mailer: PHP/' . phpversion() . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-Type: text/html; charset=UTF-8' . "\r\n";
    ob_start();
    include 'order-details-template.php';
    $order_details_template = ob_get_clean();
	mail($email, $subject, $order_details_template, $headers);
}

// Template header, bạn có thể tùy chỉnh nếu cần
function template_header($title) {
    // Lấy số lượng mặt hàng trong giỏ hàng, điều này sẽ được hiển thị trong tiêu đề.
    $num_items_in_cart = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
    $site_name = site_name;
    $admin_link = isset($_SESSION['account_loggedin']) && $_SESSION['account_admin'] ? '<a href="admin/index.php" target="_blank">Admin</a>' : '';
    $logout_link = isset($_SESSION['account_loggedin']) ? '<a title="Logout" href="index.php?page=logout"><i class="fas fa-sign-out-alt"></i></a>' : '';
    echo <<<EOT
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>$title</title>
        <link rel="icon" type="image/png" href="favicon.png">
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
        <style>
            .owl-carousel .item img {
            display: block;
            width: 100%;
            height: auto;
            }
  </style>
	</head>
	<body>
        <header>
        <img src="imgs/banner_header.jpg" width="100%" height="120px" />
            <div class="content-wrapper">
            <div class="logo"><img src="imgs/logo1.jpg" width="149" height="73" /></div>
                <nav>
                    <a href="index.php">Trang Chủ</a>
                    <a href="index.php?page=products">Cửa Hàng</a>
					<a href="index.php?page=myaccount">Đơn Hàng</a>
                    $admin_link
                </nav>
                <div class="link-icons">
                    <div class="search">
						<i class="fas fa-search"></i>
						<input type="text" placeholder="Search...">
					</div>
                    <a href="index.php?page=cart" title="Shopping Cart">
						<i class="fas fa-shopping-cart"></i>
						<span>$num_items_in_cart</span>
					</a>
                    $logout_link
					<a class="responsive-toggle" href="#">
						<i class="fas fa-bars"></i>
					</a>
                </div>
            </div>
        </header>
        <main>
EOT;
}

// Template footer
function template_footer() {
    $year = date('Y');
    $currency_code = currency_code;
    echo <<<EOT
        </main>
        <footer>
            
            <div class="content-wrapper">
                <p>© $year Trần Ngọc Khánh</p>
            </div>
        </footer>
        <script>
        let currency_code = "$currency_code";
        </script>
        <script src="script.js"></script>
    </body>
</html>
EOT;
}

// Template admin header
function template_admin_header($title) {
    echo <<<EOT
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1">
		<title>$title</title>
        <link rel="icon" type="image/png" href="../favicon.png">
		<link href="admin.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
	</head>
	<body class="admin">
        <header>
            <h1>Admin</h1>
            <a class="responsive-toggle" href="#">
                <i class="fas fa-bars"></i>
            </a>
        </header>
        <aside class="responsive-width-100 responsive-hidden">
            <a href="index.php?page=orders">• Đơn Hàng</a>
            <a href="index.php?page=products">• DS Sản Phẩm</a>
            <a href="index.php?page=categories">• DS Danh Mục</a>
            <a href="index.php?page=accounts">• DS Tài Khoản</a>
            <a href="index.php?page=images">• Hình Ảnh</a>
            <a href="index.php?page=logout">• Đăng Xuất</a>
        </aside>
        <main class="responsive-width-100">
EOT;
}

// Template admin footer
function template_admin_footer() {
    echo <<<EOT
        </main>
        <script>
        document.querySelector(".responsive-toggle").onclick = function(event) {
            event.preventDefault();
            let aside_display = document.querySelector("aside").style.display;
            document.querySelector("aside").style.display = aside_display == "flex" ? "none" : "flex";
        };
        </script>
    </body>
</html>
EOT;
}
?>
