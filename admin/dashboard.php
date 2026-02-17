
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
     <li>
    <a href="backend.php"><i class="fas fa-user-check"></i> <span>Customer Approve</span> </a>
</li>
<li>
    <a href="logout.php" style="color: #ff7675;"><i class="fas fa-power-off"></i> <span>Logout</span></a>
</li>

        </ul>
    </aside>

        <!-- Remaining Dashboard Content goes here (Table/Form) -->
    </main>

</body>
<?php
/**
 * Saral IT - Ultimate Master Dashboard (FIXED SEARCH)
 */

require_once 'db.php'; 

// --- 1. DATA GATHERING ---
function getCount($pdo, $table) {
    try { return $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn(); } catch (Exception $e) { return 0; }
}

$stats = [
    'laptop'      => getCount($pdo, 'laptop'),
    'printer'     => getCount($pdo, 'printer'),
    'cctv'        => getCount($pdo, 'cctv'),
    'networking'  => getCount($pdo, 'networking'),
    'electronic'  => getCount($pdo, 'electronic'),
    'accessories' => getCount($pdo, 'accessories'),
    'services'    => getCount($pdo, 'services'),
    'brands'      => 19 // Total brand count
];

// --- 2. FETCH RECENT ITEMS (GLOBAL) ---
$recent_items = [];
try {
    $recent_sql = "
        (SELECT title AS name, image_path, 'Laptop' AS type FROM laptop ORDER BY id DESC LIMIT 2)
        UNION ALL
        (SELECT name AS name, image_path, 'CCTV' AS type FROM cctv ORDER BY id DESC LIMIT 2)
        UNION ALL
        (SELECT name AS name, image_path, 'Networking' AS type FROM networking ORDER BY id DESC LIMIT 2)
        ORDER BY name DESC LIMIT 6
    ";
    $recent_items = $pdo->query($recent_sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { }

// --- 3. SEARCH LOGIC (Expanded to all tables) ---
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_results = [];
if (!empty($search_query)) {
    $term = "%$search_query%";
    // Searching across all relevant tables
    $sql = "
        (SELECT id, title AS name, 'Laptop' AS type, 'Laptop.php' AS page FROM laptop WHERE title LIKE ?)
        UNION ALL
        (SELECT id, title AS name, 'Printer' AS type, 'printer.php' AS page FROM printer WHERE title LIKE ?)
        UNION ALL
        (SELECT id, name AS name, 'CCTV' AS type, 'cctv.php' AS page FROM cctv WHERE name LIKE ?)
        UNION ALL
        (SELECT id, name AS name, 'Networking' AS type, 'Networking.php' AS page FROM networking WHERE name LIKE ?)
        UNION ALL
        (SELECT id, title AS name, 'Electronics' AS type, 'admin_electronic.php' AS page FROM electronic WHERE title LIKE ?)
        UNION ALL
        (SELECT id, name AS name, 'Accessory' AS type, 'admin_accessories.php' AS page FROM accessories WHERE name LIKE ?)
        LIMIT 20
    ";
    try {
        $stmt = $pdo->prepare($sql);
        // We have 6 tables in the UNION, so we pass the term 6 times
        $stmt->execute([$term, $term, $term, $term, $term, $term]);
        $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Saral Admin</title>
    
    <!-- External Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
     
    <style>
        :root {
            --sidebar-bg: #1e293b;
            --sidebar-active: #7c83c3;
            --bg-body: #f1f4f9;
        }

       
        /* --- SIDEBAR --- */
      

        /* --- MAIN CONTENT AREA --- */
        .main-content { margin-left: 260px; flex: 1; padding: 40px; }

        /* Stats Cards */
        .stat-card {
            background: #fff; border-radius: 20px; padding: 25px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03); border: none; height: 100%;
        }
        .stat-info h6 { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 5px; }
        .stat-info h2 { font-size: 32px; font-weight: 800; color: #334155; margin: 0; }
        .icon-box { width: 55px; height: 55px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        
        .bg-blue-s { background: #e3f2fd; color: #2196f3; }
        .bg-purp-s { background: #f3e5f5; color: #ab47bc; }
        .bg-red-s  { background: #ffebee; color: #ef5350; }
        .bg-org-s  { background: #fff3e0; color: #ffa726; }
        .bg-green-s{ background: #e8f5e9; color: #66bb6a; }
        .bg-yel-s  { background: #fff8e1; color: #bcaaa4; }
        .bg-grey-s { background: #eceff1; color: #78909c; }
        .bg-pink-s { background: #fce4ec; color: #ec407a; }

        /* Catalog Manager Grid */
        .section-title { font-weight: 700; color: #1e293b; margin: 40px 0 20px 0; font-size: 18px; }
        .cat-card {
            background: #fff; border-radius: 15px; padding: 30px 10px;
            text-align: center; text-decoration: none; display: block; color: #1e293b;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02); transition: 0.3s;
        }
        .cat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
        .cat-card i { font-size: 28px; display: block; margin-bottom: 12px; }
        .cat-card span { font-size: 11px; font-weight: 700; text-transform: uppercase; }

        /* General UI Containers */
        .custom-table-container { background: #fff; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden; }
        .table thead th { border-bottom: none; font-weight: 800; padding: 20px; background: #fff; }
        .table tbody td { padding: 15px 20px; vertical-align: middle; border-color: #f1f4f9; }
        .recent-img { width: 45px; height: 45px; border-radius: 10px; object-fit: cover; }
        
        /* Quick Access */
        .quick-access-card { background: #fff; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden; border: none; }
        .quick-access-list .list-group-item { border-left: none; border-right: none; border-top: none; padding: 18px 25px; transition: 0.2s; font-weight: 500; }
        .quick-access-list .list-group-item:hover { background-color: #f8fafc; }

        .search-container input { height: 50px; border-radius: 12px; border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.05); padding-left: 20px; margin-bottom: 30px; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
 

        <!-- Remaining Dashboard Content goes here (Table/Form) -->
    </main>

</body>

    <!-- MAIN -->
    <main class="main-content">
        
        <!-- Search -->
        <div class="search-container">
            <form action="dashboard.php" method="GET">
                <input type="text" name="search" class="form-control" placeholder="Search for anything (Laptops, Printers, CCTV, etc.)..." value="<?= htmlspecialchars($search_query) ?>">
            </form>
        </div>

        <!-- SEARCH RESULTS DISPLAY -->
        <?php if (!empty($search_query)): ?>
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0">Search Results for "<?= htmlspecialchars($search_query) ?>"</h4>
                <a href="dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Clear Search</a>
            </div>
            <div class="custom-table-container">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($search_results) > 0): ?>
                            <?php foreach ($search_results as $res): ?>
                            <tr>
                                <td class="fw-semibold" style="color:#334155"><?= htmlspecialchars($res['name']) ?></td>
                                <td><span class="badge bg-light text-dark border px-3 py-2"><?= $res['type'] ?></span></td>
                                <td class="text-end">
                                    <a href="<?= $res['page'] ?>?edit=<?= $res['id'] ?>&id=<?= $res['id'] ?>" class="btn btn-sm btn-primary rounded-pill px-3">Manage</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted">No items found matching your search.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <hr class="mb-5">
        <?php endif; ?>

        <!-- STATS -->
        <div class="row g-4 mb-4">
            <div class="col-md-3"><div class="stat-card"><div class="stat-info"><h6>Laptops</h6><h2><?= $stats['laptop'] ?></h2></div><div class="icon-box bg-blue-s"><i class="fas fa-laptop"></i></div></div></div>
            <div class="col-md-3"><div class="stat-card"><div class="stat-info"><h6>Printers</h6><h2><?= $stats['printer'] ?></h2></div><div class="icon-box bg-purp-s"><i class="fas fa-print"></i></div></div></div>
            <div class="col-md-3"><div class="stat-card"><div class="stat-info"><h6>CCTV Units</h6><h2><?= $stats['cctv'] ?></h2></div><div class="icon-box bg-red-s"><i class="fas fa-video"></i></div></div></div>
            <div class="col-md-3"><div class="stat-card"><div class="stat-info"><h6>Total Brands</h6><h2><?= $stats['brands'] ?></h2></div><div class="icon-box bg-org-s"><i class="fas fa-certificate"></i></div></div></div>
        </div>
        <div class="row g-4 mb-5">
            <div class="col-md-3"><div class="stat-card"><div class="stat-info"><h6>Networking</h6><h2><?= $stats['networking'] ?></h2></div><div class="icon-box bg-green-s"><i class="fas fa-network-wired"></i></div></div></div>
            <div class="col-md-3"><div class="stat-card"><div class="stat-info"><h6>Electronics</h6><h2><?= $stats['electronic'] ?></h2></div><div class="icon-box bg-yel-s"><i class="fas fa-plug"></i></div></div></div>
            <div class="col-md-3"><div class="stat-card"><div class="stat-info"><h6>Accessories</h6><h2><?= $stats['accessories'] ?></h2></div><div class="icon-box bg-grey-s"><i class="fas fa-keyboard"></i></div></div></div>
            <div class="col-md-3"><div class="stat-card"><div class="stat-info"><h6>Services</h6><h2><?= $stats['services'] ?></h2></div><div class="icon-box bg-pink-s"><i class="fas fa-tools"></i></div></div></div>
        </div>

        <!-- CATALOG MANAGER -->
        <h4 class="section-title">Catalog Manager</h4>
        <div class="row g-3 mb-5">
            <div class="col-6 col-md-2"><a href="Laptop.php" class="cat-card"><i class="fas fa-laptop" style="color:#2196f3"></i><span>Laptops</span></a></div>
            <div class="col-6 col-md-2"><a href="printer.php" class="cat-card"><i class="fas fa-print" style="color:#ab47bc"></i><span>Printers</span></a></div>
            <div class="col-6 col-md-2"><a href="cctv.php" class="cat-card"><i class="fas fa-video" style="color:#ef5350"></i><span>CCTV</span></a></div>
            <div class="col-6 col-md-2"><a href="Networking.php" class="cat-card"><i class="fas fa-network-wired" style="color:#66bb6a"></i><span>Networking</span></a></div>
            <div class="col-6 col-md-2"><a href="admin_electronic.php" class="cat-card"><i class="fas fa-plug" style="color:#8d6e63"></i><span>Electronics</span></a></div>
            <div class="col-6 col-md-2"><a href="admin_accessories.php" class="cat-card"><i class="fas fa-keyboard" style="color:#78909c"></i><span>Accessories</span></a></div>
               <div class="col-6 col-md-2"><a href="admin_services.php" class="cat-card"><i class="fas fa-tools" style="color:#ff9800"></i> <span>Services</span>
        </a>
    </div>
        </a>
    </div>

        </div>

        <!-- RECENT & QUICK ACCESS -->
        <div class="row g-4">
            <div class="col-lg-8">
                <h4 class="section-title">Recently Added</h4>
                <div class="custom-table-container">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Image</th><th>Name</th><th class="text-end">Type</th></tr></thead>
                        <tbody>
                            <?php foreach($recent_items as $item): ?>
                            <tr>
                                <td><img src="uploads/<?= $item['image_path'] ?>" class="recent-img" onerror="this.src='https://via.placeholder.com/50'"></td>
                                <td class="fw-semibold" style="color:#334155"><?= htmlspecialchars($item['name']) ?></td>
                                <td class="text-end"><span class="badge bg-light text-dark border px-3 py-2"><?= $item['type'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-4">
                <h4 class="section-title">Quick Access</h4>
                <div class="card quick-access-card">
                    <div class="list-group list-group-flush quick-access-list">
         <a href="Laptop.php" class="list-group-item list-group-item-action text-dark">
    <i class="fas fa-plus-circle text-primary me-2"></i> Add Laptop
</a>

<a href="cctv.php" class="list-group-item list-group-item-action text-dark">
    <i class="fas fa-video text-danger me-2"></i> Add Camera
</a>

<a href="Networking.php" class="list-group-item list-group-item-action text-dark">
    <i class="fas fa-network-wired text-success me-2"></i> Add Networking
</a>

<a href="printer.php" class="list-group-item list-group-item-action text-dark">
    <i class="fas fa-print text-purple me-2"></i> Add Printer
</a>

<a href="admin_accessories.php" class="list-group-item list-group-item-action text-dark">
    <i class="fas fa-keyboard text-secondary me-2"></i> Add Accessories
</a>

<a href="admin_electronic.php" class="list-group-item list-group-item-action text-dark">
    <i class="fas fa-bolt text-warning me-2"></i> Add Electronics
</a>


                </div>
            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>