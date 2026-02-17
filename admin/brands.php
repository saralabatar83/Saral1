<?php
/**
 * Saral IT - Brand Manager
 */

require_once 'db.php';      
// require_once '../includes/functions.php'; // Uncomment if you need this

// --- 1. HANDLE BRAND DELETE ---
if (isset($_GET['delete_brand'])) {
    $id = (int)$_GET['delete_brand'];
    
    // 1. Get image path
    $stmt = $pdo->prepare("SELECT logo_path FROM top_brands WHERE id = ?");
    $stmt->execute([$id]);
    $brand = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Delete file if exists
    if ($brand) {
        $filePath = "uploads/" . $brand['logo_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // 3. Delete from Database
        $delStmt = $pdo->prepare("DELETE FROM top_brands WHERE id = ?");
        $delStmt->execute([$id]);
    }

    // 4. Refresh Page
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- 2. HANDLE ADD BRAND ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_brand'])) {
    $name = $_POST['brand_name'];
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $file = $_FILES['logo'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = "brand_" . time() . "." . $ext; 
        
        if (move_uploaded_file($file['tmp_name'], "uploads/" . $newName)) {
            $stmt = $pdo->prepare("INSERT INTO top_brands (brand_name, logo_path) VALUES (?, ?)");
            $stmt->execute([$name, $newName]);
            
            // Refresh Page
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// --- 3. FETCH BRANDS ---
$brands = $pdo->query("SELECT * FROM top_brands ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// --- 4. DATA AGGREGATION (For Sidebar Stats if needed) ---
// Kept this so your sidebar variables don't crash
$total_products = 0;
$total_orders = 0;
try {
    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
} catch (Exception $e) {}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brand Manager | Saral IT</title>
    
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
     
    <style>
        :root {
            --primary: #7986cb;
            --secondary: #5c6bc0;
            --bg-body: #f4f7f6;
            --sidebar-bg: #2c3e50;
            --text-main: #333;
            --white: #ffffff;
            --danger: #e74c3c;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg-body); display: flex; min-height: 100vh; color: var(--text-main); }

        /* Sidebar Navigation */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }
        .sidebar-header { padding: 25px; background: #1a252f; text-align: center; border-bottom: 3px solid var(--primary); }
        .sidebar-header h2 span { color: var(--primary); }
        .sidebar-menu { list-style: none; padding: 10px 0; }
        .sidebar-menu li a { display: flex; align-items: center; padding: 12px 20px; color: #bdc3c7; text-decoration: none; font-size: 14px; transition: 0.2s; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background: var(--primary); color: white; }
        .sidebar-menu i { margin-right: 12px; width: 20px; text-align: center; font-size: 16px; }
        .menu-divider { padding: 20px 20px 5px; font-size: 11px; text-transform: uppercase; color: #7f8c8d; font-weight: bold; letter-spacing: 1px; }

        /* Main Viewport */
        .main-view { margin-left: 260px; flex: 1; padding: 30px; width: calc(100% - 260px); }

        /* Brand Manager Styles */
        .admin-box1 { max-width: 900px; margin: 0 auto; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .input-group1 { display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px; align-items: end; }
        .input-group1 label { font-weight: bold; font-size: 14px; color: #555; }
        .input-group1 input { padding: 12px; border: 1px solid #ddd; border-radius: 8px; width: 100%; }
        
        .btn-add1 { background: #838de7; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: bold; height: 45px; }
        .btn-add1:hover { background: var(--secondary); }

        .brand-list1 { margin-top: 40px; display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 20px; }
        .brand-item1 { background: #fff; border: 1px solid #eee; padding: 15px; border-radius: 12px; text-align: center; position: relative; transition: 0.3s; }
        .brand-item1:hover { box-shadow: 0 5px 15px rgba(0,0,0,0.05); transform: translateY(-2px); }
        .brand-item1 img { width: 100%; height: 70px; object-fit: contain; margin-bottom: 10px; }
        .brand-name-display { display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px; color: #333; }
        
        .del-link1 { 
            color: #ff4757; font-size: 11px; text-decoration: none; display: inline-block; 
            padding: 4px 10px; background: #fff0f1; border-radius: 4px; font-weight: bold; 
        }
        .del-link1:hover { background: #ff4757; color: white; }

        /* Mobile Responsive */
        @media (max-width: 992px) {
            .sidebar { width: 70px; }
            .sidebar h2, .sidebar span, .menu-divider, .sidebar-menu span { display: none; }
            .sidebar-menu i { margin: 0; font-size: 20px; }
            .main-view { margin-left: 70px; width: calc(100% - 70px); }
            .input-group1 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Saral<span>Admin</span></h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-th-large"></i> <span>Dashboard</span></a></li>
            
            <div class="menu-divider">Catalog</div>
            <li><a href="admin_categories.php"><i class="fas fa-list-ul"></i> <span>Manage Cats</span></a></li>
            <li><a href="brands.php" class="active"><i class="fas fa-images"></i> <span>Top Brands</span></a></li>
            
            <div class="menu-divider">Marketing</div>
            <li><a href="offer.php"> <i class="fas fa-fire"></i> <span>Daily Offers</span></a></li>
            
            <div class="menu-divider">System</div>
            <li><a href="sociallinks.php"><i class="fas fa-hashtag"></i> <span>Social Links</span></a></li>
            <li><a href="admin_footer.php"><i class="fas fa-shoe-prints"></i> <span>Footer Manager</span></a></li>
            
            <div class="menu-divider">Account</div>
            <li><a href="change_password.php"><i class="fas fa-key"></i> <span>Security</span></a></li>
            <li><a href="admin_header.php"><i class="fas fa-user-shield"></i> <span>Admin Home</span></a></li>
            <li><a href="backend.php"><i class="fas fa-user-check"></i> <span>Customer Approve</span></a></li>
            <li><a href="logout.php" style="color: #ff7675;"><i class="fas fa-power-off"></i> <span>Logout</span></a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <div class="main-view">
        <div class="admin-box1">
            <h2 style="margin-top:0; color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px;">Manage Top Brands</h2>
            
            <!-- ADD BRAND FORM -->
            <form method="POST" enctype="multipart/form-data" class="input-group1">
                <div>
                    <label>Brand Name</label>
                    <input type="text" name="brand_name" required placeholder="Enter brand name">
                </div>
                <div>
                    <label>Logo</label>
                    <input type="file" name="logo" required>
                </div>
                <button type="submit" name="add_brand" class="btn-add1">UPLOAD</button>
            </form>

            <!-- LIST BRANDS -->
            <div class="brand-list1">
                <?php if(count($brands) > 0): ?>
                    <?php foreach($brands as $b): ?>
                    <div class="brand-item1">
                        <!-- Image -->
                        <img src="uploads/<?= htmlspecialchars($b['logo_path']) ?>" alt="Brand Logo">
                        
                        <!-- Name -->
                        <span class="brand-name-display"><?= htmlspecialchars($b['brand_name']) ?></span>
                        
                        <!-- Delete Link (Fixed to use delete_brand) -->
                        <a href="?delete_brand=<?= $b['id'] ?>" class="del-link1" onclick="return confirm('Are you sure you want to delete this brand?')">
                            <i class="fas fa-trash"></i> DELETE
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1/-1; text-align:center; color:#888;">No brands uploaded yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>