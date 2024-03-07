<?php
// Ngăn chặn truy cập trực tiếp vào tệp
defined('shoppingcart') or exit;
// Nếu người dùng nhấp vào nút Thêm vào giỏ hàng trên trang sản phẩm, chúng ta kiểm tra dữ liệu biểu mẫu
if (isset($_POST['id_sp'], $_POST['so_luong']) && is_numeric($_POST['id_sp']) && is_numeric($_POST['so_luong'])) {
    // Đặt các biến post để dễ dàng xác định chúng, đồng thời đảm bảo chúng là số nguyên
    $id_sp = (int)$_POST['id_sp'];
    // hàm abs() sẽ ngăn không cho số lượng âm và (int) sẽ đảm bảo giá trị là số nguyên
    $so_luong = abs((int)$_POST['so_luong']);
    // Lấy tùy chọn sản phẩm
    $options = '';
    $options_price = 0.00;
    foreach ($_POST as $k => $v) {
        if (strpos($k, 'option-') !== false) {
            $options .= str_replace('option-', '', $k) . '-' . $v . ',';
            $stmt = $pdo->prepare('SELECT * FROM tuy_chon_sp WHERE tieu_de = ? AND name = ? AND id_sp = ?');
            $stmt->execute([ str_replace('option-', '', $k), $v, $id_sp ]);
            $option = $stmt->fetch(PDO::FETCH_ASSOC);
            $options_price += $option['gia_ban'];
        }
    }
    $options = rtrim($options, ',');
    // Chuẩn bị câu lệnh SQL, chúng ta về cơ bản đang kiểm tra xem sản phẩm có tồn tại trong cơ sở dữ liệu của chúng ta hay không
    $stmt = $pdo->prepare('SELECT * FROM san_pham WHERE id = ?');
    $stmt->execute([ $_POST['id_sp'] ]);
    // Lấy sản phẩm từ cơ sở dữ liệu và trả kết quả dưới dạng Mảng
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    // Kiểm tra xem sản phẩm có tồn tại không (mảng không trống)
    if ($product && $so_luong > 0) {
        // Sản phẩm tồn tại trong cơ sở dữ liệu, bây giờ chúng ta có thể tạo / cập nhật biến phiên cho giỏ hàng
        if (!isset($_SESSION['cart'])) {
            // Biến phiên giỏ hàng không tồn tại, tạo nó
            $_SESSION['cart'] = array();
        }
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            $cart_product = &get_cart_product($id_sp, $options);
            if ($cart_product) {
                // Sản phẩm đã tồn tại trong giỏ hàng, vì vậy chỉ cần cập nhật số lượng
                $cart_product['so_luong'] += $so_luong;
            } else {
                // Sản phẩm không có trong giỏ hàng nên thêm nó
                $_SESSION['cart'][] = array(
                    'id' => $id_sp,
                    'so_luong' => $so_luong,
                    'options' => $options,
                    'options_price' => $options_price
                );
            }
        }
    }
    // Ngăn chặn việc gửi lại biểu mẫu...
    header('location: index.php?page=cart');
    exit;
}
// Xóa sản phẩm khỏi giỏ hàng, kiểm tra cho tham số URL "remove", đây là id sản phẩm, đảm bảo nó là một số và kiểm tra xem nó có trong giỏ hàng không
if (isset($_GET['remove']) && is_numeric($_GET['remove']) && isset($_SESSION['cart']) && isset($_SESSION['cart'][$_GET['remove']])) {
    // Remove the product from the shopping cart
    unset($_SESSION['cart'][$_GET['remove']]);
    header('location: index.php?page=cart');
    exit;
}
// Làm trống giỏ hàng
if (isset($_POST['emptycart']) && isset($_SESSION['cart'])) {
    // Remove all products from the shopping cart
    unset($_SESSION['cart']);
    header('location: index.php?page=cart');
    exit;
}
// Cập nhật số lượng sản phẩm trong giỏ hàng nếu người dùng nhấp vào nút "Cập nhật" trên trang giỏ hàng
if (isset($_POST['update']) && isset($_SESSION['cart'])) {
    // Lặp qua dữ liệu post để chúng ta có thể cập nhật số lượng cho mọi sản phẩm trong giỏ hàng
    foreach ($_POST as $k => $v) {
        if (strpos($k, 'so_luong') !== false && is_numeric($v)) {
            $id = str_replace('so_luong-', '', $k);
            // hàm abs() sẽ ngăn không cho số lượng âm và (int) sẽ đảm bảo số nguyên
            $so_luong = abs((int)$v);
            // Luôn luôn thực hiện kiểm tra và xác nhận
            if (is_numeric($id) && isset($_SESSION['cart'][$id]) && $so_luong > 0) {
                // Cập nhật số lượng mới
                $_SESSION['cart'][$id]['so_luong'] = $so_luong;
            }
        }
    }
    header('location: index.php?page=cart');
    exit;
}
// Chuyển người dùng đến trang đặt hàng nếu họ nhấp vào nút Đặt hàng, và giỏ hàng không được trống
if (isset($_POST['checkout']) && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    header('Location: index.php?page=checkout');
    exit;
}
// Kiểm tra biến phiên cho các sản phẩm trong giỏ hàng
$products_in_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$subtotal = 0.00;
// Nếu có sản phẩm trong giỏ hàng
if ($products_in_cart) {
    // Có sản phẩm trong giỏ hàng nên chúng ta cần chọn những sản phẩm đó từ cơ sở dữ liệu
    // Mảng sản phẩm trong giỏ hàng thành mảng dấu hỏi, chúng ta cần câu lệnh SQL để bao gồm: IN (?,?,?,...vv)
    $array_to_question_marks = implode(',', array_fill(0, count($products_in_cart), '?'));
    $stmt = $pdo->prepare('SELECT * FROM san_pham WHERE id IN (' . $array_to_question_marks . ')');
    // Chúng ta sử dụng array_column để chỉ trả về các id của các sản phẩm
    $stmt->execute(array_column($products_in_cart, 'id'));
    // Lấy các sản phẩm từ cơ sở dữ liệu và trả kết quả dưới dạng Mảng
    $san_pham = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Lặp lại các sản phẩm trong giỏ hàng và thêm dữ liệu meta (tên sản phẩm, mô tả, vv)
    foreach ($products_in_cart as &$cart_product) {
        foreach ($san_pham as $product) {
            if ($cart_product['id'] == $product['id']) {
                $cart_product['meta'] = $product;
                // Tính tổng cộng
                if ($cart_product['options_price'] > 0) {
                    $subtotal += (float)$cart_product['options_price'] * (int)$cart_product['so_luong'];
                } else {
                    $subtotal += (float)$product['gia_ban'] * (int)$cart_product['so_luong'];
                }
            }
        }
    }
}
?>

