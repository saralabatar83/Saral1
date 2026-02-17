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
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    

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
 * Saral IT - Services Management Dashboard
 */

require_once 'db.php';      
require_once '../includes/functions.php'; 

// --- 1. DATABASE AUTO-SETUP ---
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        service_name VARCHAR(255) NOT NULL,
        category VARCHAR(100),
        description TEXT,
        image_path VARCHAR(255),
        is_new_release TINYINT(1) DEFAULT 0
    )");
} catch (PDOException $e) { }

// --- 2. ACTION HANDLERS ---
$msg = "";
$edit_data = null;

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$id]);
    header("Location: admin_services.php?msg=Deleted"); exit();
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_data = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_service'])) {
    $name = htmlspecialchars($_POST['service_name']);
    $cat  = $_POST['category'];
    $desc = htmlspecialchars($_POST['description']);
    $id   = $_POST['service_id'] ?? null;
    $is_new_release = isset($_POST['is_new_release']) ? 1 : 0;

    $img_name = $_POST['old_image'] ?? '';
    if (!empty($_FILES["image"]["name"])) {
        $img_name = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['image']['name']);
        if(!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/" . $img_name);
    }

    if ($id) {
        $pdo->prepare("UPDATE services SET service_name=?, category=?, description=?, image_path=?, is_new_release=? WHERE id=?")
            ->execute([$name, $cat, $desc, $img_name, $is_new_release, $id]);
    } else {
        $pdo->prepare("INSERT INTO services (service_name, category, description, image_path, is_new_release) VALUES (?, ?, ?, ?, ?)")
            ->execute([$name, $cat, $desc, $img_name, $is_new_release]);
    }
    header("Location: admin_services.php?msg=Success"); exit();
}

