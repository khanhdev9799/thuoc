<?php
defined('admin') or exit;
// Truy vấn SQL sẽ lấy tất cả các tài khoản từ cơ sở dữ liệu được sắp xếp theo cột ID
$stmt = $pdo->prepare('SELECT * FROM tai_khoan ORDER BY id DESC');
$stmt->execute();
$tai_khoan = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?=template_admin_header('Tài Khoản')?>

<h2>Tài Khoản</h2>

<div class="links">
    <a href="index.php?page=account">Thêm Tài Khoản</a>
</div>

<div class="content-block">
    <div class="table">
        <table>
            <thead>
                <tr>
                    <td class="responsive-hidden">#</td>
                    <td>Email</td>
                    <td>Tên</td>
                    <td>Địa chỉ</td>
                    <td>Admin</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tai_khoan)): ?>
                <tr>
                    <td colspan="8" style="text-align:center;">Không có tài khoản</td>
                </tr>
                <?php else: ?>
                <?php foreach ($tai_khoan as $account): ?>
                <tr class="details" onclick="location.href='index.php?page=account&id=<?=$account['id']?>'">
                    <td class="responsive-hidden"><?=$account['id']?></td>
                    <td><?=$account['email']?></td>
                    <td><?=$account['ho']?> <?=$account['ten']?></td>
                    <td>
                        <?=$account['dia_chi']?><br>
                        <?=$account['quan_huyen']?><br>
                        <?=$account['thanh_pho']?><br>
                        <?=$account['sdt']?><br>
                        <?=$account['quoc_gia']?><br>
                    </td>
                    <td><?=$account['admin']==1?'true':'false'?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?=template_admin_footer()?>
