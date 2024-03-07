<?php
defined('admin') or exit;
// Truy vấn SQL để lấy tất cả các sản phẩm từ bảng "sản phẩm"
$stmt = $pdo->prepare('SELECT p.*, GROUP_CONCAT(pi.img) AS imgs FROM san_pham p LEFT JOIN hinh_anh_sp pi ON p.id = pi.id_sp GROUP BY p.id ORDER BY p.ngay_tao ASC');
$stmt->execute();
$san_pham = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?=template_admin_header('Products')?>

<h2>Sản Phẩm</h2>

<div class="links">
    <a href="index.php?page=product">Thêm Sản Phẩm</a>
</div>

<div class="content-block">
    <div class="table">
        <table>
            <thead>
                <tr>
                    <td class="responsive-hidden">#</td>
                    <td>Tên</td>
                    <td>Giá Chiết Khấu</td>
                    <td class="responsive-hidden">Giá Gốc</td>
                    <td>Số Lượng</td>
                    <td class="responsive-hidden">Hình ảnh</td>
                    <td class="responsive-hidden">Ngày tạo</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($san_pham)): ?>
                <tr>
                    <td colspan="8" style="text-align:center;">Không có sản phẩm</td>
                </tr>
                <?php else: ?>
                <?php foreach ($san_pham as $product): ?>
                <tr class="details" onclick="location.href='index.php?page=product&id=<?=$product['id']?>'">
                    <td class="responsive-hidden"><?=$product['id']?></td>
                    <td><?=$product['name']?></td>
                    <td><?=number_format($product['gia_ban'], 0)?><?=currency_code?></td>
                    <td class="responsive-hidden"><?=number_format($product['gia_goc'], 0)?><?=currency_code?></td>
                    <td><?=$product['so_luong']?></td>
                    <td class="responsive-hidden">
                        <?PHP foreach (explode(',',$product['imgs']) as $img): ?>
                        <img src="../imgs/<?=$img?>" width="32" height="32" alt="<?=$img?>">
                        <?php endforeach; ?>
                    </td>
                    <td class="responsive-hidden"><?=date('F j, Y', strtotime($product['ngay_tao']))?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?=template_admin_footer()?>
