<?php
defined('admin') or exit;
// Gia tri mac dinh cua tai khoan
$account = array(
    'email' => '',
    'password' => '',
    'ho' => '',
    'ten' => '',
    'dia_chi' => '',
    'quan_huyen' => '',
    'thanh_pho' => '',
    'sdt' => '',
    'quoc_gia' => '',
    'admin' => 'No'
);
if (isset($_GET['id'])) {
    //  Nếu có tham số ID tồn tại, chỉnh sửa một tài khoản hiện có.
    $page = 'Edit';
    if (isset($_POST['submit'])) {
        // Cập nhật tài khoản
        $stmt = $pdo->prepare('UPDATE tai_khoan SET email = ?, password = ?, ho = ?, ten = ?, dia_chi = ?, quan_huyen = ?, thanh_pho = ?, sdt = ?, quoc_gia = ?, admin = ? WHERE id = ?');
        $password = $_POST['password'] == $account['password'] ? $_POST['password'] : password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt->execute([ $_POST['email'], $password, $_POST['ho'], $_POST['ten'], $_POST['dia_chi'], $_POST['quan_huyen'], $_POST['thanh_pho'], $_POST['sdt'], $_POST['quoc_gia'], $_POST['admin'], $_GET['id'] ]);
        header('Location: index.php?page=accounts');
        exit;
    }
    if (isset($_POST['delete'])) {
        // Xóa tài khoản
        $stmt = $pdo->prepare('DELETE FROM tai_khoan WHERE id = ?');
        $stmt->execute([ $_GET['id'] ]);
        header('Location: index.php?page=accounts');
        exit;
    }
    // lấy tài khoản trong database
    $stmt = $pdo->prepare('SELECT * FROM tai_khoan WHERE id = ?');
    $stmt->execute([ $_GET['id'] ]);
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    // tạo tài khoản
    $page = 'Create';
    if (isset($_POST['submit'])) {
        $stmt = $pdo->prepare('INSERT INTO tai_khoan (email,password,ho,ten,dia_chi,quan_huyen,thanh_pho,sdt,quoc_gia,admin) VALUES (?,?,?,?,?,?,?,?,?,?)');
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt->execute([ $_POST['email'], $password, $_POST['ho'], $_POST['ten'], $_POST['dia_chi'], $_POST['quan_huyen'], $_POST['thanh_pho'], $_POST['sdt'], $_POST['quoc_gia'], $_POST['admin'] ]);
        header('Location: index.php?page=accounts');
        exit;
    }
}
// Danh sách các quốc gia có sẵn, bạn có thể xóa bất kỳ quốc gia nào khỏi mảng
$countries = array("Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");
?>

<?=template_admin_header($page . ' Account')?>

<h2><?=$page?> Account</h2>

<div class="content-block">

    <form action="" method="post" class="form responsive-width-100">

        <label for="email">Email</label>
        <input type="email" name="email" placeholder="Email" value="<?=$account['email']?>" required>

        <label for="password">Mật Khẩu</label>
        <input type="password" name="password" placeholder="Mật Khẩu" value="<?=$account['password']?>" required>

        <label for="ho">Họ</label>
        <input type="text" name="ho" placeholder="Trần" value="<?=$account['ho']?>">

        <label for="ten">Tên</label>
        <input type="text" name="ten" placeholder="Khánh" value="<?=$account['ten']?>">

        <label for="admin">Admin</label>
        <select name="admin" required>
            <option value="0"<?=$account['admin']==0?' selected':''?>>No</option>
            <option value="1"<?=$account['admin']==1?' selected':''?>>Yes</option>
        </select>
        <br>

        <label for="dia_chi">Tên đường</label>
        <input type="text" name="dia_chi" placeholder="1 Nguyễn Huệ" value="<?=$account['dia_chi']?>">

        <label for="quan_huyen">Quận, Huyện</label>
        <input type="text" name="quan_huyen" placeholder="Quận 1" value="<?=$account['quan_huyen']?>">

        <label for="thanh_pho">Tỉnh, Thành Phố</label>
        <input type="text" name="thanh_pho" placeholder="Thành phố Hồ Chí Minh" value="<?=$account['thanh_pho']?>">

        <label for="sdt">Số Điện Thoại</label>
        <input type="text" name="sdt" placeholder="+840709999xxx" value="<?=$account['sdt']?>">

        <label for="quoc_gia">Quốc gia</label>
        <select name="quoc_gia" required>
            <?php foreach($countries as $country): ?>
            <option value="<?=$country?>"<?=$country==$account['quoc_gia']?' selected':''?>><?=$country?></option>
            <?php endforeach; ?>
        </select>
        <br>

        <div class="submit-btns">
            <input type="submit" name="submit" value="Xác nhận">
            <?php if ($page == 'Edit'): ?>
            <input type="submit" name="delete" value="Xóa" class="delete">
            <?php endif; ?>
        </div>

    </form>

</div>

<?=template_admin_footer()?>
