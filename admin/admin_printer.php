<?php
/**
 * SARAL IT SOLUTION - ALL-IN-ONE PRINTER MANAGEMENT
 * Brand, Subcategory, and Product Admin
 */
include '../db.php'; // Ensure path to your PDO connection is correct

// --- 1. DATABASE AUTO-SETUP (Runs once to ensure tables exist) ---
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS printer_brands (id INT AUTO_INCREMENT PRIMARY KEY, brand_name VARCHAR(100) NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS printer_subcategories (id INT AUTO_INCREMENT PRIMARY KEY, sub_name VARCHAR(100) NOT NULL)");
    
    // Create/Update printer table
    $pdo->exec("CREATE TABLE IF NOT EXISTS printer (
        id INT AUTO_INCREMENT PRIMARY KEY,
        brand_id INT,
        sub_id INT,
        title VARCHAR(255),
        short_description TEXT,
        long_description TEXT,
        image_path VARCHAR(255),
        target_link VARCHAR(255)
    )");

    // Safety check: Add brand_id and sub_id if they are missing from an older table version
    $cols = $pdo->query("SHOW COLUMNS FROM printer")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('brand_id', $cols)) { $pdo->exec("ALTER TABLE printer ADD COLUMN brand_id INT AFTER id"); }
    if (!in_array('sub_id', $cols)) { $pdo->exec("ALTER TABLE printer ADD COLUMN sub_id INT AFTER brand_id"); }

} catch (PDOException $e) { die("Setup Error: " . $e->getMessage()); }


// --- 2. PHP LOGIC HANDLERS ---

// A. Handle Meta (Brands & Subcategories)
if (isset($_POST['add_brand'])) {
    $pdo->prepare("INSERT INTO printer_brands (brand_name) VALUES (?)")->execute([$_POST['brand_name']]);
    header("Location: printer.php?msg=BrandAdded"); exit();
}
if (isset($_GET['del_brand'])) {
    $pdo->prepare("DELETE FROM printer_brands WHERE id = ?")->execute([$_GET['del_brand']]);
    header("Location: printer.php?msg=BrandDeleted"); exit();
}
if (isset($_POST['add_sub'])) {
    $pdo->prepare("INSERT INTO printer_subcategories (sub_name) VALUES (?)")->execute([$_POST['sub_name']]);
    header("Location: printer.php?msg=SubAdded"); exit();
}
if (isset($_GET['del_sub'])) {
    $pdo->prepare("DELETE FROM printer_subcategories WHERE id = ?")->execute([$_GET['del_sub']]);
    header("Location: printer.php?msg=SubDeleted"); exit();
}

// B. Handle Printer Save (Add/Update)
if (isset($_POST['save_printer'])) {
    $id = $_POST['id'] ?: null;
    $brand_id = $_POST['brand_id'];
    $sub_id = $_POST['sub_id'];
    $title = $_POST['title'];
    $short_desc = $_POST['short_description'];
    $target_link = $_POST['target_link'];
    
    $img_name = $_POST['existing_image'];
    if (!empty($_FILES['image']['name'])) {
        $img_name = time() . "_" . $_FILES['image']['name'];
        if(!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/$img_name");
    }

    if ($id) {
        $sql = "UPDATE printer SET brand_id=?, sub_id=?, title=?, short_description=?, image_path=?, target_link=? WHERE id=?";
        $pdo->prepare($sql)->execute([$brand_id, $sub_id, $title, $short_desc, $img_name, $target_link, $id]);
    } else {
        $sql = "INSERT INTO printer (brand_id, sub_id, title, short_description, image_path, target_link) VALUES (?, ?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$brand_id, $sub_id, $title, $short_desc, $img_name, $target_link]);
    }
    header("Location: printer.php?msg=Success"); exit();
}

// C. Handle Printer Delete
if (isset($_GET['del_printer'])) {
    $stmt = $pdo->prepare("SELECT image_path FROM printer WHERE id = ?");
    $stmt->execute([$_GET['del_printer']]);
    $img = $stmt->fetchColumn();
    if($img && file_exists("uploads/$img")) unlink("uploads/$img");
    
    $pdo->prepare("DELETE FROM printer WHERE id = ?")->execute([$_GET['del_printer']]);
    header("Location: printer.php?msg=Deleted"); exit();
}

// D. Fetch Data for Form & List
$edit_row = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM printer WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_row = $stmt->fetch();
}

