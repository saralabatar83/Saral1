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
       <li> <a href="backend.php"><i class="fas fa-user-check"></i> <span>Customer Approve</span> </a></li>
             <li><a href="logout.php" style="color: #ff7675;"><i class="fas fa-power-off"></i> <span>Logout</span></a></li>
           
        </ul>
    </aside>

        <!-- Remaining Dashboard Content goes here (Table/Form) -->
    </main>

</body>
<?php
/**
 * Saral IT - admin_categories.php
 * Managed Category and Hero Slider Management with Edit Functionality
 */

require_once 'db.php';      
require_once '../includes/functions.php'; 

$message = "";
$edit_hero_data = null;
$edit_cat_data = null;

// --- 1. HERO SLIDER LOGIC ---

// Save or Update Hero
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_hero'])) {
    $target_dir = "../images/";
    $hero_id = $_POST['hero_id'] ?? null;

    if (!empty($_FILES["hero_image"]["name"])) {
        $file_name = time() . "_" . basename($_FILES["hero_image"]["name"]);
        if (move_uploaded_file($_FILES["hero_image"]["tmp_name"], $target_dir . $file_name)) {
            if ($hero_id) {
                // Update: Delete old file first
                $stmt = $pdo->prepare("SELECT file_name FROM slider_images WHERE id = ?");
                $stmt->execute([$hero_id]);
                $old_file = $stmt->fetchColumn();
                if ($old_file) @unlink($target_dir . $old_file);

                $sql = "UPDATE slider_images SET file_name = ? WHERE id = ?";
                $pdo->prepare($sql)->execute([$file_name, $hero_id]);
                $message = "Hero slide updated!";
            } else {
                // Insert New
                $sql = "INSERT INTO slider_images (file_name) VALUES (?)";
                $pdo->prepare($sql)->execute([$file_name]);
                $message = "Hero slide uploaded!";
            }
        }
    }
}

// Delete Hero
if (isset($_GET['delete_hero'])) {
    $id = (int)$_GET['delete_hero'];
    $stmt = $pdo->prepare("SELECT file_name FROM slider_images WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetchColumn();
    if ($file) @unlink("../images/" . $file);
    $pdo->prepare("DELETE FROM slider_images WHERE id = ?")->execute([$id]);
    header("Location: admin_categories.php?msg=deleted");
    exit();
}

// Fetch Hero for Editing
if (isset($_GET['edit_hero'])) {
    $id = (int)$_GET['edit_hero'];
    $stmt = $pdo->prepare("SELECT * FROM slider_images WHERE id = ?");
    $stmt->execute([$id]);
    $edit_hero_data = $stmt->fetch();
}


// --- 2. CATEGORY LOGIC ---

// Save or Update Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    $cat_id = $_POST['cat_id'] ?? null;
    $name   = htmlspecialchars($_POST['name']);
    $link   = htmlspecialchars($_POST['target_link']);
    $color  = $_POST['bg_color'];
    
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    if ($cat_id) {
        // UPDATE LOGIC
        if (!empty($_FILES["cat_image"]["name"])) {
            $file_name = time() . "_" . basename($_FILES["cat_image"]["name"]);
            move_uploaded_file($_FILES["cat_image"]["tmp_name"], $target_dir . $file_name);
            $sql = "UPDATE shop_categories SET name=?, target_link=?, bg_color=?, image_path=? WHERE id=?";
            $pdo->prepare($sql)->execute([$name, $link, $color, $file_name, $cat_id]);
        } else {
            $sql = "UPDATE shop_categories SET name=?, target_link=?, bg_color=? WHERE id=?";
            $pdo->prepare($sql)->execute([$name, $link, $color, $cat_id]);
        }
        $message = "Category updated successfully!";
    } else {
        // INSERT LOGIC
        if (!empty($_FILES["cat_image"]["name"])) {
            $file_name = time() . "_" . basename($_FILES["cat_image"]["name"]);
            move_uploaded_file($_FILES["cat_image"]["tmp_name"], $target_dir . $file_name);
            $sql = "INSERT INTO shop_categories (name, target_link, bg_color, image_path) VALUES (?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$name, $link, $color, $file_name]);
            $message = "Category added successfully!";
        }
    }
}

// Delete Category
if (isset($_GET['delete_cat'])) {
    $id = (int)$_GET['delete_cat'];
    $pdo->prepare("DELETE FROM shop_categories WHERE id = ?")->execute([$id]);
    header("Location: admin_categories.php?msg=cat_deleted");
    exit();
}

