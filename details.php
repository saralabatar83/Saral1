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
 * Saral IT - Service Details Page
 * This file fetches data from 'top_services' joined with 'service_cats'
 */

// 1. Database Connection
// Adjust the path if your db.php is in a different folder
require_once 'admin/db.php'; 

// 2. Get and Validate Service ID from URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php");
    exit;
}

try {
    // 3. Fetch Service Details with Category Name
    $stmt = $pdo->prepare("
        SELECT s.*, c.name as category_name 
        FROM top_services s 
        LEFT JOIN service_cats c ON s.cat_id = c.id 
        WHERE s.id = ?
    ");
    $stmt->execute([$id]);
    $service = $stmt->fetch();

    if (!$service) {
        die("<center style='padding:50px;'><h3>Service not found. <a href='index.php'>Return to Home</a></h3></center>");
    }

    // 4. Configuration for Contact
    $wa_number = "9800000000"; // CHANGE TO YOUR WHATSAPP NUMBER
    $site_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $current_url = $site_url . $_SERVER['REQUEST_URI'];
    
    // Path where admin uploads images
    $image_path = "admin/uploads/" . $service['image_path'];
    
    // WhatsApp Inquiry Message
    $wa_text = "Hello Saral IT, I am interested in this service:\n\n" . 
               "*Service:* " . $service['title'] . "\n" .
               "*Price:* " . ($service['price'] ?: 'Negotiable') . "\n" .
               "*Link:* " . $current_url;
    $wa_link = "https://wa.me/" . $wa_number . "?text=" . urlencode($wa_text);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($service['title']) ?> | Service Details</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #2563eb;
            --whatsapp: #25D366;
            --dark: #0f172a;
            --gray: #64748b;
            --light-bg: #f8fafc;
            --border: #e2e8f0;
        }

        * { box-sizing: border-box; }
   

        .container { max-width: 1100px; margin: 40px auto; }
        
        /* Navigation Link */
        .back-link { 
            text-decoration: none; color: var(--primary); 
            font-weight: 600; margin-bottom: 25px; 
            display: inline-flex; align-items: center; gap: 8px;
        }

        /* Main Service Card */
        .service-card { 
            background: #fff; 
            border-radius: 24px; 
            display: flex; 
            overflow: hidden; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.06);
            border: 1px solid var(--border);
            min-height: 550px;
        }

        /* Image Side Manager */
        .img-section { 
            flex: 0 0 45%; 
            background: #f1f5f9; 
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .img-section img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; /* Prevents image stretching */
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .service-card:hover .img-section img {
            transform: scale(1.08); /* Modern hover effect */
        }

        /* Badge for New Releases */
        .new-badge { 
            position: absolute; top: 20px; left: 20px; 
            background: #ff7675; color: white; 
            padding: 6px 14px; border-radius: 8px; 
            font-size: 11px; font-weight: 800; 
            letter-spacing: 1px; z-index: 2;
            box-shadow: 0 4px 10px rgba(255,118,117,0.3);
        }

        /* Content Side Manager */
        .info-section { 
            flex: 1; 
            padding: 50px; 
            display: flex; 
            flex-direction: column; 
            justify-content: center;
        }
        
        .cat-badge { 
            display: inline-block; background: #eff6ff; color: var(--primary); 
            padding: 6px 16px; border-radius: 50px; font-size: 12px; font-weight: 700; 
            margin-bottom: 15px; text-transform: uppercase; align-self: flex-start;
        }

        h1 { font-size: 36px; margin: 0 0 10px 0; color: var(--dark); line-height: 1.2; }
        
        .price-tag { 
            font-size: 26px; color: var(--primary); 
            font-weight: 800; margin-bottom: 20px; 
            display: block;
        }
        
        /* Highlights Box */
        .highlights { 
            background: #fff9eb; border-left: 4px solid #f59e0b; 
            padding: 15px 20px; border-radius: 12px; 
            color: #92400e; margin-bottom: 30px;
            font-size: 15px; font-weight: 500;
        }

        /* Description Styling (CKEditor Support) */
        .description { 
            color: #475569; 
            margin-bottom: 40px; 
            font-size: 16px;
        }
        .description img { max-width: 100%; height: auto; border-radius: 12px; }

        /* WhatsApp Button */
        .wa-btn { 
            display: inline-flex; align-items: center; justify-content: center; gap: 12px;
            background: var(--whatsapp); color: #fff; padding: 18px 35px; 
            border-radius: 15px; text-decoration: none; font-weight: 700;
            transition: all 0.3s ease; 
            box-shadow: 0 10px 20px rgba(37, 211, 102, 0.2);
            font-size: 18px;
        }
        .wa-btn:hover { 
            background: #1eb954; 
            transform: translateY(-3px); 
            box-shadow: 0 15px 25px rgba(37, 211, 102, 0.3);
        }

        /* Responsive Settings */
        @media (max-width: 992px) {
            .service-card { flex-direction: column; min-height: auto; }
            .img-section { width: 100%; height: 350px; }
            .info-section { padding: 35px 25px; }
            h1 { font-size: 28px; }
            .wa-btn { width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Breadcrumb -->
    <a href="index.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to All Services
    </a>

    <div class="service-card">
        <!-- Photo Section -->
        <div class="img-section">
            <?php if($service['is_new_release']): ?>
          
            <?php endif; ?>

            <img src="<?= $image_path ?>" 
                 alt="<?= htmlspecialchars($service['title']) ?>"
                 onerror="this.src='https://via.placeholder.com/800x800?text=Service+Image+Coming+Soon'">
        </div>

        <!-- Text Content Section -->
        <div class="info-section">
            <span class="cat-badge">
                <i class="fas fa-th-large me-1"></i> <?= htmlspecialchars($service['category_name'] ?? 'General') ?>
            </span>
            
            <h1><?= htmlspecialchars($service['title']) ?></h1>
            
            <span class="price-tag">
                <?= $service['price'] ? htmlspecialchars($service['price']) : 'Contact for Price' ?>
            </span>

            <?php if(!empty($service['highlights'])): ?>
            <div class="highlights">
                <i class="fas fa-check-circle me-2"></i> 
                <?= htmlspecialchars($service['highlights']) ?>
            </div>
            <?php endif; ?>

            <div class="description">
                <!-- Data from CKEditor (allowing HTML tags) -->
                <?= $service['description'] ?>
            </div>

            <!-- Booking Button -->
            <a href="<?= $wa_link ?>" target="_blank" class="wa-btn">
                <i class="fab fa-whatsapp"></i> 
                Book Service on WhatsApp
            </a>
        </div>
    </div>
</div>

<footer style="text-align: center; color: var(--gray); font-size: 13px; margin: 60px 0 30px;">
    <p>&copy; <?= date('Y') ?> Saral IT Solutions. Quality Repairs & Support.</p>
</footer>

</body>
</html>

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