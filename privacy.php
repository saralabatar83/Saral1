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
include 'db.php';

// --- 1. FETCH DATA FROM 'privacy' TABLE ---
// We only need the first row (id=1)
$stmt = $pdo->query("SELECT * FROM privacy WHERE id = 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Set Defaults (in case DB is empty, though INSERT SQL prevents this)
$heroTitle = $row['hero_title'] ?? 'Privacy Policy';
$heroImage = $row['hero_image'] ?? '';
$lastUpdated = $row['last_updated'] ?? 'Recently';
$content = $row['content'] ?? '<p>Content coming soon...</p>';

// Dynamic Style for Hero Image
$heroStyle = "background-color: #001f3f;"; // Default Navy
if (!empty($heroImage)) {
    $heroStyle = "background: url('$heroImage') no-repeat center center/cover;";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($heroTitle); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
      
        * { box-sizing: border-box; }

        /* --- HERO SECTION --- */
        .hero {
            <?php echo $heroStyle; ?>
            height: 300px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            text-align: center;
            color: white;
        }
        .hero::after { content:''; position:absolute; inset:0; background:rgba(0,0,0,0.6); }
        
        .hero-content { position: relative; z-index: 2; }
        .hero h1 { font-size: 3rem; margin: 0; text-shadow: 0 2px 10px rgba(0,0,0,0.5); }
        .hero .date { margin-top: 10px; font-size: 1rem; opacity: 0.9; background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px; display: inline-block; }

        /* --- CONTENT CONTAINER --- */
        .container {
            max-width: 900px;
            margin: -60px auto 50px; /* Overlap Hero */
            background: white;
            padding: 60px;
            position: relative;
            z-index: 3;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border-top: 5px solid #C62828; /* Mudita Red */
        }

        /* --- TYPOGRAPHY --- */
        .content h2, .content h3 { color: #001f3f; margin-top: 30px; }
        .content h2 { border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; font-size: 1.8rem; }
        .content p { margin-bottom: 15px; color: #555; text-align: justify; }
        .content ul, .content ol { margin-bottom: 20px; padding-left: 20px; color: #555; }
        .content li { margin-bottom: 8px; }
        .content strong { color: #333; }
        .content a { color: #C62828; text-decoration: underline; }

        /* Responsive */
        @media (max-width: 768px) {
            .container { margin: 0; border-radius: 0; padding: 30px 20px; }
            .hero h1 { font-size: 2rem; }
        }
    </style>
</head>
<body>

    <!-- Hero -->
    <div class="hero">
        <div class="hero-content">
            <h1><?php echo htmlspecialchars($heroTitle); ?></h1>
            <div class="date">Last Updated: <?php echo htmlspecialchars($lastUpdated); ?></div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="content">
            <!-- We assume Admin puts HTML in the DB, so we decode it here -->
            <?php echo html_entity_decode($content); ?>
        </div>
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