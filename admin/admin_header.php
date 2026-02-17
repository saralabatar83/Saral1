<?php
/**
 * Saral IT - Integrated Admin Header Settings
 */
require_once 'db.php'; // Using the dashboard's db path
require_once '../includes/functions.php'; 

$msg = "";
$msg_type = "";

// --- 1. ACTION HANDLERS (PHP Logic) ---

// Handle Branding Update
if (isset($_POST['save_branding'])) {
    $brand_name = htmlspecialchars($_POST['brand_name']);
    $logo_name = $_POST['current_logo']; 

    if (!empty($_FILES['logo_file']['name'])) {
        $target_dir = "../uploads/";
        $file_ext = pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION);
        $logo_name = "logo_" . time() . "." . $file_ext;
        move_uploaded_file($_FILES['logo_file']['tmp_name'], $target_dir . $logo_name);
    }

    $stmt = $pdo->prepare("UPDATE site_branding SET logo = ?, brand_name = ? WHERE id = 1");
    if($stmt->execute([$logo_name, $brand_name])) {
        $msg = "Branding updated successfully!";
        $msg_type = "success";
    }
}

// Handle Add/Update Top Bar Item
if (isset($_POST['save_top_bar'])) {
    $icon = $_POST['icon'];
    $text_label = $_POST['text_label'];
    $link = $_POST['link'];
    $sort_order = $_POST['sort_order'];
    $position = $_POST['position'];

    if (!empty($_POST['item_id'])) {
        $stmt = $pdo->prepare("UPDATE header_top_bar SET icon=?, text_label=?, link=?, sort_order=?, position=? WHERE id=?");
        $stmt->execute([$icon, $text_label, $link, $sort_order, $position, $_POST['item_id']]);
        $msg = "Item updated!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO header_top_bar (icon, text_label, link, sort_order, position) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$icon, $text_label, $link, $sort_order, $position]);
        $msg = "Item added!";
    }
    $msg_type = "success";
}

// Handle Deleting
if (isset($_GET['delete_item'])) {
    $stmt = $pdo->prepare("DELETE FROM header_top_bar WHERE id = ?");
    $stmt->execute([$_GET['delete_item']]);
    header("Location: admin_header.php?msg=deleted");
    exit();
}

// --- 2. DATA FETCHING ---
$branding = $pdo->query("SELECT * FROM site_branding WHERE id = 1")->fetch();
$items = $pdo->query("SELECT * FROM header_top_bar ORDER BY position DESC, sort_order ASC")->fetchAll();

$edit_item = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM header_top_bar WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_item = $stmt->fetch();
}

