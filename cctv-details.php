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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id === 0) { header("Location: CCTV.php"); exit(); }

try {
    // Fetch product details
    $stmt = $pdo->prepare("SELECT c.*, b.name as bname, s.name as sname, sz.size_name 
                           FROM cctv c 
                           LEFT JOIN cctv_brands b ON c.brand_id = b.id 
                           LEFT JOIN cctv_subcategories s ON c.sub_id = s.id 
                           LEFT JOIN cctv_sizes sz ON c.size_id = sz.id
                           WHERE c.id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$p) { die("Product not found. <a href='CCTV.php'>Return to Shop</a>"); }
} catch (PDOException $e) { die("Database Error"); }

// WhatsApp Message
$wa_msg = urlencode("Hello, I am interested in the " . $p['bname'] . " " . $p['name'] . " (" . $p['size_name'] . "). Please provide price.");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($p['name']) ?> - Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- CUSTOM CSS (NO BOOTSTRAP) --- */
        :root { --primary: #0d6efd; --danger: #e11d48; --dark: #1e293b; --bg: #fdfdfd; }
        
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
       
        a { text-decoration: none; color: inherit; }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

        /* BREADCRUMB */
        .breadcrumb { margin-bottom: 30px; font-size: 14px; color: #666; }
        .breadcrumb a { color: var(--primary); font-weight: 600; }
        .breadcrumb span { margin: 0 8px; color: #ccc; }

        /* MAIN PRODUCT LAYOUT */
        .product-view { display: flex; flex-wrap: wrap; gap: 50px; margin-bottom: 60px; }
        
        /* Left Side: Image */
        .product-image-box { 
            flex: 1; 
            min-width: 350px; 
            background: #fff; 
            border: 1px solid #eee; 
            border-radius: 20px; 
            padding: 40px; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            height: 500px; 
            position: sticky; top: 20px; 
        }
        .product-image-box img { max-width: 100%; max-height: 100%; object-fit: contain; }

        /* Right Side: Info */
        .product-info { flex: 1; min-width: 350px; padding-top: 10px; display: flex; flex-direction: column; }

        /* --- FIXED BADGES (Renamed to .detail-tag to fix floating issue) --- */
        .tags-row { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .detail-tag { 
            padding: 6px 15px; 
            border-radius: 50px; 
            font-size: 12px; 
            font-weight: 700; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
            position: static; /* Ensures it stays in place */
        }
        .b-brand { background: #f5f3ff; color: #6d28d9; border: 1px solid #ddd6fe; }
        .b-type { background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; }
        .b-res { background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; }

        h1 { font-size: 32px; font-weight: 800; margin-bottom: 15px; color: #111; }
        
        .short-desc { font-size: 16px; color: #64748b; margin-bottom: 20px; padding-left: 15px; border-left: 4px solid #e2e8f0; }
        
        .price-tag { font-size: 42px; font-weight: 800; color: var(--danger); margin-bottom: 25px; }

        /* --- SPECS BOX (Above WhatsApp) --- */
        .specs-box { 
            background: #f8f9fa; 
            border: 1px solid #e2e8f0; 
            border-radius: 12px; 
            padding: 20px; 
            margin-bottom: 25px; 
            font-size: 14px;
        }
        .specs-header { 
            display: flex; 
            align-items: center; 
            margin-bottom: 15px; 
            border-bottom: 1px solid #eee; 
            padding-bottom: 10px; 
        }
        .icon-box { 
            background: var(--primary); 
            color: white; 
            width: 30px; 
            height: 30px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            border-radius: 6px; 
            margin-right: 10px; 
            font-size: 14px;
        }

        /* Action Buttons */
        .actions { display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 25px; }
        .btn { padding: 15px 30px; border-radius: 10px; font-weight: 700; font-size: 16px; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; justify-content: center; }
        
        .btn-wa { background: #25D366; color: white; border: none; flex: 2; min-width: 200px; }
        .btn-wa:hover { background: #128c7e; box-shadow: 0 5px 15px rgba(37, 211, 102, 0.3); }
        
        .btn-shop { background: transparent; color: #333; border: 2px solid #333; flex: 1; min-width: 140px; }
        .btn-shop:hover { background: #333; color: white; }

        @media (max-width: 768px) {
            .product-view { flex-direction: column; }
            .product-image-box { height: 300px; position: static; }
            h1 { font-size: 26px; }
            .actions { flex-direction: column; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="CCTV.php">CCTV Shop</a> <span>/</span> <?= htmlspecialchars($p['name']) ?>
    </div>

    <!-- Main Flex Layout -->
    <div class="product-view">
        
        <!-- Left Column: Image -->
        <div class="product-image-box">
            <img src="admin/uploads/<?= $p['image_path'] ?>" alt="<?= htmlspecialchars($p['name']) ?>">
        </div>

        <!-- Right Column: Info -->
        <div class="product-info">
            
            <!-- Tags/Badges (Fixed class name) -->
            <div class="tags-row">
                <span class="detail-tag b-brand"><?= $p['bname'] ?></span>
                <span class="detail-tag b-type"><?= $p['sname'] ?></span>
                <span class="detail-tag b-res"><i class="fa fa-expand"></i> <?= $p['size_name'] ?></span>
            </div>
            
            <h1><?= htmlspecialchars($p['name']) ?></h1>
            
            <div class="short-desc">
                <?= nl2br(htmlspecialchars($p['short_description'])) ?>
            </div>
         

            <!-- TECHNICAL SPECIFICATIONS (Placed above WhatsApp) -->
            <div class="specs-box">
                <div class="specs-header">
                    <div class="icon-box"><i class="fa fa-list-ul"></i></div>
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700;">Technical Specifications</h3>
                </div>
                <div style="line-height: 1.6; color: #475569;">
                    <?= $p['full_description'] ?>
                </div>
            </div>

            <!-- Buttons -->
            <div class="actions">
                <a href="https://wa.me/9744212267?text=<?= $wa_msg ?>" target="_blank" class="btn btn-wa">
                    <i class="fab fa-whatsapp" style="margin-right: 10px; font-size: 20px;"></i> Order on WhatsApp
                </a>
                
         
                 
            </div>

            <div style="font-size: 13px; color: #dc2626; font-weight: bold; background: #fff5f5; padding: 10px; border-radius: 8px; align-self: flex-start;">
                <i class="fa fa-truck"></i> Fast Delivery Available across Nepal
            </div>
        </div>
    </div>
</div>

</body>

<?php
/**
 * SARAL IT SOLUTION - CCTV PAGE
 * MATCHES THE MANAGED DESIGN (ROUNDED 20PX, PURPLE THEME)
 */
require_once 'config/db.php';

try {
    // 1. GET ACTIVE FILTERS FROM URL (Keeps all 3 filters active at once)
    $current_brand = isset($_GET['brand_id']) && $_GET['brand_id'] !== 'all' ? (int)$_GET['brand_id'] : 'all';
    $current_sub   = isset($_GET['sub_id']) && $_GET['sub_id'] !== 'all' ? (int)$_GET['sub_id'] : 'all'; 
    $current_size  = isset($_GET['size_id']) && $_GET['size_id'] !== 'all' ? (int)$_GET['size_id'] : 'all';

    // 2. FETCH FILTER LISTS
    $brands_list = $pdo->query("SELECT * FROM cctv_brands ORDER BY name ASC")->fetchAll();
    $subs_list   = $pdo->query("SELECT * FROM cctv_subcategories ORDER BY name ASC")->fetchAll();
    $sizes_list  = $pdo->query("SELECT * FROM cctv_sizes ORDER BY size_name ASC")->fetchAll();

    // 3. BUILD DYNAMIC QUERY
    $sql = "SELECT c.*, b.name as bname, s.name as sname, sz.size_name 
            FROM cctv c 
            LEFT JOIN cctv_brands b ON c.brand_id = b.id 
            LEFT JOIN cctv_subcategories s ON c.sub_id = s.id 
            LEFT JOIN cctv_sizes sz ON c.size_id = sz.id 
            WHERE 1=1";

    $params = [];
    if ($current_brand !== 'all') { $sql .= " AND c.brand_id = ?"; $params[] = $current_brand; }
    if ($current_sub   !== 'all') { $sql .= " AND c.sub_id = ?"; $params[] = $current_sub; }
    if ($current_size  !== 'all') { $sql .= " AND c.size_id = ?"; $params[] = $current_size; }

    $stmt = $pdo->prepare($sql . " ORDER BY c.id DESC");
    $stmt->execute($params);
    $cameras = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCTV Solutions | Saral IT Solution</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
     <style>
        :root {
            --primary-purple: #838de7;
            --accent-yellow:white;
            --sale-red: #ff4757;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
   

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



        <!-- MAIN CONTENT AREA -->
        <main class="main-display">
            <!-- Header Section -->
            <div class="grid-header">
                <h2>CCTV Security Solutions</h2>
                <p class="results-count">Showing <?= count($cameras) ?> products</p>
            </div>

            <!-- Product Grid -->
            <div class="product-grid">
                <?php if(empty($cameras)): ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 100px; background: white; border-radius: 20px;">
                        <i class="fa fa-video-slash" style="font-size: 50px; color: #eee; margin-bottom: 20px;"></i>
                        <p style="color: #999;">No cameras found matching your criteria.</p>
                        <a href="CCTV.php" style="color: var(--primary-purple); font-weight: bold;">Show All Products</a>
                    </div>
                <?php else: ?>
                    <?php foreach($cameras as $c): ?>
                    <div class="product-card">
                        <div class="img-container">
                            <?php 
                                // Fixes the system path error (basename removes C:\xampp...)
                                $img_file = basename($c['image_path']);
                                $img_url = (!empty($img_file)) ? "admin/uploads/".$img_file : "https://via.placeholder.com/300";
                            ?>
                            <img src="<?= $img_url ?>" onerror="this.src='https://via.placeholder.com/300'" alt="CCTV Camera">
                        </div>
                        
                        <div class="brand-tag">
                            <?= htmlspecialchars($c['bname'] ?? 'Brand') ?> • <?= htmlspecialchars($c['sname'] ?? 'CCTV') ?>
                        </div>
                        
                        <h3 class="product-title"><?= htmlspecialchars($c['name']) ?></h3>
                        
                        <div class="price">
                            <?php if($c['price'] > 0): ?>
                                Rs. <?= number_format((float)str_replace(',', '', $c['price'])) ?>
                            <?php else: ?>
                                <span style="color:#bbb; font-size:16px;">Price on Call</span>
                            <?php endif; ?>
                        </div>

                        <a href="cctv-details.php?id=<?= $c['id'] ?>" class="btn-details">VIEW SPECS</a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

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