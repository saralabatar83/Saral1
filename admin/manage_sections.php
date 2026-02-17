<?php
/**
 * SARAL IT SOLUTION - DYNAMIC SECTIONS MANAGER
 */
require_once '../db.php'; 

// 2. DETECT WHICH SECTION TAB IS ACTIVE (1, 2, 3, or 4)
$sec = isset($_GET['sec']) ? (int)$_GET['sec'] : 1;
if ($sec < 1 || $sec > 4) $sec = 1;
$table = "offers_sec" . $sec;

// --- DELETE LOGIC ---
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $stmt = $pdo->prepare("SELECT image_path FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetch();
    if ($img && !empty($img['image_path'])) {
        @unlink("uploads/" . $img['image_path']);
    }
    $pdo->prepare("DELETE FROM $table WHERE id = ?")->execute([$id]);
    header("Location: manage_sections.php?sec=$sec&msg=deleted"); 
    exit();
}

// --- SAVE LOGIC ---
if (isset($_POST['save_btn'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $short_desc = $_POST['short_desc'];
    $long_desc = $_POST['long_desc'];
    $image_name = $_POST['old_img'];

    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . "_" . $_FILES['image']['name'];
        if (move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $image_name)) {
            if (!empty($_POST['old_img'])) { @unlink("uploads/" . $_POST['old_img']); }
        }
    }

    if (!empty($id)) {
        $sql = "UPDATE $table SET title=?, short_desc=?, long_desc=?, image_path=? WHERE id=?";
        $pdo->prepare($sql)->execute([$title, $short_desc, $long_desc, $image_name, $id]);
    } else {
        $sql = "INSERT INTO $table (title, short_desc, long_desc, image_path) VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$title, $short_desc, $long_desc, $image_name]);
    }
    header("Location: manage_sections.php?sec=$sec&msg=success");
    exit();
}