$brands_list = $pdo->query("SELECT * FROM printer_brands ORDER BY brand_name ASC")->fetchAll();
$subs_list   = $pdo->query("SELECT * FROM printer_subcategories ORDER BY sub_name ASC")->fetchAll();
$printers    = $pdo->query("SELECT p.*, b.brand_name, s.sub_name 
                            FROM printer p 
                            LEFT JOIN printer_brands b ON p.brand_id = b.id 
                            LEFT JOIN printer_subcategories s ON p.sub_id = s.id 
                            ORDER BY p.id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Printers | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; font-family: 'Inter', sans-serif; padding: 20px; }
        .admin-card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); background: #fff; margin-bottom: 20px; }
        .badge-pill { background: #e9ecef; color: #495057; padding: 5px 12px; border-radius: 50px; font-size: 12px; display: inline-flex; align-items: center; margin: 2px; }
        .badge-pill a { color: #dc3545; text-decoration: none; margin-left: 8px; font-weight: bold; }
        .form-label { font-weight: 600; color: #4b5563; }
        .img-preview { width: 80px; height: 80px; object-fit: contain; border: 1px dashed #ddd; padding: 5px; border-radius: 10px; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        
        <!-- LEFT COLUMN: META MANAGEMENT (Brands & Subs) -->
        <div class="col-lg-3">
            <div class="admin-card p-4">
                <h5 class="mb-3"><i class="fa fa-tag text-primary"></i> Brands</h5>
                <form method="POST" class="d-flex gap-2 mb-3">
                    <input type="text" name="brand_name" class="form-control form-control-sm" placeholder="Add Brand" required>
                    <button name="add_brand" class="btn btn-sm btn-primary">Add</button>
                </form>
                <div class="mb-4">
                    <?php foreach($brands_list as $b): ?>
                        <span class="badge-pill"><?= $b['brand_name'] ?> <a href="?del_brand=<?= $b['id'] ?>">&times;</a></span>
                    <?php endforeach; ?>
                </div>

                <hr>

                <h5 class="mb-3 mt-4"><i class="fa fa-layer-group text-success"></i> Subcategories</h5>
                <form method="POST" class="d-flex gap-2 mb-3">
                    <input type="text" name="sub_name" class="form-control form-control-sm" placeholder="Add Category" required>
                    <button name="add_sub" class="btn btn-sm btn-success">Add</button>
                </form>
                <div>
                    <?php foreach($subs_list as $s): ?>
                        <span class="badge-pill"><?= $s['sub_name'] ?> <a href="?del_sub=<?= $s['id'] ?>">&times;</a></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- CENTER COLUMN: PRINTER FORM -->
        <div class="col-lg-4">
            <div class="admin-card p-4">
                <h5 class="mb-4 text-primary"><?= $edit_row ? '<i class="fa fa-edit"></i> Edit Printer' : '<i class="fa fa-plus"></i> Add Printer' ?></h5>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $edit_row['id'] ?? '' ?>">
                    <input type="hidden" name="existing_image" value="<?= $edit_row['image_path'] ?? '' ?>">

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Brand</label>
                            <select name="brand_id" class="form-select" required>
                                <option value="">Select...</option>
                                <?php foreach($brands_list as $b): ?>
                                    <option value="<?= $b['id'] ?>" <?= (@$edit_row['brand_id'] == $b['id']) ? 'selected' : '' ?>><?= $b['brand_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Subcategory</label>
                            <select name="sub_id" class="form-select" required>
                                <option value="">Select...</option>
                                <?php foreach($subs_list as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= (@$edit_row['sub_id'] == $s['id']) ? 'selected' : '' ?>><?= $s['sub_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Model / Title</label>
                        <input type="text" name="title" class="form-control" value="<?= $edit_row['title'] ?? '' ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Target Link (Button URL)</label>
                        <input type="text" name="target_link" class="form-control" value="<?= $edit_row['target_link'] ?? '' ?>" placeholder="e.g., details.php?id=1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Short Description</label>
                        <textarea name="short_description" class="form-control" rows="2"><?= $edit_row['short_description'] ?? '' ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Product Image</label>
                        <?php if(!empty($edit_row['image_path'])): ?>
                            <img src="uploads/<?= $edit_row['image_path'] ?>" class="img-preview d-block mb-2">
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control">
                    </div>

                    <button name="save_printer" class="btn btn-primary w-100 py-2 fw-bold">
                        <?= $edit_row ? "Update Changes" : "Save Printer" ?>
                    </button>
                    <?php if($edit_row): ?> <a href="printer.php" class="btn btn-link w-100 mt-2 text-muted">Cancel Edit</a> <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- RIGHT COLUMN: PRINTER LIST -->
        <div class="col-lg-5">
            <div class="admin-card p-4">
                <h5 class="mb-4"><i class="fa fa-list"></i> Printer Inventory</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Image</th>
                                <th>Details</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($printers as $p): ?>
                                <tr>
                                    <td><img src="uploads/<?= $p['image_path'] ?>" width="50" class="rounded"></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($p['title']) ?></div>
                                        <small class="text-primary"><?= $p['brand_name'] ?></small> | 
                                        <small class="text-muted"><?= $p['sub_name'] ?></small>
                                    </td>
                                    <td>
                                        <a href="?edit=<?= $p['id'] ?>" class="text-warning me-2"><i class="fa fa-edit"></i></a>
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

</body>
</html>