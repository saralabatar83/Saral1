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
            <li><a href="Banners.php"><i class="fas fa-ad"></i> <span>Banners</span></a></li>
            
            <div class="menu-divider">System</div>
            <li><a href="formfillup.php"><i class="fas fa-user-friends"></i> <span>Customers</span></a></li>
            <li><a href="services.php"><i class="fas fa-concierge-bell"></i> <span>Services</span></a></li>
            <li><a href="sociallinks.php"><i class="fas fa-hashtag"></i> <span>Social Links</span></a></li>
            <li><a href="top_setting.php"><i class="fas fa-cog"></i> <span>Header Settings</span></a></li>
            <li><a href="admin_footer.php"><i class="fas fa-shoe-prints"></i> <span>Footer Manager</span></a></li>
            
            <div class="menu-divider">Account</div>
            <li><a href="change_password.php"><i class="fas fa-key"></i> <span>Security</span></a></li>
            <li><a href="logout.php" style="color: #ff7675;"><i class="fas fa-power-off"></i> <span>Logout</span></a></li>
        </ul>
    </aside>

    <main class="main-view">
        <div class="header-top">
            <h1>Welcome, Admin</h1>
        </div>

        <?php if($message): ?>
            <div class="alert"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <p>Total Products</p>
                <h3><?php echo $total_products; ?></h3>
            </div>
            <div class="stat-card">
                <p>Total Orders</p>
                <h3><?php echo $total_orders; ?></h3>
            </div>
            <div class="stat-card">
                <p>Revenue</p>
                <h3>Rs. <?php echo number_format($revenue); ?></h3>
            </div>
        </div>

        <!-- Remaining Dashboard Content goes here (Table/Form) -->
    </main>

</body>
</html>
<?php
/**
 * SARAL IT SOLUTION - ACCESSORIES MANAGEMENT (With Sub-Categories)
 */
include '../config/db.php'; 

// --- 1. DATABASE AUTO-SETUP ---
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS accessory_brands (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS accessory_categories (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL)");
    
    // [ADDED] Sub-Categories Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS accessory_sub_categories (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        category_id INT NOT NULL, 
        name VARCHAR(100) NOT NULL
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS accessories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        brand_id INT,
        category_id INT,
        sub_category_id INT DEFAULT 0, 
        name VARCHAR(255),
        price DECIMAL(10,2),
        short_description TEXT,
        image_path VARCHAR(255),
        target_link VARCHAR(255),
        is_new_release TINYINT(1) DEFAULT 0
    )");
    
    // Safety check: Add columns if they don't exist
    $cols = $pdo->query("SHOW COLUMNS FROM accessories")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('is_new_release', $cols)) { 
        $pdo->exec("ALTER TABLE accessories ADD COLUMN is_new_release TINYINT(1) DEFAULT 0"); 
    }
    // [ADDED] Safety check for sub_category_id
    if (!in_array('sub_category_id', $cols)) { 
        $pdo->exec("ALTER TABLE accessories ADD COLUMN sub_category_id INT DEFAULT 0"); 
    }

} catch (PDOException $e) { die("Setup Error: " . $e->getMessage()); }

// --- 2. LOGIC HANDLERS ---
if (isset($_POST['add_brand'])) {
    $pdo->prepare("INSERT INTO accessory_brands (name) VALUES (?)")->execute([trim($_POST['brand_name'])]);
    header("Location: admin_accessories.php?msg=BrandAdded"); exit();
}
if (isset($_GET['del_brand'])) {
    $pdo->prepare("DELETE FROM accessory_brands WHERE id = ?")->execute([(int)$_GET['del_brand']]);
    header("Location: admin_accessories.php?msg=BrandDeleted"); exit();
}
if (isset($_POST['add_cat'])) {
    $pdo->prepare("INSERT INTO accessory_categories (name) VALUES (?)")->execute([trim($_POST['cat_name'])]);
    header("Location: admin_accessories.php?msg=CatAdded"); exit();
}
if (isset($_GET['del_cat'])) {
    $pdo->prepare("DELETE FROM accessory_categories WHERE id = ?")->execute([(int)$_GET['del_cat']]);
    // Optional: Delete related sub-cats
    $pdo->prepare("DELETE FROM accessory_sub_categories WHERE category_id = ?")->execute([(int)$_GET['del_cat']]);
    header("Location: admin_accessories.php?msg=CatDeleted"); exit();
}

