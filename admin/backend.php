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
<li><a href="top_services.php"><i class="fas fa-star"></i> <span>Top Services</span>
    </a>
</li>

</li>
            
            <div class="menu-divider">System</div>
           
           
            <li><a href="sociallinks.php"><i class="fas fa-hashtag"></i> <span>Social Links</span></a></li>
           
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
<?php
/**
 * Saral Admin - Complete Unified Dashboard
 */
require_once('../db.php'); 
session_start();

// --- 1. ACTION HANDLERS ---
if (isset($_GET['approve_id'])) {
    $id = (int)$_GET['approve_id'];
    $pdo->prepare("UPDATE customers SET is_approved = 1 WHERE id = ?")->execute([$id]);
    header("Location: backend.php?msg=User Approved Successfully");
    exit();
}

if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $pdo->prepare("DELETE FROM customers WHERE id = ?")->execute([$id]);
    header("Location: backend.php?msg=User Deleted Successfully");
    exit();
}

// --- 2. DATA AGGREGATION ---
$users = $pdo->query("SELECT * FROM customers ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

$totalUsers = count($users);
$pendingUsers = 0;
$onlineUsers = 0;
foreach($users as $u) {
    if($u['is_approved'] == 0) $pendingUsers++;
    if($u['is_online'] == 1) $onlineUsers++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaralAdmin | Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
     <!-- External Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
     
   <style>

        

        * { box-sizing: border-box; font-family: 'Poppins', sans-serif; margin: 0; padding: 0; }


        /* --- SIDEBAR STYLE (Matching Screenshot 2) --- */
     

        /* --- MAIN CONTENT STYLE --- */
        .main-content { flex: 1; margin-left: 260px; padding: 30px; }
        
        /* Stats Cards (Matching Screenshot 1) */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--white); padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-left: 5px solid transparent; }
        .stat-card.total { border-left-color: #ddd; }
        .stat-card.pending { border-left-color: #ff9f43; }
        .stat-card.online { border-left-color: #28c76f; }
        .stat-card p { color: var(--text-muted); font-size: 14px; font-weight: 600; margin-bottom: 10px; }
        .stat-card h3 { font-size: 32px; color: #333; }

        /* Table Card */
        .data-card { background: var(--white); border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; }
        .data-card-header { padding: 20px 25px; border-bottom: 1px solid #edf2f9; }
        .data-card-header h3 { font-size: 18px; color: #333; }

        table { width: 100%; border-collapse: collapse; }
        th { background: #f9fbfd; padding: 15px 25px; text-align: left; font-size: 12px; text-transform: uppercase; color: #95aac9; letter-spacing: 1px; }
        td { padding: 15px 25px; border-bottom: 1px solid #edf2f9; font-size: 14px; color: #506690; }
        
        /* Badges */
        .badge { padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 500; }
        .badge-online { background: #e8f9ef; color: #28c76f; }
        .badge-offline { background: #f1f3f9; color: #82868b; }
        .badge-approved { background: #e8f9ef; color: #28c76f; }
        .badge-pending { background: #fff4e5; color: #ff9f43; }

        /* Action Buttons */
        .btn { padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-block; border: none; cursor: pointer; }
        .btn-approve { background: #28c76f; color: white; margin-right: 5px; }
        .btn-delete { background: #ffeaeb; color: #ea5455; }
    </style>
</head>
<body>

    <!-- Sidebar Menu from Screenshot 2 -->
  
    <!-- Main Content from Screenshot 1 -->
    <main class="main-content">
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <p>Total Registered</p>
                <h3><?php echo $totalUsers; ?></h3>
            </div>
            <div class="stat-card pending">
                <p>Pending Approval</p>
                <h3><?php echo $pendingUsers; ?></h3>
            </div>
            <div class="stat-card online">
                <p>Currently Online</p>
                <h3><?php echo $onlineUsers; ?></h3>
            </div>
        </div>

        <!-- Customer Database Table -->
        <div class="data-card">
            <div class="data-card-header">
                <h3>Customer Database</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>User Details</th>
                        <th>Contact</th>
                        <th>Login Status</th>
                        <th>Approval</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($user['full_name']); ?></strong><br>
                            <small style="color:#b9bec7"><?php echo htmlspecialchars($user['email']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        <td>
                            <span class="badge <?php echo $user['is_online'] ? 'badge-online' : 'badge-offline'; ?>">
                                <?php echo $user['is_online'] ? 'Online' : 'Offline'; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php echo $user['is_approved'] ? 'badge-approved' : 'badge-pending'; ?>">
                                <?php echo $user['is_approved'] ? 'Approved' : 'Pending'; ?>
                            </span>
                        </td>
                        <td>
                            <?php if(!$user['is_approved']): ?>
                                <a href="backend.php?approve_id=<?php echo $user['id']; ?>" class="btn btn-approve">Approve</a>
                            <?php endif; ?>
                            <a href="backend.php?delete_id=<?php echo $user['id']; ?>" 
                               class="btn btn-delete" 
                               onclick="return confirm('Permanently delete this user?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>