<?=template_header('Shopping Cart')?>

<div class="cart content-wrapper">

    <h1>Giỏ Hàng</h1>

    <form action="index.php?page=cart" method="post">
        <table>
            <thead>
                <tr>
                    <td colspan="2">Sản Phẩm</td>
                    <td></td>
                    <td class="rhide">Giá</td>
                    <td>Số lượng</td>
                    <td>Tổng cộng</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products_in_cart)): ?>
                <tr>
                    <td colspan="6" style="text-align:center;">Bạn chưa thêm sản phẩm nào vào Giỏ hàng</td>
                </tr>
                <?php else: ?>
                <?php foreach ($products_in_cart as $num => $product): ?>
                <tr>
                    <td class="img">
                        <?php if (!empty($product['meta']['img']) && file_exists('imgs/' . $product['meta']['img'])): ?>
                        <a href="index.php?page=product&id=<?=$product['id']?>">
                            <img src="imgs/<?=$product['meta']['img']?>" width="50" height="50" alt="<?=$product['meta']['name']?>">
                        </a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="index.php?page=product&id=<?=$product['id']?>"><?=$product['meta']['name']?></a>
                        <br>
                        <a href="index.php?page=cart&remove=<?=$num?>" class="remove">Xóa</a>
                    </td>
                    <td class="price">
                        <?=$product['options']?>
                        <input type="hidden" name="options" value="<?=$product['options']?>">
                    </td>
                    <?php if ($product['options_price'] > 0): ?>
                    <td class="price rhide"><?=number_format($product['options_price'],0)?><?=currency_code?></td>
                    <?php else: ?>
                    <td class="price rhide"><?=number_format($product['meta']['gia_ban'],0)?><?=currency_code?></td>
                    <?php endif; ?>
                    <td class="quantity">
                        <input type="number" name="so_luong-<?=$num?>" value="<?=$product['so_luong']?>" min="1" <?php if ($product['meta']['so_luong'] != -1): ?>max="<?=$product['meta']['so_luong']?>"<?php endif; ?> placeholder="so_luong" required>
                    </td>
                    <?php if ($product['options_price'] > 0): ?>
                    <td class="price"><?=number_format($product['options_price'] * $product['so_luong'],0)?><?=currency_code?></td>
                    <?php else: ?>
                    <td class="price"><?=number_format($product['meta']['gia_ban'] * $product['so_luong'],0)?><?=currency_code?></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="subtotal">
            <span class="text">Tổng cộng</span>
            <span class="price"><?=number_format($subtotal,2)?><?=currency_code?></span>
        </div>

        <div class="buttons">
            <input type="submit" value="Xóa giỏ hàng" name="emptycart">
            <input type="submit" value="Cập nhật" name="update">
            <input type="submit" value="Thanh toán" name="checkout">
        </div>

    </form>

</div>

<?=template_footer()?>
