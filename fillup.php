<?php
/**
 * Saral IT Solution - Full Page (Header, Inquiry with Animated Border, Footer)
 */

// 1. DATABASE CONNECTION
require_once 'config/db.php'; 

// 2. FETCH DATA
try {
    // Branding & Header
    $branding = $pdo->query("SELECT * FROM site_branding WHERE id = 1")->fetch();
    $top_items = $pdo->query("SELECT * FROM header_top_bar ORDER BY sort_order ASC")->fetchAll();
    
    // Social Links
    $stmt_social = $pdo->query("SELECT * FROM social_links WHERE link_url != '#' AND link_url != ''");
    $social_links = $stmt_social->fetchAll(PDO::FETCH_ASSOC);

    // Footer Settings
    $stmt_settings = $pdo->prepare("SELECT setting_key, setting_value FROM site_settings");
    $stmt_settings->execute();
    $settings = $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $office_img = $settings['office_image'] ?? '';
    $office_link = $settings['office_image_link'] ?? '#';

    // Footer Links
    $stmt_links = $pdo->prepare("SELECT * FROM footer_links ORDER BY column_section ASC"); 
    $stmt_links->execute();
    $all_links = $stmt_links->fetchAll(PDO::FETCH_ASSOC);

    $link_columns = [];
    foreach ($all_links as $link) {
        $link_columns[$link['column_section']][] = $link;
    }

} catch (Exception $e) {
    // Silent fail if tables missing
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saral IT Solution</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="prince.css">
    <link rel="stylesheet" href="footer12.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-purple: #838de7;
            --nav-bg: #5e86b2;
            --nav-active: #0f0e0c;
            --text-dark: #333;
            --secondary-glow: #ff007f;
            --bg-dark: #080a12;
            --card-bg: rgba(18, 20, 33, 0.95);
            --input-bg: #1a1d2e;
            --text-white: #ffffff;
            --text-gray: #a0a0a0;
            --primary-glow: #00f2ff;
        }

        /* --- PAGE WRAPPER & INQUIRY CARD --- */
        .page-wrapper { 
            background: radial-gradient(circle at 50% -20%, #1a2a6c, #080a12); 
            min-height: 80vh; 
            display: flex; justify-content: center; align-items: center; 
            padding: 40px 20px;
        }

        .inquiry-card {
            background: var(--card-bg);
            width: 100%; max-width: 500px;
            padding: 40px; border-radius: 24px;
            position: relative; 
            /* overflow: hidden is needed to contain the rotating border */
            overflow: hidden; 
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8);
            color: white;
            z-index: 1;
        }

        /* --- ANIMATED BORDER CSS (Restored) --- */
        .inquiry-card::before {
            content: ''; 
            position: absolute; 
            top: -50%; 
            left: -50%; 
            width: 200%; 
            height: 200%;
            background: conic-gradient(transparent, transparent, transparent, var(--primary-glow));
            animation: rotate 6s linear infinite; 
            z-index: -2; /* Behind the card content */
        }

        /* Inner Mask (Makes the middle black so text is readable) */
        .inquiry-card::after {
            content: '';
            position: absolute;
            inset: 2px; /* This creates the thin border effect */
            background: var(--card-bg);
            border-radius: 22px;
            z-index: -1;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* --- TEXT & FORM STYLES --- */
        .inquiry-header h3 { 
            font-size: 32px; font-weight: 800; text-align: center; 
            background: linear-gradient(to right, #fff, var(--primary-glow)); 
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; 
            margin-bottom: 5px; 
        }
        .inquiry-header p { 
            color: var(--text-gray); text-align: center; font-size: 13px; 
            margin-bottom: 30px; text-transform: uppercase; 
        }

        .form-group { margin-bottom: 20px; }
        .form-group label { font-size: 12px; color: var(--primary-glow); font-weight: 600; margin-bottom: 8px; display: block; text-transform: uppercase; }
        
        .inquiry-input {
            width: 100%; padding: 14px 16px;
            background: var(--input-bg);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px; color: white;
            font-family: 'Poppins', sans-serif; font-size: 14px; outline: none;
        }
        .inquiry-input:focus { border-color: var(--primary-glow); background: #202436; }
        
        .btn-row { display: flex; gap: 12px; margin-top: 25px; }
        .submit-btn {
            padding: 16px; border: none; border-radius: 10px;
            color: white; font-size: 15px; font-weight: 600; cursor: pointer;
            text-transform: uppercase; flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-email { flex: 2; background: linear-gradient(45deg, var(--secondary-glow), #d500f9); }
        .btn-whatsapp { flex: 1; background: linear-gradient(45deg, #25D366, #128C7E); font-size: 22px; }

        @media (max-width: 992px) {
            .header-container { flex-direction: column; gap: 15px; }
        }
    </style>
</head>
<body>

<!-- 1. HEADER -->
<header class="prince-sticky-header">
    <div class="prince-top-bar">
        <div class="prince-container">
            <div class="prince-top-content">
                <?php foreach ($top_items as $item): ?>
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

    <div class="prince-main-header">
        <div class="prince-container">
            <div class="prince-header-grid">
                <a href="index.php" class="prince-logo-area">
                    <?php if (!empty($branding['logo'])): ?>
                        <img src="uploads/<?= htmlspecialchars($branding['logo']) ?>" alt="Logo" class="prince-circle-logo">
                    <?php endif; ?>
                    <span class="prince-brand-name">
                        <?= htmlspecialchars($branding['brand_name'] ?? 'Prince Brand') ?>
                    </span>
                </a>
                <form action="search.php" method="GET" class="prince-search-box">
                    <input type="text" name="q" placeholder="Search for products..." required>
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                <a href="login.php" class="prince-user-account">
                    <i class="fas fa-user"></i>
                    <span>ACCOUNT</span>
                </a>
            </div>
        </div>
    </div>

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

<!-- 2. INQUIRY FORM (With Animated Border) -->
<div class="page-wrapper">
    <div class="inquiry-card">
        <div class="inquiry-header">
            <h3>Quick Inquiry</h3>
            <p>Get a response within 2 hours</p>
        </div>
        
        <form id="inquiryForm">
            <div class="form-group">
                <label>Select Service</label>
                <select id="category" class="inquiry-input" required>
                    <option value="" disabled selected>-- How can we help? --</option>
                    <option value="Laptop">Laptop & Computer Repair</option>
                    <option value="CCTV">CCTV Security Repairs</option>
                    <option value="Repair">Expert Repair Services</option>
                    <option value="Networking">Networking </option>
                </select>
            </div>

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" id="customer_name" class="inquiry-input" placeholder="Enter your name" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" id="customer_phone" class="inquiry-input" placeholder="98XXXXXXXX" maxlength="10" required>
            </div>

            <div class="form-group">
                <label>Your Message</label>
                <textarea id="message" rows="3" class="inquiry-input" placeholder="Tell us what you need..."></textarea>
            </div>

            <div class="btn-row">
                <button type="button" onclick="handleInquiry('email')" class="submit-btn btn-email">
                    <i class="fa fa-envelope"></i> <span>Send Email</span>
                </button>
                <button type="button" onclick="handleInquiry('whatsapp')" class="submit-btn btn-whatsapp">
                    <i class="fab fa-whatsapp"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 3. FOOTER -->
<div class="sticky-social-bar">
    <?php foreach ($social_links as $row): ?>
        <a href="<?php echo htmlspecialchars($row['link_url']); ?>" 
           class="s-<?php echo strtolower($row['platform_name']); ?>" 
           target="_blank">
            <i class="fa-brands <?php echo htmlspecialchars($row['icon_class']); ?>"></i>
        </a>
    <?php endforeach; ?>
</div>

<footer class="site-footer">
    <div class="footer-content">
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

        <div class="footer-col office-col">
            <h3>OUR OFFICE</h3>
            <?php if (!empty($office_img) && file_exists($office_img)): ?>
                <a href="<?php echo htmlspecialchars($office_link); ?>">
                    <img src="<?php echo htmlspecialchars($office_img); ?>" alt="Office Logo" class="office-logo">
                </a>
                <div class="creator">
                    Code by <span class="heart">❤</span> 
                    <a href="https://prince15539.github.io/Ram-Abtar2625/" class="credit">Ram-Abtar</a>
                </div>
            <?php else: ?>
                <p style="color:#777; font-size:13px;">Image not found.</p>
            <?php endif; ?>
        </div>
    </div>
</footer>

<script>
function handleInquiry(method) {
    const cat = document.getElementById('category').value;
    const name = document.getElementById('customer_name').value;
    const phone = document.getElementById('customer_phone').value;
    const msg = document.getElementById('message').value;

    if (!cat || !name || !phone) { alert("Please fill in all fields."); return; }

    if (method === 'whatsapp') {
        const waNumber = "9744212267"; 
        const text = encodeURIComponent(`Inquiry from ${name} (${phone}) regarding ${cat}: ${msg}`);
        window.open(`https://wa.me/${waNumber}?text=${text}`, '_blank');
    } else {
        window.location.href = `mailto:saralabatar83@gmail.com?subject=Inquiry&body=${name} - ${phone} - ${msg}`;
    }
}
</script>

</body>
</html>