<?php
// Kiểm tra xem biến shoppingcart đã được định nghĩa chưa
defined('shoppingcart') or exit;
// Truy vấn SQL để lấy 8 sản phẩm mới nhất
$stmt = $pdo->prepare('SELECT * FROM san_pham ORDER BY ngay_tao DESC LIMIT 8');
$stmt->execute();
$recently_added_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?=template_header('Home')?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Carousel -->
<div id="demo" class="carousel slide" data-bs-ride="carousel">

  <!-- Indicators/dots -->
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#demo" data-bs-slide-to="0" class="active"></button>
    <button type="button" data-bs-target="#demo" data-bs-slide-to="1"></button>
    <button type="button" data-bs-target="#demo" data-bs-slide-to="2"></button>
  </div>
  
  <!-- The slideshow/carousel -->
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img src="imgs/banner1.jpg" alt="Los Angeles" class="d-block" style="width:100%">
      <div class="carousel-caption">
      
      </div>
    </div>
    <div class="carousel-item active">
      <img src="imgs/banner2.jpg" alt="Los Angeles" class="d-block" style="width:100%">
      <div class="carousel-caption">
      
      </div>
    </div>
    <div class="carousel-item active">
      <img src="imgs/banner3.jpg" alt="Los Angeles" class="d-block" style="width:100%">
      <div class="carousel-caption">
      </div>
    </div>
    
  </div>
  
  <!-- Left and right controls/icons -->
  <button class="carousel-control-prev" type="button" data-bs-target="#demo" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#demo" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>


<br/>

<div class="recentlyadded content-wrapper">
    <h2>Sản Phẩm Nổi Bật</h2>
    <div class="products">
        <?php foreach ($recently_added_products as $product): ?>
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
    
</div>

<form method="POST" action="index.php?page=products">
<div class="buttons">
	<input type="submit" value="Xem Thêm Sản Phẩm >>">
</div>
</form>

<div>
    <img src="imgs/banner-bacsi.jpg" alt="" style="width: 100%;">
</div>
<div class="owl-carousel">
    <div class="item"><img src="imgs/7_Vitabella.jpg"></div>
    <div class="item"><img src="imgs/8_Loreal.jpg"></div>
    <div class="item"><img src="imgs/hinhanh_1_1.jpg"></div>
    <div class="item"><img src="imgs/Kudos_logo.jpg"></div>
    <div class="item"><img src="imgs/Life_Space_logo.jpg"></div>
    <div class="item"><img src="imgs/Nutrigen.png"></div>
    <div class="item"><img src="imgs/pmc.jpg"></div>
    <div class="item"><img src="imgs/Sa_Sam_viet.jpg"></div>
    <div class="item"><img src="imgs/Welson-logo.jpg"></div>
  </div>
  <script>
    $(document).ready(function(){
      $('.owl-carousel').owlCarousel({
        loop:true,
        margin:10,
        autoplay:true,
        autoplayTimeout:1000,
        autoplayHoverPause:true,
            responsive:{
                0:{
                    items:1
                },
                600:{
                    items:3
                },
                1000:{
                    items:5
                }
        }
      })
    });
  </script>

<div class="content content-wrapper">
	<br/>
	<div class="who">
	<h2>VỀ CHÚNG TÔI<br/>
	<p>
	Được thành lập vào năm 2012, Pharmacity là một trong những chuỗi bán lẻ dược phẩm đầu tiên tại Việt Nam. Đến nay, Pharmacity sở hữu mạng lưới hơn 1000 nhà thuốc đạt chuẩn GPP trên toàn quốc cùng đội ngũ hơn 3.500 dược sĩ đáng tin cậy, cung cấp các sản phẩm thuốc và sản phẩm chăm sóc sức khỏe hàng đầu với giá thành cạnh tranh nhất.
    Nhà thuốc Pharmacity luôn hướng đến mục tiêu nâng cao chất lượng chăm sóc sức khỏe cho từng khách hàng. Điều này, trước đây vốn chỉ nằm trong ý tưởng của ông Chris Blank – nhà sáng lập công ty, một dược sỹ người Mỹ làm việc nhiều năm tại Việt Nam. Với niềm đam mê và sự sáng tạo của mình, ông Chris Blank đã thành lập nên Pharmacity và mang đến những trải nghiệm tốt nhất cho khách hàng.
    Hiện nay Pharmacity đã có hệ thống nhà thuốc rải khắp các quận huyện tại TP.HCM và nhiều tỉnh, thành phố lớn như Hà Nội, Đà Nẵng, Cần Thơ, Thừa Thiên Huế, Bà Rịa – Vũng Tàu, Bình Dương, Long An, Đồng Nai, Tiền Giang…
    Tới năm 2025, Pharmacity sẽ tiếp tục mở rộng hệ thống lên đến 5.000 nhà thuốc đạt chuẩn GPP trên khắp cả nước với hơn 35.000 dược sĩ đáng tin cậy, hướng đến mục tiêu trở thành nhà thuốc bán lẻ hiện đại và mang đến trải nghiệm tối ưu cho khách hàng.
	</p>
	</h2>
	</div>
	
	</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?=template_footer()?>
