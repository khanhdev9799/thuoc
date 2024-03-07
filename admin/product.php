<?php
defined('admin') or exit;

// Giá trị sản phẩm mặc định
$product = array(
    'name' => '',
    'mo_ta' => '',
    'gia_ban' => 0,
    'gia_goc' => 0,
    'so_luong' => 1,
    'ngay_tao' => date('Y-m-d\TH:i:s'),
    'img' => '',
    'imgs' => '',
    'danh_muc' => array(),
    'options' => array(),
    'options_string' => ''
);

// Lấy tất cả các danh mục từ cơ sở dữ liệu
$stmt = $pdo->query('SELECT * FROM danh_muc');
$stmt->execute();
$danh_muc = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy tất cả các hình ảnh từ thư mục "imgs"
$imgs = glob('../imgs/*.{jpg,png,gif,jpeg,webp}', GLOB_BRACE);

// Thêm hình ảnh sản phẩm vào cơ sở dữ liệu
function addProductImages($pdo, $id_sp) {
    if (isset($_POST['images_list'])) {
        $images_list = explode(',', $_POST['images_list']);
        $in  = str_repeat('?,', count($images_list) - 1) . '?';
        $stmt = $pdo->prepare('DELETE FROM hinh_anh_sp WHERE id_sp = ? AND img NOT IN (' . $in . ')');
        $stmt->execute(array_merge([ $id_sp ], $images_list));
        foreach ($images_list as $img) {
            if (empty($img)) continue;
            $stmt = $pdo->prepare('INSERT IGNORE INTO hinh_anh_sp (id_sp,img) VALUES (?,?)');
            $stmt->execute([ $id_sp, $img ]);
        }
    }
}

// Thêm danh mục sản phẩm vào cơ sở dữ liệu
function addProductCategories($pdo, $id_sp) {
    if (isset($_POST['categories_list'])) {
        $list = explode(',', $_POST['categories_list']);
        $in  = str_repeat('?,', count($list) - 1) . '?';
        $stmt = $pdo->prepare('DELETE FROM thamchieu_dm_sp WHERE id_sp = ? AND id_dm NOT IN (' . $in . ')');
        $stmt->execute(array_merge([ $id_sp ], $list));
        foreach ($list as $cat) {
            if (empty($cat)) continue;
            $stmt = $pdo->prepare('INSERT IGNORE INTO thamchieu_dm_sp (id_sp,id_dm) VALUES (?,?)');
            $stmt->execute([ $id_sp, $cat ]);
        }
    }
}

// Thêm tùy chọn sản phẩm vào cơ sở dữ liệu
function addProductOptions($pdo, $id_sp) {
    if (isset($_POST['options'])) {
        $list = explode(',', $_POST['options']);
        $stmt = $pdo->prepare('SELECT * FROM tuy_chon_sp WHERE id_sp = ?');
        $stmt->execute([ $id_sp ]);
        $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $remove_list = array();
        foreach ($options as $option) {
            $option_string = $option['tieu_de'] . '__' . $option['name'] . '__' . $option['gia_ban'];
            if (!in_array($option_string, $list)) {
                $remove_list[] = $option['id'];
            } else {
                array_splice($list, array_search($option_string, $list), 1);
            }
        }
        if (!empty($remove_list)) {
            $in = str_repeat('?,', count($remove_list) - 1) . '?';
            $stmt = $pdo->prepare('DELETE FROM tuy_chon_sp WHERE id IN (' . $in . ')');
            $stmt->execute($remove_list);
        }        
        foreach ($list as $option) {
            if (empty($option)) continue;
            $option = explode('__', $option);
            $stmt = $pdo->prepare('INSERT INTO tuy_chon_sp (tieu_de,name,gia_ban,id_sp) VALUES (?,?,?,?)');
            $stmt->execute([ $option[0], $option[1], $option[2], $id_sp ]);
        }
    }
}

$page = (isset($_GET['id'])) ? 'Edit' : 'Create';

