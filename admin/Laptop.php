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
require_once '../db.php'; 

/**
 * 1. DATABASE AUTO-SETUP & REPAIR
 */
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS brands (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS laptop_subcategories (id INT AUTO_INCREMENT PRIMARY KEY, sub_name VARCHAR(255) NOT NULL)");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS laptop (id INT AUTO_INCREMENT PRIMARY KEY)");

    $needed_columns = [
        'brand_id'          => "INT",
        'sub_id'            => "INT",
        'sku'               => "VARCHAR(100)",
        'title'             => "VARCHAR(255)",
        'price'             => "VARCHAR(100)",
        'short_description' => "TEXT",
        'long_description'  => "TEXT",
        'image_path'        => "VARCHAR(255)",
        'target_link'       => "VARCHAR(255)",
        'meta_title'        => "VARCHAR(255)",
        'meta_description'  => "TEXT",
        'meta_keywords'     => "VARCHAR(255)",
        'is_new_release'    => "TINYINT(1) DEFAULT 0" 
    ];

    $existing_cols = $pdo->query("SHOW COLUMNS FROM laptop")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($needed_columns as $column => $type) {
        if (!in_array($column, $existing_cols)) {
            $pdo->exec("ALTER TABLE laptop ADD COLUMN $column $type");
        }
    }
} catch (PDOException $e) { die("Database Error: " . $e->getMessage()); }

// --- HANDLE BRAND & CATEGORY MANAGEMENT ---
if (isset($_POST['add_brand'])) {
    $pdo->prepare("INSERT INTO brands (name) VALUES (?)")->execute([trim($_POST['new_brand_name'])]);
    header("Location: Laptop.php?msg=BrandAdded"); exit();
}
if (isset($_POST['add_category'])) {
    $pdo->prepare("INSERT INTO laptop_subcategories (sub_name) VALUES (?)")->execute([trim($_POST['new_cat_name'])]);
    header("Location: Laptop.php?msg=CategoryAdded"); exit();
}
if (isset($_GET['del_brand'])) {
    $pdo->prepare("DELETE FROM brands WHERE id = ?")->execute([$_GET['del_brand']]);
    header("Location: Laptop.php"); exit();
}
if (isset($_GET['del_cat'])) {
    $pdo->prepare("DELETE FROM laptop_subcategories WHERE id = ?")->execute([$_GET['del_cat']]);
    header("Location: Laptop.php"); exit();
}

// --- HANDLE SAVE PRODUCT ---
if (isset($_POST['save_product'])) {
    $id = $_POST['id'] ?: null;
    $img_name = $_POST['old_image'];
    if (!empty($_FILES['image']['name'])) {
        $img_name = time() . "_" . $_FILES['image']['name'];
        if(!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $img_name);
    }

    $brand_id       = $_POST['brand_id'];
    $sub_id         = $_POST['sub_id'];
    $sku            = $_POST['sku'] ?? '';
    $title          = $_POST['title'] ?? '';
    $price          = $_POST['price'] ?? '';
    $short_desc     = $_POST['short_description'] ?? '';
    $long_desc      = $_POST['long_description'] ?? '';
    $target_link    = $_POST['target_link'] ?? '';
    $meta_key       = $_POST['meta_keywords'] ?? '';
    
    $is_new_release = isset($_POST['is_new_release']) ? 1 : 0;

    $data = [$brand_id, $sub_id, $sku, $title, $price, $short_desc, $long_desc, $img_name, $target_link, $meta_key, $is_new_release];

    if ($id) {
        $sql = "UPDATE laptop SET brand_id=?, sub_id=?, sku=?, title=?, price=?, short_description=?, long_description=?, image_path=?, target_link=?, meta_keywords=?, is_new_release=? WHERE id=?";
        $data[] = $id;
    } else {
        $sql = "INSERT INTO laptop (brand_id, sub_id, sku, title, price, short_description, long_description, image_path, target_link, meta_keywords, is_new_release) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
    }
    
    $pdo->prepare($sql)->execute($data);
    header("Location: Laptop.php?msg=Success"); exit();
}

