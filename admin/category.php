<?php
defined('admin') or exit;
// Giá trị mặc định cho danh mục
$category = array(
    'name' => ''
);
if (isset($_GET['id'])) {
    // Nếu tham số ID tồn tại, chỉnh sửa một danh mục hiện có
    $page = 'Edit';
    if (isset($_POST['submit'])) {
        // cập nhật danh mục
        $stmt = $pdo->prepare('UPDATE danh_muc SET name = ? WHERE id = ?');
        $stmt->execute([ $_POST['name'], $_GET['id'] ]);
        header('Location: index.php?page=categories');
        exit;
    }
    if (isset($_POST['delete'])) {
        // xóa danh mục
        $stmt = $pdo->prepare('DELETE FROM danh_muc WHERE id = ?');
        $stmt->execute([ $_GET['id'] ]);
        header('Location: index.php?page=categories');
        exit;
    }
    // lấy danh mục từ database
    $stmt = $pdo->prepare('SELECT * FROM danh_muc WHERE id = ?');
    $stmt->execute([ $_GET['id'] ]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    // tạo mới danh mục
    $page = 'Create';
    if (isset($_POST['submit'])) {
        $stmt = $pdo->prepare('INSERT INTO danh_muc (name) VALUES (?)');
        $stmt->execute([ $_POST['name'] ]);
        header('Location: index.php?page=categories');
        exit;
    }
}
?>

<?=template_admin_header($page . ' Category')?>

<h2><?=$page?> Category</h2>

<div class="content-block">
    <form action="" method="post" class="form responsive-width-100">
        <label for="name">Tên</label>
        <input type="text" name="name" placeholder="tên" value="<?=$category['name']?>" required>
        <div class="submit-btns">
            <input type="submit" name="submit" value="Xác nhận">
            <?php if ($page == 'Edit'): ?>
            <input type="submit" name="delete" value="Xóa" class="delete">
            <?php endif; ?>
        </div>
    </form>
</div>

<?=template_admin_footer()?>
