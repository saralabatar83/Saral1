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

// --- 1. FETCH PAGE CONTENT (Hero Image/Title) ---
// We try to fetch the specific 'help_hero' section. 
// If it doesn't exist in DB, we fall back to defaults.
$stmt = $pdo->prepare("SELECT * FROM page_content WHERE section_key = ?");
$stmt->execute(['help_hero']); 
$pageData = $stmt->fetch(PDO::FETCH_ASSOC);

$heroTitle = $pageData['title'] ?? 'How can we help you?';
$heroImage = $pageData['image_url'] ?? '';

// Hero Style Logic
$heroStyle = "background-color: #001f3f;"; // Default Navy Blue
if (!empty($heroImage)) {
    $heroStyle = "background: url('$heroImage') no-repeat center center/cover;";
}

// --- 2. FETCH FAQs ---
$stmt = $pdo->query("SELECT * FROM faqs ORDER BY display_order ASC");
$faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center | Mudita Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- GENERAL --- */
   

        /* --- HERO SECTION --- */
        .hero {
            <?php echo $heroStyle; ?>
            height: 250px; /* Taller for Help Center */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            text-align: center;
            color: white;
        }
        .hero::after { content:''; position:absolute; inset:0; background:rgba(0,0,0,0.6); }
        
        .hero-content { position: relative; z-index: 2; width: 100%; max-width: 700px; padding: 0 20px; }
        
        .hero h1 { 
            font-size: 2.8rem; margin: 0 0 10px 0; 
            text-shadow: 2px 2px 10px rgba(0,0,0,0.5);
        }

        /* --- SEARCH BAR --- */
        .search-box {
            position: relative;
            width: 100%;
            max-width: 600px;
            margin: 20px auto 0;
        }
        .search-box input {
            width: 100%;
            padding: 15px 20px;
            padding-right: 50px;
            border-radius: 30px;
            border: none;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            outline: none;
        }
        .search-box i {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #C62828;
            font-size: 1.2rem;
        }

        /* --- SUPPORT CARDS (Contact) --- */
        .support-options {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: -40px; /* Overlap hero */
            position: relative;
            z-index: 3;
            padding: 0 20px;
        }
        .support-card {
            background: white;
            padding: 25px;
            width: 250px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            border-bottom: 4px solid #C62828;
        }
        .support-card:hover { transform: translateY(-5px); }
        .support-card i { font-size: 2rem; color: #C62828; margin-bottom: 15px; }
        .support-card h3 { margin: 10px 0; color: #333; }
        .support-card p { margin: 0; color: #666; font-size: 0.9rem; }
        .support-card a { text-decoration: none; color: #001f3f; font-weight: bold; display: block; margin-top: 10px; }

        /* --- FAQ SECTION --- */
        .faq-section {
            max-width: 900px;
            margin: 60px auto;
            padding: 0 20px;
        }
        .section-title { text-align: center; margin-bottom: 40px; color: #001f3f; font-size: 2rem; }

        .accordion-item {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            margin-bottom: 10px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .accordion-header {
            background: white;
            padding: 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s;
        }
        .accordion-header:hover { background: #f9f9f9; }
        .accordion-header h4 { margin: 0; font-size: 1.1rem; color: #333; }
        .accordion-header i { transition: transform 0.3s; color: #C62828; }

        /* Active State */
        .accordion-item.active .accordion-header { background: #f4f4f4; border-bottom: 1px solid #ddd; }
        .accordion-item.active .accordion-header i { transform: rotate(180deg); }
        
        .accordion-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out, padding 0.3s ease;
            background: #fff;
            padding: 0 20px;
        }
        
        .accordion-item.active .accordion-body {
            max-height: 500px; /* Adjust if answers are very long */
            padding: 20px;
        }
        
        /* Render HTML inside answers correctly */
        .accordion-body p { margin: 0; line-height: 1.6; color: #555; }
        .accordion-body a { color: #C62828; }

        /* --- ADMIN BTN --- */
        .admin-btn {
            position: fixed; bottom: 20px; right: 20px;
            background: #222; color: white; padding: 10px 20px;
            border-radius: 30px; text-decoration: none;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            transition: 0.3s; z-index: 100;
        }
        .admin-btn:hover { background: #C62828; transform: translateY(-2px); }

    </style>
</head>
<body>

    <!-- Hero with Search -->
    <div class="hero">
        <div class="hero-content">
            <h1><?php echo htmlspecialchars($heroTitle); ?></h1>
            
        </div>
    </div>

    <!-- Contact Options -->
    <div class="support-options">
        <div class="support-card">
            <i class="fas fa-phone-alt"></i>
            <h3>Call Us</h3>
            <p>Mon-Fri, 9am - 6pm</p>
            <a href="tel:+9779800000000">+977 980 000 0000</a>
        </div>
        <div class="support-card">
            <i class="fas fa-envelope"></i>
            <h3>Email Us</h3>
            <p>We usually reply within 24hrs</p>
            <a href="mailto:support@mudita.com">support@mudita.com</a>
        </div>
        <div class="support-card">
            <i class="fas fa-map-marker-alt"></i>
            <h3>Visit Us</h3>
            <p>Kathmandu, Nepal</p>
            <a href="#">Get Directions</a>
        </div>
    </div>

    <!-- FAQ Accordion -->
    <div class="faq-section">
        <h2 class="section-title">Frequently Asked Questions</h2>
        
        <div id="faqContainer">
            <?php if(count($faqs) > 0): ?>
                <?php foreach($faqs as $faq): ?>
                    <div class="accordion-item">
                        <div class="accordion-header">
                            <h4><?php echo htmlspecialchars($faq['question']); ?></h4>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="accordion-body">
                            <!-- html_entity_decode allows you to use <b> or links in your DB answers -->
                            <?php echo html_entity_decode($faq['answer']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center;">No FAQs found. Please check back later.</p>
            <?php endif; ?>
        </div>
    </div>

    

    <!-- JavaScript for Accordion & Search -->
    <script>
        // 1. Accordion Logic
        const headers = document.querySelectorAll('.accordion-header');

        headers.forEach(header => {
            header.addEventListener('click', () => {
                const item = header.parentElement;
                
                // Optional: Close others when opening one (Accordion Style)
                // document.querySelectorAll('.accordion-item').forEach(i => {
                //     if(i !== item) i.classList.remove('active');
                // });

                item.classList.toggle('active');
            });
        });

        // 2. Simple Client-Side Search
        const searchInput = document.getElementById('faqSearch');
        const faqContainer = document.getElementById('faqContainer');
        const faqItems = document.querySelectorAll('.accordion-item');

        searchInput.addEventListener('keyup', (e) => {
            const term = e.target.value.toLowerCase();

            faqItems.forEach(item => {
                const question = item.querySelector('h4').textContent.toLowerCase();
                const answer = item.querySelector('.accordion-body').textContent.toLowerCase();

                if(question.includes(term) || answer.includes(term)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>

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