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
// ==========================================
// 1. BACKEND LOGIC (PHP)
// ==========================================
include 'db.php';

$message = "";
$messageType = ""; 

// Get Job ID and Title
if (isset($_GET['id'])) {
    $job_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT position FROM jobs WHERE id = ?");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$job) { die("Job not found."); }
} else {
    header("Location: index.php");
    exit();
}

// Handle Database Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $cover = $_POST['cover_letter'];
    $job_id_post = $_POST['job_id'];

    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        $allowed = ['pdf', 'doc', 'docx'];
        $filename = $_FILES['resume']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        if (in_array(strtolower($ext), $allowed)) {
            if (!is_dir('uploads/resumes')) { mkdir('uploads/resumes', 0777, true); }
            $new_filename = time() . "_" . $filename;
            $destination = "uploads/resumes/" . $new_filename;

            if (move_uploaded_file($_FILES['resume']['tmp_name'], $destination)) {
                // Insert into DB
                $sql = "INSERT INTO applications (job_id, applicant_name, applicant_email, cover_letter, resume_path) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([$job_id_post, $name, $email, $cover, $destination])) {
                    // Send Email via PHP (Backend)
                    $to = "mandalramabatar15@gmail.com"; 
                    $subject = "New Application: " . $job['position'];
                    $resume_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/" . $destination;
                    $email_content = "Name: $name\nEmail: $email\nLink: $resume_link";
                    $headers = "From: no-reply@jobportal.com";
                    @mail($to, $subject, $email_content, $headers);

                    $message = "Application submitted to Database successfully!";
                    $messageType = "success";
                } else {
                    $message = "Database error.";
                    $messageType = "error";
                }
            } else {
                $message = "File upload failed.";
                $messageType = "error";
            }
        } else {
            $message = "Invalid file type.";
            $messageType = "error";
        }
    } else {
        $message = "Please upload a resume.";
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply - <?php echo htmlspecialchars($job['position']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Changed :root to .root1 */
        .root1 { 
            --primary: #dbdae2; 
            --bg-gradient: linear-gradient(135deg, white 0%, #98929e 100%); 
        }

        /* Changed body to .body1 and scoped other styles inside it */
        .body1 { 
            font-family: 'Poppins', sans-serif; 
            background: var(--bg-gradient); 
            min-height: 100vh; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            padding: 20px; 
            margin: 0; 
        }
        
        .body1 .card { background: white; width: 100%; max-width: 550px; border-radius: 16px; box-shadow: 0 15px 30px rgba(0,0,0,0.2); overflow: hidden; }
        .body1 .card-header { background: #f9fafb; padding: 25px 30px; border-bottom: 1px solid #e5e7eb; }
        .body1 .back-link { text-decoration: none; color: #6b7280; font-size: 14px; display: block; margin-bottom: 10px; }
        
        .body1 h2 { margin: 0; color: #1f2937; font-size: 22px; }
        .body1 .job-title { color: #555; font-weight: bold; } /* Fallback color or use specific hex if var doesn't cascade */
        .body1 .card-body { padding: 30px; }

        .body1 .form-group { margin-bottom: 15px; }
        .body1 label { display: block; font-weight: 500; color: #374151; margin-bottom: 5px; font-size: 14px; }
        
        .body1 input[type="text"], 
        .body1 input[type="email"], 
        .body1 textarea { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #d1d5db; 
            border-radius: 8px; 
            box-sizing: border-box; 
            font-family: inherit; 
        }
        
        .body1 input:focus, 
        .body1 textarea:focus { 
            outline: none; 
            border-color: #98929e; 
        }
        
        /* Main Submit Button */
        .body1 .btn-submit { width: 100%; background: var(--primary); color: #333; padding: 12px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; transition: 0.3s; }
        .body1 .btn-submit:hover { background: #d4d1f6; }

        /* OR Divider */
        .body1 .divider { text-align: center; margin: 20px 0; position: relative; color: #9ca3af; font-size: 13px; }
        .body1 .divider::before, .body1 .divider::after { content: ""; position: absolute; top: 50%; width: 40%; height: 1px; background: #e5e7eb; }
        .body1 .divider::before { left: 0; }
        .body1 .divider::after { right: 0; }

        /* Direct Buttons Grid */
        .body1 .direct-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        
        .body1 .btn-direct { display: flex; align-items: center; justify-content: center; padding: 10px; border-radius: 8px; border: none; cursor: pointer; font-weight: 500; font-size: 14px; transition: 0.3s; color: white; font-family: 'Poppins', sans-serif; }
        .body1 .btn-direct i { margin-right: 8px; font-size: 16px; }
        
        .body1 .btn-whatsapp { background: #25D366; }
        .body1 .btn-whatsapp:hover { background: #1ebc57; }
        
        .body1 .btn-email { background: #EA4335; }
        .body1 .btn-email:hover { background: #d63022; }

        .body1 .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .body1 .alert-success { background: #ecfdf5; color: #065f46; }
        .body1 .alert-error { background: #fef2f2; color: #991b1b; }
    </style>
</head>
<!-- Removed generic body tag styling, used a wrapper class instead -->
<body>

    <!-- Wrapped everything in root1 and body1 classes -->
    <div class="root1">
        <div class="body1">

            <div class="card">
                <div class="card-header">
                    <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Jobs</a>
                    <?php if ($messageType != 'success'): ?>
                        <h2>Apply for <span class="job-title"><?php echo htmlspecialchars($job['position']); ?></span></h2>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($messageType != 'success'): ?>
                        
                        <!-- 1. DATABASE FORM -->
                        <form action="" method="POST" enctype="multipart/form-data">
                            <input type="hidden" id="job_position" value="<?php echo htmlspecialchars($job['position']); ?>">
                            <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job_id); ?>">

                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="name" id="app_name" required placeholder="Ramabtar">
                            </div>

                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email" id="app_email" required placeholder="@example.com">
                            </div>

                            <div class="form-group">
                                <label>Resume </label>
                                <input type="file" name="resume" required accept=".pdf,.doc,.docx" style="padding: 10px 0;">
                            </div>
                            <div class="form-group">
    <label>Citizenship Photo</label>
    <input type="file" name="citizenship" required accept=".jpg,.jpeg,.png,.pdf" style="padding: 10px 0;">
</div>


                            <div class="form-group">
                                <label>Cover Letter</label>
                                <textarea name="cover_letter" id="app_msg" rows="3" placeholder="Short introduction..."></textarea>
                            </div>

                            <!-- I ADDED THIS BUTTON BACK SO THE FORM WORKS -->
                            <button type="submit" class="btn-submit">Submit to Database</button>
                        </form>

                        <!-- 2. DIRECT ACTION BUTTONS (JS) -->
                        <div class="divider">OR SEND DIRECTLY</div>
                        
                        <div class="direct-actions">
                            <!-- WhatsApp Button -->
                            <button type="button" class="btn-direct btn-whatsapp" onclick="handleInquiry('whatsapp')">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </button>
                            
                            <!-- Email App Button -->
                            <button type="button" class="btn-direct btn-email" onclick="handleInquiry('email')">
                                <i class="fas fa-envelope"></i> Email App
                            </button>
                        </div>

                    <?php else: ?>
                        <!-- Success State -->
                        <div style="text-align:center; padding: 20px;">
                            <i class="fas fa-check-circle" style="font-size: 50px; color: #10b981;"></i>
                            <h3>Application Saved!</h3>
                            <p>Your details are in our database.</p>
                            <a href="index.php" class="back-link" style="color: #667eea; font-weight:600;">Browse More Jobs</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div> <!-- End body1 -->
    </div> <!-- End root1 -->

    <!-- JAVASCRIPT FOR DIRECT INQUIRY -->
    <script>
        function handleInquiry(method) {
            // Get Values from the Form Inputs using IDs
            const position = document.getElementById('job_position').value;
            const name = document.getElementById('app_name').value.trim();
            const email = document.getElementById('app_email').value.trim();
            const msg = document.getElementById('app_msg').value.trim();

            // Simple Validation
            if (!name || !email) {
                alert("⚠️ Please fill in Name and Email first.");
                return;
            }

            // WhatsApp Logic
            if (method === 'whatsapp') {
                const waNumber = "9744212267"; // YOUR WHATSAPP NUMBER
                
                const text = encodeURIComponent(
                    `*NEW JOB APPLICATION*\n━━━━━━━━━━━━\n` +
                    `💼 *Role:* ${position}\n` +
                    `👤 *Name:* ${name}\n` +
                    `📧 *Email:* ${email}\n` +
                    `💬 *Note:* ${msg}\n` +
                    `--------------------\n` +
                    `*I will attach my Resume in the next message.*`
                );
                
                // Opens WhatsApp
                window.open(`https://wa.me/${waNumber}?text=${text}`, '_blank');
            } 
            // Email App Logic (mailto)
            else {
                const recipient = "mandalramabatar15@gmail.com"; // YOUR EMAIL
                const subject = encodeURIComponent(`Application for: ${position} - ${name}`);
                const body = encodeURIComponent(
                    `Dear HR,\n\nI am applying for the position of ${position}.\n\n` +
                    `Name: ${name}\n` +
                    `Email: ${email}\n\n` +
                    `Cover Letter:\n${msg}\n\n` +
                    `[Please attach your Resume file manually to this email before sending]`
                );
                
                // Opens Outlook/Gmail App
                window.location.href = `mailto:${recipient}?subject=${subject}&body=${body}`;
            }
        }
    </script>

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