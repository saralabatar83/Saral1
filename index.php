<?php
require_once 'config/db.php'; 

// Fetch Branding Data
$branding = $pdo->query("SELECT * FROM site_branding WHERE id = 1")->fetch();
$top_items = $pdo->query("SELECT * FROM header_top_bar ORDER BY sort_order ASC")->fetchAll();
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saral IT Solution</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="prince.css">
</head>
<body>

<header class="prince-sticky-header">
    <!-- 1. PRINCE TOP BAR (Lavender) -->
  <!-- 1. PRINCE TOP BAR (Lavender) -->
<div class="prince-top-bar">
    <div class="prince-container">
        <div class="prince-top-content">
            <?php foreach ($top_items as $item): ?>
                <!-- Added the dynamic link here -->
                <a href="<?= htmlspecialchars($item['link']) ?>" class="prince-top-item-wrapper">
                    <div class="prince-top-item">
                        <i class="fas <?= htmlspecialchars($item['icon']) ?>"></i>
                        <span><?= htmlspecialchars($item['text_label']) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

    <!-- 2. PRINCE MAIN HEADER (White) -->
     <div class="prince-main-header">
        <div class="prince-container">
            <div class="prince-header-grid">
                
                <!-- Logo & Brand -->
                <a href="index.php" class="prince-logo-area">
                    <?php if (!empty($branding['logo'])): ?>
                        <img src="uploads/<?= htmlspecialchars($branding['logo']) ?>" alt="Logo" class="prince-circle-logo">
                    <?php endif; ?>
                    <span class="prince-brand-name">
                        <?= htmlspecialchars($branding['brand_name'] ?? 'Prince Brand') ?>
                    </span>
                </a>

                <!-- Search Bar -->
                <form action="search.php" method="GET" class="prince-search-box">
                    <input type="text" name="q" placeholder="Search for products..." required>
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>

                <!-- User Icon -->
                <a href="register.php" class="prince-user-account">
                    <i class="fas fa-user"></i>
                    <span>ACCOUNT</span>
                </a>

            </div>
        </div>
    </div>

    <!-- 3. PRINCE NAVIGATION (Slate Blue) -->
    <nav class="prince-nav-bar">
        <div class="prince-container">
            <ul class="prince-nav-links">
                <li><a href="index.php" class="prince-link">HOME</a></li>
                <li><a href="Laptops.php" class="prince-link">LAPTOP & COMPUTER</a></li>
                <li><a href="printers.php" class="prince-link">PRINTER</a></li>
                <li><a href="Cctv.php" class="prince-link">CCTV</a></li>
                <li><a href="Electronic.php" class="prince-link">ELECTRONIC</a></li>
                <li><a href="Accessories.php" class="prince-link">ACCESSORIES</a></li>
                <li><a href="Networking.php" class="prince-link">NETWORKING</a></li>
                <li><a href="Services.php" class="prince-link">SERVICE</a></li>
                <li><a href="newrelease.php" class="prince-link prince-btn-release">NEW RELEASES</a></li>
            </ul>
        </div>
    </nav>
</header>
<?php
/**
 * SARAL IT SOLUTION - CLEAN VERSION
 */
require_once 'config/db.php';

// Get current page name
$currentPage = basename($_SERVER['PHP_SELF']);

