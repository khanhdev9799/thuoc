<?php
// Ngăn truy cập trực tiếp vào tệp
defined('shoppingcart') or exit;

// Xóa tất cả các sản phẩm trong giỏ hàng, không cần thiết nữa vì đơn hàng đã được xử lý

unset($_SESSION['cart']);
?>

<?=template_header('Place Order')?>

<?php if ($error): ?>
<p class="content-wrapper error"><?=$error?></p>
<?php else: ?>
<div class="placeorder content-wrapper">
    <h1>Đơn hàng của bạn đã được đặt</h1>
    <p>Cảm ơn bạn đã đặt hàng với chúng tôi, chúng tôi sẽ liên hệ với bạn qua email kèm theo chi tiết đơn hàng của bạn.</p>
</div>

<?php endif; ?>

<?=template_footer()?>
