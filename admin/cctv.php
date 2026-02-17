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
 * 1. DATABASE AUTO-SETUP & REPAIR
 */
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS cctv_brands (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS cctv_subcategories (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS cctv_sizes (id INT AUTO_INCREMENT PRIMARY KEY, size_name VARCHAR(100) NOT NULL)");
    
    // Create cctv table with is_new_release column
    $pdo->exec("CREATE TABLE IF NOT EXISTS cctv (
        id INT AUTO_INCREMENT PRIMARY KEY,
        brand_id INT,
        sub_id INT,
        size_id INT,
        name VARCHAR(255),
        short_description TEXT,
        full_description TEXT,
        price DECIMAL(10,2),
        image_path VARCHAR(255),
        target_link VARCHAR(255),
        meta_title VARCHAR(255),
        meta_description TEXT,
        meta_keywords VARCHAR(255),
        is_new_release TINYINT(1) DEFAULT 0
    )");

    // Safety column checks
    $cols = $pdo->query("SHOW COLUMNS FROM cctv")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('brand_id', $cols)) { $pdo->exec("ALTER TABLE cctv ADD COLUMN brand_id INT AFTER id"); }
    // ADD NEW RELEASE COLUMN IF MISSING
    if (!in_array('is_new_release', $cols)) { $pdo->exec("ALTER TABLE cctv ADD COLUMN is_new_release TINYINT(1) DEFAULT 0"); }
    
} catch (PDOException $e) { }

// --- 2. PHP LOGIC (DELETES, ADDS, SAVES) ---
if (isset($_GET['del_brand'])) { $pdo->prepare("DELETE FROM cctv_brands WHERE id = ?")->execute([$_GET['del_brand']]); header("Location: cctv.php"); exit(); }
if (isset($_GET['del_sub'])) { $pdo->prepare("DELETE FROM cctv_subcategories WHERE id = ?")->execute([$_GET['del_sub']]); header("Location: cctv.php"); exit(); }
if (isset($_GET['del_size'])) { $pdo->prepare("DELETE FROM cctv_sizes WHERE id = ?")->execute([$_GET['del_size']]); header("Location: cctv.php"); exit(); }

if (isset($_GET['delete_id'])) {
    $pdo->prepare("DELETE FROM cctv WHERE id = ?")->execute([$_GET['delete_id']]);
    header("Location: cctv.php?msg=deleted"); exit();
}