// --- DELETE PRODUCT ---
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM laptop WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: Laptop.php?msg=Deleted"); exit();
}

// --- DATA FETCH ---
$edit_row = ['id'=>'','brand_id'=>'','sub_id'=>'','sku'=>'','title'=>'','short_description'=>'','long_description'=>'','price'=>'','image_path'=>'','target_link'=>'','meta_keywords'=>'','is_new_release'=>0];
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM laptop WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if($res) $edit_row = $res;
}

$brands = $pdo->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll();
$categories = $pdo->query("SELECT * FROM laptop_subcategories ORDER BY sub_name ASC")->fetchAll();

// --- SEARCH & INVENTORY FETCH LOGIC (UPDATED) ---
$search_keyword = "";
$sql_inventory = "SELECT l.*, b.name as bname, s.sub_name 
                  FROM laptop l 
                  LEFT JOIN brands b ON l.brand_id = b.id 
                  LEFT JOIN laptop_subcategories s ON l.sub_id = s.id";

// Check if search exists
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_keyword = trim($_GET['search']);
    $sql_inventory .= " WHERE l.title LIKE :search 
                        OR b.name LIKE :search 
                        OR s.sub_name LIKE :search 
                        OR l.sku LIKE :search";
}

$sql_inventory .= " ORDER BY l.id DESC";

$stmt_inv = $pdo->prepare($sql_inventory);

if ($search_keyword) {
    $stmt_inv->execute(['search' => "%$search_keyword%"]);
} else {
    $stmt_inv->execute();
}

