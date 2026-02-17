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

// --- 1. FETCH DATA ---
$stmt = $pdo->query("SELECT * FROM page_content");
$content = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $content[$row['section_key']] = $row;
}

// Set Defaults (Prevents errors if DB is empty)
$heroTitle = $content['hero']['title'] ?? 'Welcome to Mudita Store';
$heroImage = $content['hero']['image_url'] ?? '';
$aboutTitle = $content['about']['title'] ?? 'About Us';
// We use html_entity_decode to ensure HTML tags (<b>, <ul>, etc.) render correctly
$aboutText  = isset($content['about']['body_text']) ? html_entity_decode($content['about']['body_text']) : 'Content coming soon...';

// --- 2. HERO IMAGE LOGIC ---
$heroStyle = "background-color: #333;"; // Default dark color
if (!empty($heroImage)) {
    // Determine if it's a URL or a local file
    $heroStyle = "background: url('$heroImage') no-repeat center center/cover;";
}

// --- 3. FETCH VALUES ---
$stmt = $pdo->query("SELECT * FROM store_values ORDER BY id ASC");
$values = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mudita Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- GENERAL --- */
   

        /* --- HERO SECTION --- */
        .hero {
            <?php echo $heroStyle; ?>
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            text-align: center;
        }
        .hero::after { content:''; position:absolute; inset:0; background:rgba(0,0,0,0.5); }
        .hero h1 { 
            color: white; 
            position: relative; 
            z-index: 2; 
            font-size: 3rem; 
            text-transform: uppercase; 
            letter-spacing: 2px;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.7);
            margin: 0; padding: 20px;
        }

        /* --- ABOUT SECTION (RICH TEXT SUPPORT) --- */
        .about { background: #ddc2c2; color: Black; padding: 10px 10px; }
        .about-container { max-width: 900px; margin: 0 auto; text-align: center; }
        
        .about h2 { font-size: 2.5rem; margin-top: 0; border-bottom: 2px solid rgba(255,255,255,0.3); display: inline-block; padding-bottom: px; }

        /* CRITICAL: CSS for MS Word Features (Lists, Bold, Align) */
        .about-content {
            font-size: 1.1rem;
            text-align: left; /* Default text align */
            margin-top: 30px;
        }
        /* Make Bold text Pop (Yellow) */
        .about-content strong, .about-content b { color: #FFD54F; font-weight: 800; }
        /* Links */
        .about-content a { color: #81D4FA; text-decoration: underline; }
        
        /* Fix Lists (Bullets & Numbers) on Red Background */
        .about-content ul { list-style-type: disc; padding-left: 40px; margin-bottom: 15px; }
        .about-content ol { list-style-type: decimal; padding-left: 40px; margin-bottom: 15px; }
        .about-content li { margin-bottom: 5px; }
        
        /* Fix Multilevel Lists (Nested) */
        .about-content ul ul { list-style-type: circle; }
        .about-content ul ul ul { list-style-type: square; }
        
        /* Handle Alignment Classes from Editor */
        .about-content p { margin-bottom: 15px; }

        /* --- VALUES SECTION --- */
        .mv-section { background: #f9f9f9; padding: 10px; }
        .mv-grid { display: flex; flex-wrap: wrap; justify-content: center; gap: 30px; max-width: 1200px; margin: 0 auto; }
        .card { 
            background: white; padding: 30px; flex: 1; min-width: 300px; 
            border-top: 4px solid #C62828; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); 
            border-radius: 5px;
        }
        .card h3 { color: #C62828; display: flex; align-items: center; gap: 10px; margin-top: 0; }

        /* --- CORE VALUES (Circle Layout) --- */
        .cv-section { padding: ; text-align: center; background: white; }
        .cv-grid { 
            display: flex; justify-content: center; align-items: center; 
            gap: 50px; flex-wrap: wrap; max-width: 1100px; margin: 0 auto; 
        }
        .cv-col { flex: 1; min-width: 250px; text-align: left; }
        .cv-col.right { text-align: left; }
        
        /* Mobile adjustment for columns */
        @media(max-width: 768px) {
            .cv-col, .cv-col.right { text-align: center; }
        }

        .cv-item { margin-bottom: 30px; }
        .cv-item i { color: #2E7D32; font-size: 1.4rem; margin-right: 8px; }
        .cv-item h4 { display: inline; font-size: 1.2rem; color: #333; }
        .cv-item p { margin: 5px 0 0 32px; font-size: 0.95rem; color: #666; }
        
        /* Circle Center */
        .circle {
            width: 160px; height: 160px; background: white;
            border: 6px solid #f0f0f0; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 1.2rem; color: #001f3f;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            text-align: center;
        }

        .admin-btn:hover { background: #C62828; transform: translateY(-2px); }
    </style>
</head>
<body>

    <!-- Hero -->
    <div class="hero">
        <h1><?php echo htmlspecialchars($heroTitle); ?></h1>
    </div>

    <!-- About Section (Displays Rich Text) -->
    <div class="about">
        <div class="about-container">
            <h2><?php echo htmlspecialchars($aboutTitle); ?></h2>
            
            <!-- THIS DIV DISPLAYS THE FORMATTED TEXT -->
            <div class="about-content">
                <?php echo $aboutText; ?>
            </div>
        </div>
    </div>

    <!-- Mission & Vision -->
    <div class="mv-section">
        <div class="mv-grid">
            <?php foreach($values as $v): ?>
                <?php if($v['category'] == 'mission'): ?>
                    <div class="card">
                        <h3><i class="fas <?php echo $v['icon_name']; ?>"></i> <?php echo $v['title']; ?></h3>
                        <p><?php echo $v['description']; ?></p>
                    </div>
                <?php endif; ?>
                <?php if($v['category'] == 'vision'): ?>
                    <div class="card">
                        <h3><i class="fas <?php echo $v['icon_name']; ?>"></i> <?php echo $v['title']; ?></h3>
                        <p><?php echo $v['description']; ?></p>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Core Values -->
    <div class="cv-section">
        <h2 style="color:#001f3f; font-size:2.5rem; margin-bottom:50px;">CORE VALUES</h2>
        <div class="cv-grid">
            
            <!-- Left Column (First 3 values) -->
            <div class="cv-col">
                <?php $i=0; foreach($values as $v): if($v['category']=='core_value' && $i<3): $i++; ?>
                    <div class="cv-item">
                        <div>
                            <i class="fas <?php echo $v['icon_name']; ?>"></i>
                            <h4><?php echo $v['title']; ?></h4>
                        </div>
                        <p><?php echo $v['description']; ?></p>
                    </div>
                <?php endif; endforeach; ?>
            </div>
            
            <!-- Center Circle -->
            <div class="circle">CORE<br>VALUES</div>
            
            <!-- Right Column (Next 3 values) -->
            <div class="cv-col right">
                <?php $i=0; foreach($values as $v): if($v['category']=='core_value'): $i++; if($i>3 && $i<=6): ?>
                    <div class="cv-item">
                        <div>
                            <i class="fas <?php echo $v['icon_name']; ?>"></i>
                            <h4><?php echo $v['title']; ?></h4>
                        </div>
                        <p><?php echo $v['description']; ?></p>
                    </div>
                <?php endif; endif; endforeach; ?>
            </div>

        </div>
    </div>
  <style>
.admin-btn{position:fixed;bottom:20px;right:20px;background:#222;color:#fff;padding:10px 20px;border-radius:30px;text-decoration:none;box-shadow:0 4px 10px rgba(0,0,0,.3);transition:.3s;z-index:100;}
</style>


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

