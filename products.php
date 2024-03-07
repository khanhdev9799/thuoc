<?php
// Ngăn truy cập trực tiếp vào tệp
defined('shoppingcart') or exit;

// Lấy tất cả các danh mục từ cơ sở dữ liệu
$stmt = $pdo->query('SELECT * FROM danh_muc');
$stmt->execute();
$danh_muc = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh mục hiện tại từ yêu cầu GET, nếu không có, đặt danh mục được chọn mặc định thành: all
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$category_sql = '';
if ($category != 'all') {
    $category_sql = 'JOIN thamchieu_dm_sp pc ON pc.id_dm = :id_dm AND pc.id_sp = p.id JOIN danh_muc c ON c.id = pc.id_dm';
}

// Lấy sắp xếp từ yêu cầu GET, sẽ xảy ra nếu người dùng thay đổi một mục trong hộp chọn
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'sort3';

// Số lượng sản phẩm được hiển thị trên mỗi trang
$num_products_on_each_page = 8;

// Trang hiện tại, trong URL này sẽ xuất hiện như index.php?page=products&p=1, index.php?page=products&p=2, v.v.
$current_page = isset($_GET['p']) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 1;

// Chọn sản phẩm được sắp xếp theo ngày thêm
if ($sort == 'sort1') {
    // sort1 = Alphabetical A-Z
    $stmt = $pdo->prepare('SELECT p.* FROM san_pham p ' . $category_sql . ' ORDER BY p.name ASC LIMIT :page,:num_products');
} elseif ($sort == 'sort2') {
    // sort2 = Alphabetical Z-A
    $stmt = $pdo->prepare('SELECT p.* FROM san_pham p ' . $category_sql . ' ORDER BY p.name DESC LIMIT :page,:num_products');
} elseif ($sort == 'sort3') {
    // sort3 = Newest
    $stmt = $pdo->prepare('SELECT p.* FROM san_pham p ' . $category_sql . ' ORDER BY p.ngay_tao DESC LIMIT :page,:num_products');
} elseif ($sort == 'sort4') {
    // sort4 = Oldest
    $stmt = $pdo->prepare('SELECT p.* FROM san_pham p ' . $category_sql . ' ORDER BY p.ngay_tao ASC LIMIT :page,:num_products');
} else {
    // Không có sắp xếp được chỉ định, lấy các sản phẩm mà không sắp xếp
    $stmt = $pdo->prepare('SELECT p.* FROM san_pham p ' . $category_sql . ' LIMIT :page,:num_products');
}

// bindValue sẽ cho phép chúng ta sử dụng số nguyên trong câu lệnh SQL, chúng ta cần sử dụng cho LIMIT
if ($category != 'all') {
    $stmt->bindValue(':id_dm', $category, PDO::PARAM_INT);
}
$stmt->bindValue(':page', ($current_page - 1) * $num_products_on_each_page, PDO::PARAM_INT);
$stmt->bindValue(':num_products', $num_products_on_each_page, PDO::PARAM_INT);
$stmt->execute();

// Lấy các sản phẩm từ cơ sở dữ liệu và trả kết quả dưới dạng mảng
$san_pham = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy tổng số sản phẩm
$total_products = $pdo->query('SELECT COUNT(*) FROM san_pham')->fetchColumn();
?>

<?php
$stmt = $pdo->prepare('SELECT COUNT(*) FROM san_pham p ' . $category_sql);
if ($category != 'all') {
    $stmt->bindValue(':id_dm', $category, PDO::PARAM_INT);
}
$stmt->execute();
$total_products = $stmt->fetchColumn();
?>


<?=template_header('Products')?>

<div class="products content-wrapper">

    <h1>Cửa Hàng</h1>

    <div class="products-header">
        <p><?=$total_products?> Sản phẩm</p>
        <form action="" method="get" class="products-form">
            <input type="hidden" name="page" value="products">
            <label class="category">
               Danh mục sản phẩm
                <select name="category">
                    <option value="all"<?=($category == 'all' ? ' selected' : '')?>>Tất cả</option>
                    <?php foreach ($danh_muc as $c): ?>
                    <option value="<?=$c['id']?>"<?=($category == $c['id'] ? ' selected' : '')?>><?=$c['name']?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="sortby">
                Sắp xếp theo
                <select name="sort">
                    <option value="sort1"<?=($sort == 'sort1' ? ' selected' : '')?>>A-Z</option>
                    <option value="sort2"<?=($sort == 'sort2' ? ' selected' : '')?>>Z-A</option>
                    <option value="sort3"<?=($sort == 'sort3' ? ' selected' : '')?>>Mới nhất</option>
                    <option value="sort4"<?=($sort == 'sort4' ? ' selected' : '')?>>Cũ nhất</option>
                </select>
            </label>
        </form>
    </div>

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

    <div class="buttons">
        <?php if ($current_page > 1): ?>
        <a href="index.php?page=products&p=<?=$current_page-1?>&category=<?=$category?>&sort=<?=$sort?>">Trước đó</a>
        <?php endif; ?>
        <?php if ($total_products > ($current_page * $num_products_on_each_page) - $num_products_on_each_page + count($san_pham)): ?>
        <a href="index.php?page=products&p=<?=$current_page+1?>&category=<?=$category?>&sort=<?=$sort?>">Kế tiếp</a>
        <?php endif; ?>
    </div>

</div>

<?=template_footer()?>
