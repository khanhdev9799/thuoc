<?php
defined('admin') or exit;

// Truy vấn SQL sẽ lấy tất cả các đơn hàng và sắp xếp theo ngày tạo
$stmt = $pdo->prepare('SELECT
    p.img AS img,
    p.name AS name,
    t.*,
    ti.don_gia AS gia_ban,
    ti.muc_sl AS so_luong,
    ti.muc_tuy_chon AS options
    FROM chi_tiet_gd t
    JOIN danh_sach_gd ti ON ti.ma_gd = t.ma_gd
    JOIN san_pham p ON p.id = ti.id_san_pham
    ORDER BY t.ngay_dat DESC');
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?=template_admin_header('Đơn Hàng')?>

<h2>Đơn Hàng</h2>

<div class="content-block">
    <div class="table">
        <table>
            <thead>
                <tr>
                    <td colspan="2">Tên sản phẩm</td>
                    <td class="responsive-hidden">Ngày đặt</td>
                    <td class="responsive-hidden">Giá</td>
                    <td>Số lượng</td>
                    <td>Tổng tiền</td>
                    <td class="responsive-hidden">Email</td>
                    <td class="responsive-hidden">Trạng thái</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="8" style="text-align:center;">Không có đơn đặt hàng nào gần đây</td>
                </tr>
                <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <tr class="details">
                    <td class="img">
                        <?php if (!empty($order['img']) && file_exists('../imgs/' . $order['img'])): ?>
                        <img src="../imgs/<?=$order['img']?>" width="32" height="32" alt="<?=$order['name']?>">
                        <?php endif; ?>
                    </td>
                    <td><?=$order['name']?></td>
                    <td class="responsive-hidden"><?=date('F j, Y', strtotime($order['ngay_dat']))?></td>
                    <td class="responsive-hidden"><?=number_format($order['gia_ban'], 0)?><?=currency_code?></td>
                    <td><?=$order['so_luong']?></td>
                    <td><?=number_format($order['gia_ban'] * $order['so_luong'], 0)?><?=currency_code?></td>
                    <td class="responsive-hidden"><?=$order['email']?></td>
                    <td class="responsive-hidden"><?=$order['tinh_trang']?></td>
                </tr>
                <tr class="expanded-details">
                    <td colspan="8">
                        <div>
                            <div>
                                <span>Mã giao dịch</span>
                                <span><?=$order['ma_gd']?></span>
                            </div>
                            <div>
                                <span>Phương thức thanh toán</span>
                                <span><?=$order['pt_thanhtoan']?></span>
                            </div>
                            <div>
                                <span>Ngày tạo</span>
                                <span><?=$order['ngay_dat']?></span>
                            </div>
                            <div>
                                <span>Tên</span>
                                <span><?=$order['ho']?> <?=$order['ten']?></span>
                            </div>
                            <div>
                                <span>ID tài khoản</span>
                                <span><?=$order['id_taikhoan']?></span>
                            </div>
                            <div>
                                <span>Email</span>
                                <span><?=$order['email']?></span>
                            </div>
                            <div>
                                <span>Trạng thái</span>
                                <span><?=$order['tinh_trang']?></span>
                            </div>
                            <div>
                                <span>Địa chỉ</span>
                                <span>
                                    <?=$order['dia_chi']?><br>
                                    <?=$order['quan_huyen']?><br>
                                    <?=$order['thanh_pho']?><br>
                                    <?=$order['sdt']?><br>
                                    <?=$order['quoc_gia']?>
                                </span>
                            </div>
                            <div>
                                <span>Đơn vị mua</span>
                                <span><?=$order['options']?></span>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <script>
        document.querySelectorAll(".admin .details").forEach(function(detail) {
            detail.onclick = function() {
                let display = this.nextElementSibling.style.display == 'table-row' ? 'none' : 'table-row';
                this.nextElementSibling.style.display = display;
            };
        });
        </script>
    </div>
</div>

<?=template_admin_footer()?>