// [ADDED] Sub-Category Handlers
if (isset($_POST['add_sub_cat'])) {
    $parent = $_POST['parent_cat_id'];
    $sub_name = trim($_POST['sub_cat_name']);
    if($parent && $sub_name){
        $pdo->prepare("INSERT INTO accessory_sub_categories (category_id, name) VALUES (?, ?)")->execute([$parent, $sub_name]);
    }
    header("Location: admin_accessories.php?msg=SubAdded"); exit();
}
if (isset($_GET['del_sub'])) {
    $pdo->prepare("DELETE FROM accessory_sub_categories WHERE id = ?")->execute([(int)$_GET['del_sub']]);
    header("Location: admin_accessories.php?msg=SubDeleted"); exit();
}

// SAVE PRODUCT HANDLER
if (isset($_POST['save_accessory'])) {
    $id = $_POST['id'] ?: null;
    $brand_id = $_POST['brand_id'];
    $cat_id = $_POST['category_id'];
    $sub_cat_id = !empty($_POST['sub_category_id']) ? $_POST['sub_category_id'] : 0; // [ADDED]
    $name = $_POST['name'];
    $price = $_POST['price'];
    $short_desc = $_POST['short_description'];
    $target_link = $_POST['target_link'];
    $is_new_release = isset($_POST['is_new_release']) ? 1 : 0; 

    $img_name = $_POST['existing_image'];
    if (!empty($_FILES['image']['name'])) {
        $img_name = time() . "_" . $_FILES['image']['name'];
        if(!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/$img_name");
    }

    if ($id) {
        $sql = "UPDATE accessories SET brand_id=?, category_id=?, sub_category_id=?, name=?, price=?, short_description=?, image_path=?, target_link=?, is_new_release=? WHERE id=?";
        $pdo->prepare($sql)->execute([$brand_id, $cat_id, $sub_cat_id, $name, $price, $short_desc, $img_name, $target_link, $is_new_release, $id]);
    } else {
        $sql = "INSERT INTO accessories (brand_id, category_id, sub_category_id, name, price, short_description, image_path, target_link, is_new_release) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$brand_id, $cat_id, $sub_cat_id, $name, $price, $short_desc, $img_name, $target_link, $is_new_release]);
    }
    header("Location: admin_accessories.php?msg=Success"); exit();
}

if (isset($_GET['del_acc'])) {
    $pdo->prepare("DELETE FROM accessories WHERE id = ?")->execute([(int)$_GET['del_acc']]);
    header("Location: admin_accessories.php?msg=Deleted"); exit();
}

// FETCH DATA
$item_data = ['id'=>'','brand_id'=>'','category_id'=>'','sub_category_id'=>'','name'=>'','price'=>'','short_description'=>'','image_path'=>'','target_link'=>'','is_new_release'=>0];
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM accessories WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $res = $stmt->fetch();
    if($res) $item_data = $res; 
}

$brands_list = $pdo->query("SELECT * FROM accessory_brands ORDER BY name ASC")->fetchAll();
$cats_list   = $pdo->query("SELECT * FROM accessory_categories ORDER BY name ASC")->fetchAll();

// [ADDED] Fetch Sub Cats
$sub_cats_list = $pdo->query("SELECT s.*, c.name as parent_name FROM accessory_sub_categories s JOIN accessory_categories c ON s.category_id = c.id ORDER BY c.name ASC, s.name ASC")->fetchAll();

