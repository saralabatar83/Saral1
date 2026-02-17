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
                <a href="login.php" class="prince-user-account">
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
 * SARAL IT SOLUTION - NEW RELEASES FRONTEND
 * Pulls all "ticked" items from Laptop, Printer, CCTV, Networking, Electronics, Accessories, and Services.
 */
require_once 'config/db.php'; 

try {
    // We use UNION to combine different tables. 
    // IMPORTANT: All SELECTs must have the same number of columns in the same order.
    
    $sql = "
    (SELECT id, title, price, image_path, short_description, 'Laptop' as product_type FROM laptop WHERE is_new_release = 1)
    UNION ALL
    (SELECT id, title, price, image_path, short_description, 'Printer' as product_type FROM printer WHERE is_new_release = 1)
    UNION ALL
    (SELECT id, name as title, price, image_path, short_description, 'CCTV' as product_type FROM cctv WHERE is_new_release = 1)
    UNION ALL
    (SELECT id, name as title, price, image_path, short_description, 'Networking' as product_type FROM networking WHERE is_new_release = 1)
    UNION ALL
    (SELECT id, title, price, image_path, short_desc as short_description, 'Electronic' as product_type FROM electronic WHERE is_new_release = 1)
    UNION ALL
    (SELECT id, name as title, price, image_path, short_description, 'Accessory' as product_type FROM accessories WHERE is_new_release = 1)
    UNION ALL
    (SELECT id, service_name as title, 0 as price, image_path, description as short_description, 'Service' as product_type FROM services WHERE is_new_release = 1)
    
    ORDER BY id DESC";

    $stmt = $pdo->query($sql);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $items = [];
    // echo $e->getMessage(); // Uncomment for debugging
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Releases | Saral IT Solution</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --primary: #838de7; --red: #ff4757; --bg: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; padding-bottom: 50px; }
        
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .header-section { text-align: center; margin-bottom: 40px; }
        .header-section h1 { font-size: 32px; color: #333; text-transform: uppercase; margin: 0; }
        .header-section span { color: var(--primary); }
        .header-section p { color: #666; margin-top: 10px; }

        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }

        .product-card {
            background: #fff; border-radius: 15px; overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s;
            border: 1px solid #eee; display: flex; flex-direction: column; position: relative;
        }
        .product-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }

        .badge {
            position: absolute; top: 15px; right: 15px; background: var(--red);
            color: white; padding: 4px 10px; font-size: 10px; font-weight: bold;
            border-radius: 20px; z-index: 10; text-transform: uppercase;
            box-shadow: 0 2px 5px rgba(255, 71, 87, 0.4);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse { 0% {transform: scale(1);} 50% {transform: scale(1.1);} 100% {transform: scale(1);} }

        .img-box { width: 100%; height: 220px; padding: 20px; display: flex; align-items: center; justify-content: center; background: #fff; box-sizing: border-box; border-bottom: 1px solid #f0f0f0; }
        .img-box img { max-width: 100%; max-height: 100%; object-fit: contain; transition: 0.3s; }
        .product-card:hover .img-box img { transform: scale(1.05); }

        .details { padding: 20px; flex-grow: 1; text-align: center; }
        .type-tag { font-size: 11px; color: var(--primary); font-weight: 800; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 5px; }
        .title { font-size: 18px; font-weight: 700; margin-bottom: 10px; color: #2c3e50; height: 50px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .price { font-size: 20px; color: #2ecc71; font-weight: 800; margin-bottom: 10px; }
        .price.service-price { color: #3498db; font-size: 16px; }

        .desc-text { font-size: 13px; color: #7f8c8d; margin-bottom: 15px; height: 40px; overflow: hidden; }

        .btn-whatsapp {
            background: #25D366; color: white; text-decoration: none;
            padding: 12px; font-weight: bold; display: flex;
            align-items: center; justify-content: center; gap: 8px; transition: 0.3s;
            border-top: 1px solid #eee;
        }
        .btn-whatsapp:hover { background: #1ebd5e; color: white; }
    </style>
</head>
<body>

    <!-- Optional: Include a Navbar here if you have one -->

    <div class="container">
        <div class="header-section">
            <h1>🔥 Latest <span>New Releases</span></h1>
            <p>Check out our freshly added products and services.</p>
        </div>

        <div class="product-grid">
            <?php if(!empty($items)): ?>
                <?php foreach($items as $row): ?>
                    <?php 
                        // Logic for Service Price vs Product Price
                        $is_service = ($row['product_type'] == 'Service');
                        // Image Path Fix: Assuming images are in 'admin/uploads/'
                        $img_src = !empty($row['image_path']) ? 'admin/uploads/' . $row['image_path'] : 'https://via.placeholder.com/300x200?text=No+Image';
                    ?>
                    
                    <div class="product-card">
                        <div class="badge"><i class="fas fa-fire"></i> NEW</div>
                        
                        <div class="img-box">
                            <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($row['title']) ?>">
                        </div>

                        <div class="details">
                            <span class="type-tag"><?= $row['product_type'] ?></span>
                            <div class="title"><?= htmlspecialchars($row['title']) ?></div>
                            
                            <?php if(!$is_service): ?>
                                <div class="price">Rs. <?= number_format((float)$row['price']) ?></div>
                            <?php else: ?>
                                <div class="price service-price">Service Inquiry</div>
                            <?php endif; ?>

                            <p class="desc-text">
                                <?= mb_strimwidth(htmlspecialchars(strip_tags($row['short_description'] ?? '')), 0, 60, "...") ?>
                            </p>
                        </div>

                        <a href="https://wa.me/9779800000000?text=Hi, I am interested in the New Release: <?= urlencode($row['title']) ?>" class="btn-whatsapp" target="_blank">
                            <i class="fab fa-whatsapp"></i> Buy / Inquire
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 60px; background: #fff; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    <i class="fas fa-box-open" style="font-size: 50px; color: #ddd; margin-bottom: 20px;"></i>
                    <h3 style="color: #888;">No New Releases Yet</h3>
                    <p style="color: #aaa;">Please go to the Admin Panel and tick "New Release" on some products.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

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
    <a href="" class="admin-btn" title="Back to Admin">
        <i class="fas fa-arrow-up"></i>
    </a>
</body>
</html>