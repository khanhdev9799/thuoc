<?php
// Ngăn chặn truy cập trực tiếp vào tệp
defined('shoppingcart') or exit;
// Mặc định cho các giá trị của các phần tử biểu mẫu đầu vào
$account = array(
    'ho' => '',
    'ten' => '',
    'dia_chi' => '',
    'quan_huyen' => '',
    'thanh_pho' => '',
    'sdt' => '',
    'quoc_gia' => 'Vietnam'
);
// Kiểm tra xem người dùng đã đăng nhập chưa
if (isset($_SESSION['account_loggedin'])) {
    $stmt = $pdo->prepare('SELECT * FROM tai_khoan WHERE id = ?');
    $stmt->execute([ $_SESSION['id_taikhoan'] ]);
    // Fetch the account from the database and return the result as an Array
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
}
// Đảm bảo khi người dùng gửi biểu mẫu, tất cả dữ liệu đã được gửi và giỏ hàng không trống
if (isset($_POST['ho'], $_POST['ten'], $_POST['dia_chi'], $_POST['quan_huyen'], $_POST['thanh_pho'], $_POST['sdt'], $_POST['quoc_gia'], $_SESSION['cart'])) {
    $id_taikhoan = null;
    // Nếu người dùng đã đăng nhập
    if (isset($_SESSION['account_loggedin'])) {
        // Tài khoản đã đăng nhập, cập nhật chi tiết người dùng
        $stmt = $pdo->prepare('UPDATE tai_khoan SET ho = ?, ten = ?, dia_chi = ?, quan_huyen = ?, thanh_pho = ?, sdt = ?, quoc_gia = ? WHERE id = ?');
        $stmt->execute([ $_POST['ho'], $_POST['ten'], $_POST['dia_chi'], $_POST['quan_huyen'], $_POST['thanh_pho'], $_POST['sdt'], $_POST['quoc_gia'], $_SESSION['id_taikhoan'] ]);
        $id_taikhoan = $_SESSION['id_taikhoan'];
    } else if (isset($_POST['email'], $_POST['password'], $_POST['cpassword']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        // Người dùng chưa đăng nhập, kiểm tra xem tài khoản đã tồn tại với email họ gửi không
        $stmt = $pdo->prepare('SELECT id FROM tai_khoan WHERE email = ?');
        $stmt->execute([ $_POST['email'] ]);
    	if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            // Email đã tồn tại, người dùng nên đăng nhập thay vì tạo tài khoản mới
    		$error = 'Account already exists with this email, please login instead!';
        } else if ($_POST['password'] != $_POST['cpassword']) {
            // Mật khẩu và xác nhận mật khẩu không khớp...
            $error = 'Passwords do not match!';
    	} else {
            // Email không tồn tại, tạo tài khoản mới
            $stmt = $pdo->prepare('INSERT INTO tai_khoan (email, password, ho, ten, dia_chi, quan_huyen, thanh_pho, sdt, quoc_gia) VALUES (?,?,?,?,?,?,?,?,?)');
            // Mã hóa mật khẩu
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt->execute([ $_POST['email'], $password, $_POST['ho'], $_POST['ten'], $_POST['dia_chi'], $_POST['quan_huyen'], $_POST['thanh_pho'], $_POST['sdt'], $_POST['quoc_gia'] ]);
            $id_taikhoan = $pdo->lastInsertId();
            $stmt = $pdo->prepare('SELECT * FROM tai_khoan WHERE id = ?');
            $stmt->execute([ $id_taikhoan ]);
            // Lấy thông tin tài khoản từ cơ sở dữ liệu và trả kết quả dưới dạng một Mảng
            $account = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } else if (strtolower(account_required) == 'true') {
        $error = 'Yêu cầu tạo tài khoản!';
    }
    if (!$error) {
        // Không có lỗi, xử lý đơn hàng
        $products_in_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
        $subtotal = 0.00;
        // Nếu có sản phẩm trong giỏ hàng
        if ($products_in_cart) {
            // Có sản phẩm trong giỏ hàng nên chúng ta cần lấy các sản phẩm đó từ cơ sở dữ liệu
            // Chuyển đổi mảng sản phẩm trong giỏ hàng thành chuỗi dấu hỏi, chúng ta cần câu lệnh SQL để bao gồm: IN (?,?,?,...v.v.)
            $array_to_question_marks = implode(',', array_fill(0, count($products_in_cart), '?'));
            $stmt = $pdo->prepare('SELECT * FROM san_pham WHERE id IN (' . $array_to_question_marks . ')');
            // Chúng tôi sử dụng array_column để chỉ lấy id của các sản phẩm
            $stmt->execute(array_column($products_in_cart, 'id'));
            // Lấy các sản phẩm từ cơ sở dữ liệu và trả kết quả dưới dạng một Mảng
            $san_pham = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Duyệt qua các sản phẩm trong giỏ hàng và thêm thông tin meta (tên sản phẩm, mô tả, vv.)
            foreach ($products_in_cart as &$cart_product) {
                foreach ($san_pham as $product) {
                    if ($cart_product['id'] == $product['id']) {
                        $cart_product['meta'] = $product;
                        // Tính tổng tiền
                        if ($cart_product['options_price'] > 0) {
                            $subtotal += (float)$cart_product['options_price'] * (int)$cart_product['so_luong'];
                        } else {
                            $subtotal += (float)$product['gia_ban'] * (int)$cart_product['so_luong'];
                        }
                    }
                }
            }
        }
        if (isset($_POST['checkout']) && $products_in_cart) {
            // Xử lý thanh toán bình thường
            // Duyệt qua từng sản phẩm trong giỏ hàng của người dùng
            // ID giao dịch duy nhất
            $transaction_id = strtoupper(uniqid('SC') . substr(md5(mt_rand()), 0, 5));
            $stmt = $pdo->prepare('INSERT INTO chi_tiet_gd (ma_gd, tong_tien, tinh_trang, ngay_dat, email, ho, ten, dia_chi, quan_huyen, thanh_pho, sdt, quoc_gia, id_taikhoan, pt_thanhtoan) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([
                $transaction_id,
                $subtotal,
                'Hoàn thành',
                date('Y-m-d H:i:s'),
                $account ? $account['email'] : $_POST['email'],
                $_POST['ho'],
                $_POST['ten'],
                $_POST['dia_chi'],
                $_POST['quan_huyen'],
                $_POST['thanh_pho'],
                $_POST['sdt'],
                $_POST['quoc_gia'],
                $id_taikhoan,
                'website'
            ]);
            $order_id = $pdo->lastInsertId();
            foreach ($products_in_cart as $product) {
                // Đối với mỗi sản phẩm trong giỏ hàng, thêm một giao dịch mới vào cơ sở dữ liệu của chúng tôi
                $stmt = $pdo->prepare('INSERT INTO danh_sach_gd (ma_gd, id_san_pham, don_gia, muc_sl, muc_tuy_chon) VALUES (?,?,?,?,?)');
                $stmt->execute([ $transaction_id, $product['id'], $product['options_price'] > 0 ? $product['options_price'] : $product['meta']['gia_ban'], $product['so_luong'], $product['options'] ]);
                // Cập nhật số lượng sản phẩm trong bảng sản phẩm
                $stmt = $pdo->prepare('UPDATE san_pham SET so_luong = so_luong - ? WHERE so_luong > 0 AND id = ?');
                $stmt->execute([ $product['so_luong'], $product['id'] ]);
            }
            if ($id_taikhoan != null) {
                // Đăng nhập người dùng với thông tin cung cấp
                session_regenerate_id();
                $_SESSION['account_loggedin'] = TRUE;
                $_SESSION['id_taikhoan'] = $id_taikhoan;
                $_SESSION['account_admin'] = $account ? $account['admin'] : 0;
            }
            send_order_details_email(
                $account ? $account['email'] : $_POST['email'],
                $products_in_cart,
                $_POST['ho'],
                $_POST['ten'],
                $_POST['dia_chi'],
                $_POST['quan_huyen'],
                $_POST['thanh_pho'],
                $_POST['sdt'],
                $_POST['quoc_gia'],
                $subtotal,
                $order_id
            );
			
			
            header('Location: index.php?page=placeorder');
            exit;
        }
	
    }
}
// Chuyển hướng người dùng đến trang giỏ hàng nếu giỏ hàng trống