if (isset($_POST['add_brand'])) { $pdo->prepare("INSERT INTO cctv_brands (name) VALUES (?)")->execute([$_POST['brand_name']]); header("Location: cctv.php"); exit(); }
if (isset($_POST['add_sub'])) { $pdo->prepare("INSERT INTO cctv_subcategories (name) VALUES (?)")->execute([$_POST['sub_name']]); header("Location: cctv.php"); exit(); }
if (isset($_POST['add_size'])) { $pdo->prepare("INSERT INTO cctv_sizes (size_name) VALUES (?)")->execute([$_POST['size_name']]); header("Location: cctv.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_product'])) {
    $id = $_POST['id'];
    $is_new_release = isset($_POST['is_new_release']) ? 1 : 0; // Capture the Tick
    
    $img = $_POST['existing_image'];
    if (!empty($_FILES['image']['name'])) {
        $img = time() . "_" . $_FILES['image']['name'];
        if(!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $img);
    }

    $data = [
        $_POST['brand_id'], $_POST['sub_id'], $_POST['size_id'], $_POST['name'], 
        $_POST['short_description'], $_POST['full_description'], $_POST['price'], 
        $img, $_POST['target_link'], $_POST['meta_title'], $_POST['meta_description'], 
        $_POST['meta_keywords'], $is_new_release
    ];

    if ($id) {
        $sql = "UPDATE cctv SET brand_id=?, sub_id=?, size_id=?, name=?, short_description=?, full_description=?, price=?, image_path=?, target_link=?, meta_title=?, meta_description=?, meta_keywords=?, is_new_release=? WHERE id=?";
        $data[] = $id;
    } else {
        $sql = "INSERT INTO cctv (brand_id, sub_id, size_id, name, short_description, full_description, price, image_path, target_link, meta_title, meta_description, meta_keywords, is_new_release) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
    }
    $pdo->prepare($sql)->execute($data);
    header("Location: cctv.php?msg=success"); exit();
}

// --- 3. FETCH DATA ---
$brands = $pdo->query("SELECT * FROM cctv_brands ORDER BY name ASC")->fetchAll();
$subs = $pdo->query("SELECT * FROM cctv_subcategories ORDER BY name ASC")->fetchAll();
$sizes = $pdo->query("SELECT * FROM cctv_sizes ORDER BY size_name ASC")->fetchAll();
$products = $pdo->query("SELECT c.*, b.name as bname, sz.size_name FROM cctv c LEFT JOIN cctv_brands b ON c.brand_id = b.id LEFT JOIN cctv_sizes sz ON c.size_id = sz.id ORDER BY c.id DESC")->fetchAll();

$edit_mode = false;
$row = ['id'=>'','brand_id'=>'','sub_id'=>'','size_id'=>'','name'=>'','price'=>'','short_description'=>'','full_description'=>'','image_path'=>'','target_link'=>'','meta_title'=>'','meta_description'=>'','meta_keywords'=>'','is_new_release'=>0];
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM cctv WHERE id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if($res) {
        $row = $res;
        $edit_mode = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CCTV Admin | Saral IT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --sidebar-width: 260px; --primary-dark: #2c3e50; --accent: #3498db; }
        body { background: #f4f7f6; margin: 0; display: flex; font-family: 'Segoe UI', sans-serif; }

      
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 25px; min-width: 0; }
        .manager-card { background: #fff; padding: 15px; border-radius: 12px; border: 1px solid #ddd; height: 100%; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .badge-del { background: #eee; padding: 5px 10px; border-radius: 20px; font-size: 11px; margin-right: 5px; display: inline-block; margin-top: 5px; text-decoration: none; color: #333; border: 1px solid #ccc; }
        .badge-del:hover { background: #ff7675; color: white; }
        .new-release-tag { background: #ff4757; color: white; font-size: 9px; padding: 2px 5px; border-radius: 3px; font-weight: bold; text-transform: uppercase; }
        label { font-size: 12px; font-weight: bold; color: #666; margin-top: 10px; display: block; }
    </style>
</head>
<body>

<!-- SIDEBAR -->


<main class="main-content">
    <div class="container-fluid">
        <!-- META MANAGERS -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="manager-card">
                    <h6><i class="fa fa-tag text-primary"></i> Brands</h6>
                    <form method="POST" class="d-flex gap-1 mb-2">
                        <input type="text" name="brand_name" class="form-control form-control-sm" placeholder="Add Brand" required>
                        <button name="add_brand" class="btn btn-sm btn-primary">Add</button>
                    </form>
                    <?php foreach($brands as $b): ?>
                        <a href="?del_brand=<?= $b['id'] ?>" class="badge-del"><?= $b['name'] ?> &times;</a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="manager-card">
                    <h6><i class="fa fa-layer-group text-success"></i> Categories</h6>
                    <form method="POST" class="d-flex gap-1 mb-2">
                        <input type="text" name="sub_name" class="form-control form-control-sm" placeholder="Add Type" required>
                        <button name="add_sub" class="btn btn-sm btn-success">Add</button>
                    </form>
                    <?php foreach($subs as $s): ?>
                        <a href="?del_sub=<?= $s['id'] ?>" class="badge-del"><?= $s['name'] ?> &times;</a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="manager-card">
                    <h6><i class="fa fa-expand text-info"></i> Resolution</h6>
                    <form method="POST" class="d-flex gap-1 mb-2">
                        <input type="text" name="size_name" class="form-control form-control-sm" placeholder="Add Res" required>
                        <button name="add_size" class="btn btn-sm btn-info text-white">Add</button>
                    </form>
                    <?php foreach($sizes as $sz): ?>
                        <a href="?del_size=<?= $sz['id'] ?>" class="badge-del"><?= $sz['size_name'] ?> &times;</a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- FORM -->
            <div class="col-lg-4">
                <div class="card p-3 shadow-sm border-0 rounded-4">
                    <h5 class="text-primary"><?= $edit_mode ? "Update Product" : "New Camera" ?></h5>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="existing_image" value="<?= $row['image_path'] ?>">

                        <div class="row g-2">
                            <div class="col-4">
                                <label>Brand</label>
                                <select name="brand_id" class="form-select form-select-sm" required>
                                    <?php foreach($brands as $b): ?>
                                        <option value="<?= $b['id'] ?>" <?= $row['brand_id']==$b['id']?'selected':'' ?>><?= $b['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-4">
                                <label>Type</label>
                                <select name="sub_id" class="form-select form-select-sm" required>
                                    <?php foreach($subs as $s): ?>
                                        <option value="<?= $s['id'] ?>" <?= $row['sub_id']==$s['id']?'selected':'' ?>><?= $s['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-4">
                                <label>Resolution</label>
                                <select name="size_id" class="form-select form-select-sm" required>
                                    <?php foreach($sizes as $sz): ?>
                                        <option value="<?= $sz['id'] ?>" <?= $row['size_id']==$sz['id']?'selected':'' ?>><?= $sz['size_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- NEW RELEASE TICK BOX -->
                        <div class="form-check form-switch mt-3 p-3 border rounded bg-light">
                            <input class="form-check-input" type="checkbox" name="is_new_release" value="1" id="newRel" <?= $row['is_new_release'] ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold text-danger m-0" for="newRel" style="display:inline; font-size:12px;">
                                <i class="fa fa-fire"></i> SHOW IN NEW RELEASES
                            </label>
                        </div>

                        <label>Camera Name</label>
                        <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($row['name']) ?>" required>
                        
                        <label>Price & Target Link</label>
                        <div class="input-group">
                            <input type="number" name="price" class="form-control form-control-sm" value="<?= $row['price'] ?>" placeholder="Price">
                            <input type="text" name="target_link" class="form-control form-control-sm" value="<?= $row['target_link'] ?>" placeholder="Link">
                        </div>

                        <label>Description</label>
                        <textarea name="full_description" class="form-control form-control-sm" rows="3"><?= $row['full_description'] ?></textarea>

                        <label>Product Image</label>
                        <input type="file" name="image" class="form-control form-control-sm">

                        <div class="mt-3 p-2 bg-light rounded shadow-sm border">
                            <small class="fw-bold">SEO Config</small>
                            <input type="text" name="meta_title" placeholder="Meta Title" class="form-control form-control-sm mb-1" value="<?= $row['meta_title'] ?>">
                            <textarea name="meta_description" placeholder="Meta Desc" class="form-control form-control-sm mb-0"><?= $row['meta_description'] ?></textarea>
                        </div>

                        <button type="submit" name="save_product" class="btn btn-primary w-100 mt-3 fw-bold shadow-sm">Save Product</button>
                    </form>
                </div>
            </div>

            <!-- INVENTORY -->
            <div class="col-lg-8">
                <div class="card p-3 shadow-sm border-0 rounded-4">
                    <h5 class="mb-3">CCTV Inventory</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Brand / Res</th>
                                    <th>Price</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($products as $p): ?>
                                <tr>
                                    <td><img src="uploads/<?= $p['image_path'] ?>" width="35" height="35" class="rounded border" onerror="this.src='https://via.placeholder.com/35'"></td>
                                    <td>
                                        <strong><?= htmlspecialchars($p['name']) ?></strong>
                                        <?php if($p['is_new_release']): ?> <span class="new-release-tag">NEW</span> <?php endif; ?>
                                    </td>
                                    <td><span class="text-muted small"><?= $p['bname'] ?> (<?= $p['size_name'] ?>)</span></td>
                                    <td class="text-danger small fw-bold">Rs. <?= number_format($p['price']) ?></td>
                                    <td class="text-end">
                                        <a href="?edit_id=<?= $p['id'] ?>" class="text-warning me-2"><i class="fa fa-edit"></i></a>
                                        <a href="?delete_id=<?= $p['id'] ?>" class="text-danger" onclick="return confirm('Delete permanently?')"><i class="fa fa-trash"></i></a>
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

</body>
</html>