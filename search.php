<?php
// Ngăn truy cập trực tiếp vào tệp
defined('shoppingcart') or exit;
// Kiểm tra cho truy vấn tìm kiếm
if (isset($_GET['query']) && $_GET['query'] != '') {
    // Tránh tấn công XSS bằng cách chuyển đổi các ký tự đặc biệt trong truy vấn người dùng
    $search_query = htmlspecialchars($_GET['query'], ENT_QUOTES, 'UTF-8');
    // Lấy các sản phẩm theo thứ tự ngày thêm mới nhất
    $stmt = $pdo->prepare('SELECT * FROM san_pham WHERE name LIKE ? ORDER BY ngay_tao DESC');
    // bindValue cho phép chúng ta sử dụng số nguyên trong câu lệnh SQL, chúng ta cần sử dụng cho LIMIT
    $stmt->execute(['%' . $search_query . '%']);
    // Lấy các sản phẩm từ cơ sở dữ liệu và trả kết quả dưới dạng Mảng
    $san_pham = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Lấy tổng số sản phẩm
    $total_products = count($san_pham);
} else {
    // Báo lỗi đơn giản, nếu không có truy vấn tìm kiếm được chỉ định tại sao người dùng lại ở trang này?
    $error = 'Không có truy vấn tìm kiếm được chỉ định!';
}
?>

<?=template_header('Search')?>

<?php if ($error): ?>

<p class="content-wrapper error"><?=$error?></p>

<?php else: ?>

<div class="products content-wrapper">

    <h1>Search Results for "<?=$search_query?>"</h1>

    <p><?=$total_products?> Products</p>

    <div class="products-wrapper">
        <?php foreach ($san_pham as $product): ?>
        <a href="index.php?page=product&id=<?=$product['id']?>" class="product">
            <?php if (!empty($product['img']) && file_exists('imgs/' . $product['img'])): ?>
            <img src="imgs/<?=$product['img']?>" width="200" height="200" alt="<?=$product['name']?>">
            <?php endif; ?>
            <span class="name"><?=$product['name']?></span>
            <span class="price">
                <?=number_format($product['gia_ban'],0)?><?=currency_code?>
                <?php if ($product['gia_goc'] > 0): ?>
                <span class="rrp"><?=number_format($product['gia_goc'],0)?><?=currency_code?></span>
                <?php endif; ?>
            </span>
        </a>
        <?php endforeach; ?>
    </div>

</div>

<?php endif; ?>

<?=template_footer()?>
