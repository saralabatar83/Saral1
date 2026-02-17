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
 * SARAL IT SOLUTION - UNIVERSAL SEARCH (INTEGRATED ELECTRONICS)
 * Searches across: Laptops, CCTV, Printers, Electronics, Accessories, Networking, and Services.
 */
require_once 'config/db.php';

// 1. INPUT HANDLING
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$type_filter  = $_GET['type'] ?? ''; 
$brand_filter = (isset($_GET['brand_id']) && $_GET['brand_id'] !== 'all') ? (int)$_GET['brand_id'] : 'all';
$sub_filter   = (isset($_GET['sub_id']) && $_GET['sub_id'] !== 'all') ? (int)$_GET['sub_id'] : 'all';
$sort = $_GET['sort'] ?? 'newest';

// 2. FETCH REFERENCE LISTS FOR DYNAMIC SIDEBARS
// Laptop Lists
$laptop_brands = $pdo->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll();
$laptop_cats   = $pdo->query("SELECT * FROM laptop_subcategories ORDER BY sub_name ASC")->fetchAll();

// Electronic Lists (New)
$elec_brands = $pdo->query("SELECT * FROM electronic_brands ORDER BY name ASC")->fetchAll();
$elec_subs   = $pdo->query("SELECT * FROM electronic_subs ORDER BY name ASC")->fetchAll();

$results = [];
$stats = [
    'laptop'      => ['count' => 0, 'label' => 'Laptops & Computers', 'icon' => 'fa-laptop'],
    'cctv'        => ['count' => 0, 'label' => 'CCTV Security', 'icon' => 'fa-video'],
    'printer'     => ['count' => 0, 'label' => 'Printers', 'icon' => 'fa-print'],
    'electronic'  => ['count' => 0, 'label' => 'Electronics', 'icon' => 'fa-microchip'],
    'accessories' => ['count' => 0, 'label' => 'Accessories', 'icon' => 'fa-keyboard'],
    'networking'  => ['count' => 0, 'label' => 'Networking', 'icon' => 'fa-network-wired'],
    'services'    => ['count' => 0, 'label' => 'Our Services', 'icon' => 'fa-gears']
];