// [UPDATED] Fetch Products with Sub Category Name
$products    = $pdo->query("SELECT a.*, b.name as brand_name, c.name as cat_name, s.name as sub_name 
                            FROM accessories a 
                            LEFT JOIN accessory_brands b ON a.brand_id = b.id 
                            LEFT JOIN accessory_categories c ON a.category_id = c.id 
                            LEFT JOIN accessory_sub_categories s ON a.sub_category_id = s.id
                            ORDER BY a.id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Accessories | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; margin: 0; }
        .main-content { padding: 30px; min-height: 100vh; }
        .admin-card { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); background: #fff; margin-bottom: 20px; }
        .badge-item { background: #f8f9fa; color: #333; padding: 6px 12px; border-radius: 6px; font-size: 13px; display: inline-flex; align-items: center; margin: 3px; border: 1px solid #ddd; }
        .badge-item a { color: #ff4757; text-decoration: none; margin-left: 10px; font-weight: bold; }
        .new-tag { font-size: 10px; background: #ff4757; color: white; padding: 2px 5px; border-radius: 3px; margin-left: 5px; vertical-align: middle; }
    </style>
</head>
<body>

<main class="main-content">
    <div class="container-fluid">
        <div class="row">
            
            <!-- LEFT COLUMN: BRANDS, CATEGORIES & SUB-CATEGORIES -->
            <div class="col-xl-3 col-lg-4">
                <div class="admin-card p-4">
                    <!-- Brands -->
                    <h6 class="text-primary"><i class="fa fa-tags"></i> Setup Brands</h6>
                    <form method="POST" class="d-flex gap-2 mb-2">
                        <input type="text" name="brand_name" class="form-control form-control-sm" placeholder="Brand Name" required>
                        <button name="add_brand" class="btn btn-primary btn-sm">Add</button>
                    </form>
                    <div class="mb-3">
                        <?php foreach($brands_list as $b): ?>
                            <span class="badge-item"><?= htmlspecialchars($b['name']) ?> <a href="?del_brand=<?= $b['id'] ?>" onclick="return confirm('Delete?')">&times;</a></span>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr>
                    
                    <!-- Categories -->
                    <h6 class="text-success"><i class="fa fa-list"></i> Categories</h6>
                    <form method="POST" class="d-flex gap-2 mb-2">
                        <input type="text" name="cat_name" class="form-control form-control-sm" placeholder="Cat Name" required>
                        <button name="add_cat" class="btn btn-success text-white btn-sm">Add</button>
                    </form>
                    <div class="mb-3">
                        <?php foreach($cats_list as $c): ?>
                            <span class="badge-item"><?= htmlspecialchars($c['name']) ?> <a href="?del_cat=<?= $c['id'] ?>" onclick="return confirm('Delete?')">&times;</a></span>
                        <?php endforeach; ?>
                    </div>

                    <hr>

                    <!-- [ADDED] Sub-Categories -->
                    <h6 class="text-info"><i class="fa fa-indent"></i> Sub-Categories</h6>
                    <form method="POST" class="mb-2">
                        <select name="parent_cat_id" class="form-select form-select-sm mb-1" required>
                            <option value="">Select Parent...</option>
                            <?php foreach($cats_list as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="d-flex gap-2">
                            <input type="text" name="sub_cat_name" class="form-control form-control-sm" placeholder="Sub Name" required>
                            <button name="add_sub_cat" class="btn btn-info text-white btn-sm">Add</button>
                        </div>
                    </form>
                    <div style="max-height: 200px; overflow-y:auto;">
                        <?php foreach($sub_cats_list as $sc): ?>
                            <div class="badge-item w-100 d-flex justify-content-between">
                                <span><small class="text-muted"><?= substr($sc['parent_name'],0,3) ?> > </small> <?= htmlspecialchars($sc['name']) ?></span>
                                <a href="?del_sub=<?= $sc['id'] ?>" onclick="return confirm('Delete?')">&times;</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- CENTER COLUMN: PRODUCT FORM -->
            <div class="col-xl-4 col-lg-8">
                <div class="admin-card p-4">
                    <h5 class="mb-4"><?= $item_data['id'] ? 'Edit Accessory' : 'Add Accessory' ?></h5>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $item_data['id'] ?>">
                        <input type="hidden" name="existing_image" value="<?= $item_data['image_path'] ?>">

                        <!-- NEW RELEASE TOGGLE -->
                        <div class="form-check form-switch mb-3 p-3 bg-light rounded border">
                            <input class="form-check-input" type="checkbox" name="is_new_release" value="1" id="newRel" <?= $item_data['is_new_release'] ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold text-danger" for="newRel">
                                <i class="fas fa-fire"></i> Mark as New Release
                            </label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($item_data['name']) ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Brand</label>
                                <select name="brand_id" class="form-select" required>
                                    <option value="">Select...</option>
                                    <?php foreach($brands_list as $b): ?>
                                        <option value="<?= $b['id'] ?>" <?= ($item_data['brand_id'] == $b['id']) ? 'selected' : '' ?>><?= $b['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Main Category</label>
                                <select name="category_id" id="mainCatSelect" class="form-select" required onchange="filterSubCats()">
                                    <option value="">Select...</option>
                                    <?php foreach($cats_list as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= ($item_data['category_id'] == $c['id']) ? 'selected' : '' ?>><?= $c['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- [ADDED] Sub Category Select -->
                        <div class="mb-3">
                            <label class="form-label">Sub Category</label>
                            <select name="sub_category_id" id="subCatSelect" class="form-select">
                                <option value="0" data-parent="0">Select Main First...</option>
                                <?php foreach($sub_cats_list as $sc): ?>
                                    <option value="<?= $sc['id'] ?>" data-parent="<?= $sc['category_id'] ?>" <?= ($item_data['sub_category_id'] == $sc['id']) ? 'selected' : '' ?>>
                                        <?= $sc['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="short_description" class="form-control" rows="2"><?= $item_data['short_description'] ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <?php if(!empty($item_data['image_path'])): ?>
                                <div class="mb-2"><img src="uploads/<?= $item_data['image_path'] ?>" width="60" class="rounded border"></div>
                            <?php endif; ?>
                            <input type="file" name="image" class="form-control">
                        </div>

                        <button name="save_accessory" class="btn btn-dark w-100 py-2">Save Product</button>
                        <?php if($item_data['id']): ?>
                            <a href="admin_accessories.php" class="btn btn-sm btn-link w-100 text-muted mt-2">Cancel Edit</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- RIGHT COLUMN: INVENTORY LIST -->
            <div class="col-xl-5 col-lg-12">
                <div class="admin-card p-4">
                    <h5 class="mb-4">Inventory List</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Img</th>
                                    <th>Details</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($products as $p): ?>
                                    <tr>
                                        <td><img src="uploads/<?= $p['image_path'] ?>" width="40" height="40" style="object-fit:cover" class="rounded border" onerror="this.src='https://via.placeholder.com/40'"></td>
                                        <td>
                                            <div class="fw-bold small">
                                                <?= htmlspecialchars($p['name']) ?>
                                                <?php if($p['is_new_release']): ?>
                                                    <span class="new-tag">NEW</span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="text-muted small">Rs.<?= number_format($p['price']) ?></span><br>
                                            <!-- [UPDATED] Show Category > SubCat -->
                                            <span style="font-size:11px; color:#666;">
                                                <?= $p['cat_name'] ?>
                                                <?= $p['sub_name'] ? ' <i class="fa fa-angle-right"></i> '.$p['sub_name'] : '' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?edit=<?= $p['id'] ?>" class="text-primary me-2"><i class="fa fa-edit"></i></a>
                                            <a href="?del_acc=<?= $p['id'] ?>" class="text-danger" onclick="return confirm('Delete?')"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<!-- [ADDED] Script to Filter Sub Categories -->
<script>
function filterSubCats() {
    var mainCat = document.getElementById('mainCatSelect').value;
    var subSelect = document.getElementById('subCatSelect');
    var options = subSelect.querySelectorAll('option');

    subSelect.value = "0"; // Reset selection

    options.forEach(function(opt) {
        if(opt.value === "0") {
            opt.style.display = "block";
            return;
        }
        if(opt.getAttribute('data-parent') == mainCat) {
            opt.style.display = "block";
        } else {
            opt.style.display = "none";
        }
    });
}
// Run on load for edit mode
window.onload = function() {
    filterSubCats();
    <?php if($item_data['sub_category_id']): ?>
        document.getElementById('subCatSelect').value = "<?= $item_data['sub_category_id'] ?>";
    <?php endif; ?>
};
</script>

</body>
</html>