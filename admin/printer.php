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
 * SARAL IT SOLUTION - CUSTOMER INQUIRY MANAGEMENT
 */
require_once '../db.php'; 

// --- 1. DATABASE AUTO-SETUP (Ensure inquiries are saved) ---
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS inquiries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category VARCHAR(100),
        name VARCHAR(100),
        phone VARCHAR(20),
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) { }

// --- 2. BACKEND LOGIC (Save & Email) ---
$message_sent = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_inquiry'])) {
    $to       = "saralabatar83@gmail.com"; 
    $category = htmlspecialchars($_POST['Category']);
    $name     = htmlspecialchars($_POST['Name']);
    $phone    = htmlspecialchars($_POST['Phone']);
    $msg      = htmlspecialchars($_POST['Message']);

    // Save to Database first
    $stmt = $pdo->prepare("INSERT INTO inquiries (category, name, phone, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$category, $name, $phone, $msg]);

    // Send Email
    $subject = "New Website Inquiry from $name";
    $headers = "From: Saral IT Admin <no-reply@saralitsolution.com>\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body    = "Customer: $name\nPhone: $phone\nInterested In: $category\nMessage: $msg";
    
    @mail($to, $subject, $body, $headers);
    $message_sent = true;
}

// --- 3. DELETE INQUIRY ---
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM inquiries WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: formfillup.php?msg=Deleted"); exit();
}

