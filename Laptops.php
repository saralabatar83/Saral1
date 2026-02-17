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
// 1. DATABASE CONNECTION
require_once 'db.php'; 

try {
    // 2. GET ACTIVE FILTERS FROM URL (Maintains both filters simultaneously)
    $current_brand = isset($_GET['brand_id']) && $_GET['brand_id'] !== 'all' ? (int)$_GET['brand_id'] : 'all';
    $current_cat   = isset($_GET['cat_id']) && $_GET['cat_id'] !== 'all' ? (int)$_GET['cat_id'] : 'all';

    // 3. BUILD DYNAMIC QUERY
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

    // 4. FETCH FILTER LISTS FOR SIDEBAR
    $brands = $pdo->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll();
    $categories = $pdo->query("SELECT * FROM laptop_subcategories ORDER BY sub_name ASC")->fetchAll();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laptops & Computers | Saral IT Solution</title>
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-purple: #838de7;
            --sale-red: #ff4757;
            --bg-light: #f8f9fa;
            --text-dark: #2d3436;
        }

        /* --- RESET & BASE --- */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
       
        a { text-decoration: none; transition: 0.3s; }

        /* --- LAYOUT WRAPPER --- */
        .wrapper { display: flex; max-width: 1500px; margin: 20px auto; gap: 30px; }

        /* --- SIDEBAR (Rounded 20px Design) --- */
        .sidebar { width: 202px; flex-shrink: 0; }
        .filter-card { 
            background: #fff; border-radius: 20px; padding: 25px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 25px; border: 1px solid #f0f0f0; 
        }
        .filter-title { font-size: 12px; font-weight: 700; color: #aaa; text-transform: uppercase; margin-bottom: 20px; letter-spacing: 1px; }
        .filter-list { display: flex; flex-direction: column; gap: 5px; }
        
        .filter-item { display: block; padding: 12px 18px; color: #555; font-size: 15px; border-radius: 12px; }
        .filter-item:hover { background: #f8f9ff; color: var(--primary-purple); }
        .filter-item.active { 
            background-color: var(--primary-purple) !important; color: #fff !important; 
            font-weight: 600; box-shadow: 0 4px 12px rgba(131, 141, 231, 0.3); 
        }

        .reset-link { color: var(--sale-red); font-size: 13px; font-weight: 700; padding-left: 15px; display: block; margin-top: 10px; }

        /* --- MAIN DISPLAY --- */
        .main-display { flex: 1; }

        /* --- HEADER (STRICTLY LEFT ALIGNED) --- */
        .grid-header { 
            display: flex; flex-direction: column; align-items: flex-start; 
            text-align: left; width: 100%; margin-bottom: 30px; padding-left: 5px;
        }
        .grid-header h2 { font-size: 26px; font-weight: 800; color: #2d3436; margin: 0; }
        .results-count { color: #888; font-size: 14px; margin-top: 5px; }

        /* --- PRODUCT GRID --- */
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        
        /* --- PRODUCT CARD (Matching Managed Style) --- */
        .product-card { 
            background: #fff; border-radius: 20px; padding: 25px; border: 1px solid #eee; 
            display: flex; flex-direction: column; text-align: center; transition: 0.3s;
        }
        .product-card:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }

        .img-container { height: 180px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; }
        .img-container img { max-width: 100%; max-height: 100%; object-fit: contain; }

        .brand-tag { font-size: 11px; font-weight: 800; color: var(--primary-purple); text-transform: uppercase; margin-bottom: 10px; }
        .product-title { font-size: 18px; font-weight: 700; color: #333; height: 48px; overflow: hidden; margin-bottom: 15px; line-height: 1.3; }
        
        .price { font-size: 24px; font-weight: 800; color: var(--sale-red); margin-bottom: 20px; }
        
        .btn-view { 
            background: var(--primary-purple); color: white; padding: 14px; border-radius: 12px; 
            font-weight: 700; font-size: 14px; text-transform: uppercase; margin-top: auto;
        }
        .btn-view:hover { background: #6c79e0; }

        /* Responsive Settings for Phone */
        @media (max-width: 900px) {
            .wrapper { flex-direction: column; }
            .sidebar { width: 100%; }
            .product-grid { grid-template-columns: 1fr 1fr; gap: 15px; }
            .product-card { padding: 15px; }
            .price { font-size: 18px; }
        }
    </style>
</head>
<body>

    <div class="wrapper">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="filter-card">
                <div class="filter-title">Categories</div>
                <div class="filter-list">
                    <a href="?cat_id=all&brand_id=<?= $current_brand ?>" class="filter-item <?= ($current_cat == 'all') ? 'active' : '' ?>">All Categories</a>
                    <?php foreach($categories as $cat): ?>
                        <a href="?cat_id=<?= $cat['id'] ?>&brand_id=<?= $current_brand ?>" 
                           class="filter-item <?= ($current_cat == $cat['id']) ? 'active' : '' ?>">
                           <?= htmlspecialchars($cat['sub_name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="filter-card">
                <div class="filter-title">Brands</div>
                <div class="filter-list">
                    <a href="?brand_id=all&cat_id=<?= $current_cat ?>" class="filter-item <?= ($current_brand == 'all') ? 'active' : '' ?>">All Brands</a>
                    <?php foreach($brands as $brand): ?>
                        <a href="?brand_id=<?= $brand['id'] ?>&cat_id=<?= $current_cat ?>" 
                           class="filter-item <?= ($current_brand == $brand['id']) ? 'active' : '' ?>">
                           <?= htmlspecialchars($brand['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <a href="laptops.php" class="reset-link"><i class="fa fa-sync-alt"></i> RESET FILTERS</a>
        </aside>

        <!-- MAIN DISPLAY AREA -->
        <main class="main-display">
            <!-- Header (Strictly Left Aligned) -->
            <div class="grid-header">
                <h2>Laptops & Computers</h2>
                <p class="results-count">Showing <?= count($laptops) ?> results</p>
            </div>

            <!-- Product Grid -->
            <div class="product-grid">
                <?php if(empty($laptops)): ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 100px; background: white; border-radius: 20px;">
                        <i class="fa fa-laptop" style="font-size: 50px; color: #eee; margin-bottom: 20px;"></i>
                        <p style="color: #999;">No products found matching your criteria.</p>
                        <a href="laptops.php" style="color: var(--primary-purple); font-weight: bold;">Show All Products</a>
                    </div>
                <?php else: ?>
                    <?php foreach($laptops as $p): ?>
                    <div class="product-card">
                        <div class="img-container">
                            <?php 
                                // FIX: Extract filename only to avoid system path errors (C:\xampp...)
                                $img_file = basename($p['image_path']);
                                $img_url = (!empty($img_file)) ? "admin/uploads/".$img_file : "https://via.placeholder.com/300";
                            ?>
                            <img src="<?= $img_url ?>" onerror="this.src='https://via.placeholder.com/300'" alt="Laptop">
                        </div>
                        
                        <div class="brand-tag">
                            <?= htmlspecialchars($p['bname'] ?? 'Brand') ?> • <?= htmlspecialchars($p['sub_name'] ?? 'Laptop') ?>
                        </div>
                        
                        <h3 class="product-title"><?= htmlspecialchars($p['title']) ?></h3>
                        
                        <div class="price">
                            <?php if(!empty($p['price']) && $p['price'] > 0): ?>
                                Rs. <?= number_format((float)str_replace(',', '', $p['price'])) ?>
                            <?php else: ?>
                             
                               <a href="https://wa.me/9767220473?text=<?= $wa_msg ?>" target="_blank" class="wa-btn"> <span style="color:#bbb; font-size:16px;">Price on Call</span>
                            <?php endif; ?>
                        </div>

                        <a href="product-details.php?id=<?= $p['id'] ?>" class="btn-view">VIEW SPECS</a>
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