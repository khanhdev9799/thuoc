<?php
// Ngăn truy cập trực tiếp vào tệp
defined('shoppingcart') or exit;
// Kiểm tra để đảm bảo rằng tham số id được chỉ định trong URL
if (isset($_GET['id'])) {
    // Chuẩn bị câu lệnh và thực thi, ngăn chặn tấn công SQL injection
    $stmt = $pdo->prepare('SELECT * FROM san_pham WHERE id = ?');
    $stmt->execute([ $_GET['id'] ]);
    // Lấy sản phẩm từ cơ sở dữ liệu và trả kết quả dưới dạng mảng
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    // Kiểm tra xem sản phẩm có tồn tại không (mảng không trống)
    if (!$product) {
        // Hiển thị thông báo lỗi đơn giản nếu id cho sản phẩm không tồn tại (mảng trống)
        $error = 'Sản phẩm không tồn tại!';
    }
    // Chọn hình ảnh sản phẩm (nếu có) từ bảng hình ảnh sản phẩm
    $stmt = $pdo->prepare('SELECT * FROM hinh_anh_sp WHERE id_sp = ?');
    $stmt->execute([ $_GET['id'] ]);
    // Lấy hình ảnh sản phẩm từ cơ sở dữ liệu và trả kết quả dưới dạng mảng
    $product_imgs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Chọn các tùy chọn sản phẩm (nếu có) từ bảng tùy chọn sản phẩm
    $stmt = $pdo->prepare('SELECT tieu_de, GROUP_CONCAT(name) AS options, GROUP_CONCAT(gia_ban) AS prices FROM tuy_chon_sp WHERE id_sp = ? GROUP BY tieu_de');
    $stmt->execute([ $_GET['id'] ]);
    // Lấy các tùy chọn sản phẩm từ cơ sở dữ liệu và trả kết quả dưới dạng mảng
    $product_options = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Hiển thị thông báo lỗi đơn giản nếu id không được chỉ định
    $error = 'Sản phẩm không tồn tại!';
}
?>


<?=template_header(isset($product) && $product ? $product['name'] : 'Error')?>

<?php if ($error): ?>

<p class="content-wrapper error"><?=$error?></p>

<?php else: ?>

<div class="product content-wrapper">

    <div class="product-imgs">

        <?php if (!empty($product['img']) && file_exists('imgs/' . $product['img'])): ?>
        <img class="product-img-large" src="imgs/<?=$product['img']?>" width="500" height="500" alt="<?=$product['name']?>">
        <?php endif; ?>

        <div class="product-small-imgs">
            <?php foreach ($product_imgs as $product_img): ?>
            <img class="product-img-small<?=$product_img['img']==$product['img']?' selected':''?>" src="imgs/<?=$product_img['img']?>" width="150" height="150" alt="<?=$product['name']?>">
            <?php endforeach; ?>
        </div>

    </div>

    <div class="product-wrapper">

        <h1 class="name"><?=$product['name']?></h1>

        <span class="price">
            <?=number_format($product['gia_ban'],0)?><?=currency_code?>
            <?php if ($product['gia_goc'] > 0): ?>
            <span class="rrp"><?=number_format($product['gia_goc'],0)?><?=currency_code?></span>
            <?php endif; ?>
        </span>

        <form id="product-form" action="index.php?page=cart" method="post">
            <?php foreach ($product_options as $option): ?>
            <select name="option-<?= $option['tieu_de'] ?>" required onchange="updatePrice(this)">
                <option value="" selected disabled style="display:none"><?=$option['tieu_de']?></option>
                <?php
                $options_names = explode(',', $option['options']);
                $options_prices = explode(',', $option['prices']);
                ?>
                <?php foreach ($options_names as $k => $name): ?>
                <option value="<?=$name?>" data-price="<?=$options_prices[$k]?>"><?=$name?></option>
                <?php endforeach; ?>
            </select>
            <?php endforeach; ?>
            <input type="number" name="so_luong" value="1" min="1" <?php if ($product['so_luong'] != -1): ?>max="<?=$product['so_luong']?>"<?php endif; ?> placeholder="Số lượng" required>
            <input type="hidden" name="id_sp" value="<?=$product['id']?>">
            <input type="submit" value="<?=$product['so_luong']==0?'Out of Stock':'Thêm vào giỏ hàng'?>">
        </form>

        <div class="description">
            <?=$product['mo_ta']?>
        </div>

    </div>

</div>
<script>
    function updatePrice(selectElement) {
        var selectedOption = selectElement.options[selectElement.selectedIndex];
        var price = selectedOption.getAttribute('data-price');
        var productPrice = <?= $product['gia_ban'] ?>;
        if (price) {
            productPrice = parseFloat(price);
        }
        document.querySelector('.price').innerText = formatPrice(productPrice) + '<?= currency_code ?>';
    }

    function formatPrice(price) {
        return Number(price).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
</script>

<?php endif; ?>

<?=template_footer()?>