$inventory = $stmt_inv->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laptop Management | Saral Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    <style>
        :root { --sidebar-width: 260px;  }
        body { background: #f4f6f9; margin: 0; display: flex; }

        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 30px; box-sizing: border-box; }
        .admin-card { background: white; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 25px; }

        .badge-pill { background: #e9ecef; padding: 5px 15px; border-radius: 50px; font-size: 12px; display: inline-flex; align-items: center; margin: 3px; border: 1px solid #ddd; color: #333; }
        .badge-pill a { color: #e74c3c; margin-left: 8px; text-decoration: none; font-weight: bold; }

        .inventory-item { display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee; }
        .inv-img { width: 45px; height: 45px; border-radius: 6px; object-fit: cover; margin-right: 12px; border: 1px solid #eee; }
        .inv-details { flex: 1; }
        .inv-details h6 { margin: 0; font-size: 13px; font-weight: 700; }
        .inv-details small { font-size: 11px; color: #888; }
        
        .new-release-tag { background: #ff4757; color: white; font-size: 9px; padding: 2px 5px; border-radius: 3px; text-transform: uppercase; font-weight: bold; }
    </style>
</head>
<body>


    <main class="main-content">
        <div class="row">
            <!-- LEFT: FORM -->
            <div class="col-lg-7">
                <div class="admin-card">
                    <h5 class="text-primary fw-bold mb-4"><i class="fas fa-plus-circle me-2"></i> <?= $edit_row['id'] ? 'Edit Laptop' : 'Add New Laptop' ?></h5>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $edit_row['id'] ?>">
                        <input type="hidden" name="old_image" value="<?= $edit_row['image_path'] ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Brand</label>
                                <select name="brand_id" class="form-select" required>
                                    <option value="">-- Select Brand --</option>
                                    <?php foreach($brands as $b): ?>
                                        <option value="<?= $b['id'] ?>" <?= $edit_row['brand_id'] == $b['id'] ? 'selected' : '' ?>><?= $b['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Category</label>
                                <select name="sub_id" class="form-select" required>
                                    <option value="">-- Select Category --</option>
                                    <?php foreach($categories as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= $edit_row['sub_id'] == $c['id'] ? 'selected' : '' ?>><?= $c['sub_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3 p-3 border rounded bg-light">
                            <input class="form-check-input" type="checkbox" name="is_new_release" value="1" id="newRelease" 
                            <?= ($edit_row['is_new_release'] == 1) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold text-danger" for="newRelease">
                                <i class="fa fa-fire"></i> SHOW IN NEW RELEASES SECTION
                            </label>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Model Title</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($edit_row['title']) ?>" required>
                            </div>
                        </div>

                        <div class="mb-3"><label class="form-label fw-bold">Short Highlights</label><textarea name="short_description" class="form-control" rows="2"><?= $edit_row['short_description'] ?></textarea></div>
                        <div class="mb-3"><label class="form-label fw-bold">Specifications</label><textarea name="long_description" id="editor1"><?= $edit_row['long_description'] ?></textarea></div>

                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label fw-bold">Image</label><input type="file" name="image" class="form-control"></div>
                           
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Keywords (SEO)</label>
                            <input type="text" name="meta_keywords" class="form-control" value="<?= $edit_row['meta_keywords'] ?>" placeholder="laptop, gaming, dell">
                        </div>

                        <button type="submit" name="save_product" class="btn btn-primary w-100 py-2 fw-bold">SAVE LAPTOP</button>
                    </form>
                </div>
            </div>

            <!-- RIGHT: MANAGERS & INVENTORY -->
            <div class="col-lg-5">
                <div class="admin-card">
                    <h6 class="fw-bold mb-3">Brand Manager</h6>
                    <form method="POST" class="d-flex gap-2 mb-3">
                        <input type="text" name="new_brand_name" class="form-control form-control-sm" placeholder="Add Brand" required>
                        <button name="add_brand" class="btn btn-dark btn-sm">Add</button>
                    </form>
                    <div><?php foreach($brands as $b): ?><span class="badge-pill"><?= $b['name'] ?> <a href="?del_brand=<?= $b['id'] ?>">&times;</a></span><?php endforeach; ?></div>
                </div>

                <div class="admin-card">
                    <h6 class="fw-bold mb-3">Category Manager</h6>
                    <form method="POST" class="d-flex gap-2 mb-3">
                        <input type="text" name="new_cat_name" class="form-control form-control-sm" placeholder="Add Category" required>
                        <button name="add_category" class="btn btn-dark btn-sm">Add</button>
                    </form>
                    <div><?php foreach($categories as $c): ?><span class="badge-pill"><?= $c['sub_name'] ?> <a href="?del_cat=<?= $c['id'] ?>">&times;</a></span><?php endforeach; ?></div>
                </div>

                <div class="admin-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold m-0">Laptop Inventory</h6>
                        <span class="badge bg-secondary"><?= count($inventory) ?> Items</span>
                    </div>

                    <!-- SEARCH BAR ADDED HERE -->
                    <form method="GET" class="mb-3">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="Search by name, brand..." value="<?= htmlspecialchars($search_keyword) ?>">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                            <?php if(!empty($search_keyword)): ?>
                                <a href="Laptop.php" class="btn btn-danger"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                        </div>
                    </form>
                    <!-- END SEARCH BAR -->

                    <div style="max-height: 400px; overflow-y: auto;">
                        <?php if(count($inventory) > 0): ?>
                            <?php foreach($inventory as $item): ?>
                            <div class="inventory-item">
                                <img src="uploads/<?= $item['image_path'] ?>" class="inv-img" onerror="this.src='https://via.placeholder.com/50'">
                                <div class="inv-details">
                                    <h6><?= htmlspecialchars($item['title']) ?> 
                                        <?php if($item['is_new_release']): ?><span class="new-release-tag">NEW</span><?php endif; ?>
                                    </h6>
                                    <small><?= $item['bname'] ?> | <?= $item['sub_name'] ?></small>
                                </div>
                                <div>
                                    <a href="?edit=<?= $item['id'] ?>" class="text-primary me-2"><i class="fas fa-edit"></i></a>
                                    <a href="?delete=<?= $item['id'] ?>" class="text-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center p-3 text-muted">No laptops found.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>CKEDITOR.replace('editor1');</script>
</body>
</html>