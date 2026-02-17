<?php
/**
 * CHARGERS & CABLES INVENTORY MANAGER - SIDEBAR LAYOUT
 */
require_once 'db.php'; 

// --- 1. SEO UTILITY ---
function generatePowerSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    return strtolower(preg_replace('~-+~', '-', $text));
}

$msg = "";
$msg_type = "";
$edit_item = null;

// --- 2. DELETE ACTION ---
if (isset($_GET['del'])) {
    $id = $_GET['del'];
    // Delete image file first
    $stmt = $pdo->prepare("SELECT product_image FROM chargers_cables WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    if($img && file_exists("uploads/products/$img")) { unlink("uploads/products/$img"); }

    $pdo->prepare("DELETE FROM chargers_cables WHERE id = ?")->execute([$id]);
    header("Location: admin_chargers.php?msg=deleted");
    exit();
}

// --- 3. FETCH FOR EDIT ---
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM chargers_cables WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_item = $stmt->fetch(PDO::FETCH_ASSOC);
}

// --- 4. SAVE ACTION ---
if (isset($_POST['save_item'])) {
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title']);
    $watt = $_POST['wattage'];
    $conn = $_POST['connector_type'];
    $len = $_POST['cable_length'];
    $sale = $_POST['sale_price'];
    $orig = $_POST['original_price'];
    $short = $_POST['short_desc'];
    $m_title = !empty($_POST['meta_title']) ? $_POST['meta_title'] : $title;
    $m_desc = $_POST['meta_desc'] ?? '';
    
    // Auto-generate slug
    $slug = generatePowerSlug($title);

    // Image Upload
    $img_file = $_POST['old_img'] ?? '';
    if (!empty($_FILES['img']['name'])) {
        $upload_dir = "uploads/products/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $img_file = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES["img"]["name"]));
        move_uploaded_file($_FILES["img"]["tmp_name"], $upload_dir . $img_file);
    }

    try {
        if ($id) {
            // UPDATE
            $sql = "UPDATE chargers_cables SET title=?, slug=?, wattage=?, connector_type=?, cable_length=?, short_desc=?, sale_price=?, original_price=?, product_image=?, meta_title=?, meta_desc=? WHERE id=?";
            $pdo->prepare($sql)->execute([$title, $slug, $watt, $conn, $len, $short, $sale, $orig, $img_file, $m_title, $m_desc, $id]);
            $msg = "Item updated successfully!";
            $msg_type = "success";
        } else {
            // INSERT
            $sql = "INSERT INTO chargers_cables (title, slug, wattage, connector_type, cable_length, short_desc, sale_price, original_price, product_image, meta_title, meta_desc) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
            $pdo->prepare($sql)->execute([$title, $slug, $watt, $conn, $len, $short, $sale, $orig, $img_file, $m_title, $m_desc]);
            $msg = "New Item added successfully!";
            $msg_type = "success";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $msg = "Error: This URL Slug already exists.";
        } else {
            $msg = "Database Error: " . $e->getMessage();
        }
        $msg_type = "error";
    }
}

