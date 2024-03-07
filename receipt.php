<?php
// Ngăn truy cập trực tiếp vào tệp

defined('shoppingcart') or exit;

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
	
?>

<?=template_header('Receipt')?>

<?php foreach ($chi_tiet_gd as $transaction): ?>
            
	<table>			
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

	</table>
<?=template_footer()?>