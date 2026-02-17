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
 * SARAL IT SOLUTION - NETWORKING DETAILS (Standalone)
 */
require_once 'config/db.php';

// 1. Get Product ID safely
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Redirect if no ID provided
if ($id === 0) { 
    header("Location: Networking.php"); 
    exit(); 
}

try {
    // 2. Fetch Product Details
    $stmt = $pdo->prepare("SELECT n.*, b.name as bname, c.name as cname 
                           FROM networking n 
                           LEFT JOIN networking_brands b ON n.brand_id = b.id 
                           LEFT JOIN networking_categories c ON n.category_id = c.id 
                           WHERE n.id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$p) { die("Product not found!"); }

} catch (PDOException $e) { 
    die("Database Error"); 
}

// 3. Prepare WhatsApp Message
$wa_msg = urlencode("Hello, I am interested in: " . $p['name'] . ". Is it available?");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title><?= htmlspecialchars($p['meta_title'] ?: $p['name']) ?></title>
    <meta name="description" content="<?= htmlspecialchars($p['meta_description'] ?: $p['short_description']) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($p['meta_keywords']) ?>">

    <!-- FontAwesome (Kept for icons only) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- CSS VARIABLES & RESET --- */
        :root {
            --primary: #838de7;
            --dark: #2c3e50;
            --light-bg: #f8fafc;
            --green: #27ae60;
            --border: #e2e8f0;
            --text-muted: #64748b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body { 
            background-color: var(--light-bg); 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            color: var(--dark);
            line-height: 1.6;
            padding-bottom: 50px;
        }

        a { text-decoration: none; color: inherit; }

        /* --- LAYOUT --- */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* --- BACK BUTTON --- */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            font-weight: 700;
            font-size: 0.85rem;
            margin-bottom: 25px;
            text-transform: uppercase;
            transition: color 0.2s;
        }
        .back-link:hover { color: var(--primary); }

        /* --- HERO SECTION (Image + Info) --- */
        .hero-section {
            background: white;
            border-radius: 24px;
            padding: 50px;
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            gap: 50px;
        }

        .hero-left {
            flex: 1;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main-image {
            max-width: 100%;
            max-height: 450px;
            object-fit: contain;
        }

        .hero-right {
            flex: 1;
        }

        /* --- BADGES --- */
        .badge-row { margin-bottom: 20px; }
        
        .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 6px 16px;
            border-radius: 50px;
            margin-right: 8px;
        }

        .badge-cat { background-color: #3498db; color: white; }
        .badge-brand { background-color: #f1f5f9; color: var(--dark); border: 1px solid #ddd; }

        /* --- TYPOGRAPHY --- */
        .prod-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .short-desc {
            font-size: 1.1rem;
            color: var(--text-muted);
            border-left: 5px solid var(--primary);
            padding-left: 20px;
            margin-bottom: 30px;
        }

        .price-tag {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--green);
            margin-bottom: 30px;
        }

        /* --- WHATSAPP BUTTON --- */
        .btn-wa {
            background-color: #25D366;
            color: white;
            padding: 18px 40px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 18px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
        }
        
        .btn-wa:hover {
            background-color: #128c7e;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 211, 102, 0.4);
        }

        /* --- SPECS SECTION --- */
        .specs-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-top: 40px;
            border: 1px solid var(--border);
        }

        .specs-title {
            font-size: 1.25rem;
            font-weight: 700;
            border-bottom: 1px solid var(--border);
            padding-bottom: 15px;
            margin-bottom: 20px;
            color: var(--dark);
        }

        .specs-content {
            color: #475569;
            font-size: 1.05rem;
            white-space: pre-line; /* Preserves line breaks from DB */
            line-height: 1.8;
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 991px) {
            .hero-section {
                flex-direction: column;
                padding: 30px;
            }
            .prod-title { font-size: 2rem; }
            .main-image { max-height: 300px; }
            .btn-wa { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Back Link -->
        <a href="Networking.php" class="back-link">
            <i class="fa fa-arrow-left"></i> Back to Networking
        </a>

        <!-- HERO SECTION -->
        <div class="hero-section">
            <!-- Left: Image -->
            <div class="hero-left">
                <img src="admin/uploads/<?= htmlspecialchars($p['image_path']) ?>" 
                     class="main-image" 
                     alt="<?= htmlspecialchars($p['name']) ?>"
                     onerror="this.src='https://via.placeholder.com/500x400?text=Product+Image'">
            </div>

            <!-- Right: Details -->
            <div class="hero-right">
               

                <h1 class="prod-title"><?= htmlspecialchars($p['name']) ?></h1>
                
                <div class="short-desc">
                    <?= nl2br(htmlspecialchars($p['short_description'])) ?>
                </div>
                
                <div class="price-tag">Rs. <?= number_format($p['price']) ?></div>

                <a href="https://wa.me/9851000000?text=<?= $wa_msg ?>" target="_blank" class="btn-wa">
                    <i class="fab fa-whatsapp" style="font-size: 24px;"></i> 
                    INQUIRE ON WHATSAPP
                </a>
            </div>
        </div>

        <!-- TECHNICAL SPECIFICATIONS -->
        <div class="specs-card">
            <h3 class="specs-title">
                <i class="fa fa-microchip" style="color: var(--primary); margin-right: 10px;"></i> 
                Technical Specifications
            </h3>
            <div class="specs-content"><?= htmlspecialchars($p['long_description']) ?></div>
        </div>
    </div>

</body>
</html>
<?php
/**
 * SARAL IT SOLUTION - NETWORKING PAGE
 * MATCHES THE MANAGED DESIGN (ROUNDED 20PX, PURPLE THEME)
 */
require_once 'config/db.php';

try {
    // 1. GET ACTIVE FILTERS FROM URL (Maintains both filters simultaneously)
    $current_brand = isset($_GET['brand_id']) && $_GET['brand_id'] !== 'all' ? (int)$_GET['brand_id'] : 'all';
    $current_cat   = isset($_GET['cat_id']) && $_GET['cat_id'] !== 'all' ? (int)$_GET['cat_id'] : 'all';

    // 2. FETCH FILTER LISTS FOR SIDEBAR
    $brands_list = $pdo->query("SELECT * FROM networking_brands ORDER BY name ASC")->fetchAll();
    $cats_list   = $pdo->query("SELECT * FROM networking_categories ORDER BY name ASC")->fetchAll();

    // 3. BUILD DYNAMIC QUERY
    $sql = "SELECT n.*, b.name as bname, c.name as cname 
            FROM networking n 
            LEFT JOIN networking_brands b ON n.brand_id = b.id 
            LEFT JOIN networking_categories c ON n.category_id = c.id 
            WHERE 1=1";

    $params = [];
    if ($current_brand !== 'all') { 
        $sql .= " AND n.brand_id = ?"; 
        $params[] = $current_brand; 
    }
    if ($current_cat !== 'all') { 
        $sql .= " AND n.category_id = ?"; 
        $params[] = $current_cat; 
    }

    $sql .= " ORDER BY n.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Networking Solutions | Saral IT Solution</title>
    <!-- Icons -->
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


     
     

        <!-- MAIN CONTENT AREA -->
        <main class="main-display">
            <!-- Header Section -->
            <div class="grid-header">
                <h2>Networking Products</h2>
                <p class="results-count">Showing <?= count($products) ?> results</p>
            </div>

            <!-- Product Grid -->
            <div class="product-grid">
                <?php if(empty($products)): ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 100px; background: white; border-radius: 20px;">
                        <i class="fa fa-wifi" style="font-size: 50px; color: #eee; margin-bottom: 20px;"></i>
                        <p style="color: #999;">No products found matching your selection.</p>
                        <a href="Networking.php" style="color: var(--primary-purple); font-weight: bold;">Show All Products</a>
                    </div>
                <?php else: ?>
                    <?php foreach($products as $p): ?>
                    <div class="product-card">
                        <div class="img-container">
                            <?php 
                                // Fixes system path error (basename removes local drive paths like C:\xampp...)
                                $img_file = basename($p['image_path']);
                                $img_url = (!empty($img_file)) ? "admin/uploads/".$img_file : "https://via.placeholder.com/300";
                            ?>
                            <img src="<?= $img_url ?>" onerror="this.src='https://via.placeholder.com/300'" alt="Networking Product">
                        </div>
                        
                        <div class="brand-tag">
                            <?= htmlspecialchars($p['bname'] ?? 'Brand') ?> • <?= htmlspecialchars($p['cname'] ?? 'Networking') ?>
                        </div>
                        
                        <h3 class="product-title"><?= htmlspecialchars($p['name']) ?></h3>
                  
                    

                        <a href="networking-details.php?id=<?= $p['id'] ?>" class="btn-details">VIEW SPECS</a>
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
    <link rel="stylesheet" href="footer1.css">
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
                <?php else: ?>
                    <p style="color:#777; font-size:13px;">Image not found.</p>
                <?php endif; ?>
            </div>

        </div>
    </footer>

    <!-- ADMIN / UP ARROW BUTTON -->
    <a href="" class="admin-btn" title="Back to Admin">
        <i class="fas fa-arrow-up"></i>
    </a>

</body>
</html>