// --- 5. FETCH LIST ---
$items = $pdo->query("SELECT * FROM chargers_cables ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chargers & Cables Admin | Saral IT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* --- GLOBAL & LAYOUT --- */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; display: flex; min-height: 100vh; }
        
        /* --- SIDEBAR --- */
        .sidebar { width: 250px; background-color: #2c3e50; color: white; flex-shrink: 0; padding-top: 20px; }
        .sidebar h3 { text-align: center; margin-bottom: 30px; letter-spacing: 1px; color: #fff; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li a { display: block; padding: 15px 25px; color: #b8c7ce; text-decoration: none; border-left: 4px solid transparent; transition: 0.3s; }
        .sidebar ul li a:hover, .sidebar ul li a.active { background-color: #1a252f; color: #fff; border-left-color: #3498db; }
        .sidebar i { width: 25px; text-align: center; margin-right: 10px; }

        /* --- MAIN CONTENT --- */
        .main-content { flex-grow: 1; padding: 30px; overflow-y: auto; }
        .box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 1100px; margin: 0 auto; }
        
        /* --- FORM STYLES --- */
        h2 { margin-bottom: 25px; color: #2c3e50; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; }
        
        /* 3-Column Grid for Specs */
        .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px; }

        label { font-weight: 600; font-size: 0.9rem; color: #555; display: block; margin-top: 15px; margin-bottom: 5px; }
        input, textarea, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 0.95rem; }
        input:focus { border-color: #3498db; outline: none; }
        
        /* SEO Section */
        .seo-section { background: #f8faff; padding: 20px; border-radius: 6px; border: 1px dashed #cbd5e0; margin-top: 25px; }
        .seo-section h4 { margin-top: 0; color: #3498db; font-size: 1rem; margin-bottom: 10px; }

        /* Alerts */
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert.success { background: #d1e7dd; color: #0f5132; }
        .alert.error { background: #f8d7da; color: #842029; }

        /* Buttons */
        .btn { background: #3498db; color: white; border: none; padding: 12px 25px; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 1rem; margin-top: 20px; width: 100%; transition: 0.3s; }
        .btn:hover { background: #2980b9; }
        .btn-cancel { display: block; text-align: center; margin-top: 10px; color: #666; text-decoration: none; }

        /* --- TABLE --- */
        .table-container { margin-top: 40px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        th { background: #ecf0f1; color: #2c3e50; padding: 15px; text-align: left; font-size: 0.9rem; }
        td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: middle; }
        
        .price-tag { font-weight: bold; color: #27ae60; }
        .price-old { text-decoration: line-through; color: #999; font-size: 0.85rem; margin-left: 5px; }
        .badge { background: #eef2ff; color: #4338ca; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; }
        .spec-details { font-size: 0.85rem; color: #666; margin-top: 4px; }

        .action-btn { padding: 6px 10px; border-radius: 4px; color: white; text-decoration: none; font-size: 0.85rem; margin-right: 5px; }
        .btn-edit { background: #f1c40f; color: #fff; }
        .btn-del { background: #e74c3c; color: #fff; }

        @media (max-width: 768px) {
            body { flex-direction: column; }
            .sidebar { width: 100%; padding: 10px; }
            .sidebar ul li { display: inline-block; }
            .form-grid-3, .form-grid-2 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- LEFT SIDEBAR -->
    <nav class="sidebar">
        <h3><i class="fas fa-cogs"></i> Admin Panel</h3>
        <ul>
                <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="Accesories.php"><i class="fas fa-headphones"></i> Accessories</a></li>
            <li><a href="admin_mice.php"><i class="fas fa-mouse"></i> Mouse</a></li>
            <li><a href="admin_keyboard.php"><i class="fas fa-keyboard"></i> Keyboard</a></li>
            <li><a href="admin_monitors.php"><i class="fas fa-desktop"></i> Monitor</a></li>
            <li><a href="admin_harddisks.php"><i class="fas fa-hdd"></i> Hard Drives</a></li>
            <li><a href="admin_chargers.php" class="active"><i class="fas fa-battery-full"></i> Chargers</a></li>
            <li style="margin-top: 20px; border-top: 1px solid #3d566e;">
                <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a>
            </li>
        </ul>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="box">
            <h2><?= $edit_item ? '<i class="fas fa-edit"></i> Edit Product' : '<i class="fas fa-plus-circle"></i> Add Charger/Cable' ?></h2>
            
            <?php if($msg): ?>
                <div class="alert <?= $msg_type ?>"><?= $msg ?></div>
            <?php endif; ?>
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
                <div class="alert success">Item deleted successfully.</div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $edit_item['id'] ?? '' ?>">
                <input type="hidden" name="old_img" value="<?= $edit_item['product_image'] ?? '' ?>">
                
                <div class="form-group">
                    <label>Product Title</label>
                    <input type="text" name="title" value="<?= $edit_item['title'] ?? '' ?>" required placeholder="e.g. Apple 20W USB-C Power Adapter">
                </div>

                <!-- 3 Columns for Specs -->
                <div class="form-grid-3">
                    <div>
                        <label>Wattage</label>
                        <input type="text" name="wattage" placeholder="e.g. 20W" value="<?= $edit_item['wattage'] ?? '' ?>">
                    </div>
                    <div>
                        <label>Connector Type</label>
                        <input type="text" name="connector_type" placeholder="e.g. USB-C to Lightning" value="<?= $edit_item['connector_type'] ?? '' ?>">
                    </div>
                    <div>
                        <label>Cable Length</label>
                        <input type="text" name="cable_length" placeholder="e.g. 1 Meter" value="<?= $edit_item['cable_length'] ?? '' ?>">
                    </div>
                </div>

                <!-- 2 Columns for Prices -->
                <div class="form-grid-2">
                    <div>
                        <label>Sale Price (NPR)</label>
                        <input type="number" name="sale_price" value="<?= $edit_item['sale_price'] ?? '' ?>" required>
                    </div>
                    <div>
                        <label>Original Price (NPR)</label>
                        <input type="number" name="original_price" value="<?= $edit_item['original_price'] ?? '' ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Short Description / Features</label>
                    <textarea name="short_desc" rows="3"><?= $edit_item['short_desc'] ?? '' ?></textarea>
                </div>

                <!-- SEO MANAGEMENT SECTION -->
                <div class="seo-section">
                    <h4><i class="fab fa-google"></i> SEO Settings</h4>
                    <div class="form-grid-2">
                        <div>
                            <label>Meta Title</label>
                            <input type="text" name="meta_title" value="<?= $edit_item['meta_title'] ?? '' ?>" placeholder="Defaults to Title">
                        </div>
                        <div>
                            <label>Meta Description</label>
                            <input type="text" name="meta_desc" value="<?= $edit_item['meta_desc'] ?? '' ?>" placeholder="Search Snippet">
                        </div>
                    </div>
                    <?php if($edit_item): ?>
                        <div style="margin-top:10px; font-size:0.85rem; color:#666;">
                            Current Slug: <span class="badge"><?= $edit_item['slug'] ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Product Image</label>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <input type="file" name="img">
                        <?php if(!empty($edit_item['product_image'])): ?>
                            <img src="uploads/products/<?= $edit_item['product_image'] ?>" height="50" style="border:1px solid #ddd; border-radius:4px;">
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" name="save_item" class="btn">
                    <?= isset($edit_item) ? 'Update Product' : 'Save Product' ?>
                </button>
                <?php if(isset($edit_item)): ?>
                    <a href="admin_chargers.php" class="btn-cancel">Cancel Editing</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- LIST TABLE -->
        <div class="table-container">
            <h3 style="color:#2c3e50; margin-bottom:15px;">Inventory List</h3>
            <table>
                <thead>
                    <tr>
                        <th width="80">Image</th>
                        <th>Product Details</th>
                        <th>Specs</th>
                        <th>Price</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $row): ?>
                    <tr>
                        <td>
                            <img src="uploads/products/<?= $row['product_image'] ?>" width="60" height="45" style="object-fit: contain; border: 1px solid #eee; background: #fff; border-radius: 4px;" onerror="this.src='https://via.placeholder.com/60'">
                        </td>
                        <td>
                            <div style="font-weight: bold;"><?= htmlspecialchars($row['title']) ?></div>
                            <div class="badge"><?= htmlspecialchars($row['slug']) ?></div>
                        </td>
                        <td>
                            <?php if($row['wattage']): ?>
                                <div class="spec-details"><i class="fas fa-bolt"></i> <?= htmlspecialchars($row['wattage']) ?></div>
                            <?php endif; ?>
                            <?php if($row['cable_length']): ?>
                                <div class="spec-details"><i class="fas fa-ruler"></i> <?= htmlspecialchars($row['cable_length']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="price-tag">Rs. <?= number_format($row['sale_price']) ?></span>
                            <span class="price-old"><?= number_format($row['original_price']) ?></span>
                        </td>
                        <td>
                            <a href="?edit=<?= $row['id'] ?>" class="action-btn btn-edit" title="Edit"><i class="fas fa-pen"></i></a>
                            <a href="?del=<?= $row['id'] ?>" class="action-btn btn-del" title="Delete" onclick="return confirm('Delete this item?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>