// Fetch Category for Editing
if (isset($_GET['edit_cat'])) {
    $id = (int)$_GET['edit_cat'];
    $stmt = $pdo->prepare("SELECT * FROM shop_categories WHERE id = ?");
    $stmt->execute([$id]);
    $edit_cat_data = $stmt->fetch();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Manager - Saral IT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
     
    <style>
        /* [ ... Keeping your CSS exactly the same ... ] */
        :root {
            --primary: #7986cb;
            --secondary: #5c6bc0;
            --bg-body: #f4f7f6;
            --sidebar-bg: #2c3e50;
            --sidebar-hover: #34495e;
            --text-main: #333;
            --white: #ffffff;
            --danger: #e74c3c;
            --sidebar-width: 260px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: var(--bg-body); display: flex; min-height: 100vh; color: var(--text-main); }

    
        .main-view { margin-left: var(--sidebar-width); flex: 1; padding: 25px; width: calc(100% - var(--sidebar-width)); }
        .dashboard-grid { display: grid; grid-template-columns: 1.4fr 1fr; gap: 20px; align-items: start; }
        .panel { background: var(--white); padding: 12px 15px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 20px; border: 1px solid #eee; }
        .panel h2 { margin-bottom: 12px; font-size: 14px; color: #444; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
        .form-group { display: flex; flex-direction: column; margin-bottom: 8px; }
        label { margin-bottom: 3px; font-weight: 700; font-size: 11px; color: #64748b; }
        input[type="text"], input[type="file"], input[type="color"] { padding: 6px 10px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 12px; height: 32px; outline: none; }
        .btn-submit { background: var(--primary); color: white; border: none; padding: 7px 15px; border-radius: 4px; cursor: pointer; font-weight: 700; font-size: 11px; width: fit-content; }
        .btn-cancel { background: #94a3b8; color: white; text-decoration: none; padding: 7px 15px; border-radius: 4px; font-size: 11px; margin-left: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 8px 5px; font-size: 10px; text-transform: uppercase; color: #94a3b8; border-bottom: 1px solid #f1f5f9; }
        td { padding: 6px 5px; border-bottom: 1px solid #f8fafc; font-size: 12px; vertical-align: middle; }
        .thumb-sm { width: 32px; height: 32px; object-fit: cover; border-radius: 4px; }
        .cat-icon-wrap { width: 34px; height: 34px; border-radius: 6px; display: flex; align-items: center; justify-content: center; }
        .badge-dest { background: #f1f5f9; color: #94a3b8; padding: 2px 6px; border-radius: 4px; font-size: 10px; }
        .action-icon { color: #6366f1; font-size: 14px; margin-left: 10px; cursor: pointer; text-decoration: none; }
        .action-icon.del { color: var(--danger); }
        .alert { padding: 10px 15px; background: #d4edda; color: #155724; border-radius: 6px; margin-bottom: 20px; font-size: 12px; border-left: 4px solid #28a745; }
    </style>
</head>
<body>

  
    <main class="main-view">
        <header style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
            <h1 style="font-size: 24px;">Store Manager</h1>
        </header>

        <?php if($message): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="dashboard-grid">
            
            <!-- LEFT COLUMN: Hero Slides -->
            <div class="col-left">
                <div class="panel">
                    <h2><i class="fas fa-image"></i> <?php echo $edit_hero_data ? 'Edit' : 'Add'; ?> Hero Slide</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="hero_id" value="<?php echo $edit_hero_data['id'] ?? ''; ?>">
                        <div class="form-group">
                            <label>Slide Image <?php echo $edit_hero_data ? '(Leave empty to keep current)' : ''; ?></label>
                            <input type="file" name="hero_image" <?php echo $edit_hero_data ? '' : 'required'; ?>>
                        </div>
                        <button type="submit" name="save_hero" class="btn-submit">
                            <?php echo $edit_hero_data ? 'Update Slide' : 'Upload Slide'; ?>
                        </button>
                        <?php if($edit_hero_data): ?>
                            <a href="admin_categories.php" class="btn-cancel">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="panel">
                    <h2><i class="fas fa-list"></i> Active Slides</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Preview</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $slides = $pdo->query("SELECT * FROM slider_images ORDER BY id DESC")->fetchAll();
                            foreach($slides as $s): ?>
                            <tr>
                                <td><img src="../images/<?php echo $s['file_name']; ?>" class="thumb-sm"></td>
                                <td style="text-align: right;">
                                    <a href="?edit_hero=<?php echo $s['id']; ?>" class="action-icon"><i class="fas fa-edit"></i></a>
                                    <a href="?delete_hero=<?php echo $s['id']; ?>" class="action-icon del" onclick="return confirm('Delete slide?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- RIGHT COLUMN: Categories -->
            <div class="col-right">
                <div class="panel">
                    <h2><i class="fas fa-plus-circle"></i> <?php echo $edit_cat_data ? 'Edit' : 'Add New'; ?> Category</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="cat_id" value="<?php echo $edit_cat_data['id'] ?? ''; ?>">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Category Name</label>
                                <input type="text" name="name" value="<?php echo $edit_cat_data['name'] ?? ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Target Link</label>
                                <input type="text" name="target_link" value="<?php echo $edit_cat_data['target_link'] ?? ''; ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Background Accent</label>
                                <input type="color" name="bg_color" value="<?php echo $edit_cat_data['bg_color'] ?? '#7986cb'; ?>">
                            </div>
                            <div class="form-group">
                                <label>Icon (Optional if editing)</label>
                                <input type="file" name="cat_image" <?php echo $edit_cat_data ? '' : 'required'; ?>>
                            </div>
                        </div>
                        <button type="submit" name="save_category" class="btn-submit">
                            <?php echo $edit_cat_data ? 'Update Category' : 'Create Category'; ?>
                        </button>
                        <?php if($edit_cat_data): ?>
                            <a href="admin_categories.php" class="btn-cancel">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="panel">
                    <h2><i class="fas fa-layer-group"></i> Shop Categories</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Icon</th>
                                <th>Name</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $cats = $pdo->query("SELECT * FROM shop_categories ORDER BY id DESC")->fetchAll();
                            foreach($cats as $c): ?>
                            <tr>
                                <td>
                                    <div class="cat-icon-wrap" style="background: <?php echo $c['bg_color']; ?>;">
                                        <img src="uploads/<?php echo $c['image_path']; ?>" style="width: 20px;">
                                    </div>
                                </td>
                                <td style="font-weight: 800; text-transform: uppercase;"><?php echo $c['name']; ?></td>
                                <td style="text-align: right;">
                                    <a href="?edit_cat=<?php echo $c['id']; ?>" class="action-icon"><i class="fas fa-edit"></i></a>
                                    <a href="?delete_cat=<?php echo $c['id']; ?>" class="action-icon del" onclick="return confirm('Delete category?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</body>
</html>