<?php
// 1. DATABASE CONNECTION
require_once 'db.php'; 

$msg = "";
$edit_mode = false;
$prod_to_edit = [
    'id' => '', 'name' => '', 'specs' => '', 'price_original' => '', 
    'price_discounted' => '', 'discount_percent' => '', 'tag' => '', 
    'warranty' => '', 'image_path' => ''
];

// --- 2. HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $stmt = $pdo->prepare("SELECT image_path FROM shop_products WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    
    if ($img && file_exists("uploads/" . $img)) {
        unlink("uploads/" . $img);
    }

    $pdo->prepare("DELETE FROM shop_products WHERE id = ?")->execute([$id]);
    header("Location: admin_products.php?msg=deleted");
    exit();
}

// --- 3. HANDLE EDIT FETCH ---
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM shop_products WHERE id = ?");
    $stmt->execute([$edit_id]);
    $prod_to_edit = $stmt->fetch();
    if ($prod_to_edit) {
        $edit_mode = true;
    }
}

// --- 4. HANDLE ADD OR UPDATE ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_product'])) {
    $id = $_POST['prod_id'];
    $name = $_POST['name'];
    $specs = $_POST['specs'];
    $price_original = $_POST['price_original'];
    $price_discounted = $_POST['price_discounted'];
    $discount_percent = $_POST['discount_percent'];
    $tag = $_POST['tag'];
    $warranty = $_POST['warranty'];
    $existing_image = $_POST['existing_image'];

    // Image Upload Logic
    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . $_FILES['image']['name'];
        if (move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $imageName)) {
            if ($existing_image && file_exists("uploads/" . $existing_image)) {
                unlink("uploads/" . $existing_image);
            }
        }
    } else {
        $imageName = $existing_image; 
    }

    if ($id) {
        // UPDATE
        $sql = "UPDATE shop_products SET name=?, specs=?, price_original=?, price_discounted=?, discount_percent=?, tag=?, warranty=?, image_path=? WHERE id=?";
        $pdo->prepare($sql)->execute([$name, $specs, $price_original, $price_discounted, $discount_percent, $tag, $warranty, $imageName, $id]);
    } else {
        // INSERT
        $sql = "INSERT INTO shop_products (name, specs, price_original, price_discounted, discount_percent, tag, warranty, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$name, $specs, $price_original, $price_discounted, $discount_percent, $tag, $warranty, $imageName]);
    }
    
    header("Location: admin_products.php?msg=success");
    exit();
}