if ($query !== '') {
    $searchTerm = "%$query%";
    
    // 3. MASTER UNION QUERY (Fixed table name 'electronic' and columns)
    $sql = "
        SELECT id, title AS p_name, price, image_path, 'laptop' AS p_type, brand_id, sub_id FROM laptop WHERE (title LIKE ? OR brand LIKE ? OR short_description LIKE ?)
        UNION ALL 
        SELECT id, name AS p_name, price, image_path, 'cctv' AS p_type, 0 AS brand_id, 0 AS sub_id FROM cctv WHERE name LIKE ?
        UNION ALL 
        SELECT id, title AS p_name, price, image_path, 'printer' AS p_type, 0 AS brand_id, 0 AS sub_id FROM printer WHERE title LIKE ?
        UNION ALL 
        /* INTEGRATED ELECTRONIC TABLE */
        SELECT id, title AS p_name, price, image_path, 'electronic' AS p_type, brand_id, sub_id FROM electronic WHERE (title LIKE ? OR short_desc LIKE ?)
        UNION ALL 
        SELECT id, name AS p_name, price, image_path, 'accessories' AS p_type, 0 AS brand_id, 0 AS sub_id FROM accessories WHERE name LIKE ?
        UNION ALL 
        SELECT id, name AS p_name, price, image_path, 'networking' AS p_type, 0 AS brand_id, 0 AS sub_id FROM networking WHERE name LIKE ?
        UNION ALL
        SELECT id, title AS p_name, 0 AS price, image_path, 'offers_sec1' AS p_type, 0 AS brand_id, 0 AS sub_id FROM offers_sec1 WHERE title LIKE ?
        UNION ALL
        SELECT id, title AS p_name, 0 AS price, image_path, 'offers_sec2' AS p_type, 0 AS brand_id, 0 AS sub_id FROM offers_sec2 WHERE title LIKE ?
        UNION ALL
        SELECT id, title AS p_name, 0 AS price, image_path, 'offers_sec3' AS p_type, 0 AS brand_id, 0 AS sub_id FROM offers_sec3 WHERE title LIKE ?
        UNION ALL
        SELECT id, title AS p_name, 0 AS price, image_path, 'offers_sec4' AS p_type, 0 AS brand_id, 0 AS sub_id FROM offers_sec4 WHERE title LIKE ?
    ";

    $stmt = $pdo->prepare($sql);
    
    // Total 13 parameters to match the '?' marks in the SQL above
    $params = [
        $searchTerm, $searchTerm, $searchTerm, // Laptop (Title, Brand, Desc)
        $searchTerm,                           // CCTV
        $searchTerm,                           // Printer
        $searchTerm, $searchTerm,              // Electronic (Title, Short Desc)
        $searchTerm,                           // Accessories
        $searchTerm,                           // Networking
        $searchTerm, $searchTerm, $searchTerm, $searchTerm // Offers/Services 1-4
    ];
    
    $stmt->execute($params);
    $all_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. FILTERING & STATS
    foreach ($all_results as $row) {
        $sidebar_type = (strpos($row['p_type'], 'offers_sec') !== false) ? 'services' : $row['p_type'];
        if(isset($stats[$sidebar_type])) { $stats[$sidebar_type]['count']++; }
        
        $match = true;
        // Global category filter
        if ($type_filter) {
            if ($type_filter === 'services') {
                if (strpos($row['p_type'], 'offers_sec') === false) $match = false;
            } else {
                if ($row['p_type'] !== $type_filter) $match = false;
            }
        }
        
        // Laptop & Electronic specific filters (Both use brand_id and sub_id)
        if ($row['p_type'] === 'laptop' || $row['p_type'] === 'electronic') {
            if ($brand_filter !== 'all' && $row['brand_id'] != $brand_filter) $match = false;
            if ($sub_filter !== 'all' && $row['sub_id'] != $sub_filter) $match = false;
        }

        if ($match) $results[] = $row;
    }

    // 5. SORTING
    usort($results, function($a, $b) use ($sort) {
        if ($sort == 'price_low') return (float)$a['price'] <=> (float)$b['price'];
        if ($sort == 'price_high') return (float)$b['price'] <=> (float)$a['price'];
        return $b['id'] <=> $a['id'];
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Universal Search | Saral IT Solution</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #838de7; --nav-orange: #ff9800; --bg: #f4f7f6; --text-dark: #333; }
       
        a { text-decoration: none; color: inherit; transition: 0.3s; }
        
        /* Navigation */
       
        .search-hero { background: #fff; padding: 30px 5%; text-align: center; border-bottom: 1px solid #eee; }
        .wrapper { display: flex; max-width: 1450px; margin: 20px auto; gap: 25px; padding: 0 2%; }

        /* Sidebar Filters */
        .sidebar { width: 260px; flex-shrink: 0; }
        .filter-card { background: #fff; border-radius: 10px; padding: 15px; margin-bottom: 20px; border: 1px solid #eee; }
        .filter-title { font-size: 11px; font-weight: bold; color: #999; text-transform: uppercase; margin-bottom: 12px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        
        .filter-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; font-size: 14px; border-radius: 5px; margin-bottom: 2px; }
        .filter-item:hover { background: #f0f2ff; color: var(--primary); }
        .filter-item.active { background: var(--primary); color: #fff !important; }
       

        /* Results */
        .main-content { flex: 1; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .product-card { background: #fff; border-radius: 12px; padding: 20px; text-align: center; border: 1px solid #eee; transition: 0.3s; display: flex; flex-direction: column; height: 100%; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); border-color: var(--primary); }
        
        .type-tag { font-size: 9px; color: #aaa; text-transform: uppercase; font-weight: bold; margin-bottom: 10px; display: block; }
        .img-box { height: 160px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; }
        .img-box img { max-width: 100%; max-height: 100%; object-fit: contain; }
        
        .product-card h4 { font-size: 14px; font-weight: 600; margin: 10px 0; height: 40px; overflow: hidden; line-height: 1.4; }
        .price { color: #ff4757; font-size: 18px; font-weight: 800; margin-top: auto; }
        .btn-view { margin-top: 15px; background: #2c3e50; color: #fff; padding: 10px; border-radius: 6px; font-size: 12px; font-weight: bold; cursor: pointer; border: none; }
        .product-card:hover .btn-view { background: var(--primary); }

        @media (max-width: 992px) { .wrapper { flex-direction: column; } .sidebar { width: 100%; } }
    </style>
</head>
<body>

 
    <div class="search-hero">
        <a href="index.php" style="color: var(--primary); font-weight: bold; font-size: 14px;">← BACK TO HOME</a>
        <h1 style="margin-top: 15px;">Universal Search: "<span><?= htmlspecialchars($query) ?></span>"</h1>
        <p><?= count($results) ?> matches found</p>
    </div>

    <div class="wrapper">
        <aside class="sidebar">
            <div class="filter-card">
                <div class="filter-title">Filter by Category</div>
                <a href="search.php?q=<?=urlencode($query)?>" class="filter-item <?= !$type_filter ? 'active' : '' ?>">All Results</a>
                <?php foreach($stats as $key => $val): if($val['count'] > 0): ?>
                    <a href="search.php?q=<?=urlencode($query)?>&type=<?=$key?>" class="filter-item <?= $type_filter == $key ? 'active' : '' ?>">
                        <span><i class="fa <?=$val['icon']?>"></i> <?=$val['label']?></span>
                       
                <?php endif; endforeach; ?>
            </div>

            <!-- ELECTRONIC SPECIFIC FILTERS -->
            <?php if($type_filter == 'electronic'): ?>
                <div class="filter-card">
                    <div class="filter-title">Electronic Brands</div>
                    <a href="search.php?q=<?=urlencode($query)?>&type=electronic&brand_id=all&sub_id=<?=$sub_filter?>" class="filter-item <?= $brand_filter == 'all' ? 'active' : '' ?>">All Brands</a>
                    <?php foreach($elec_brands as $b): ?>
                        <a href="search.php?q=<?=urlencode($query)?>&type=electronic&brand_id=<?=$b['id']?>&sub_id=<?=$sub_filter?>" class="filter-item <?= $brand_filter == $b['id'] ? 'active' : '' ?>"><?=$b['name']?></a>
                    <?php endforeach; ?>
                </div>

                <div class="filter-card">
                    <div class="filter-title">Electronic Types</div>
                    <a href="search.php?q=<?=urlencode($query)?>&type=electronic&brand_id=<?=$brand_filter?>&sub_id=all" class="filter-item <?= $sub_filter == 'all' ? 'active' : '' ?>">All Types</a>
                    <?php foreach($elec_subs as $s): ?>
                        <a href="search.php?q=<?=urlencode($query)?>&type=electronic&brand_id=<?=$brand_filter?>&sub_id=<?=$s['id']?>" class="filter-item <?= $sub_filter == $s['id'] ? 'active' : '' ?>"><?=$s['name']?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <a href="search.php?q=<?=urlencode($query)?>" style="color: #ff4757; font-size: 12px; font-weight: bold; margin-left: 10px;">
                <i class="fa fa-refresh"></i> RESET FILTERS
            </a>
        </aside>

        <main class="main-content">
            <div class="product-grid">
                <?php foreach($results as $item): 
                    // ROUTING LOGIC
                    $link = "index.php";
                    if($item['p_type'] == 'laptop') $link = "product-details.php?id=".$item['id'];
                    elseif($item['p_type'] == 'electronic') $link = "electronic-details.php?id=".$item['id'];
                    elseif($item['p_type'] == 'printer') $link = "printer-details.php?id=".$item['id'];
                    elseif($item['p_type'] == 'cctv') $link = "cctv-details.php?id=".$item['id'];
                    elseif($item['p_type'] == 'accessories') $link = "accessory-details.php??id=".$item['id'];
                    elseif(strpos($item['p_type'], 'offers_sec') !== false) {
                        $link = "detailoffer.php?table=".$item['p_type']."&id=".$item['id'];
                    }
                ?>
                <div class="product-card">
                    <span class="type-tag"><?= str_replace('electronic', 'Electronics', $item['p_type']) ?></span>
                    <div class="img-box">
                        <img src="admin/uploads/<?= basename($item['image_path']) ?>" onerror="this.src='https://via.placeholder.com/200'">
                    </div>
                    <h4><?= htmlspecialchars($item['p_name']) ?></h4>
                    <div class="price"><?= ($item['price'] > 0) ? "Rs. ".number_format((float)$item['price']) : "Call for Price" ?></div>
                    <button class="btn-view" onclick="location.href='<?=$link?>'">VIEW DETAILS</button>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>
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