try {
    // 1. Header & Top Bar
    $stmtTop = $pdo->query("SELECT * FROM header_settings WHERE section IN ('left', 'center', 'top_bar') ORDER BY sort_order ASC");
    $topItems = $stmtTop->fetchAll(PDO::FETCH_ASSOC);

    // 2. Hero Slider
    $sliderStmt = $pdo->query("SELECT file_name FROM slider_images ORDER BY id DESC");
    $slides = $sliderStmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Service Categories (Sidebar)
    $categories = $pdo->query("SELECT * FROM service_cats ORDER BY name ASC")->fetchAll();

    // 4. Services Filter logic
    $cat_id = isset($_GET['cat']) && is_numeric($_GET['cat']) ? (int)$_GET['cat'] : null;
    $query = "SELECT s.*, c.name as cname FROM top_services s LEFT JOIN service_cats c ON s.cat_id = c.id";
    if ($cat_id) { $query .= " WHERE s.cat_id = :cat_id"; }
    $query .= " ORDER BY s.is_new_release DESC, s.id DESC";
    $stmt = $pdo->prepare($query);
    if ($cat_id) { $stmt->execute(['cat_id' => $cat_id]); } else { $stmt->execute(); }
    $services = $stmt->fetchAll();

    // 5. Brands
    $brands = $pdo->query("SELECT * FROM top_brands")->fetchAll(PDO::FETCH_ASSOC);
    $loopBrands = array_merge($brands, $brands); 

    // 6. Footer & Social
    $stmt_social = $pdo->query("SELECT * FROM social_links WHERE link_url != '#' AND link_url != ''");
    $social_links = $stmt_social->fetchAll(PDO::FETCH_ASSOC);
    $stmt_settings = $pdo->prepare("SELECT setting_key, setting_value FROM site_settings");
    $stmt_settings->execute();
    $settings = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
    $office_img = $settings['office_image'] ?? '';
    $office_link = $settings['office_image_link'] ?? '#';

    $stmt_links = $pdo->prepare("SELECT * FROM footer_links ORDER BY column_section ASC"); 
    $stmt_links->execute();
    $all_links = $stmt_links->fetchAll(PDO::FETCH_ASSOC);
    $link_columns = [];
    foreach ($all_links as $link) { $link_columns[$link['column_section']][] = $link; }

} catch (PDOException $e) {
    die("Database Connection Error.");
}

$whatsapp_number = "9779800000000";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saral IT Solution | Home</title>
    
    <!-- External CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link rel="stylesheet" href="style1.css">
    <script src="script.js"></script>
</head>
<body>

<!-- HERO SECTION -->
<section class="hero-section">
    <div class="hero-card">
        <h1>SARAL IT SOLUTION</h1>
        <p>Innovative • Reliable • Professional</p>
        <a href="fillup.php" class="hero-btn-link">Services Form</a>
    </div>

    <div class="swiper heroSwiper">
        <div class="swiper-wrapper">
            <?php foreach($slides as $srow): ?>
                <div class="swiper-slide">
                    <img src="images/<?= $srow['file_name'] ?>" alt="Slide Image">
                </div>
            <?php endforeach; ?>
        </div>
      <style>
  .hero-arrow {
    --swiper-navigation-size: 22px; /* Adjust this for "small" feel */
    color: Black!important;
  }
</style>

<div class="swiper-button-next hero-arrow"></div>
<div class="swiper-button-prev hero-arrow"></div>
<div class="swiper-pagination"></div>
    </div>
</section>

<!-- SHOP CATEGORIES MARQUEE -->
<section class="shop-cat-section">
    <div class="cat-header"><h2>Our products</h2></div>
    <div class="swiper catSwiper">
        <div class="swiper-wrapper">
            <?php
            $stmt_cat = $pdo->query("SELECT * FROM shop_categories ORDER BY id DESC");
            while($cat = $stmt_cat->fetch()):
            ?>
            <div class="swiper-slide">
                <a href="<?= htmlspecialchars($cat['target_link']) ?>" class="cat-card" style="background-color: <?= $cat['bg_color'] ?>;">
                    <span class="cat-name"><?= htmlspecialchars($cat['name']) ?></span>
                    <div class="cat-img-box">
                        <img src="admin/uploads/<?= $cat['image_path'] ?>" alt="<?= htmlspecialchars($cat['name']) ?>">
                    </div>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
        <div class="swiper-pagination" style="position: relative; margin-top: 30px;"></div>
    </div>

</section>



<!-- OFFERS SECTIONS -->
<div class="section-wrap">
    <?php
    $offer_tables = ["offers_sec2" => "OUR SERVICES", "offers_sec3" => "LATEST PRODUCT", "offers_sec4" => "SPECIAL OFFERS"];
    foreach ($offer_tables as $table => $title):
        $stmt_off = $pdo->prepare("SELECT * FROM $table ORDER BY id DESC LIMIT 12");
        $stmt_off->execute();
        $rows = $stmt_off->fetchAll();
        if(count($rows) > 0): ?>
            <h2 class='section-title'><?= $title ?></h2>
            <div class="swiper offerSwiper">
                <div class="swiper-wrapper">
                    <?php foreach ($rows as $row): ?>
                        <div class="swiper-slide">
                            <a href="detailoffer.php?table=<?= $table ?>&id=<?= $row['id'] ?>" class="card">
                                <img src="admin/uploads/<?= htmlspecialchars($row['image_path']) ?>">
                                <h4><?= htmlspecialchars($row['title']) ?></h4>
                                <p><?= htmlspecialchars($row['short_desc']) ?></p>
                                
                                <div class="btn-more">View Details</div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<!-- DYNAMIC BANNER -->