// --- FETCH DATA FOR EDITING ---
$edit = ['id'=>'','title'=>'','short_desc'=>'','long_desc'=>'','image_path'=>''];
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $res = $stmt->fetch();
    if($res) $edit = $res;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Sections | Saral IT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root { --sidebar-width: 260px; --primary-dark: #0f172a; --accent: #838de7; }
        body { background: #f4f7f6; margin: 0; display: flex; font-family: 'Segoe UI', sans-serif; }

        /* --- SIDEBAR STYLES --- */
        .sidebar { width: var(--sidebar-width); height: 100vh; background: var(--primary-dark); color: white; position: fixed; left: 0; top: 0; overflow-y: auto; z-index: 1000; }
        .sidebar-header { padding: 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h2 { font-size: 22px; font-weight: 700; margin: 0; color: white; }
        .sidebar-header span { color: var(--accent); }
        .sidebar-menu { list-style: none; padding: 20px 0; margin: 0; }
        .sidebar-menu li a { display: block; padding: 12px 25px; color: #bdc3c7; text-decoration: none; transition: 0.3s; font-size: 14px; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { color: white; background: rgba(255,255,255,0.1); border-left: 4px solid var(--accent); }
        .sidebar-menu li a i { margin-right: 12px; width: 20px; text-align: center; }
        .menu-divider { padding: 15px 25px 5px; font-size: 11px; text-transform: uppercase; color: #7f8c8d; font-weight: bold; }

        /* --- MAIN CONTENT AREA --- */
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 30px; min-width: 0; }
        
        /* Tabs */
        .tabs { margin-bottom: 25px; display: flex; gap: 10px; flex-wrap: wrap; }
        .tabs a { padding: 10px 20px; background: #fff; border: 1px solid #ddd; text-decoration: none; color: #333; border-radius: 8px; font-weight: bold; transition: 0.2s; }
        .tabs a.active { background: var(--accent); color: #fff; border-color: var(--accent); }

        .flex-layout { display: flex; gap: 25px; align-items: flex-start; flex-wrap: wrap; }
        
        /* Form Card */
        .form-card { width: 380px; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .form-card h3 { margin-top: 0; font-size: 18px; color: var(--accent); font-weight: 700; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        label { font-size: 13px; font-weight: 600; color: #666; margin-top: 10px; display: block; }
        input, textarea { width: 100%; padding: 10px; margin: 5px 0 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
        .btn-save { background: var(--accent); color: #fff; border: none; padding: 14px; width: 100%; cursor: pointer; font-weight: bold; border-radius: 8px; transition: 0.3s; }
        .btn-save:hover { opacity: 0.9; }
        
        /* List Table */
        .list-card { flex: 1; background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 12px; text-transform: uppercase; color: #888; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        td { padding: 15px 0; border-bottom: 1px solid #eee; vertical-align: middle; }
        img.thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #eee; }
        .act-edit { color: #3498db; text-decoration: none; font-weight: bold; font-size: 14px; }
        .act-del { color: #e74c3c; text-decoration: none; font-weight: bold; font-size: 14px; margin-left: 15px; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Saral<span>Admin</span></h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            
            <div class="menu-divider">Catalog Management</div>
            <li><a href="all_categories.php"><i class="fas fa-layer-group"></i> Categories</a></li>
            <li><a href="Laptop.php"><i class="fas fa-laptop"></i> Laptops</a></li>
            <li><a href="printer.php"><i class="fas fa-print"></i> Printers</a></li>
            <li><a href="cctv.php"><i class="fas fa-video"></i> CCTV</a></li>
            <li><a href="admin_accessories.php"><i class="fas fa-keyboard"></i> Accessories</a></li>
            
            <div class="menu-divider">Promotion & UI</div>
            <li><a href="offer.php"><i class="fas fa-tags"></i> Daily Offers</a></li>
      
            <li><a href="sliderimg.php"><i class="fas fa-images"></i> Slider Manager</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        
        <!-- SECTION TABS -->
        <div class="tabs">

            <a href="?sec=2" class="<?= $sec==2?'active':'' ?>">latest categories</a>
            <a href="?sec=3" class="<?= $sec==3?'active':'' ?>">Featured Solution</a>
            <a href="?sec=4" class="<?= $sec==4?'active':'' ?>">Special offers</a>
        </div>

        <div class="flex-layout">
            
            <!-- FORM FOR ADD/EDIT -->
            <div class="form-card">
                <h3><?= $edit['id'] ? 'Edit Item' : 'Add New Item' ?></h3>
                <form action="offer.php?sec=<?= $sec ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                    <input type="hidden" name="old_img" value="<?= $edit['image_path'] ?>">
                    
                    <label>Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($edit['title']) ?>" required>
                    
                    <label>Short Description</label>
                    <textarea name="short_desc" required rows="2"><?= htmlspecialchars($edit['short_desc']) ?></textarea>
                    
                    <label>Long Description</label>
                    <textarea name="long_desc" rows="3"><?= htmlspecialchars($edit['long_desc']) ?></textarea>
                    
                    <label>Image Upload</label>
                    <?php if($edit['image_path']): ?>
                        <img src="uploads/<?= $edit['image_path'] ?>" style="width:60px; height:60px; object-fit:cover; border-radius:8px; margin-bottom:10px; display:block;">
                    <?php endif; ?>
                    <input type="file" name="image">
                    
                    <button type="submit" name="save_btn" class="btn-save">SAVE TO SECTION <?= $sec ?></button>
                    
                    <?php if($edit['id']): ?>
                        <a href="offer.php?sec=<?= $sec ?>" style="display:block; text-align:center; margin-top:15px; color:#999; font-size:13px; text-decoration:none;">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- LIST OF ITEMS -->
            <div class="list-card">
                <table>
                    <thead>
                        <tr>
                            <th>Preview</th>
                            <th>Title</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM $table ORDER BY id DESC");
                        while($row = $stmt->fetch()){
                            echo "<tr>
                                <td style='width:70px;'><img src='uploads/{$row['image_path']}' class='thumb'></td>
                                <td>
                                    <div class='fw-bold'>{$row['title']}</div>
                                    <div class='text-muted small'>".mb_strimwidth($row['short_desc'], 0, 50, "...")."</div>
                                </td>
                                <td class='text-end'>
                                    <a href='?sec=$sec&edit={$row['id']}' class='act-edit'><i class='fas fa-edit'></i></a>
                                    <a href='?sec=$sec&del={$row['id']}' class='act-del' onclick='return confirm(\"Delete this permanently?\")'><i class='fas fa-trash'></i></a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <?php if($stmt->rowCount() == 0): ?>
                    <p class="text-center py-5 text-muted">No items found in this section.</p>
                <?php endif; ?>
            </div>

        </div>
    </main>

</body>
</html>