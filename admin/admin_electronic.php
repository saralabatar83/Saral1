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
require_once 'db.php'; 

/**
 * 1. DATABASE AUTO-SETUP
 */
try {
    // Create Meta Tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS electronic_brands (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS electronic_subs (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL)");
    
    // Create main table with is_new_release column
    $pdo->exec("CREATE TABLE IF NOT EXISTS electronic (
        id INT AUTO_INCREMENT PRIMARY KEY,
        brand_id INT,
        sub_id INT,
        title VARCHAR(255),
        price DECIMAL(10,2),
        short_desc TEXT,
        long_desc TEXT,
        image_path VARCHAR(255),
        meta_title VARCHAR(255),
        meta_description TEXT,
        is_new_release TINYINT(1) DEFAULT 0
    )");

    // Column safety check
    $cols = $pdo->query("SHOW COLUMNS FROM electronic")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('brand_id', $cols)) { $pdo->exec("ALTER TABLE electronic ADD COLUMN brand_id INT AFTER id"); }
    // ADD NEW RELEASE COLUMN IF MISSING
    if (!in_array('is_new_release', $cols)) { $pdo->exec("ALTER TABLE electronic ADD COLUMN is_new_release TINYINT(1) DEFAULT 0"); }
} catch (PDOException $e) { }

// --- 2. LOGIC HANDLERS ---
if (isset($_POST['add_brand'])) {
    $pdo->prepare("INSERT INTO electronic_brands (name) VALUES (?)")->execute([trim($_POST['brand_name'])]);
    header("Location: admin_electronic.php"); exit();
}
if (isset($_GET['del_brand'])) {
    $pdo->prepare("DELETE FROM electronic_brands WHERE id = ?")->execute([(int)$_GET['del_brand']]);
    header("Location: admin_electronic.php"); exit();
}
if (isset($_POST['add_sub'])) {
    $pdo->prepare("INSERT INTO electronic_subs (name) VALUES (?)")->execute([trim($_POST['sub_name'])]);
    header("Location: admin_electronic.php"); exit();
}
if (isset($_GET['del_sub'])) {
    $pdo->prepare("DELETE FROM electronic_subs WHERE id = ?")->execute([(int)$_GET['del_sub']]);
    header("Location: admin_electronic.php"); exit();
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM electronic WHERE id = ?")->execute([(int)$_GET['delete']]);
    header("Location: admin_electronic.php?msg=deleted"); exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_product'])) {
    $id = $_POST['id'];
    $is_new_release = isset($_POST['is_new_release']) ? 1 : 0; // Capture the Tick

    $image = $_POST['existing_image'];
    if (!empty($_FILES['image']['name'])) {
        $image = time() . "_" . $_FILES['image']['name'];
        if(!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $image);
    }
    
    $data = [
        $_POST['brand_id'], $_POST['sub_id'], $_POST['title'], $_POST['price'], 
        $_POST['short_desc'], $_POST['long_desc'], $image, 
        $_POST['meta_title'], $_POST['meta_description'], 
        $is_new_release // <--- SAVED HERE
    ];

    if ($id) {
        $sql = "UPDATE electronic SET brand_id=?, sub_id=?, title=?, price=?, short_desc=?, long_desc=?, image_path=?, meta_title=?, meta_description=?, is_new_release=? WHERE id=?";
        $data[] = $id;
    } else {
        $sql = "INSERT INTO electronic (brand_id, sub_id, title, price, short_desc, long_desc, image_path, meta_title, meta_description, is_new_release) VALUES (?,?,?,?,?,?,?,?,?,?)";
    }
    $pdo->prepare($sql)->execute($data);
    header("Location: admin_electronic.php?msg=success"); exit();
}

// Fetch Lists
$brands = $pdo->query("SELECT * FROM electronic_brands ORDER BY name ASC")->fetchAll();
$subs = $pdo->query("SELECT * FROM electronic_subs ORDER BY name ASC")->fetchAll();
$products = $pdo->query("SELECT e.*, b.name as bname, s.name as sname FROM electronic e LEFT JOIN electronic_brands b ON e.brand_id = b.id LEFT JOIN electronic_subs s ON e.sub_id = s.id ORDER BY e.id DESC")->fetchAll();

