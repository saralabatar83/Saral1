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
// 1. Unified Database Connection
require_once 'admin/db.php'; 

// 2. Fetch Item Detail Logic
$table = $_GET['table'] ?? '';
$id = (int)($_GET['id'] ?? 0);

// Security check for table names
$allowed = ['offers_sec2', 'offers_sec3', 'offers_sec4'];
if(!in_array($table, $allowed) || $id == 0) {
    die("Invalid request selection.");
}

$stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();

if(!$item) die("The requested item was not found.");

// 3. WhatsApp Message Logic
$phoneNumber = "9800000000"; // Replace with your actual number
$productTitle = htmlspecialchars($item['title']);
$pageUrl = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$wa_text = "Hello, I am interested in: " . $productTitle . ".\nLink: " . $pageUrl;
$wa_link = "https://wa.me/" . $phoneNumber . "?text=" . urlencode($wa_text);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($item['title']) ?> | Details</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-blue: #2563eb;
            --wa-green: #25D366;
            --soft-bg: #f8fafc;
        }

   

        /* --- PRINCE1 DETAIL CARD STYLING --- */
        .prince1-wrapper {
            padding: 20px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .prince1-breadcrumb {
            margin-bottom: 20px;
            font-size: 14px;
        }
        .prince1-breadcrumb a { color: var(--primary-blue); text-decoration: none; font-weight: 600; }

        .prince1 { 
            background: #fff; 
            border-radius: 20px; 
            display: flex; 
            overflow: hidden; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            min-height: 450px;
        }

        /* Photo Side (Managed for small/medium size) */
        .prince1 .img-container { 
            flex: 0 0 40%; 
            background: #fafafa;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            border-right: 1px solid #f0f0f0;
        }
        
        .prince1 .img-container img { 
            max-width: 100%; 
            max-height: 380px; 
            width: auto;
            border-radius: 12px;
            object-fit: contain; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        /* Content Side */
        .prince1 .content { 
            flex: 1;
            padding: 40px; 
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .prince1 h1 { font-size: 30px; margin: 0 0 10px 0; color: #0f172a; line-height: 1.2; }
        
        .prince1 .short-desc { 
            font-size: 18px; 
            color: #64748b; 
            margin-bottom: 25px;
            font-weight: 500;
            border-left: 4px solid var(--primary-blue);
            padding-left: 15px;
        }

        .prince1 .long-desc { 
            font-size: 16px; 
            color: #475569; 
            margin-bottom: 35px;
            white-space: pre-line;
            line-height: 1.8;
        }

        /* WhatsApp Action */
        .wa-btn { 
            align-self: flex-start;
            display: inline-flex; 
            align-items: center; 
            gap: 12px;
            background: var(--wa-green); 
            color: #fff; 
            padding: 16px 32px; 
            border-radius: 50px; 
            text-decoration: none; 
            font-weight: 700; 
            font-size: 16px;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 15px -3px rgba(37, 211, 102, 0.2);
        }
        .wa-btn:hover { background: #1eb954; transform: translateY(-3px); box-shadow: 0 20px 25px -5px rgba(37, 211, 102, 0.3); }

        /* Responsive Design */
        @media (max-width: 900px) {
            .prince1 { flex-direction: column; }
            .prince1 .img-container { flex: none; width: 100%; border-right: none; border-bottom: 1px solid #f0f0f0; padding: 20px; }
            .prince1 .img-container img { max-height: 280px; }
            .prince1 .content { padding: 30px; }
            .wa-btn { width: 100%; justify-content: center; box-sizing: border-box; }
        }
    </style>
</head>
<body>

<main class="prince1-wrapper">
    <!-- Breadcrumb -->
    <div class="prince1-breadcrumb">
        <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
    </div>

    <!-- The prince1 Detail Card -->
    <section class="prince1">
        <!-- Photo Side -->
        <div class="img-container">
            <img src="admin/uploads/<?= htmlspecialchars($item['image_path']) ?>" 
                 alt="<?= htmlspecialchars($item['title']) ?>"
                 onerror="this.src='https://via.placeholder.com/400x400?text=No+Image+Available'">
        </div>

        <!-- Text Content Side -->
        <div class="content">
            <h1><?= htmlspecialchars($item['title']) ?></h1>
            <div class="short-desc"><?= htmlspecialchars($item['short_desc']) ?></div>
            
            <div class="long-desc">
                <?= nl2br(htmlspecialchars($item['long_desc'])) ?>
            </div>

            <!-- WhatsApp Action -->
            <a href="<?= $wa_link ?>" target="_blank" class="wa-btn">
                <i class="fab fa-whatsapp" style="font-size: 24px;"></i> 
                Quick Inquiry on WhatsApp
            </a>
        </div>
    </section>
</main>

<footer style="text-align: center; padding: 40px 20px; color: #94a3b8; font-size: 14px;">
    &copy; <?= date('Y') ?> Your Company | Quality Service
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