if (!$branding) { $branding = ['logo' => '', 'brand_name' => 'Saral IT', 'id' => 1]; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header Manager - Saral IT Admin</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
     
    <style>
        /* Specific Styles for Header Manager Cards */
        .main-content { padding: 30px; margin-left: 260px; background: #f4f7f6; min-height: 100vh; }
        .card { background: #fff; border-radius: 12px; padding: 25px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eee; }
        .card-title { font-weight: 700; font-size: 1.1rem; margin-bottom: 20px; color: #2d3436; display: flex; align-items: center; gap: 10px; }
        .card-title i { color: #6c5ce7; }
        
        .form-row { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .form-group { flex: 1; min-width: 200px; display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 0.8rem; font-weight: 600; color: #636e72; }
        .form-group input, .form-group select { padding: 10px; border: 1px solid #dfe6e9; border-radius: 6px; outline: none; }
        
        .btn-save { background: #6c5ce7; color: white; border: none; padding: 11px 25px; border-radius: 6px; cursor: pointer; font-weight: 600; transition: 0.3s; }
        .btn-save:hover { background: #a29bfe; }
        
        .logo-preview { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #eee; margin-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: left; padding: 12px; background: #f9f9f9; font-size: 0.8rem; color: #b2bec3; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid #f1f2f6; font-size: 0.9rem; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-left { background: #e3f2fd; color: #1976d2; }
        .badge-right { background: #f3e5f5; color: #7b1fa2; }
        
        .action-links a { margin-right: 10px; text-decoration: none; font-weight: 600; font-size: 0.8rem; }
        .edit-link { color: #0984e3; }
        .delete-link { color: #d63031; }

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
            <li><a href="all categories.php"><i class="fas fa-folder"></i> <span>Categories</span></a></li>
            <li><a href="admin_categories.php"><i class="fas fa-list-ul"></i> <span>Manage Cats</span></a></li>
            <li><a href="brands.php"><i class="fas fa-images"></i> <span>Top Brands</span></a></li>
<!-- Marketing Section -->
<div class="menu-divider">Marketing</div>
<li><a href="offer.php"> <i class="fas fa-fire"></i> <span>Daily Offers</span>


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
    <!-- MAIN CONTENT AREA -->
    <main class="main-content">
        
        <div class="dashboard-header">
            <h1>Header & Branding Manager</h1>
            <p>Control your website logo, name, and top contact bar.</p>
        </div>

        <?php if ($msg || isset($_GET['msg'])): ?>
            <div style="background: #55efc4; color: #00b894; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
                <?= $msg ?: ($_GET['msg'] == 'deleted' ? 'Item removed successfully!' : '') ?>
            </div>
        <?php endif; ?>

        <!-- SECTION 1: BRANDING -->
        <div class="card">
            <div class="card-title"><i class="fas fa-fingerprint"></i> Website Branding</div>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group" style="flex: 0 0 auto;">
                        <label>Current Logo</label>
                        <img src="../uploads/<?= $branding['logo'] ?>" class="logo-preview">
                        <input type="file" name="logo_file">
                        <input type="hidden" name="current_logo" value="<?= $branding['logo'] ?>">
                    </div>
                    <div class="form-group">
                        <label>Brand Display Name</label>
                        <input type="text" name="brand_name" value="<?= htmlspecialchars($branding['brand_name']) ?>" required>
                    </div>
                    <button type="submit" name="save_branding" class="btn-save">Update Branding</button>
                </div>
            </form>
        </div>

        <!-- SECTION 2: TOP BAR ITEMS -->
        <div class="card">
            <div class="card-title"><i class="fas fa-bars-staggered"></i> <?= $edit_item ? 'Edit' : 'Add' ?> Top Bar Item</div>
            <form method="POST">
                <input type="hidden" name="item_id" value="<?= $edit_item['id'] ?? '' ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label>Icon (e.g. fas fa-phone)</label>
                        <input type="text" name="icon" value="<?= $edit_item['icon'] ?? 'fas fa-phone' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Text Label</label>
                        <input type="text" name="text_label" value="<?= $edit_item['text_label'] ?? '' ?>" placeholder="e.g. info@saralit.com" required>
                    </div>
                    <div class="form-group">
                        <label>Position</label>
                        <select name="position">
                            <option value="left" <?= (isset($edit_item['position']) && $edit_item['position'] == 'left') ? 'selected' : '' ?>>Left Side</option>
                            <option value="right" <?= (isset($edit_item['position']) && $edit_item['position'] == 'right') ? 'selected' : '' ?>>Right Side</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 0 0 100px;">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" value="<?= $edit_item['sort_order'] ?? '1' ?>">
                    </div>
                    <div class="form-group">
                        <label>Link (optional)</label>
                        <input type="text" name="link" value="<?= $edit_item['link'] ?? '#' ?>">
                    </div>
                    <button type="submit" name="save_top_bar" class="btn-save"><?= $edit_item ? 'Update' : 'Add Item' ?></button>
                </div>
            </form>
        </div>

        <!-- SECTION 3: INVENTORY TABLE -->
        <div class="card">
            <div class="card-title"><i class="fas fa-list"></i> Current Top Bar Items</div>
            <table>
                <thead>
                    <tr>
                        <th>Pos</th>
                        <th>Icon</th>
                        <th>Text</th>
                        <th>Sort</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><span class="badge badge-<?= $item['position'] ?>"><?= strtoupper($item['position']) ?></span></td>
                        <td><i class="<?= $item['icon'] ?>"></i></td>
                        <td><?= htmlspecialchars($item['text_label']) ?></td>
                        <td><?= $item['sort_order'] ?></td>
                        <td class="action-links">
                            <a href="?edit=<?= $item['id'] ?>" class="edit-link">Edit</a>
                            <a href="?delete_item=<?= $item['id'] ?>" class="delete-link" onclick="return confirm('Delete this item?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>