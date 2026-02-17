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

// Fetch Jobs with Category Names
$sql = "SELECT j.*, c.name as cat_name, s.name as sub_name 
        FROM jobs j 
        LEFT JOIN job_categories c ON j.category_id = c.id
        LEFT JOIN job_sub_categories s ON j.sub_category_id = s.id
        ORDER BY j.id DESC";
$jobs = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Openings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
      
        .job-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; max-width: 1200px; margin: 0 auto; }
        
        .card { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-top: 5px solid #C62828; display: flex; flex-direction: column; }
        
        /* IMAGE BOX STYLING */
        .img-box { 
            height: 200px; 
            background: #f4f4f4; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            overflow: hidden; 
            border-bottom: 1px solid #eee;
            position: relative;
        }
        .img-box img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            transition: transform 0.3s;
        }
        .card:hover .img-box img { transform: scale(1.05); }
        .img-box i { font-size: 3rem; color: #ccc; }
        
        .content { padding: 20px; flex-grow: 1; }
        .badge { background: #e3f2fd; color: #1565c0; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; }
        .title { margin: 10px 0; font-size: 1.3rem; color: #333; }
        .sub { color: #666; font-size: 0.9rem; display: flex; align-items: center; gap: 5px; }
        .salary { color: #2E7D32; font-weight: bold; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee; }
        
        .btn { display: block; background: #333; color: white; text-align: center; padding: 10px; margin: 20px; text-decoration: none; border-radius: 5px; transition: 0.2s; }
        .btn:hover { background: #C62828; }

        /* DEBUG TEXT STYLE (Hidden by default, shown if image missing) */
        .debug-path { 
            position: absolute; bottom: 0; left: 0; 
            background: rgba(0,0,0,0.8); color: white; 
            font-size: 10px; width: 100%; padding: 4px; 
            text-align: center; word-break: break-all;
        }
    </style>
</head>
<body>

    <h2 style="text-align:center; color:#333; margin-bottom:30px;">Current Job Openings</h2>

    <div class="job-grid">
        <?php foreach($jobs as $job): ?>
            <div class="card">
                
                <!-- AUTO-DETECT IMAGE LOGIC START -->
                <div class="img-box">
                    <?php 
                        $db_val = $job['photo_path'];
                        $filename = basename($db_val); // Get just "image.jpg" without old paths
                        
                        // Define all possible places the image could be
                        $possible_paths = [
                            "uploads/" . $filename,       // 1. Same folder > uploads
                            "../uploads/" . $filename,    // 2. Parent folder > uploads
                            "admin/uploads/" . $filename, // 3. Admin folder > uploads
                            $db_val                       // 4. Exact path stored in DB
                        ];

                        $final_src = "";

                        // Check which path actually exists
                        if (!empty($filename)) {
                            foreach ($possible_paths as $path) {
                                if (file_exists($path)) {
                                    $final_src = $path;
                                    break; // Found it! Stop looking.
                                }
                            }
                        }

                        // Display logic
                        if (!empty($final_src)) {
                            echo '<img src="' . htmlspecialchars($final_src) . '" alt="Job Photo">';
                        } else {
                            // If still not found, show icon and debug info
                            echo '<i class="fas fa-image" style="color:#ff9800;"></i>';
                            if(!empty($filename)) {
                                echo '<div class="debug-path">File not found.<br>Looking for: ' . htmlspecialchars($filename) . '</div>';
                            } else {
                                echo '<i class="fas fa-briefcase"></i>';
                            }
                        }
                    ?>
                </div>
                <!-- AUTO-DETECT IMAGE LOGIC END -->

                <div class="content">
                    <span class="badge"><?php echo htmlspecialchars($job['cat_name'] ?? 'General'); ?></span>
                    <h3 class="title"><?php echo htmlspecialchars($job['position']); ?></h3>
                    
                    <div class="sub">
                        <i class="fas fa-layer-group"></i> 
                        <?php echo htmlspecialchars($job['sub_name'] ?? '-'); ?>
                    </div>
                    
                    <div class="salary">
                        <i class="fas fa-money-bill-wave"></i> <?php echo htmlspecialchars($job['salary']); ?>
                    </div>
                </div>
                
                <a href="apply_now.php?id=<?php echo $job['id']; ?>" class="btn">Apply Now</a>
            </div>
        <?php endforeach; ?>
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