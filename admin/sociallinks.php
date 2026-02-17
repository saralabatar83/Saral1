<?php 
// 1. Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Include database connection 
require_once 'db.php'; 

// Bridge: Ensure $pdo is available
if (!isset($pdo)) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=saral_db", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Database Connection Error: " . $e->getMessage());
    }
}

// 3. --- LOGIC: UPDATE LINKS ---
$message = "";
if (isset($_POST['update_links'])) {
    try {
        $stmt = $pdo->prepare("UPDATE social_links SET link_url = ? WHERE id = ?");
        foreach ($_POST['links'] as $id => $url) {
            $stmt->execute([$url, $id]);
        }
        $message = "All social links updated successfully!";
    } catch (Exception $e) {
        $message = "Update Failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saral IT - Social Links</title>
    
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
     
    <style>
        :root {
            --primary: #7986cb;
            --secondary: #5c6bc0;
            --bg-body: #f4f7f6;
            --sidebar-bg: #2c3e50;
            --sidebar-hover: #34495e;
            --text-main: #333;
            --white: #ffffff;
            --danger: #e74c3c;
            --success: #27ae60;
            --sidebar-width: 260px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        
        body { 
            background: var(--bg-body); 
            display: flex; 
            min-height: 100vh; 
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* --- FIXED SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: white;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 25px;
            background: #1a252f;
            text-align: center;
            border-bottom: 3px solid var(--primary);
            position: sticky; top: 0; z-index: 10;
        }
        .sidebar-header h2 span { color: var(--primary); }

        .sidebar-menu { list-style: none; padding: 10px 0 100px 0; }
        .sidebar-menu li a {
            display: flex; align-items: center; padding: 12px 20px;
            color: #bdc3c7; text-decoration: none; font-size: 14px; transition: 0.2s;
        }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background: var(--primary); color: white; }
        .sidebar-menu i { margin-right: 12px; width: 20px; text-align: center; font-size: 16px; }
        
        .menu-divider { 
            padding: 20px 20px 5px; font-size: 11px; text-transform: uppercase; 
            color: #7f8c8d; font-weight: bold; letter-spacing: 1px;
        }

        /* --- MAIN VIEWPORT --- */
        .main-view { 
            margin-left: var(--sidebar-width); 
            flex: 1; 
            padding: 30px; 
            width: calc(100% - var(--sidebar-width)); 
            min-height: 100vh;
        }

        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }

        /* --- UI PANELS --- */
        .panel { background: var(--white); padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); max-width: 800px; margin: 0 auto; }
        .panel h2 { margin-bottom: 25px; font-size: 18px; color: var(--sidebar-bg); border-bottom: 1px solid #eee; padding-bottom: 15px; display: flex; align-items: center; gap: 10px; }

        .social-row { 
            display: flex; 
            align-items: flex-start; 
            gap: 20px; 
            margin-bottom: 25px; 
            padding-bottom: 20px; 
            border-bottom: 1px solid #f5f5f5; 
        }
        .social-row:last-child { border-bottom: none; }

        .icon-container {
            width: 50px;
            height: 50px;
            background: #f8f9fa;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .input-group { flex: 1; }
        .input-group label { display: block; font-weight: 700; font-size: 13px; margin-bottom: 8px; color: #555; }
        .input-group input { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            outline: none; 
            font-size: 14px; 
            transition: 0.3s;
        }
        .input-group input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(121, 134, 203, 0.1); }
        .input-group small { color: #888; font-size: 11px; margin-top: 5px; display: block; }

        .btn-save { 
            background: var(--primary); 
            color: white; 
            border: none; 
            padding: 15px; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: 700; 
            width: 100%; 
            font-size: 15px; 
            transition: 0.3s;
            margin-top: 10px;
        }
        .btn-save:hover { background: var(--secondary); transform: translateY(-1px); }

        .alert { 
            padding: 15px; background: #d4edda; color: #155724; border-radius: 8px; 
            margin-bottom: 25px; border-left: 5px solid #28a745; display: flex; align-items: center; gap: 10px;
        }

        @media (max-width: 992px) {
            :root { --sidebar-width: 70px; }
            .sidebar h2, .sidebar span, .menu-divider { display: none; }
            .sidebar-menu i { margin: 0; font-size: 20px; width: 100%; }
            .sidebar-menu li a { justify-content: center; padding: 20px 0; }
        }
    </style>
</head>
<body>

    <!-- FIXED SIDEBAR -->
           <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Saral<span>Admin</span></h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-th-large"></i> <span>Dashboard</span></a></li>
            
            <div class="menu-divider">Catalog</div>
          
            <li><a href="admin_categories.php"><i class="fas fa-list-ul"></i> <span>Manage Cats</span></a></li>
            <li><a href="brands.php"><i class="fas fa-images"></i> <span>Top Brands</span></a></li>
<!-- Marketing Section -->
<div class="menu-divider">Marketing</div>
<li><a href="offer.php"> <i class="fas fa-fire"></i> <span>Daily Offers</span>
    </a>
</li>

    </a>
</li>

</li>
            
            <div class="menu-divider">System</div>
            <li><a href="sociallinks.php"><i class="fas fa-hashtag"></i> <span>Social Links</span></a></li>
           
            <li><a href="admin_footer.php"><i class="fas fa-shoe-prints"></i> <span>Footer Manager</span></a></li>
            
            <div class="menu-divider">Account</div>
            <li><a href="change_password.php"><i class="fas fa-key"></i> <span>Security</span></a></li>
             <li><a href="admin_header.php"><i class="fas fa-user-shield"></i> <span>Admin Home</span></a></li>
            <li> <a href="backend.php"><i class="fas fa-user-check"></i> <span>Customer Approve</span> </a></li>
             <li><a href="logout.php" style="color: #ff7675;"><i class="fas fa-power-off"></i> <span>Logout</span></a></li>
           
        </ul>
    </aside>
    <main class="main-view">
        <header class="header-top">
            <h1>Social Media Integration</h1>
            <div class="admin-badge" style="background: white; padding: 10px 20px; border-radius: 30px; font-weight: 600; font-size: 13px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <i class="fas fa-share-nodes" style="color: var(--primary);"></i> External Links
            </div>
        </header>

        <!-- Feedback Alert -->
        <?php if($message): ?>
            <div class='alert'>
                <i class='fas fa-check-circle'></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="panel">
            <h2><i class="fas fa-link" style="color: var(--primary);"></i> Manage Platforms</h2>
            
            <form method="POST" action="sociallinks.php">
                <?php
                $stmt = $pdo->query("SELECT * FROM social_links");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($rows as $row):
                    // Logic for custom brand colors
                    $brandColor = '#7474d1'; // Default
                    if ($row['platform_name'] == 'WhatsApp') $brandColor = '#25D366';
                    if ($row['platform_name'] == 'Facebook') $brandColor = '#1877F2';
                    if ($row['platform_name'] == 'Instagram') $brandColor = '#E4405F';
                    if ($row['platform_name'] == 'YouTube') $brandColor = '#FF0000';
                ?>
                    <div class="social-row">
                        <div class="icon-container" style="color: <?= $brandColor ?>;">
                            <i class="fa-brands <?= htmlspecialchars($row['icon_class']) ?>"></i>
                        </div>
                        <div class="input-group">
                            <label><?= htmlspecialchars($row['platform_name']) ?></label>
                            <input type="text" 
                                   name="links[<?= $row['id'] ?>]" 
                                   value="<?= htmlspecialchars($row['link_url'] ?? '') ?>" 
                                   placeholder="<?= $row['platform_name'] == 'WhatsApp' ? 'https://wa.me/9800000000' : 'https://...' ?>">
                            
                            <?php if($row['platform_name'] == 'WhatsApp'): ?>
                                <small>Format: <code>https://wa.me/yourphonenumber</code> (include country code without +)</small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <button type="submit" name="update_links" class="btn-save">
                    <i class="fas fa-save" style="margin-right: 8px;"></i> SAVE ALL CHANGES
                </button>
            </form>
        </div>
    </main>

</body>
</html>