if (isset($_GET['id'])) {
    // Nếu ID tồn tại, chỉnh sửa một sản phẩm đã tồn tại
    if (isset($_POST['submit'])) {
        // Cập nhật thông tin của sản phẩm
        $stmt = $pdo->prepare('UPDATE san_pham SET name = ?, `mo_ta` = ?, gia_ban = ?, gia_goc = ?, so_luong = ?, img = ?, ngay_tao = ? WHERE id = ?');
        $stmt->execute([ $_POST['name'], $_POST['mo_ta'], $_POST['gia_ban'], $_POST['gia_goc'], $_POST['so_luong'], $_POST['main_image'], date('Y-m-d H:i:s', strtotime($_POST['date'])), $_GET['id'] ]);
        addProductImages($pdo, $_GET['id']);
        addProductCategories($pdo, $_GET['id']);
        addProductOptions($pdo, $_GET['id']);
        header('Location: index.php?page=products');
        exit;
    }

    if (isset($_POST['delete'])) {
        // Xóa sản phẩm cùng với hình ảnh, danh mục, và tùy chọn của nó
        $stmt = $pdo->prepare('DELETE p, pi, po, pc FROM san_pham p LEFT JOIN hinh_anh_sp pi ON pi.id_sp = p.id LEFT JOIN tuy_chon_sp po ON po.id_sp = p.id LEFT JOIN thamchieu_dm_sp pc ON pc.id_sp = p.id WHERE p.id = ?');
        $stmt->execute([ $_GET['id'] ]);
        header('Location: index.php?page=products');
        exit;
    }

    // Lấy thông tin sản phẩm và hình ảnh từ cơ sở dữ liệu
    $stmt = $pdo->prepare('SELECT p.*, GROUP_CONCAT(pi.img) AS imgs FROM san_pham p LEFT JOIN hinh_anh_sp pi ON p.id = pi.id_sp WHERE p.id = ? GROUP BY p.id');
    $stmt->execute([ $_GET['id'] ]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Lấy danh mục sản phẩm
    $stmt = $pdo->prepare('SELECT c.name, c.id FROM thamchieu_dm_sp pc JOIN danh_muc c ON c.id = pc.id_dm WHERE pc.id_sp = ?');
    $stmt->execute([ $_GET['id'] ]);
    $product['danh_muc'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Lấy các tùy chọn của sản phẩm
    $stmt = $pdo->prepare('SELECT * FROM tuy_chon_sp WHERE id_sp = ?');
    $stmt->execute([ $_GET['id'] ]);
    $product['options'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $product['options_string'] = '';
    foreach($product['options'] as $option) {
        $product['options_string'] .= $option['tieu_de'] . '__' . $option['name'] . '__' . $option['gia_ban'] . ',';
    }
    $product['options_string'] = rtrim($product['options_string'], ',');
} else {
    // Tạo một sản phẩm mới
    if (isset($_POST['submit'])) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO san_pham (name,`mo_ta`,gia_ban,gia_goc,so_luong,img,ngay_tao) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([ $_POST['name'], $_POST['mo_ta'], $_POST['gia_ban'], $_POST['gia_goc'], $_POST['so_luong'], $_POST['main_image'], date('Y-m-d H:i:s', strtotime($_POST['date'])) ]);
        addProductImages($pdo, $pdo->lastInsertId());
        addProductCategories($pdo, $pdo->lastInsertId());
        addProductOptions($pdo, $pdo->lastInsertId());
        header('Location: index.php?page=products');
        exit;
    }
}
?>


<?=template_admin_header($page . ' Product')?>

<h2><?=$page?> Product</h2>

<div class="content-block">

    <form action="" method="post" class="form responsive-width-100">

        <label for="name">Tên sản phẩm</label>
        <input type="text" name="name" placeholder="tên" value="<?=$product['name']?>" required>

        <label for="mo_ta">Mô tả (HTML)</label>
        <textarea name="mo_ta" placeholder="mô tả (HTML)"><?=$product['mo_ta']?></textarea>

        <label for="gia_ban">Giá bán</label>
        <input type="number" name="gia_ban" placeholder="Giá bán" min="0" step=".01" value="<?=$product['gia_ban']?>" required>

        <label for="gia_goc">Giá gốc</label>
        <input type="number" name="gia_goc" placeholder="giá gốc" min="0" step=".01" value="<?=$product['gia_goc']?>" required>

        <label for="quantity">số lượng</span></label>
        <input type="number" name="so_luong" placeholder="số lượng" min="-1" value="<?=$product['so_luong']?>" title="-1 = unlimited" required>

        <label for="date">Ngày tạo</label>
        <input type="datetime-local" name="date" placeholder="Date" value="<?=date('Y-m-d\TH:i:s', strtotime($product['ngay_tao']))?>" required>

        <label for="add_categories">Loại thuốc</label>
        <div style="display:flex;flex-flow:wrap;">
            <select name="add_categories" id="add_categories" style="width:50%;" multiple>
                <?php foreach ($danh_muc as $cat): ?>
                <option value="<?=$cat['id']?>"><?=$cat['name']?></option>
                <?php endforeach; ?>
            </select>
            <select name="danh_muc" style="width:50%;" multiple>
                <?php foreach ($product['danh_muc'] as $cat): ?>
                <option value="<?=$cat['id']?>"><?=$cat['name']?></option>
                <?php endforeach; ?>
            </select>
            <button id="add_selected_categories" style="width:50%;">Thêm</button>
            <button id="remove_selected_categories" style="width:50%;">Xóa</button>
            <input type="hidden" name="categories_list" value="<?=implode(',', array_column($product['danh_muc'], 'id'))?>">
        </div>

        <label for="add_option">Tùy chọn</label>
        <div style="display:flex;flex-flow:wrap;">
            <input type="text" name="option_title" placeholder="Tiêu đề tùy chọn(Đơn vị mua)" style="width:47%;margin-right:13px;">
            <input type="text" name="option_name" placeholder="tên lựa chọn" style="width:50%;">
            <input type="number" name="option_price" min="0" step=".01" placeholder="Giá tùy chọn (ví dụ: 15 000)">
            <button id="add_option" style="margin-bottom:10px;">Thêm</button>
            <select name="options" multiple>
                <?php foreach ($product['options'] as $option): ?>
                <option value="<?=$option['tieu_de']?>__<?=$option['name']?>__<?=$option['gia_ban']?>"><?=$option['tieu_de']?>,<?=$option['name']?>,<?=$option['gia_ban']?></option>
                <?php endforeach; ?>
            </select>
            <button id="remove_selected_options">Xóa</button>
            <input type="hidden" name="options" value="<?=$product['options_string']?>">
        </div>

        <label for="add_images">Hình ảnh</label>
        <div style="display:flex;flex-flow:wrap;">
            <select name="add_images" id="add_images" style="width:50%;" multiple>
                <?php foreach ($imgs as $img): ?>
                <option value="<?=basename($img)?>"><?=basename($img)?></option>
                <?php endforeach; ?>
            </select>
            <select name="images" style="width:50%;" multiple>
                <?php foreach (explode(',', $product['imgs']) as $img): ?>
                <?php if (!empty($img)): ?>
                <option value="<?=$img?>"><?=$img?></option>
                <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <button id="add_selected_images" style="width:50%;">Thêm</button>
            <button id="remove_selected_images" style="width:50%;">Xóa</button>
            <input type="hidden" name="images_list" value="<?=$product['imgs']?>">
        </div>

        <div>
            <label for="main_image">Hình ảnh đại diện</label>
            <select name="main_image" id="main_image">
                <?php foreach (explode(',', $product['imgs']) as $img): ?>
                <option value="<?=$img?>"<?=$product['img'] == $img ? ' selected' : ''?>><?=$img?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="submit-btns">
            <input type="submit" name="submit" value="Xác nhận">
            <?php if ($page == 'Edit'): ?>
            <input type="submit" name="delete" value="Delete" class="delete">
            <?php endif; ?>
        </div>

    </form>

</div>

<script>
document.querySelector("#remove_selected_options").onclick = function(e) {
    e.preventDefault();
    document.querySelectorAll("select[name='add_categories'] option").forEach(function(option) {
        if (option.selected) {
            let list = document.querySelector("input[name='categories_list']").value.split(",");
            if (!list.includes(option.value)) {
                list.push(option.value);
            }
            document.querySelector("input[name='categories_list']").value = list.join(",");
            document.querySelector("select[name='danh_muc']").innerHTML += '<option value="' + option.value + '">' + option.text + '</option>';
        }
    });
};
document.querySelector("#add_option").onclick = function(e) {
    e.preventDefault();
    if (document.querySelector("input[name='option_title']").value == "") {
        document.querySelector("input[name='option_title']").focus();
        return;
    }
    if (document.querySelector("input[name='option_name']").value == "") {
        document.querySelector("input[name='option_name']").focus();
        return;
    }
    if (document.querySelector("input[name='option_price']").value == "") {
        document.querySelector("input[name='option_price']").focus();
        return;
    }
    let option = document.createElement("option");
    option.value = document.querySelector("input[name='option_title']").value + '__' + document.querySelector("input[name='option_name']").value + '__' + document.querySelector("input[name='option_price']").value;
    option.text = document.querySelector("input[name='option_title']").value + ',' + document.querySelector("input[name='option_name']").value + ',' + document.querySelector("input[name='option_price']").value;
    document.querySelector("select[name='options']").add(option);
    document.querySelector("input[name='option_title']").value = "";
    document.querySelector("input[name='option_name']").value = "";
    document.querySelector("input[name='option_price']").value = "";
    document.querySelectorAll("select[name='options'] option").forEach(function(option) {
        let list = document.querySelector("input[name='options']").value.split(",");
        if (!list.includes(option.value)) {
            list.push(option.value);
        }
        document.querySelector("input[name='options']").value = list.join(",");
    });
};
document.querySelector("#remove_selected_categories").onclick = function(e) {
    e.preventDefault();
    document.querySelectorAll("select[name='danh_muc'] option").forEach(function(option) {
        if (option.selected) {
            let list = document.querySelector("input[name='categories_list']").value.split(",");
            list.splice(list.indexOf(option.value), 1);
            document.querySelector("input[name='categories_list']").value = list.join(",");
            option.remove();
        }
    });
};
document.querySelector("#add_selected_categories").onclick = function(e) {
    e.preventDefault();
    document.querySelectorAll("select[name='add_categories'] option").forEach(function(option) {
        if (option.selected) {
            let list = document.querySelector("input[name='categories_list']").value.split(",");
            if (!list.includes(option.value)) {
                list.push(option.value);
            }
            document.querySelector("input[name='categories_list']").value = list.join(",");
            document.querySelector("select[name='danh_muc']").add(option.cloneNode(true));
        }
    });
};
document.querySelector("#remove_selected_images").onclick = function(e) {
    e.preventDefault();
    document.querySelectorAll("select[name='images'] option").forEach(function(option) {
        if (option.selected) {
            let images_list = document.querySelector("input[name='images_list']").value.split(",");
            images_list.splice(images_list.indexOf(option.value), 1);
            document.querySelector("input[name='images_list']").value = images_list.join(",");
            document.querySelectorAll("select[name='main_image'] option").forEach(i => i.value == option.value ? i.remove() : false);
            option.remove();
        }
    });
};
document.querySelector("#add_selected_images").onclick = function(e) {
    e.preventDefault();
    document.querySelectorAll("select[name='add_images'] option").forEach(function(option) {
        if (option.selected) {
            let images_list = document.querySelector("input[name='images_list']").value.split(",");
            if (!images_list.includes(option.value)) {
                images_list.push(option.value);
            }
            let add_to_main_images = true;
            document.querySelectorAll("select[name='main_image'] option").forEach(i => add_to_main_images = i.value == option.value ? false : add_to_main_images);
            document.querySelector("input[name='images_list']").value = images_list.join(",");
            document.querySelector("select[name='images']").add(option.cloneNode(true));
            if (add_to_main_images) {
                document.querySelector("select[name='main_image']").add(option.cloneNode(true));
            }
        }
    });
};
</script>

<?=template_admin_footer()?>