$products = $pdo->query("SELECT * FROM shop_products ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products | Admin Panel</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 20px; }
        .admin-box { max-width: 1100px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .admin-nav { display: flex; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .admin-nav a { text-decoration: none; font-weight: bold; color: #64748b; font-size: 14px; }
        h2 { color: #1e293b; margin-top: 0; }
        
        .form-container { background: #f8fafc; padding: 20px; border-radius: 10px; margin-bottom: 30px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; border: 1px solid #e2e8f0; }
        .full-width { grid-column: span 3; }
        
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #475569; font-size: 13px; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-family: inherit; }
        
        .btn-save { background: #1e293b; color: white; border: none; padding: 12px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; transition: 0.3s; margin-top: 10px; }
        .btn-save:hover { background: #334155; }
        .btn-cancel { background: #94a3b8; color: white; text-decoration: none; padding: 12px; border-radius: 6px; text-align: center; display: block; margin-top: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #edf2f7; }
        th { background: #f1f5f9; color: #475569; text-transform: uppercase; font-size: 12px; }
        
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-red { background: #fee2e2; color: #dc2626; }
        .badge-green { background: #dcfce7; color: #166534; }
        
        .action-links a { text-decoration: none; font-weight: bold; margin-right: 15px; }
        .edit { color: #3b82f6; }
        .delete { color: #ef4444; }
        .alert { padding: 10px; background: #dcfce7; color: #166534; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid #22c55e; }
    </style>
</head>
<body>

<div class="admin-box">
    <div class="admin-nav">
        <div>
            <a href="admin_dashboard.php">Dashboard</a> | 
            <a href="admin_categories.php">📂 Categories</a> |
            <a href="admin_products.php" style="color:#1e293b">💻 Products</a>
        </div>
        <a href="../index.php" target="_blank" style="color: #3b82f6;">View Store &nearr;</a>
    </div>

    <h2><?= $edit_mode ? "Edit Product" : "Add New Laptop" ?></h2>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert">Product list updated successfully!</div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="form-container">
        <input type="hidden" name="prod_id" value="<?= $prod_to_edit['id'] ?>">
        <input type="hidden" name="existing_image" value="<?= $prod_to_edit['image_path'] ?>">

        <div class="full-width">
            <label>Product Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($prod_to_edit['name']) ?>" placeholder="e.g. Acer Aspire Lite 14" required>
        </div>

        <div class="full-width">
            <label>Specifications (Separator: |)</label>
            <input type="text" name="specs" value="<?= htmlspecialchars($prod_to_edit['specs']) ?>" placeholder="i5-13500H | 16GB RAM | 512GB SSD" required>
        </div>

        <div>
            <label>Original Price (Rs)</label>
            <input type="number" step="0.01" name="price_original" value="<?= $prod_to_edit['price_original'] ?>" required>
        </div>

        <div>
            <label>Discounted Price (Rs)</label>
            <input type="number" step="0.01" name="price_discounted" value="<?= $prod_to_edit['price_discounted'] ?>" required>
        </div>

        <div>
            <label>Discount %</label>
            <input type="number" name="discount_percent" value="<?= $prod_to_edit['discount_percent'] ?>" placeholder="e.g. 8">
        </div>

        <div>
            <label>Badge/Tag</label>
            <select name="tag">
                <option value="">None</option>
                <option value="NEW ARRIVAL" <?= $prod_to_edit['tag'] == 'NEW ARRIVAL' ? 'selected' : '' ?>>NEW ARRIVAL</option>
                <option value="BEST SELLER" <?= $prod_to_edit['tag'] == 'BEST SELLER' ? 'selected' : '' ?>>BEST SELLER</option>
            </select>
        </div>

        <div>
            <label>Warranty Info</label>
            <input type="text" name="warranty" value="<?= htmlspecialchars($prod_to_edit['warranty']) ?>" placeholder="2-Year Warranty">
        </div>

        <div>
            <label>Product Image</label>
            <input type="file" name="image" <?= $edit_mode ? "" : "required" ?>>
        </div>

        <div class="full-width">
            <button type="submit" name="save_product" class="btn-save">
                <?= $edit_mode ? "UPDATE PRODUCT" : "SAVE PRODUCT" ?>
            </button>
            <?php if($edit_mode): ?>
                <a href="admin_products.php" class="btn-cancel">CANCEL EDIT</a>
            <?php endif; ?>
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Product & Specs</th>
                <th>Pricing</th>
                <th>Badges</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td>
                    <img src="uploads/<?= $p['image_path'] ?>" width="60" height="45" style="object-fit: contain; border: 1px solid #eee; border-radius: 4px;">
                </td>
                <td>
                    <strong><?= htmlspecialchars($p['name']) ?></strong><br>
                    <small style="color: #64748b;"><?= htmlspecialchars($p['specs']) ?></small>
                </td>
                <td>
                    <span style="text-decoration: line-through; color: #94a3b8; font-size: 12px;">Rs <?= number_format($p['price_original'], 0) ?></span><br>
                    <span style="color: #dc2626; font-weight: bold;">Rs <?= number_format($p['price_discounted'], 0) ?></span>
                </td>
                <td>
                    <?php if($p['tag']): ?>
                        <span class="badge badge-green"><?= $p['tag'] ?></span>
                    <?php endif; ?>
                    <?php if($p['discount_percent']): ?>
                        <span class="badge badge-red"><?= $p['discount_percent'] ?>% OFF</span>
                    <?php endif; ?>
                </td>
                <td class="action-links">
                    <a href="admin_products.php?edit=<?= $p['id'] ?>" class="edit">Edit</a>
                    <a href="admin_products.php?delete=<?= $p['id'] ?>" class="delete" onclick="return confirm('Delete this product?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>