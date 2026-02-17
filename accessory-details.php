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
 * SARAL IT SOLUTION - ACCESSORY DETAILS (NO BOOTSTRAP)
 */
require_once 'config/db.php';

// 1. GET PRODUCT ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id === 0) { header("Location: Accessories.php"); exit(); }

try {
    // 2. FETCH PRODUCT DETAILS
    $stmt = $pdo->prepare("SELECT a.*, b.name as bname, c.name as cname 
                           FROM accessories a 
                           LEFT JOIN accessory_brands b ON a.brand_id = b.id 
                           LEFT JOIN accessory_categories c ON a.category_id = c.id 
                           WHERE a.id = ?");
    $stmt->execute([$id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$p) { die("<h3>Item not found.</h3> <a href='Accessories.php'>Go Back</a>"); }

} catch (PDOException $e) { 
    die("Database Error."); 
}

// 3. WHATSAPP LINK
$wa_number = "9779800000000"; // <--- CHANGE THIS TO YOUR NUMBER
$wa_msg = urlencode("Hello, I am interested in the " . ($p['bname'] ?? '') . " " . $p['name'] . ". Please share the best price.");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($p['name']) ?> - Details</title>
    
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* 1. GLOBAL RESET (No Bootstrap) */
        * { box-sizing: border-box; }
      
        a { text-decoration: none; color: inherit; transition: 0.3s; }
        h1, h2, h3, h4, p { margin: 0 0 15px 0; }

        /* 2. LAYOUT CONTAINER */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* 3. BACK BUTTON */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 25px;
            font-size: 0.95rem;
        }
        .back-link:hover { color: #333; transform: translateX(-3px); }

        /* 4. PRODUCT CARD WRAPPER */
        .product-wrapper {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #e0e0e0;
            overflow: hidden;
            display: flex;
            flex-wrap: wrap; /* Allows stacking on mobile */
        }

        /* 5. IMAGE SECTION (LEFT) */
        .image-section {
            flex: 1 1 500px; /* Base width 500px, but can grow/shrink */
            background-color: #fff;
            border-right: 1px solid #f0f0f0;
            padding: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 400px;
        }

        .main-img {
            max-width: 100%;
            max-height: 400px;
            width: auto;
            height: auto;
            object-fit: contain;
            transition: transform 0.3s;
        }
        .main-img:hover { transform: scale(1.05); }

        /* 6. DETAILS SECTION (RIGHT) */
        .info-section {
            flex: 1 1 400px;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Badges */
        .meta-badges { margin-bottom: 20px; display: flex; gap: 10px; }
        .badge {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 5px 12px;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }
        .b-blue { background: #e0f2fe; color: #0284c7; }
        .b-gray { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

        /* Typography */
        .product-title {
            font-size: 2rem;
            font-weight: 800;
            color: #1e293b;
            line-height: 1.2;
            margin-bottom: 15px;
        }
        .short-desc {
            color: #64748b;
            font-size: 1.05rem;
            margin-bottom: 30px;
            border-left: 3px solid #7b7fdb;
            padding-left: 15px;
        }

        /* Price */
        .price-area {
            font-size: 2.2rem;
            font-weight: 800;
            color: #27ae60;
            margin-bottom: 30px;
        }
        .currency { font-size: 1.2rem; font-weight: 600; vertical-align: top; }

        /* WhatsApp Button */
        .btn-whatsapp {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background-color: #25D366;
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            padding: 16px 32px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
            width: fit-content;
        }
        .btn-whatsapp:hover {
            background-color: #1ebc57;
            transform: translateY(-2px);
        }

        /* 7. SPECIFICATIONS BOX */
        .specs-box {
            margin-top: 40px;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
        }
        .specs-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .specs-content {
            color: #555;
            white-space: pre-line; /* Preserves line breaks from DB */
        }

        /* Responsive */
        @media (max-width: 768px) {
            .product-wrapper { flex-direction: column; }
            .image-section { border-right: none; border-bottom: 1px solid #f0f0f0; padding: 20px; min-height: 300px; }
            .info-section { padding: 30px 20px; }
            .product-title { font-size: 1.6rem; }
            .btn-whatsapp { width: 100%; }
        }
    </style>
</head>
<body>

    <div class="container">
        
        <!-- Back Link -->
        <a href="Accessories.php" class="back-link">
            <i class="fa fa-arrow-left"></i> Back to Accessories
        </a>

        <!-- Main Product Card -->
        <div class="product-wrapper">
            
            <!-- Left: Image -->
            <div class="image-section">
                <img src="admin/uploads/<?= htmlspecialchars($p['image_path']) ?>" 
                     alt="<?= htmlspecialchars($p['name']) ?>" 
                     class="main-img"
                     onerror="this.onerror=null; this.src='https://via.placeholder.com/400x400?text=No+Image';">
            </div>

            <!-- Right: Details -->
            <div class="info-section">
                
             

                <h1 class="product-title"><?= htmlspecialchars($p['name']) ?></h1>

                <div class="short-desc">
                    <?= htmlspecialchars($p['short_description']) ?>
                </div>

            
                <a href="https://wa.me/<?= $wa_number ?>?text=<?= $wa_msg ?>" target="_blank" class="btn-whatsapp">
                    <i class="fa-brands fa-whatsapp"></i> Buy on WhatsApp
                </a>
            </div>

        </div>

        <!-- Technical Specifications (Only if data exists) -->
        <?php if(!empty($p['long_description'])): ?>
            <div class="specs-box">
                <h3 class="specs-title">Technical Specifications</h3>
                <div class="specs-content"><?= htmlspecialchars($p['long_description']) ?></div>
            </div>
        <?php endif; ?>

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

