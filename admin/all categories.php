<?php
/**
 * Saral IT - Admin Dashboard
 */

require_once 'db.php';      
require_once '../includes/functions.php'; 

// --- 1. ACTION HANDLERS ---
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name     = htmlspecialchars($_POST['name']);
    $cat      = htmlspecialchars($_POST['category']);
    $price    = (float)$_POST['price'];
    $label    = htmlspecialchars($_POST['discount']);
    $img      = htmlspecialchars($_POST['image']);

    $sql = "INSERT INTO products (name, category, price, sale_label, image_url) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$name, $cat, $price, $label, $img])) {
        $message = "Product added successfully!";
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: dashboard.php?msg=deleted");
    exit();
}

// --- 2. DATA AGGREGATION ---
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_orders   = 0; 
$revenue        = 0;

try {
    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $revenue      = $pdo->query("SELECT SUM(amount) FROM orders")->fetchColumn();
} catch (Exception $e) { }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saral IT - Managed Admin</title>
    
    <!-- Fonts & Icons -->
     <link
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- External CSS -->
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
  <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Saral<span>Admin</span></h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-th-large"></i> <span>Dashboard</span></a></li>
            
            <div class="menu-divider">Catalog</div>
       
            <li><a href="admin_categories.php"><i class="fas fa-list-ul"></i> <span>Manage Cats</span></a></li>
            <li><a href="brands.php"><i class="fas fa-images"></i> <span>Top Brands</span></a></li>

            <div class="menu-divider">Marketing</div>
            <li><a href="offer.php"><i class="fas fa-fire"></i> <span>Daily Offers</span></a></li>
            
            
            <div class="menu-divider">System</div>
           
           
            <li><a href="sociallinks.php"><i class="fas fa-hashtag"></i> <span>Social Links</span></a></li>
            <li><a href="top_setting.php"><i class="fas fa-cog"></i> <span>Header Settings</span></a></li>
            <li><a href="admin_footer.php"><i class="fas fa-shoe-prints"></i> <span>Footer Manager</span></a></li>
            
            <div class="menu-divider">Account</div>
            <li><a href="change_password.php"><i class="fas fa-key"></i> <span>Security</span></a></li>
             <li><a href="admin_header.php"><i class="fas fa-user-shield"></i> <span>Admin Home</span></a></li>
            <li><a href="logout.php" style="color: #ff7675;"><i class="fas fa-power-off"></i> <span>Logout</span></a></li>
           
        </ul>
    </aside>


        <!-- Remaining Dashboard Content goes here (Table/Form) -->
    </main>

</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saral IT - Catalog Selection</title>
    
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #7986cb;
            --bg-body: #f8fafc;
            --text-main: #1e293b;
            --white: #ffffff;
            --border: #e2e8f0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        
       

        .container { 
            max-width: 1100px;
            margin: 0 auto;
        }

        /* --- HEADER SECTION --- */
        .page-header {
            margin-bottom: 40px;
            border-left: 5px solid var(--primary);
            padding-left: 20px;
        }
        .page-header h1 { 
            font-size: 28px; 
            font-weight: 800; 
            letter-spacing: -0.5px;
            color: #0f172a;
        }
        .page-header p { 
            color: #64748b; 
            font-size: 14px; 
            margin-top: 4px; 
        }

        /* --- COMPACT CATEGORY GRID --- */
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 20px;
        }

        .category-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: var(--white);
            border-radius: 12px;
            padding: 30px 15px;
            text-decoration: none;
            color: var(--text-main);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
        }

        .category-card i {
            font-size: 32px;
            margin-bottom: 15px;
            color: #94a3b8;
            transition: 0.3s;
        }

        .category-card span {
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
        }

        /* Hover States */
        .category-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 10px 15px -3px rgba(121, 134, 203, 0.2);
        }

        .category-card:hover i {
            color: var(--primary);
        }

        /* Responsive Fix for mobile */
        @media (max-width: 480px) {
            .category-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
        }
    </style>
</head>
<body>

    <main class="container">
        <!-- Section Header -->
        <header class="page-header">
            <h1>Catalog Manager</h1>
            <p>Select a category to manage your store products</p>
        </header>

        <!-- Category Items -->
        <div class="category-grid">
            <?php
            $categories = [
                ['title' => 'Laptops', 'icon' => 'fa-laptop', 'link' => 'Laptop.php'],
                ['title' => 'Printers', 'icon' => 'fa-print', 'link' => 'printer.php'],
                ['title' => 'CCTV Cameras', 'icon' => 'fa-video', 'link' => 'cctv.php'],
                ['title' => 'Networking', 'icon' => 'fa-network-wired', 'link' => 'Networking.php'],
                ['title' => 'Electronics', 'icon' => 'fa-microchip', 'link' => 'admin_electronic.php'],
                ['title' => 'Accessories', 'icon' => 'fa-keyboard', 'link' => 'admin_accessories.php'],
                ['title' => 'Services', 'icon' => 'fa-screwdriver-wrench', 'link' => 'admin_services.php']
            ];

            foreach ($categories as $cat): ?>
                <a href="<?= $cat['link'] ?>" class="category-card">
                    <i class="fa-solid <?= $cat['icon'] ?>"></i>
                    <span><?= $cat['title'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </main>

</body>
</html>