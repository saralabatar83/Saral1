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
require_once 'config/db.php';

// 1. GET THE ID FROM URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    die("Error: No laptop selected. <a href='Laptops&computer.php'>Go back to shop.</a>");
}

try {
    // 2. FETCH THE PRODUCT
    $stmt = $pdo->prepare("
        SELECT laptop.*, brands.name as bname, sizes.size_name as sname 
        FROM laptop 
        LEFT JOIN brands ON laptop.brand_id = brands.id 
        LEFT JOIN sizes ON laptop.size_id = sizes.id 
        WHERE laptop.id = ?
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("Error: Product not found. <a href='Laptops&computer.php'>Go back.</a>");
    }

    // 3. SAFETY CHECK FOR GALLERY (Prevents the Line 63 Warning)
    $gallery_data = $product['gallery'] ?? '[]';
    $gallery = json_decode($gallery_data, true);
    if (!is_array($gallery)) {
        $gallery = []; // Set to empty array if decoding fails or is null
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['sku'] ?? 'Laptop Details') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: ; margin: 0; }
        .back-link { max-width: 1100px; margin: 0 auto 20px; }
        .back-link a { text-decoration: none; color: #838de7; font-weight: bold; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 30px; border-radius: 12px; display: flex; gap: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        
        /* Left Side: Images */
        .gallery-side { flex: 1; }
        .main-img-box { width: 100%; border: 1px solid #eee; border-radius: 8px; padding: 10px; text-align: center; }
        .main-img-box img { max-width: 100%; height: auto; max-height: 400px; object-fit: contain; }
        .thumbs { display: flex; gap: 10px; margin-top: 15px; flex-wrap: wrap; }
        .thumb { width: 70px; height: 70px; object-fit: cover; border: 1px solid #ddd; cursor: pointer; border-radius: 4px; transition: 0.2s; }
        .thumb:hover { border-color: #838de7; }

        /* Right Side: Details */
        .info-side { flex: 1.2; }
        .badge { background: #838de7; color: white; padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; margin-bottom: 10px; display: inline-block; text-transform: uppercase; }
        .h-title { font-size: 28px; margin: 10px 0; color: #333; }
        .model-ref { color: #888; font-size: 14px; margin-bottom: 20px; }
        .highlight-box { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 5px solid #838de7; }
        .wa-btn { background: #25D366; color: white; padding: 15px 25px; text-decoration: none; display: inline-flex; align-items: center; gap: 10px; border-radius: 8px; font-weight: bold; transition: 0.3s; }
        .wa-btn:hover { background: #1eb954; transform: translateY(-2px); }
        .specs-section { margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>

<div class="back-link">
    <a href="Laptops&computer.php"><i class="fa fa-arrow-left"></i> Back to Laptops</a>
</div>

<div class="container">
    <!-- LEFT: IMAGE GALLERY -->
    <div class="gallery-side">
        <div class="main-img-box">
            <?php 
                $main_img = !empty($product['image_path']) ? "admin/uploads/".$product['image_path'] : "https://via.placeholder.com/400x300?text=No+Image";
            ?>
            <img id="mainView" src="<?= $main_img ?>">
        </div>
        
        <div class="thumbs">
            <!-- Add Main Image to thumbs -->
            <img src="<?= $main_img ?>" class="thumb" onclick="document.getElementById('mainView').src=this.src">
            
            <!-- Loop through additional gallery images ONLY if they exist -->
            <?php if (!empty($gallery)): ?>
                <?php foreach($gallery as $img): ?>
                    <img src="admin/uploads/<?= htmlspecialchars($img) ?>" class="thumb" onclick="document.getElementById('mainView').src=this.src">
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- RIGHT: PRODUCT INFO -->
    <div class="info-side">

        
        <h1 class="h-title"><?= htmlspecialchars($product['sku'] ?? 'Unnamed Laptop') ?></h1>
        <p class="model-ref">Model Reference: <?= htmlspecialchars($product['slug'] ?? 'N/A') ?></p>
        
        <div class="highlight-box">
            <strong>Short Description:</strong><br>
            <?= nl2br(htmlspecialchars($product['short_description'] ?? 'No highlights available.')) ?>
        </div>

       
        <div class="specs-section">
            <h3>Full Specifications:</h3>
            <div style="line-height: 1.8; color: #555;">
                <?= $product['long_description'] ?: 'No specifications provided.' ?>
            </div>
            <?php 
            $wa_msg = urlencode("Hello, I am interested in the laptop: " . ($product['sku'] ?? 'Unit'));
        ?>
        <a href="https://wa.me/9767220473?text=<?= $wa_msg ?>" target="_blank" class="wa-btn">
            <i class="fab fa-whatsapp"></i> Inquiry on WhatsApp
        </a>
        </div>
    </div>
</div>


</body>
</html>
<?php
// 1. DATABASE CONNECTION
require_once 'config/db.php'; 

// 2. GET ACTIVE FILTERS FROM URL
$current_brand = isset($_GET['brand_id']) && $_GET['brand_id'] !== 'all' ? (int)$_GET['brand_id'] : 'all';
$current_cat   = isset($_GET['cat_id']) && $_GET['cat_id'] !== 'all' ? (int)$_GET['cat_id'] : 'all';

// 3. BUILD DYNAMIC QUERY FOR PRODUCTS
$sql = "SELECT l.*, b.name as bname, s.sub_name 
        FROM laptop l 
        LEFT JOIN brands b ON l.brand_id = b.id 
        LEFT JOIN laptop_subcategories s ON l.sub_id = s.id 
        WHERE 1=1"; 

$params = [];
if ($current_brand !== 'all') {
    $sql .= " AND l.brand_id = ?";
    $params[] = $current_brand;
}
if ($current_cat !== 'all') {
    $sql .= " AND l.sub_id = ?";
    $params[] = $current_cat;
}

$sql .= " ORDER BY l.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$laptops = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. FETCH FILTER LISTS
$brands = $pdo->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll();
$categories = $pdo->query("SELECT * FROM laptop_subcategories ORDER BY sub_name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laptops & Computers | Saral IT Solution</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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

<?php
// 1. DATABASE CONNECTION
require_once 'config/db.php'; 

// 2. GET ACTIVE FILTERS FROM URL
$current_brand = isset($_GET['brand_id']) && $_GET['brand_id'] !== 'all' ? (int)$_GET['brand_id'] : 'all';
$current_cat   = isset($_GET['cat_id']) && $_GET['cat_id'] !== 'all' ? (int)$_GET['cat_id'] : 'all';

// 3. BUILD DYNAMIC QUERY FOR PRODUCTS
$sql = "SELECT l.*, b.name as bname, s.sub_name 
        FROM laptop l 
        LEFT JOIN brands b ON l.brand_id = b.id 
        LEFT JOIN laptop_subcategories s ON l.sub_id = s.id 
        WHERE 1=1"; 

$params = [];
if ($current_brand !== 'all') {
    $sql .= " AND l.brand_id = ?";
    $params[] = $current_brand;
}
if ($current_cat !== 'all') {
    $sql .= " AND l.sub_id = ?";
    $params[] = $current_cat;
}

$sql .= " ORDER BY l.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$laptops = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. FETCH FILTER LISTS
$brands = $pdo->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll();
$categories = $pdo->query("SELECT * FROM laptop_subcategories ORDER BY sub_name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laptops & Computers | Saral IT Solution</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-purple: #838de7;
            --accent-yellow:white;
            --sale-red: #ff4757;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #f4f7f6; padding-top: px; }
        a { text-decoration: none; transition: 0.3s; }

        /* --- CONTENT WRAPPER --- */
        .wrapper { display: flex; max-width: 1300px; margin: 0 auto; gap: 25px; padding: 10px 10px; }

        /* --- SIDEBAR FILTERS --- */
        .sidebar { width: 180px; flex-shrink: 0; }
        .filter-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 20px; border: 1px solid #eee; }
        .filter-title { font-size: 13px; font-weight: bold; color: #888; text-transform: uppercase; margin-bottom: 15px; letter-spacing: 0.5px; }
        
        .filter-item { 
            display: block; padding: 10px 15px; color: #444; font-size: 14px; border-radius: 6px; margin-bottom: 4px; 
        }
        .filter-item:hover { background: #f0f2ff; color: var(--primary-purple); }
        .filter-item.active { 
            background-color: var(--primary-purple); color: #fff !important; font-weight: 500; 
        }

        /* --- MAIN PRODUCT GRID (Yellow Background) --- */
        .main-display { flex: 1; background-color: var(--accent-yellow); padding: 25px; border-radius: 12px; }
        .grid-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-left: 5px solid #000; padding-left: 15px; }

        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }
        .product-card { background: #fff; border-radius: 10px; padding: 15px; text-align: center; transition: 0.3s; border: 1px solid #eee; display: flex; flex-direction: column; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        
        .img-container { height: 160px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; }
        .img-container img { max-width: 100%; max-height: 100%; object-fit: contain; }

        .brand-info { font-size: 11px; font-weight: bold; color: var(--primary-purple); text-transform: uppercase; margin-bottom: 5px; }
        .product-title { font-size: 15px; font-weight: bold; height: 40px; overflow: hidden; margin-bottom: 10px; color: #333; line-height: 1.3; }
        .price { color: var(--sale-red); font-size: 18px; font-weight: 800; margin-bottom: 15px; }
        
        .btn-details { background: var(--primary-purple); color: white; padding: 10px; border-radius: 5px; font-weight: bold; font-size: 13px; margin-top: auto; }
        .btn-details:hover { background: #6c79e0; }

        @media (max-width: 992px) {
            .wrapper { flex-direction: column; }
            .sidebar { width: 100%; }
        }
    </style>
</head>
<body>

 

        <!-- PRODUCT GRID AREA -->
        <main class="main-display">
            <div class="grid-header">
                <h2 style="font-size: 20px;">Available Laptops</h2>
            </div>

            <div class="product-grid">
                <?php if(empty($laptops)): ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 50px;">
                        <img src="https://cdn-icons-png.flaticon.com/512/6134/6134065.png" width="100" style="opacity: 0.2;">
                        <p style="color: #666; margin-top: 15px;">No products found in this selection.</p>
                    </div>
                <?php endif; ?>

                <?php foreach($laptops as $p): ?>
                <div class="product-card">
                    <div class="img-container">
                        <img src="admin/uploads/<?= $p['image_path'] ?>" onerror="this.src='https://via.placeholder.com/200x150?text=No+Image'">
                    </div>
                    <div class="brand-info"><?= htmlspecialchars($p['bname'] ?? 'Generic') ?> | <?= htmlspecialchars($p['sub_name'] ?? 'Laptop') ?></div>
                    <h3 class="product-title"><?= htmlspecialchars($p['title']) ?></h3>
                    <div class="price">
                        <?php if($p['price']): ?>
                            Rs. <?= number_format((float)str_replace(',', '', $p['price'])) ?>
                        <?php else: ?>
                            Price on Call
                        <?php endif; ?>
                    </div>
                    <a href="product-details.php?id=<?= $p['id'] ?>" class="btn-details">VIEW SPECS</a>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
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