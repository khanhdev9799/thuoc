<?php
// Ngăn truy cập trực tiếp vào tệp
defined('shoppingcart') or exit;

// Người dùng nhấp vào nút "Đăng nhập", tiến hành quá trình đăng nhập... kiểm tra dữ liệu POST và xác thực email
if (isset($_POST['login'], $_POST['email'], $_POST['password']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    // Kiểm tra xem tài khoản có tồn tại không
    $stmt = $pdo->prepare('SELECT * FROM tai_khoan WHERE email = ?');
    $stmt->execute([ $_POST['email'] ]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    // Nếu tài khoản tồn tại, xác minh mật khẩu
    if ($account && password_verify($_POST['password'], $account['password'])) {
        // Người dùng đã đăng nhập, tạo dữ liệu phiên
        session_regenerate_id();
        $_SESSION['account_loggedin'] = TRUE;
        $_SESSION['id_taikhoan'] = $account['id'];
        $_SESSION['account_admin'] = $account['admin'];
        $products_in_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
        if ($products_in_cart) {
            // Người dùng có sản phẩm trong giỏ hàng, chuyển hướng họ đến trang thanh toán
            header('Location: index.php?page=checkout');
        } else {
            // Chuyển hướng người dùng trở lại cùng một trang, họ sau đó có thể xem lịch sử đặt hàng của mình
            header('Location: index.php?page=myaccount');
        }
        exit;
    } else {
        $error = 'Email/Mật khẩu không chính xác!';
    }
}

// Biến sẽ hiển thị lỗi đăng ký
$register_error = '';

// Người dùng nhấp vào nút "Đăng ký", tiến hành quá trình đăng ký... kiểm tra dữ liệu POST và xác thực email
if (isset($_POST['register'], $_POST['email'], $_POST['password'], $_POST['cpassword']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    // Kiểm tra xem tài khoản có tồn tại không
    $stmt = $pdo->prepare('SELECT * FROM tai_khoan WHERE email = ?');
    $stmt->execute([ $_POST['email'] ]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($account) {
        // Tài khoản đã tồn tại!
        $register_error = 'Tài khoản đã tồn tại với email này!';
    } else if ($_POST['cpassword'] != $_POST['password']) {
        $register_error = 'Mật khẩu không khớp!';
    } else {
        // Tài khoản không tồn tại, tạo tài khoản mới
        $stmt = $pdo->prepare('INSERT INTO tai_khoan (email, password, ho, ten, dia_chi, quan_huyen, thanh_pho, sdt, quoc_gia) VALUES (?,?,"","","","","","","")');
        // Mã hóa mật khẩu
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt->execute([ $_POST['email'], $password ]);
        $id_taikhoan = $pdo->lastInsertId();
        // Tự động đăng nhập người dùng
        session_regenerate_id();
        $_SESSION['account_loggedin'] = TRUE;
        $_SESSION['id_taikhoan'] = $id_taikhoan;
        $_SESSION['account_admin'] = 0;
        $products_in_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
        if ($products_in_cart) {
            // Người dùng có sản phẩm trong giỏ hàng, chuyển hướng họ đến trang thanh toán
            header('Location: index.php?page=checkout');
        } else {
            // Chuyển hướng người dùng trở lại cùng một trang, họ sau đó có thể xem lịch sử đặt hàng của mình
            header('Location: index.php?page=myaccount');
        }
        exit;
    }
}

// Nếu người dùng đã đăng nhập
if (isset($_SESSION['account_loggedin'])) {
    // Chọn tất cả các giao dịch của người dùng, điều này sẽ xuất hiện dưới "Đơn hàng của tôi"
    $stmt = $pdo->prepare('SELECT
        p.img AS img,
        p.name AS name,
        t.ngay_dat AS transaction_date,
        ti.don_gia AS gia_ban,
        ti.muc_sl AS so_luong
        FROM chi_tiet_gd t
        JOIN danh_sach_gd ti ON ti.ma_gd = t.ma_gd
        JOIN tai_khoan a ON a.id = t.id_taikhoan
        JOIN san_pham p ON p.id = ti.id_san_pham
        WHERE t.id_taikhoan = ?
        ORDER BY t.ngay_dat DESC');
    $stmt->execute([ $_SESSION['id_taikhoan'] ]);
    $chi_tiet_gd = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<?=template_header('My Account')?>

<div class="myaccount content-wrapper">

    <?php if (!isset($_SESSION['account_loggedin'])): ?>

    <div class="login-register">

        <div class="login">

            <h1>Đăng nhập</h1>

            <form action="index.php?page=myaccount" method="post">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="example@example.com" required>
                <label for="password">Mật Khẩu</label>
                <input type="password" name="password" id="password" placeholder="Password" required>
                <input name="login" type="submit" value="Đăng nhập">
            </form>

            <?php if ($error): ?>
            <p class="error"><?=$error?></p>
            <?php endif; ?>

        </div>

        <div class="register">

            <h1>Đăng kí</h1>

            <form action="index.php?page=myaccount" method="post">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" placeholder="example@example.com" required>
                <label for="password">Mật Khẩu</label>
                <input type="password" name="password" id="password" placeholder="Password" required>
                <label for="cpassword">Xác nhận mật khẩu</label>
                <input type="password" name="cpassword" id="cpassword" placeholder="Confirm Password" required>
                <input name="register" type="submit" value="Register">
            </form>

            <?php if ($register_error): ?>
            <p class="error"><?=$register_error?></p>
            <?php endif; ?>

        </div>

    </div>

    <?php else: ?>

    <h1>Tài khoản của tôi</h1>

    <h2>Đơn hàng của tôi</h2>

    <table>
        <thead>
            <tr>
                <td colspan="2">Tên sản phẩm</td>
                <td class="rhide">Ngày</td>
                <td class="rhide">Giá</td>
                <td>Số lượng</td>
                <td>Tổng cộng</td>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($chi_tiet_gd)): ?>
            <tr>
                <td colspan="6" style="text-align:center;">Bạn chưa có đơn hàng nào</td>
            </tr>
            <?php else: ?>
            <?php foreach ($chi_tiet_gd as $transaction): ?>
            
			<tr>
                <td class="img">
                    <?php if (!empty($transaction['img']) && file_exists('imgs/' . $transaction['img'])): ?>
                    <img src="imgs/<?=$transaction['img']?>" width="50" height="50" alt="<?=$transaction['name']?>">
                    <?php endif; ?>
                </td>
                <td><?=$transaction['name']?></td>
                <td class="rhide"><?=$transaction['transaction_date']?></td>
                <td class="price rhide"><?=number_format($transaction['gia_ban'],0)?><?=currency_code?></td>
                <td class="quantity"><?=$transaction['so_luong']?></td>
                <td class="price"><?=number_format($transaction['gia_ban'] * $transaction['so_luong'],0)?><?=currency_code?></td>
            </tr>
			
			
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php endif; ?>

</div>

<?=template_footer()?>
