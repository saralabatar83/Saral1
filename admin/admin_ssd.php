<?php
/**
 * SSD BACKEND MANAGER
 * Location: admin/admin_ssd.php
 */
require_once 'db.php'; // Adjusted path to go up one level to find config

// --- 1. SEO UTILITY: Generate Slug ---
function generateSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return strtolower($text);
}

$msg = "";
$edit_data = null;

// --- 2. DELETE ACTION ---
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM ssd WHERE id = ?");
    if($stmt->execute([$_GET['delete']])) {
        header("Location: admin_ssd.php?msg=Deleted Successfully");
        exit;
    }
}

// --- 3. FETCH DATA FOR EDITING ---
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM ssd WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

// --- 4. HANDLE FORM SUBMISSION (ADD / UPDATE) ---
if (isset($_POST['save_ssd'])) {
    $id = $_POST['id'] ?? null;
    $title = $_POST['title'];
    $slug = generateSlug($title);
    $capacity = $_POST['capacity'];
    $sale_price = $_POST['sale_price'];
    $original_price = $_POST['original_price'];
    $meta_desc = $_POST['meta_description'];
    
    // --- IMAGE UPLOAD LOGIC ---
    $image_filename = $_POST['old_image'] ?? '';

    if (!empty($_FILES['product_image']['name'])) {
        // Correct path relative to this file (admin/admin_ssd.php)
        $target_dir = "uploads/products/"; 

        // Create directory if it doesn't exist
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $image_filename = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES["product_image"]["name"]));
        $target_file = $target_dir . $image_filename;

        if (!move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            $msg = "<div class='error'>Error: Could not upload image. Check folder permissions.</div>";
        }
    }

    // --- DATABASE OPERATIONS ---
    if (empty($msg)) {
        if ($id) {
            // UPDATE
            $sql = "UPDATE ssd SET title=?, slug=?, capacity=?, sale_price=?, original_price=?, product_image=?, meta_description=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $slug, $capacity, $sale_price, $original_price, $image_filename, $meta_desc, $id]);
            $msg = "<div class='success'>SSD Updated Successfully!</div>";
        } else {
            // INSERT
            $sql = "INSERT INTO ssd (title, slug, capacity, sale_price, original_price, product_image, meta_description) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $slug, $capacity, $sale_price, $original_price, $image_filename, $meta_desc]);
            $msg = "<div class='success'>New SSD Added Successfully!</div>";
        }
    }
}

// --- 5. FETCH ALL PRODUCTS FOR THE TABLE ---
$list = $pdo->query("SELECT * FROM ssd ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SSD Manager | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f0f2f5; padding: 30px; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        h2 { color: #333; margin-bottom: 25px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        
        .success { background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 6px; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 6px; }

        form { background: #f9f9f9; padding: 25px; border-radius: 8px; border: 1px solid #eee; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; }
        input, textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        
        button { background: #0056b3; color: white; border: none; padding: 12px 30px; cursor: pointer; border-radius: 6px; font-weight: bold; font-size: 16px; }
        button:hover { background: #004494; }

        table { width: 100%; border-collapse: collapse; margin-top: 40px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #333; }
        .btn-edit { color: #0056b3; text-decoration: none; font-weight: bold; margin-right: 15px; }
        .btn-del { color: #dc3545; text-decoration: none; font-weight: bold; }
        .slug-text { font-size: 11px; color: #00a65a; background: #eafff5; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>

<div class="container">
    <h2><i class="fa fa-hdd"></i> SSD Backend Inventory</h2>
    
    <?= $msg ?>
    <?php if(isset($_GET['msg'])) echo "<div class='success'>".$_GET['msg']."</div>"; ?>

    <!-- ADD / EDIT FORM -->
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
        <input type="hidden" name="old_image" value="<?= $edit_data['product_image'] ?? '' ?>">

        <div class="grid">
            <div class="form-group">
                <label>Product Title</label>
                <input type="text" name="title" value="<?= $edit_data['title'] ?? '' ?>" required placeholder="e.g. Kingston XS1000 1TB">
            </div>
            <div class="form-group">
                <label>Storage Capacity</label>
                <input type="text" name="capacity" value="<?= $edit_data['capacity'] ?? '' ?>" required placeholder="e.g. 1TB, 500GB">
            </div>
        </div>

        <div class="grid">
            <div class="form-group">
                <label>Sale Price (NPR)</label>
                <input type="number" name="sale_price" value="<?= $edit_data['sale_price'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label>Original Price (NPR)</label>
                <input type="number" name="original_price" value="<?= $edit_data['original_price'] ?? '' ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>SEO Meta Description</label>
            <textarea name="meta_description" rows="2"><?= $edit_data['meta_description'] ?? '' ?></textarea>
        </div>

        <div class="form-group">
            <label>Product Image</label>
            <input type="file" name="product_image" <?= isset($edit_data) ? '' : 'required' ?>>
            <?php if(isset($edit_data)): ?>
                <p style="font-size: 12px; color: #666;">Current: <?= $edit_data['product_image'] ?></p>
            <?php endif; ?>
        </div>

        <button type="submit" name="save_ssd">
            <i class="fa fa-save"></i> <?= isset($edit_data) ? 'Update SSD Product' : 'Save SSD Product' ?>
        </button>
        <?php if(isset($edit_data)): ?>
            <a href="admin_ssd.php" style="margin-left:15px; color: #666;">Cancel Edit</a>
        <?php endif; ?>
    </form>

    <!-- LIST TABLE -->
    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Title & Slug</th>
                <th>Price</th>
                <th>Capacity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($list as $row): ?>
            <tr>
                <td>
                    <?php if($row['product_image']): ?>
                        <img src="uploads/products/<?= $row['product_image'] ?>" width="60" style="border-radius: 4px;">
                    <?php else: ?>
                        <div style="width:60px; height:40px; background:#eee; text-align:center; font-size:10px; line-height:40px;">No Image</div>
                    <?php endif; ?>
                </td>
                <td>
                    <strong><?= htmlspecialchars($row['title']) ?></strong><br>
                    <span class="slug-text">/product/<?= $row['slug'] ?></span>
                </td>
                <td>
                    <span style="color:#e63946; font-weight:bold;">Rs.<?= number_format($row['sale_price']) ?></span><br>
                    <small style="text-decoration:line-through; color:#aaa;">Rs.<?= number_format($row['original_price']) ?></small>
                </td>
                <td><?= htmlspecialchars($row['capacity']) ?></td>
                <td>
                    <a href="?edit=<?= $row['id'] ?>" class="btn-edit"><i class="fa fa-edit"></i> Edit</a>
                    <a href="?delete=<?= $row['id'] ?>" class="btn-del" onclick="return confirm('Are you sure you want to delete this product?')"><i class="fa fa-trash"></i> Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>