<!-- TOP BRANDS -->
<div class="brand-section1">
    <h2>Top Brands</h2>
    <div class="slider-viewport1" id="viewport1">
        <div class="slider-track1" id="track1">
            <?php foreach($loopBrands as $b): ?>
                <div class="brand-card1">
                    <img src="admin/uploads/<?= $b['logo_path'] ?>" alt="Logo">
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>



<a href="index.php" class="admin-btn"><i class="fas fa-arrow-up"></i></a>

<!-- External JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>


</body>
</html>
<?php
// 1. DATABASE CONNECTION
include_once 'db.php'; 

// 2. INITIALIZE VARIABLES (Prevents "Undefined variable" warnings)
$office_img = '';
$office_link = '#';
$link_columns = [];
$social_links = [];

try {
    // FETCH SOCIAL LINKS
    $stmt_social = $pdo->query("SELECT * FROM social_links WHERE link_url != '#' AND link_url != ''");
    $social_links = $stmt_social->fetchAll(PDO::FETCH_ASSOC);

    // FETCH OFFICE SETTINGS
    $stmt_settings = $pdo->prepare("SELECT setting_key, setting_value FROM site_settings");
    $stmt_settings->execute();
    $settings = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
    
    if (isset($settings['office_image'])) $office_img = $settings['office_image'];
    if (isset($settings['office_image_link'])) $office_link = $settings['office_image_link'];

    // FETCH FOOTER LINKS
    $stmt_links = $pdo->prepare("SELECT * FROM footer_links ORDER BY column_section ASC"); 
    $stmt_links->execute();
    $all_links = $stmt_links->fetchAll(PDO::FETCH_ASSOC);

    foreach ($all_links as $link) {
        $link_columns[$link['column_section']][] = $link;
    }
} catch (Exception $e) {
    // Error handling (optional: error_log($e->getMessage());)
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Site</title>
    <!-- CSS Link -->
    <link rel="stylesheet" href="footer12.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <!-- DYNAMIC SOCIAL BAR -->
    <div class="sticky-social-bar">
        <?php foreach ($social_links as $row): ?>
            <a href="<?php echo htmlspecialchars($row['link_url']); ?>" 
               class="s-<?php echo strtolower($row['platform_name']); ?>" 
               target="_blank">
                <i class="fa-brands <?php echo htmlspecialchars($row['icon_class']); ?>"></i>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- MAIN FOOTER -->
    <footer class="site-footer">
        <div class="footer-content">
            
            <!-- Link Columns -->
            <?php foreach ($link_columns as $section_title => $links): ?>
                <div class="footer-col">
                    <h3><?php echo htmlspecialchars($section_title); ?></h3>
                    <ul>
                        <?php foreach ($links as $link): ?>
                            <li><a href="<?php echo htmlspecialchars($link['link_url']); ?>">
                                <?php echo htmlspecialchars($link['link_text']); ?>
                            </a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>

            <!-- Office Column -->
            <div class="footer-col office-col">
                <h3>OUR OFFICE</h3>
                <?php if (!empty($office_img) && file_exists($office_img)): ?>
                    <a href="<?php echo htmlspecialchars($office_link); ?>">
                        <img src="<?php echo htmlspecialchars($office_img); ?>" alt="Office Logo" class="office-logo">
                    </a>
<div class="creator">
    Code by <span class="heart">❤</span> 
    <a href="https://prince15539.github.io/Ram-Abtar2625/" class="credit">
        Ram-Abtar
    </a>
</div>


                <?php else: ?>
                    <p style="color:#777; font-size:13px;">Image not found.</p>
                <?php endif; ?>
            </div>

        </div>
    


    <!-- ADMIN / UP ARROW BUTTON -->
    <a href="index.php" class="admin-btn" title="Back to Admin">
        <i class="fas fa-arrow-up"></i>
  
    </a>

</body>
</html>