if (empty($_SESSION['cart'])) {
    header('Location: index.php?page=cart');
    exit;
}
// Danh sách các quốc gia có sẵn, bạn có thể xóa bỏ bất kỳ quốc gia nào khỏi mảng này
$countries = array("Malaysia","Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");

?>

<?=template_header('Checkout')?>

<div class="checkout content-wrapper">

    <h1>Thủ tục thanh toán</h1>

    <p class="error"><?=$error?></p>

    <?php if (!isset($_SESSION['account_loggedin'])): ?>
    <p>Bạn đã có tài khoản? <a href="index.php?page=myaccount">Đăng nhập</a></p>
    <?php endif; ?>

    <form action="index.php?page=checkout" method="post">

        <?php if (!isset($_SESSION['account_loggedin'])): ?>
        <h2>Tạo tài khoản<?php if (strtolower(account_required) == 'false'): ?> (optional)<?php endif; ?></h2>

        <label for="email">Email</label>
        <input type="email" name="email" id="email" placeholder="john@example.com">

        <label for="password">Mật khẩu</label>
        <input type="password" name="password" id="password" placeholder="Mật khẩu">

        <label for="cpassword">Xác nhận mật khẩu</label>
        <input type="password" name="cpassword" id="cpassword" placeholder="Xác nhận mật khẩu">
        <?php endif; ?>

        <h2>Chi tiết vận chuyển</h2>

        <div class="row1">
            <label for="ho">Họ</label>
            <input type="text" value="<?=$account['ho']?>" name="ho" id="ho" placeholder="Trần" required>
        </div>

        <div class="row2">
            <label for="ten">Tên</label>
            <input type="text" value="<?=$account['ten']?>" name="ten" id="ten" placeholder="Ngọc Khánh" required>
        </div>

        <label for="dia_chi">Địa Chỉ</label>
        <input type="text" value="<?=$account['dia_chi']?>" name="dia_chi" id="dia_chi" placeholder="số 1 đường Nguyễn Huệ" required>

        <label for="quan_huyen">Quận(Huyện)</label>
        <input type="text" value="<?=$account['quan_huyen']?>" name="quan_huyen" id="quan_huyen" placeholder="Tp. Hồ Chí Minh" required>

        <div class="row1">
            <label for="thanh_pho">Tỉnh,Thành Phố</label>
            <input type="text" value="<?=$account['thanh_pho']?>" name="thanh_pho" id="thanh_pho" placeholder="Nam" required>
        </div>

        <div class="row2">
            <label for="sdt">Số Điện Thoại</label>
            <input type="text" value="<?=$account['sdt']?>" name="sdt" id="sdt" placeholder="43000" required>
        </div>

        <label for="quoc_gia">Quốc Gia</label>
        <select name="quoc_gia" required>
            <?php foreach($countries as $country): ?>
            <option value="<?=$country?>"<?=$country==$account['quoc_gia']?' selected':''?>><?=$country?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="checkout">Đặt Hàng</button>

		
    </form>

</div>

<?=template_footer()?>
