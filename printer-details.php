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

// 1. Get Product ID
$id = $_GET['id'] ?? null;
if (!$id) { header("Location: view_printers.php"); exit(); }

// 2. Fetch Printer Data (Including price)
try {
    $stmt = $pdo->prepare("SELECT p.*, b.brand_name, s.sub_name 
                           FROM printer p 
                           LEFT JOIN printer_brands b ON p.brand_id = b.id 
                           LEFT JOIN printer_subcategories s ON p.sub_id = s.id 
                           WHERE p.id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$p) { die("Error: Product not found."); }

} catch (PDOException $e) { die("Database Error: " . $e->getMessage()); }

// WhatsApp Message (Updated to include price if available)
$price_text = (!empty($p['price'])) ? " with price Rs. " . $p['price'] : "";
$wa_msg = urlencode("Hello, I am interested in the " . $p['brand_name'] . " " . $p['title'] . $price_text . ". Please share details.");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($p['title']) ?> - Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            background: #f4f7f6; 
            font-family: 'Segoe UI', Arial, sans-serif; 
            color: #333; 
         
        }

        .main-card {
            max-width: 1100px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            display: flex;
            flex-wrap: wrap;
            padding: 30px;
            gap: 30px;
        }

        /* Left Column: Image Area */
        .left-column { flex: 1; min-width: 350px; }
        .image-container {
            border: 1px solid #f0f0f0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        .main-product-img {
            width: 100%;
            height: 350px;
            object-fit: contain;
        }

        /* Right Column: Details Area */
        .right-column { flex: 1.5; min-width: 350px; }

        .brand-tag {
            font-size: 13px;
            color: #fd0d0d;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
            display: block;
        }

        .product-title {
            font-size: 32px;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 10px;
        }

        /* Price Styling */
        .price-tag {
            font-size: 24px;
            color: #9fa6a2;
            font-weight: 700;
            margin-bottom: 20px;
            display: block;
        }
        .price-tag span { font-size: 16px; color: #888; font-weight: 400; }

        .model-ref { font-size: 14px; color: #999; margin-bottom: 25px; }

        /* The Box for Short Description (Same as your Image) */
        .short-desc-box {
            background-color: #f8fafd;
            border-left: 5px solid #818cf8;
            padding: 20px 25px;
            border-radius: 6px;
            margin-bottom: 30px;
        }
        .short-desc-box h4 { font-size: 17px; margin-bottom: 8px; font-weight: 700; }

        .divider { height: 1px; background: #eee; margin-bottom: 25px; }

        .specs-header { font-size: 18px; font-weight: 700; margin-bottom: 15px; }
        .specs-body { color: #555; margin-bottom: 30px; }

        /* WhatsApp Button */
        .btn-whatsapp {
            background-color: #25D366;
            color: white;
            padding: 14px 30px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-weight: 600;
            gap: 10px;
            transition: 0.3s;
        }
        .btn-whatsapp:hover { opacity: 0.9; transform: translateY(-2px); }

        @media (max-width: 850px) {
            .main-card { flex-direction: column; padding: 20px; }
            .left-column, .right-column { min-width: 100%; }
        }
    </style>
</head>
<body>

<div class="main-card">
    <!-- Image Section -->
    <div class="left-column">
        <div class="image-container">
            <img src="admin/uploads/<?= htmlspecialchars($p['image_path']) ?>" class="main-product-img" alt="Printer">
        </div>
    </div>

    <!-- Content Section -->
    <div class="right-column">
        <span class="brand-tag"><?= htmlspecialchars($p['brand_name']) ?></span>
        <h1 class="product-title"><?= htmlspecialchars($p['title']) ?></h1>
        
        <!-- PRICE DISPLAY -->
        <?php if(!empty($p['price'])): ?>
            <div class="price-tag">
                <span>Price:</span> Rs. <?= htmlspecialchars($p['price']) ?>
            </div>
        <?php endif; ?>

        <p class="model-ref">Category: <?= htmlspecialchars($p['sub_name']) ?></p>

        <!-- Managed Box Style -->
        <div class="short-desc-box">
            <h4>Short Description:</h4>
            <div class="short-desc-text">
                <?= nl2br(htmlspecialchars($p['short_description'])) ?>
            </div>
        </div>

        <div class="divider"></div>

        <div class="specs-section">
            <h4 class="specs-header">Full Specifications:</h4>
            <div class="specs-body">
                <?php if (!empty($p['long_description'])): ?>
                    <?= $p['long_description']; ?>
                <?php else: ?>
                    <p>No specifications provided.</p>
                <?php endif; ?>
            </div>
        </div>

        <a href="https://wa.me/9800000000?text=<?= $wa_msg ?>" target="_blank" class="btn-whatsapp">
            <i class="fab fa-whatsapp"></i> Inquiry on WhatsApp
        </a>
    </div>
</div>

</body>
<?php
// 1. DATABASE CONNECTION
require_once 'config/db.php'; 

try {
    // 2. GET ACTIVE FILTERS FROM URL
    // Maintains both filters simultaneously so users can filter "Epson" + "Ink Tank"
    $curr_brand = isset($_GET['brand_id']) && $_GET['brand_id'] !== 'all' ? (int)$_GET['brand_id'] : 'all';
    $curr_cat   = isset($_GET['sub_id']) && $_GET['sub_id'] !== 'all' ? (int)$_GET['sub_id'] : 'all';

    // 3. BUILD DYNAMIC QUERY
    $sql = "SELECT p.*, b.brand_name as bname, s.sub_name as sname 
            FROM printer p 
            LEFT JOIN printer_brands b ON p.brand_id = b.id 
            LEFT JOIN printer_subcategories s ON p.sub_id = s.id 
            WHERE 1=1"; 

    $params = [];
    if ($curr_brand !== 'all') {
        $sql .= " AND p.brand_id = ?";
        $params[] = $curr_brand;
    }
    if ($curr_cat !== 'all') {
        $sql .= " AND p.sub_id = ?";
        $params[] = $curr_cat;
    }

    $sql .= " ORDER BY p.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $printers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. FETCH FILTER LISTS FOR SIDEBAR
    $brands_list = $pdo->query("SELECT * FROM printer_brands ORDER BY brand_name ASC")->fetchAll();
    $subs_list   = $pdo->query("SELECT * FROM printer_subcategories ORDER BY sub_name ASC")->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printers & Scanners | Saral IT Solution</title>
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
   <style>
        :root {
            --primary-purple: #838de7;
            --accent-yellow:white;
            --sale-red: #ff4757;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
      
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

    <!-- STICKY SOCIALS -->


        <!-- MAIN CONTENT -->
        <main class="main-display">
            <div class="grid-header">
                <h2>Printers & Scanners</h2>
                <p class="results-count">Showing <?= count($printers) ?> high-quality products</p>
            </div>

            <div class="product-grid">
                <?php if(empty($printers)): ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 100px; background: white; border-radius: 20px;">
                        <i class="fa fa-print" style="font-size: 50px; color: #eee; margin-bottom: 20px;"></i>
                        <p style="color: #999;">No printers found matching these filters.</p>
                        <a href="printers.php" style="color: var(--primary-purple); font-weight: bold;">Browse All Printers</a>
                    </div>
                <?php else: ?>
                    <?php foreach($printers as $p): ?>
                    <div class="product-card">
                        <div class="img-container">
                            <?php 
                                $img_file = basename($p['image_path']);
                                $img_url = (!empty($img_file)) ? "admin/uploads/".$img_file : "https://via.placeholder.com/300";
                            ?>
                            <img src="<?= $img_url ?>" onerror="this.src='https://via.placeholder.com/300'" alt="Printer">
                        </div>
                        
                        <div class="brand-tag">
                            <?= htmlspecialchars($p['bname'] ?? 'Brand') ?> • <?= htmlspecialchars($p['sname'] ?? 'Printer') ?>
                        </div>
                        
                        <h3 class="product-title"><?= htmlspecialchars($p['title']) ?></h3>
                        
                        <div class="price">
                            <?php if(!empty($p['price']) && $p['price'] > 0): ?>
                                Rs. <?= number_format((float)str_replace(',', '', $p['price'])) ?>
                            <?php else: ?>
                                <span style="color:#bbb; font-size:16px;">Price on Call</span>
                            <?php endif; ?>
                        </div>

                        <a href="printer-details.php?id=<?= $p['id'] ?>" class="btn-details">VIEW DETAILS</a>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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