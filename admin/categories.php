<?php
defined('admin') or exit;
// Truy vấn SQL để lấy tất cả các danh mục từ bảng "danh_muc"
$stmt = $pdo->prepare('SELECT * FROM danh_muc');
$stmt->execute();
$danh_muc = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?=template_admin_header('Categories')?>

<h2>Danh mục sản phẩm</h2>

<div class="links">
    <a href="index.php?page=category">Thêm danh mục</a>
</div>

<div class="content-block">
    <div class="table">
        <table>
            <thead>
                <tr>
                    <td>#</td>
                    <td>Tên</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($danh_muc)): ?>
                <tr>
                    <td colspan="8" style="text-align:center;">Không có danh mục sản phẩm</td>
                </tr>
                <?php else: ?>
                <?php foreach ($danh_muc as $category): ?>
                <tr class="details" onclick="location.href='index.php?page=category&id=<?=$category['id']?>'">
                    <td><?=$category['id']?></td>
                    <td><?=$category['name']?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?=template_admin_footer()?>
