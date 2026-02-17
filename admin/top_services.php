<?php
/**
 * Saral IT - Top Services & Category Manager
 */
require_once 'db.php'; 
require_once '../includes/functions.php'; 

// --- 1. DATABASE AUTO-SETUP ---
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS service_cats (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100))");
    $pdo->exec("CREATE TABLE IF NOT EXISTS top_services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cat_id INT,
        title VARCHAR(255),
        price VARCHAR(100),
        highlights TEXT,
        description LONGTEXT,
        image_path VARCHAR(255),
        is_new_release TINYINT(1) DEFAULT 0
    )");
} catch (PDOException $e) { die("Database Error: " . $e->getMessage()); }

// --- 2. ACTION HANDLERS ---
$message = "";

// Add Category
if (isset($_POST['add_cat'])) {
    $pdo->prepare("INSERT INTO service_cats (name) VALUES (?)")->execute([trim($_POST['cat_name'])]);
    $message = "Category added successfully!";
}

// Delete Category
if (isset($_GET['del_cat'])) {
    $pdo->prepare("DELETE FROM service_cats WHERE id = ?")->execute([$_GET['del_cat']]);
    header("Location: top_services.php"); exit();
}

// Delete Service
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM top_services WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: top_services.php?msg=deleted"); exit();
}

// Save/Update Service
if (isset($_POST['save_service'])) {
    $id = $_POST['id'];
    $new_rel = isset($_POST['is_new_release']) ? 1 : 0;
    
    $img = $_POST['existing_image'];
    if (!empty($_FILES['image']['name'])) {
        $img = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES['image']['name']);
        if(!is_dir('uploads')) mkdir('uploads', 0777, true);
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $img);
    }

    $data = [$_POST['cat_id'], $_POST['title'], $_POST['price'], $_POST['highlights'], $_POST['description'], $img, $new_rel];

    if ($id) {
        $sql = "UPDATE top_services SET cat_id=?, title=?, price=?, highlights=?, description=?, image_path=?, is_new_release=? WHERE id=?";
        $data[] = $id;
    } else {
        $sql = "INSERT INTO top_services (cat_id, title, price, highlights, description, image_path, is_new_release) VALUES (?,?,?,?,?,?,?)";
    }
    $pdo->prepare($sql)->execute($data);
    header("Location: top_services.php?msg=saved"); exit();
}

// --- 3. DATA FETCHING ---
$categories = $pdo->query("SELECT * FROM service_cats ORDER BY name ASC")->fetchAll();
$services = $pdo->query("SELECT s.*, c.name as cname FROM top_services s LEFT JOIN service_cats c ON s.cat_id = c.id ORDER BY s.id DESC")->fetchAll();

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM top_services WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services | Saral IT Admin</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

    <style>
        .main-content { margin-left: 260px; padding: 30px; background: #f4f7f6; min-height: 100vh; }
        .admin-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: none; margin-bottom: 25px; }
        .form-label { font-weight: 600; font-size: 0.85rem; color: #636e72; margin-bottom: 8px; }
        .tag-pill { background: #f1f3f5; border-radius: 50px; padding: 5px 15px; font-size: 12px; margin: 4px; display: inline-flex; align-items: center; border: 1px solid #dee2e6; color: #2d3436; text-decoration: none; transition: 0.2s; }
        .tag-pill:hover { background: #ff7675; color: white; }
        .inventory-item { border-bottom: 1px solid #f1f2f6; padding: 12px 0; display: flex; align-items: center; gap: 15px; }
        .inventory-img { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; }
        .badge-new { background: #ff7675; color: white; font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: bold; margin-left: 5px; }
        .release-box { background: #fff5f5; border: 1px solid #ffe3e3; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        @media (max-width: 992px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
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

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 fw-bold">Service & Repair Manager</h1>
                <p class="text-muted">Manage premium services and new technical releases.</p>
            </div>
        </div>

        <?php if ($message || isset($_GET['msg'])): ?>
            <div class="alert alert-success border-0 shadow-sm">
                <i class="fas fa-check-circle me-2"></i> Action processed successfully!
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- FORM COLUMN -->
            <div class="col-lg-7">
                <div class="admin-card">
                    <h5 class="fw-bold mb-4"><i class="fas fa-edit text-primary me-2"></i> <?= $edit ? 'Update Service' : 'Add New Service' ?></h5>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
                        <input type="hidden" name="existing_image" value="<?= $edit['image_path'] ?? '' ?>">

                   

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select name="cat_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php foreach($categories as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= isset($edit['cat_id']) && $edit['cat_id'] == $c['id'] ? 'selected' : '' ?>><?= $c['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Starting Price</label>
                                <input type="text" name="price" class="form-control" value="<?= $edit['price'] ?? '' ?>" placeholder="e.g. Rs. 1,500">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Service Title</label>
                                <input type="text" name="title" class="form-control" value="<?= $edit['title'] ?? '' ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Brief Highlights (One line)</label>
                                <input type="text" name="highlights" class="form-control" value="<?= $edit['highlights'] ?? '' ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Detailed Description</label>
                                <textarea name="description" id="editor"><?= $edit['description'] ?? '' ?></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Feature Image</label>
                                <input type="file" name="image" class="form-control">
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" name="save_service" class="btn btn-primary px-4 py-2 fw-bold">Save Service</button>
                                <?php if($edit): ?> <a href="top_services.php" class="btn btn-light ms-2">Cancel</a> <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- INVENTORY & CATS COLUMN -->
            <div class="col-lg-5">
                <!-- Category Manager -->
                <div class="admin-card">
                    <h6 class="fw-bold mb-3">Service Categories</h6>
                    <form method="POST" class="d-flex gap-2 mb-3">
                        <input type="text" name="cat_name" class="form-control form-control-sm" placeholder="e.g. Printer Repair" required>
                        <button name="add_cat" class="btn btn-sm btn-dark">Add</button>
                    </form>
                    <div class="d-flex flex-wrap">
                        <?php foreach($categories as $c): ?>
                            <a href="?del_cat=<?= $c['id'] ?>" class="tag-pill" onclick="return confirm('Delete this category?')">
                                <?= $c['name'] ?> <i class="fas fa-times ms-2 opacity-50"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Service List -->
                <div class="admin-card">
                    <h6 class="fw-bold mb-3">Live Services</h6>
                    <div class="service-list">
                        <?php foreach($services as $s): ?>
                        <div class="inventory-item">
                            <img src="uploads/<?= $s['image_path'] ?>" class="inventory-img" onerror="this.src='https://via.placeholder.com/50'">
                            <div class="flex-grow-1">
                                <div class="fw-bold small">
                                    <?= htmlspecialchars($s['title']) ?>
                                    <?php if($s['is_new_release']): ?><span class="badge-new">NEW</span><?php endif; ?>
                                </div>
                                <div class="text-muted" style="font-size: 0.7rem;">
                                    <?= $s['cname'] ?> • <?= $s['price'] ?>
                                </div>
                            </div>
                            <div class="action-btns">
                                <a href="?edit=<?= $s['id'] ?>" class="text-primary me-2"><i class="fas fa-edit"></i></a>
                                <a href="?delete=<?= $s['id'] ?>" class="text-danger" onclick="return confirm('Delete service?')"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>CKEDITOR.replace('editor');</script>
</body>
</html>