// Fetch all inquiries for the "Customers" list
$customers = $pdo->query("SELECT * FROM inquiries ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Inquiries | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root { --sidebar-width: 260px; --primary-dark: #0f172a; --accent: #838de7; }
        body { background: #f4f7f6; margin: 0; display: flex; font-family: 'Segoe UI', sans-serif; }

        /* --- SIDEBAR STYLES --- */
       
        /* --- CONTENT AREA --- */
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; min-width: 0; }
        .card { background: white; border-radius: 15px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        
        .form-label { font-weight: 600; font-size: 13px; color: #555; }
        .table thead { background: #f8fafc; }
        .table th { font-size: 12px; text-transform: uppercase; color: #64748b; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
  
<?php
include '../db.php'; 

// --- 1. DATABASE AUTO-SETUP & COLUMN REPAIR ---
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS printer_brands (id INT AUTO_INCREMENT PRIMARY KEY, brand_name VARCHAR(100) NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS printer_subcategories (id INT AUTO_INCREMENT PRIMARY KEY, sub_name VARCHAR(100) NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS printer (id INT AUTO_INCREMENT PRIMARY KEY, brand_id INT, sub_id INT, title VARCHAR(255), price VARCHAR(100), short_description TEXT, long_description TEXT, image_path VARCHAR(255), target_link VARCHAR(255), meta_title VARCHAR(255), meta_description TEXT, meta_keywords VARCHAR(255))");

    $cols = $pdo->query("SHOW COLUMNS FROM printer")->fetchAll(PDO::FETCH_COLUMN);
    $needed = [
        'long_description' => "TEXT",
        'meta_title'       => "VARCHAR(255)",
        'meta_description' => "TEXT",
        'meta_keywords'    => "VARCHAR(255)",
        'price'            => "VARCHAR(100)",
        'is_new_release'   => "TINYINT(1) DEFAULT 0"
    ];
    
    foreach($needed as $col => $type) {
        if (!in_array($col, $cols)) { 
            $pdo->exec("ALTER TABLE printer ADD COLUMN $col $type"); 
        }
    }
} catch (PDOException $e) { die("Setup Error: " . $e->getMessage()); }

// --- 2. LOGIC HANDLERS ---

// Add Brand
if (isset($_POST['add_brand'])) {
    $pdo->prepare("INSERT INTO printer_brands (brand_name) VALUES (?)")->execute([$_POST['brand_name']]);
    header("Location: printer.php?msg=BrandAdded"); exit();
}

// Add Category
if (isset($_POST['add_sub'])) {
    $pdo->prepare("INSERT INTO printer_subcategories (sub_name) VALUES (?)")->execute([$_POST['sub_name']]);
    header("Location: printer.php?msg=SubAdded"); exit();
}

// Delete Handlers
if (isset($_GET['del_brand'])) { $pdo->prepare("DELETE FROM printer_brands WHERE id = ?")->execute([$_GET['del_brand']]); header("Location: printer.php"); exit(); }
if (isset($_GET['del_sub'])) { $pdo->prepare("DELETE FROM printer_subcategories WHERE id = ?")->execute([$_GET['del_sub']]); header("Location: printer.php"); exit(); }
if (isset($_GET['del_printer'])) { $pdo->prepare("DELETE FROM printer WHERE id = ?")->execute([$_GET['del_printer']]); header("Location: printer.php"); exit(); }

// SAVE/UPDATE PRINTER
if (isset($_POST['save_printer'])) {
    $id = $_POST['id'] ?: null;
    $is_new_release = isset($_POST['is_new_release']) ? 1 : 0;

    $img_name = $_POST['existing_image'];
    if (!empty($_FILES['image']['name'])) {
        $img_name = time() . "_" . $_FILES['image']['name'];
        if(!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/$img_name");
    }

    $data = [
        $_POST['brand_id'], $_POST['sub_id'], $_POST['title'], $_POST['price'], 
        $_POST['short_description'], $_POST['long_description'], 
        $img_name, $_POST['target_link'], 
        $_POST['meta_title'], $_POST['meta_description'], $_POST['meta_keywords'],
        $is_new_release
    ];

    if ($id) {
        $sql = "UPDATE printer SET brand_id=?, sub_id=?, title=?, price=?, short_description=?, long_description=?, image_path=?, target_link=?, meta_title=?, meta_description=?, meta_keywords=?, is_new_release=? WHERE id=?";
        $data[] = $id;
    } else {
        $sql = "INSERT INTO printer (brand_id, sub_id, title, price, short_description, long_description, image_path, target_link, meta_title, meta_description, meta_keywords, is_new_release) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";
    }
    $pdo->prepare($sql)->execute($data);
    header("Location: printer.php?msg=Saved"); exit();
}

// Fetch edit data
$edit_row = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM printer WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_row = $stmt->fetch();
}

// Global Lists
$brands_list = $pdo->query("SELECT * FROM printer_brands ORDER BY brand_name ASC")->fetchAll();
$subs_list   = $pdo->query("SELECT * FROM printer_subcategories ORDER BY sub_name ASC")->fetchAll();
$printers    = $pdo->query("SELECT p.*, b.brand_name, s.sub_name FROM printer p LEFT JOIN printer_brands b ON p.brand_id = b.id LEFT JOIN printer_subcategories s ON p.sub_id = s.id ORDER BY p.id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printer Management | Saral Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    <style>
        :root { --sidebar-width: 260px; --primary-dark: #2c3e50; --accent: #3498db; --bg-light: #f4f7f6; }
        body { background-color: var(--bg-light); margin: 0; display: flex; }
      
      
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; min-height: 100vh; box-sizing: border-box; }
        .admin-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 25px; }
        .seo-section { background: #fffcf0; border: 1px solid #ffeeba; padding: 15px; border-radius: 8px; }
        .badge-pill { background: #e9ecef; padding: 5px 12px; border-radius: 50px; font-size: 12px; display: inline-flex; align-items: center; margin: 3px; border: 1px solid #dee2e6; }
        .badge-pill a { color: #dc3545; margin-left: 8px; text-decoration: none; font-weight: bold; }
        .new-release-badge { background: #ff4757; color: white; font-size: 9px; padding: 2px 5px; border-radius: 3px; font-weight: bold; }
    </style>
</head>
<body>



<main class="main-content">
    <div class="container-fluid">
        <div class="row">
            
            <!-- LEFT COLUMN: PRINTER FORM -->
            <div class="col-lg-7">
                <div class="admin-card">
                    <h5 class="text-primary mb-4">
                        <i class="fas <?= $edit_row ? 'fa-edit' : 'fa-plus-circle' ?> me-2"></i>
                        <?= $edit_row ? 'Edit Printer' : 'Add New Printer' ?>
                    </h5>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $edit_row['id'] ?? '' ?>">
                        <input type="hidden" name="existing_image" value="<?= $edit_row['image_path'] ?? '' ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold mb-1">Brand</label>
                                <select name="brand_id" class="form-select" required>
                                    <option value="">-- Choose Brand --</option>
                                    <?php foreach($brands_list as $b): ?>
                                        <option value="<?= $b['id'] ?>" <?= (@$edit_row['brand_id'] == $b['id']) ? 'selected' : '' ?>><?= $b['brand_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold mb-1">Category</label>
                                <select name="sub_id" class="form-select" required>
                                    <option value="">-- Choose Category --</option>
                                    <?php foreach($subs_list as $s): ?>
                                        <option value="<?= $s['id'] ?>" <?= (@$edit_row['sub_id'] == $s['id']) ? 'selected' : '' ?>><?= $s['sub_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- NEW RELEASE TICK BOX -->
                        <div class="form-check form-switch mb-3 p-3 border rounded bg-light">
                            <input class="form-check-input" type="checkbox" name="is_new_release" id="newRel" value="1" 
                            <?= (isset($edit_row['is_new_release']) && $edit_row['is_new_release'] == 1) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold text-danger" for="newRel">
                                <i class="fa fa-fire"></i> SHOW IN NEW RELEASES SECTION
                            </label>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="fw-bold mb-1">Model Title</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($edit_row['title'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="fw-bold mb-1">Price</label>
                                <input type="text" name="price" class="form-control" placeholder="e.g. 24,000" value="<?= $edit_row['price'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold mb-1">Highlights</label>
                            <textarea name="short_description" class="form-control" rows="2"><?= $edit_row['short_description'] ?? '' ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="fw-bold mb-1">Specifications</label>
                            <textarea name="long_description" id="editor1"><?= $edit_row['long_description'] ?? '' ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="fw-bold mb-1">Image</label><input type="file" name="image" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="fw-bold mb-1">Link</label><input type="text" name="target_link" class="form-control" value="<?= $edit_row['target_link'] ?? '' ?>" required></div>
                        </div>

                        <div class="seo-section mt-3">
                            <h6 class="mb-3"><i class="fa fa-search me-2"></i>SEO Settings</h6>
                            <input type="text" name="meta_title" class="form-control mb-2" placeholder="Meta Title" value="<?= $edit_row['meta_title'] ?? '' ?>">
                            <textarea name="meta_description" class="form-control mb-2" rows="2" placeholder="Meta Description"><?= $edit_row['meta_description'] ?? '' ?></textarea>
                            <input type="text" name="meta_keywords" class="form-control" placeholder="Keywords" value="<?= $edit_row['meta_keywords'] ?? '' ?>">
                        </div>

                        <button name="save_printer" class="btn btn-primary w-100 mt-4 py-2 fw-bold">SAVE PRINTER</button>
                    </form>
                </div>
            </div>

            <!-- RIGHT COLUMN: MANAGERS -->
            <div class="col-lg-5">
                <!-- Brand Manager -->
                <div class="admin-card">
                    <h6 class="fw-bold mb-3">Quick Brand Manager</h6>
                    <form method="POST" class="d-flex gap-2 mb-3">
                        <input type="text" name="brand_name" class="form-control" placeholder="New Brand Name" required>
                        <button name="add_brand" class="btn btn-dark">Add</button>
                    </form>
                    <div class="mb-3">
                        <?php foreach($brands_list as $b): ?>
                            <span class="badge-pill"><?= $b['brand_name'] ?> <a href="?del_brand=<?= $b['id'] ?>">&times;</a></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Category Manager (FIXED) -->
                <div class="admin-card">
                    <h6 class="fw-bold mb-3">Quick Category Manager</h6>
                    <form method="POST" class="d-flex gap-2 mb-3">
                        <input type="text" name="sub_name" class="form-control" placeholder="New Category Name" required>
                        <button name="add_sub" class="btn btn-dark">Add</button>
                    </form>
                    <div class="mb-3">
                        <?php foreach($subs_list as $s): ?>
                            <span class="badge-pill"><?= $s['sub_name'] ?> <a href="?del_sub=<?= $s['id'] ?>">&times;</a></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Inventory List -->
                <div class="admin-card">
                    <h6 class="fw-bold mb-3">Printer Inventory</h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <tbody>
                                <?php foreach($printers as $p): ?>
                                <tr>
                                    <td><img src="uploads/<?= $p['image_path'] ?>" width="40" height="40" class="rounded border" style="object-fit:cover;" onerror="this.src='https://via.placeholder.com/40'"></td>
                                    <td>
                                        <div class="fw-bold small">
                                            <?= htmlspecialchars($p['title']) ?>
                                            <?php if($p['is_new_release']): ?> <span class="new-release-badge">NEW</span> <?php endif; ?>
                                        </div>
                                        <div class="small text-muted"><?= $p['brand_name'] ?> | <?= $p['sub_name'] ?></div>
                                    </td>
                                    <td class="text-end">
                                        <a href="?edit=<?= $p['id'] ?>" class="text-primary me-2"><i class="fa fa-edit"></i></a>
                                        <a href="?del_printer=<?= $p['id'] ?>" class="text-danger" onclick="return confirm('Delete this printer?')"><i class="fa fa-trash"></i></a>
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

<script>CKEDITOR.replace('editor1');</script>
</body>
</html>