$services = $pdo->query("SELECT * FROM services ORDER BY is_new_release DESC, id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services Management | Saral Admin</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --bg-body: #f1f4f9;
            --accent-blue: #3498db;
            --text-dark: #1e293b;
        }

        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; color: var(--text-dark); margin: 0; }

        /* --- SIDEBAR --- */
     
        /* --- MAIN CONTENT --- */
        .main-content { margin-left: var(--sidebar-width); padding: 40px; }
        .admin-card { background: #fff; border-radius: 20px; padding: 30px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.02); margin-bottom: 30px; }
        
        /* Heading Styles */
        .card-title { font-size: 1.25rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; }
        .card-title i { margin-right: 12px; color: var(--accent-blue); }

        /* Input Styles */
        label { font-weight: 600; font-size: 13px; color: #64748b; margin-top: 18px; margin-bottom: 8px; display: block; }
        .form-control, .form-select { 
            padding: 12px; border-radius: 10px; border: 1px solid #e2e8f0; font-size: 14px; color: #334155;
        }
        .form-control:focus { box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1); border-color: var(--accent-blue); }

        /* Custom Toggle Switch (Matches Image) */
        .toggle-container { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 15px; display: flex; align-items: center; }
        .form-check-input { width: 2.4em; height: 1.2em; cursor: pointer; background-color: #cbd5e1; border: none; }
        .form-check-input:checked { background-color: var(--accent-blue); }
        .toggle-label { color: #e11d48; font-weight: 700; font-size: 13px; margin-left: 10px; cursor: pointer; }

        /* Button Style */
        .btn-publish { background: var(--accent-blue); color: white; border: none; padding: 14px; border-radius: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; width: 100%; margin-top: 25px; transition: 0.3s; }
        .btn-publish:hover { background: #2980b9; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3); }

        /* Table Styling */
        .table th { color: #94a3b8; font-size: 11px; text-transform: uppercase; font-weight: 700; border: none; padding-bottom: 20px; }
        .table td { vertical-align: middle; padding: 15px 10px; border-bottom: 1px solid #f1f4f9; }
        .service-icon { width: 45px; height: 45px; border-radius: 10px; object-fit: cover; border: 1px solid #f1f4f9; }
        .service-name { font-weight: 700; color: #1e293b; font-size: 15px; margin: 0; }
        .service-desc { font-size: 12px; color: #94a3b8; margin: 0; }
        .category-badge { background: #e0f2fe; color: #0369a1; padding: 6px 15px; border-radius: 8px; font-size: 12px; font-weight: 700; text-decoration: none; }
        .new-label { background: #ff4757; color: white; font-size: 9px; padding: 2px 6px; border-radius: 4px; font-weight: 800; margin-left: 8px; }

        /* Action Buttons */
        .btn-action { width: 32px; height: 32px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.2s; }
        .btn-edit { border: 1px solid var(--accent-blue); color: var(--accent-blue); margin-right: 5px; }
        .btn-edit:hover { background: var(--accent-blue); color: white; }
        .btn-delete { border: 1px solid #ff4757; color: #ff4757; }
        .btn-delete:hover { background: #ff4757; color: white; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->


    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="row">
            
            <!-- FORM SECTION -->
            <div class="col-lg-4">
                <div class="admin-card">
                    <div class="card-title"><i class="fas fa-plus-circle"></i> <?= $edit_data ? 'Edit Service' : 'New Service' ?></div>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="service_id" value="<?= $edit_data['id'] ?? '' ?>">
                        <input type="hidden" name="old_image" value="<?= $edit_data['image_path'] ?? '' ?>">
                        
                        <div class="toggle-container mb-4 mt-3">
                            <div class="form-check form-switch p-0 m-0">
                                <input class="form-check-input" type="checkbox" name="is_new_release" value="1" id="newRel" <?= ($edit_data['is_new_release'] ?? 0) ? 'checked' : '' ?>>
                                <label class="toggle-label" for="newRel"><i class="fas fa-fire me-1"></i> Mark as New Service</label>
                            </div>
                        </div>

                        <label>Service Title</label>
                        <input type="text" name="service_name" class="form-control" placeholder="Laptop Repair, CCTV Install..." value="<?= htmlspecialchars($edit_data['service_name'] ?? '') ?>" required>
                        
                        <label>Category</label>
                        <select name="category" class="form-select">
                            <option <?= ($edit_data['category'] ?? '') == 'Computer Repair' ? 'selected' : '' ?>>Computer Repair</option>
                            <option <?= ($edit_data['category'] ?? '') == 'CCTV Tech' ? 'selected' : '' ?>>CCTV Tech</option>
                            <option <?= ($edit_data['category'] ?? '') == 'Networking' ? 'selected' : '' ?>>Networking</option>
                        </select>

                        <label>Service Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Brief details..."><?= htmlspecialchars($edit_data['description'] ?? '') ?></textarea>
                        
                        <label>Icon / Image</label>
                        <input type="file" name="image" class="form-control">
                        
                        <button type="submit" name="save_service" class="btn-publish">
                            <?= $edit_data ? 'Update Service' : 'Publish Service' ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- TABLE SECTION -->
            <div class="col-lg-8">
                <div class="admin-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="card-title"><i class="fas fa-list-ul"></i> Active Services</div>
                        <span class="badge bg-light text-dark border px-3 py-2"><?= count($services) ?> Total</span>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Icon</th>
                                    <th>Service Details</th>
                                    <th>Category</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($services as $s): ?>
                                <tr>
                                    <td><img src="uploads/<?= $s['image_path'] ?>" class="service-icon" onerror="this.src='https://via.placeholder.com/50?text=S'"></td>
                                    <td>
                                        <p class="service-name"><?= htmlspecialchars($s['service_name']) ?><?php if($s['is_new_release']): ?><span class="new-label">NEW</span><?php endif; ?></p>
                                        <p class="service-desc"><?= mb_strimwidth(htmlspecialchars($s['description']), 0, 40, "...") ?></p>
                                    </td>
                                    <td><span class="category-badge"><?= $s['category'] ?></span></td>
                                    <td class="text-end">
                                        <a href="?edit=<?= $s['id'] ?>" class="btn-action btn-edit"><i class="fas fa-edit"></i></a>
                                        <a href="?delete=<?= $s['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>