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
    $pdo->exec("CREATE TABLE IF NOT EXISTS networking_categories (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS networking_brands (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL)");
    
    // Create networking table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS networking (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT,
        brand_id INT,
        name VARCHAR(255),
        slug VARCHAR(255),
        price DECIMAL(10,2),
        short_description TEXT,
        long_description TEXT,
        image_path VARCHAR(255),
        meta_title VARCHAR(255),
        meta_description TEXT,
        meta_keywords VARCHAR(255),
        is_new_release TINYINT(1) DEFAULT 0
    )");

    // Safety column check
    $cols = $pdo->query("SHOW COLUMNS FROM networking")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('category_id', $cols)) { $pdo->exec("ALTER TABLE networking ADD COLUMN category_id INT AFTER id"); }
    // ADD NEW RELEASE COLUMN IF MISSING
    if (!in_array('is_new_release', $cols)) { $pdo->exec("ALTER TABLE networking ADD COLUMN is_new_release TINYINT(1) DEFAULT 0"); }
} catch (PDOException $e) { }

// --- Helper Functions ---
function createSlug($str) {
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    return preg_replace('/-+/', '-', $str);
}

$msg = ""; $edit = false;
$item = ['id'=>'','category_id'=>'','brand_id'=>'','name'=>'','slug'=>'','price'=>'','short_description'=>'','long_description'=>'','image_path'=>'','meta_title'=>'','meta_description'=>'','meta_keywords'=>'','is_new_release'=>0];

// --- 2. META ACTIONS ---
if (isset($_POST['add_cat'])) {
    $pdo->prepare("INSERT INTO networking_categories (name) VALUES (?)")->execute([trim($_POST['cat_name'])]);
    header("Location: Networking.php"); exit();
}
if (isset($_GET['delete_cat'])) {
    $pdo->prepare("DELETE FROM networking_categories WHERE id = ?")->execute([$_GET['delete_cat']]);
    header("Location: Networking.php"); exit();
}
if (isset($_POST['add_brand'])) {
    $pdo->prepare("INSERT INTO networking_brands (name) VALUES (?)")->execute([trim($_POST['brand_name'])]);
    header("Location: Networking.php"); exit();
}
if (isset($_GET['delete_brand'])) {
    $pdo->prepare("DELETE FROM networking_brands WHERE id = ?")->execute([$_GET['delete_brand']]);
    header("Location: Networking.php"); exit();
}

// --- 3. PRODUCT ACTIONS ---
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM networking WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $res = $stmt->fetch();
    if($res) { $item = $res; $edit = true; }
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM networking WHERE id = ?")->execute([(int)$_GET['delete']]);
    header("Location: Networking.php"); exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save'])) {
    $id = $_POST['id'];
    $is_new_release = isset($_POST['is_new_release']) ? 1 : 0; // Capture the Tick

    $imageName = $_POST['old_img'];
    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['image']['name']);
        if(!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $imageName);
    }
    $slug = empty($_POST['slug']) ? createSlug($_POST['name']) : createSlug($_POST['slug']);
    
    $data = [
        $_POST['category_id'], $_POST['brand_id'], $_POST['name'], $slug, $_POST['price'], 
        $_POST['short_description'], $_POST['long_description'], $imageName, 
        $_POST['meta_title'], $_POST['meta_description'], $_POST['meta_keywords'],
        $is_new_release // <--- SAVED HERE
    ];

    if ($id) {
        $sql = "UPDATE networking SET category_id=?, brand_id=?, name=?, slug=?, price=?, short_description=?, long_description=?, image_path=?, meta_title=?, meta_description=?, meta_keywords=?, is_new_release=? WHERE id=?";
        $data[] = $id;
    } else {
        $sql = "INSERT INTO networking (category_id, brand_id, name, slug, price, short_description, long_description, image_path, meta_title, meta_description, meta_keywords, is_new_release) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    }
    $pdo->prepare($sql)->execute($data);
    header("Location: Networking.php?success=1"); exit();
}