$edit_mode = false;
$item = ['id' => '', 'brand_id' => '', 'sub_id' => '', 'title' => '', 'price' => '', 'short_desc' => '', 'long_desc' => '', 'image_path' => '', 'meta_title' => '', 'meta_description' => '', 'is_new_release' => 0];
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM electronic WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $res = $stmt->fetch();
    if ($res) { $item = $res; $edit_mode = true; }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Electronics Admin | Saral IT</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
     
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; --primary-dark: #2c3e50; --accent: #3498db; }
        body { background: #f4f7f6; margin: 0; display: flex; font-family: 'Segoe UI', sans-serif; }

        
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 25px; min-width: 0; }
        .manager-card { background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #ddd; height: 100%; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .main-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: none; overflow: hidden; }
        .badge-del { background: #f8f9fa; color: #333; padding: 5px 12px; border-radius: 20px; font-size: 11px; margin: 3px; display: inline-block; text-decoration: none; border: 1px solid #ddd; }
        .badge-del:hover { background: #ff7675; color: white; border-color: #d63031; }
        .new-release-tag { background: #ff4757; color: white; font-size: 9px; padding: 2px 5px; border-radius: 3px; font-weight: bold; text-transform: uppercase; }
        label { font-weight: bold; font-size: 12px; color: #555; margin-top: 10px; display: block; }
    </style>
</head>
<body>



<main class="main-content">
    <div class="container-fluid">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="manager-card">
                    <h6><i class="fa fa-tag text-primary"></i> Brands Manager</h6>
                    <form method="POST" class="d-flex gap-2 mb-3">
                        <input type="text" name="brand_name" class="form-control form-control-sm" placeholder="New Brand" required>
                        <button name="add_brand" class="btn btn-sm btn-primary">Add</button>
                    </form>
                    <?php foreach($brands as $b): ?>
                        <a href="?del_brand=<?= $b['id'] ?>" class="badge-del"><?= $b['name'] ?> &times;</a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="manager-card">
                    <h6><i class="fa fa-layer-group text-success"></i> Category Manager</h6>
                    <form method="POST" class="d-flex gap-2 mb-3">
                        <input type="text" name="sub_name" class="form-control form-control-sm" placeholder="New Category" required>
                        <button name="add_sub" class="btn btn-sm btn-success">Add</button>
                    </form>
                    <?php foreach($subs as $s): ?>
                        <a href="?del_sub=<?= $s['id'] ?>" class="badge-del"><?= $s['name'] ?> &times;</a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="main-card row g-0">
            <div class="col-lg-4 p-4 border-end bg-white">
                <h5 class="text-primary border-bottom pb-2 mb-3"><?= $edit_mode ? "Update Product" : "New Electronic" ?></h5>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                    <input type="hidden" name="existing_image" value="<?= $item['image_path'] ?>">

                    <!-- NEW RELEASE TICK -->
                    <div class="form-check form-switch mt-2 p-3 border rounded bg-light">
                        <input class="form-check-input" type="checkbox" name="is_new_release" value="1" id="newRel" <?= $item['is_new_release'] ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold text-danger m-0" for="newRel" style="display:inline; font-size:12px;">
                            <i class="fa fa-fire"></i> SHOW IN NEW RELEASES
                        </label>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label>Brand</label>
                            <select name="brand_id" class="form-select form-select-sm" required>
                                <?php foreach($brands as $b): ?>
                                    <option value="<?= $b['id'] ?>" <?= $item['brand_id']==$b['id']?'selected':'' ?>><?= $b['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label>Category</label>
                            <select name="sub_id" class="form-select form-select-sm" required>
                                <?php foreach($subs as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= $item['sub_id']==$s['id']?'selected':'' ?>><?= $s['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <label>Product Name</label>
                    <input type="text" name="title" class="form-control form-control-sm" value="<?= htmlspecialchars($item['title']) ?>" required>
                    
                    <label>Price (Rs.)</label>
                    <input type="number" step="0.01" name="price" class="form-control form-control-sm" value="<?= $item['price'] ?>" required>

                    <label>Description (Technical)</label>
                    <textarea name="long_desc" class="form-control form-control-sm" rows="3"><?= $item['long_desc'] ?></textarea>

                    <div class="mt-3 p-2 bg-light rounded border border-info border-opacity-25">
                        <small class="fw-bold text-info"><i class="fa fa-search"></i> SEO TAGS</small>
                        <input type="text" name="meta_title" placeholder="Meta Title" class="form-control form-control-sm mt-2 mb-1" value="<?= $item['meta_title'] ?>">
                        <textarea name="meta_description" placeholder="Meta Desc" class="form-control form-control-sm"><?= $item['meta_description'] ?></textarea>
                    </div>

                    <label>Product Image</label>
                    <input type="file" name="image" class="form-control form-control-sm">

                    <button type="submit" name="save_product" class="btn btn-primary w-100 mt-4 py-2 fw-bold shadow-sm">SAVE PRODUCT</button>
                    <?php if($edit_mode): ?><a href="admin_electronic.php" class="btn btn-link w-100 text-muted mt-2">Cancel Edit</a><?php endif; ?>
                </form>
            </div>

            <div class="col-lg-8 p-4 bg-light bg-opacity-50">
                <h5 class="mb-3">Item Inventory</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle bg-white rounded shadow-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Brand/Category</th>
                                <th>Price</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($products as $p): ?>
                            <tr>
                                <td><img src="uploads/<?= $p['image_path'] ?>" width="40" height="40" class="rounded border" onerror="this.src='https://via.placeholder.com/40'"></td>
                                <td>
                                    <strong><?= htmlspecialchars($p['title']) ?></strong>
                                    <?php if($p['is_new_release']): ?> <span class="new-release-tag">NEW</span> <?php endif; ?>
                                </td>
                                <td><span class="text-muted small"><?= $p['bname'] ?> | <?= $p['sname'] ?></span></td>
                                <td class="fw-bold text-danger small">Rs. <?= number_format($p['price']) ?></td>
                                <td class="text-end">
                                    <a href="?edit=<?= $p['id'] ?>" class="text-warning me-2"><i class="fa fa-edit"></i></a>
                                    <a href="?delete=<?= $p['id'] ?>" class="text-danger" onclick="return confirm('Delete product?')"><i class="fa fa-trash"></i></a>
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

</body>
</html>