// 4. FETCH DATA
$categories = $pdo->query("SELECT * FROM networking_categories ORDER BY name ASC")->fetchAll();
$brands = $pdo->query("SELECT * FROM networking_brands ORDER BY name ASC")->fetchAll();
$products = $pdo->query("SELECT n.*, c.name as cname, b.name as bname FROM networking n LEFT JOIN networking_categories c ON n.category_id = c.id LEFT JOIN networking_brands b ON n.brand_id = b.id ORDER BY n.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Networking Admin | Saral IT</title>
    
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
       :root { --sidebar-width: 260px; --primary-dark: #2c3e50; --accent: #3498db; }
        body { background: #f4f7f6; margin: 0; display: flex; font-family: 'Segoe UI', sans-serif; }

      
    
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 30px; min-width: 0; }
        .card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px; border: none; }
        
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .badge-tag { background: #f0f2f5; padding: 5px 12px; border-radius: 20px; font-size: 12px; margin: 3px; display: inline-block; border: 1px solid #ddd; }
        .badge-tag a { color: #ff4757; text-decoration: none; margin-left: 8px; font-weight: bold; }
        
        label { font-weight: bold; display: block; margin-top: 15px; font-size: 0.85em; color: #666; }
        input, textarea, select { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 14px; }
        .btn1 { background: var(--accent); color: white; border: none; padding: 15px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 10px; transition: 0.3s; }
        .btn1:hover { background: #2980b9; }
        
        .seo-box { background: #f9f9ff; border: 1px dashed var(--accent); padding: 15px; border-radius: 8px; margin: 20px 0; }
        
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #f8fafc; color: #888; font-size: 11px; text-transform: uppercase; }
        .tag { padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; color: white; }
        .tag-cat { background: var(--accent); }
        .tag-brand { background: #4a5568; }
        .new-release-badge { background: #ff4757; color: white; font-size: 9px; padding: 2px 5px; border-radius: 3px; font-weight: bold; text-transform: uppercase; }

        /* Form Switch Styling */
        .form-check { display: flex; align-items: center; gap: 10px; padding: 10px; border-radius: 8px; background: #fffcf5; border: 1px solid #ffeeba; margin-top: 15px; }
        .form-check input { width: auto; cursor: pointer; }
        .form-check label { margin: 0; color: #d9534f; cursor: pointer; }
    </style>
</head>
<body>



<main class="main-content">
    
    <div class="meta-grid">
        <div class="card">
            <h6><i class="fa fa-folder text-primary"></i> Categories</h6>
            <form method="POST" style="display:flex; gap:5px;"><input type="text" name="cat_name" placeholder="Add Category" required><button name="add_cat" class="btn1" style="width:auto; margin:0; padding:0 15px;">Add</button></form>
            <div style="margin-top:10px;"><?php foreach($categories as $c): ?><span class="badge-tag"><?= $c['name'] ?> <a href="?delete_cat=<?= $c['id'] ?>" onclick="return confirm('Delete?')">&times;</a></span><?php endforeach; ?></div>
        </div>
        <div class="card">
            <h6><i class="fa fa-tag text-dark"></i> Brands</h6>
            <form method="POST" style="display:flex; gap:5px;"><input type="text" name="brand_name" placeholder="Add Brand" required><button name="add_brand" class="btn1" style="width:auto; margin:0; padding:0 15px;">Add</button></form>
            <div style="margin-top:10px;"><?php foreach($brands as $b): ?><span class="badge-tag"><?= $b['name'] ?> <a href="?delete_brand=<?= $b['id'] ?>" onclick="return confirm('Delete?')">&times;</a></span><?php endforeach; ?></div>
        </div>
    </div>

    <div class="card">
        <h5 style="margin-top:0;"><?= $edit ? "Edit Product" : "Add Networking Device" ?></h5>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $item['id'] ?>">
            <input type="hidden" name="old_img" value="<?= $item['image_path'] ?>">
            
            <!-- NEW RELEASE TICK -->
            <div class="form-check">
                <input type="checkbox" name="is_new_release" value="1" id="newRel" <?= $item['is_new_release'] ? 'checked' : '' ?>>
                <label for="newRel"><i class="fa fa-fire"></i> SHOW IN NEW RELEASES SECTION</label>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 2fr 1fr; gap: 15px;">
                <div><label>Category</label><select name="category_id" required><option value="">-</option><?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>" <?= $item['category_id']==$c['id']?'selected':'' ?>><?= $c['name'] ?></option><?php endforeach; ?></select></div>
                <div><label>Brand</label><select name="brand_id" required><option value="">-</option><?php foreach($brands as $b): ?><option value="<?= $b['id'] ?>" <?= $item['brand_id']==$b['id']?'selected':'' ?>><?= $b['name'] ?></option><?php endforeach; ?></select></div>
                <div><label>Product Name</label><input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" required></div>
                <div><label>Price (Rs.)</label><input type="number" name="price" value="<?= $item['price'] ?>" required></div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div><label>Short Summary</label><textarea name="short_description" rows="2"><?= $item['short_description'] ?></textarea></div>
                <div><label>Full Specifications</label><textarea name="long_description" rows="2"><?= $item['long_description'] ?></textarea></div>
            </div>

            <label>Product Image</label><input type="file" name="image">

            <div class="seo-box">
                <h6 style="margin-top:0;">SEO Configuration</h6>
                <div style="display:flex; gap:15px;"><input type="text" name="slug" value="<?= $item['slug'] ?>" placeholder="URL Slug"><input type="text" name="meta_title" value="<?= $item['meta_title'] ?>" placeholder="Meta Title"></div>
                <textarea name="meta_description" placeholder="Meta Description"><?= $item['meta_description'] ?></textarea>
            </div>

            <button type="submit" name="save" class="btn1 shadow"><?= $edit ? "Update Product" : "Save Product" ?></button>
            <?php if($edit): ?><a href="Networking.php" style="display:block; text-align:center; margin-top:10px; color:#666;">Cancel Edit</a><?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h6 style="margin-top:0;">Networking Inventory</h6>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Img</th><th>Details</th><th>Brand/Cat</th><th>Price</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach($products as $p): ?>
                    <tr>
                        <td><img src="uploads/<?= $p['image_path'] ?>" width="35" height="35" style="object-fit:cover; border-radius:5px;" onerror="this.src='https://via.placeholder.com/35'"></td>
                        <td>
                            <strong><?= htmlspecialchars($p['name']) ?></strong>
                            <?php if($p['is_new_release']): ?> <span class="new-release-badge">NEW</span> <?php endif; ?>
                            <br><small style="color:#999"><?= $p['slug'] ?></small>
                        </td>
                        <td><span class="tag tag-brand"><?= $p['bname'] ?></span> <span class="tag tag-cat"><?= $p['cname'] ?></span></td>
                        <td class="fw-bold text-danger">Rs. <?= number_format($p['price']) ?></td>
                        <td>
                            <a href="?edit=<?= $p['id'] ?>" class="text-primary"><i class="fa fa-edit"></i></a> &nbsp;
                            <a href="?delete=<?= $p['id'] ?>" class="text-danger" onclick="return confirm('Delete?')"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>