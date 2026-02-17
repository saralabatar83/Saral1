<?php
/**
 * SARAL IT SOLUTION - DYNAMIC SECTIONS MANAGER
 * Managed Pattern Implementation
 */
require_once 'db.php'; 

// 1. DETECT ACTIVE SECTION
$sec = isset($_GET['sec']) ? (int)$_GET['sec'] : 2;
if ($sec < 2 || $sec > 4) $sec = 2; 
$table = "offers_sec" . $sec;

$message = "";

// --- 2. DELETE LOGIC ---
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $stmt = $pdo->prepare("SELECT image_path FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetch();
    if ($img && !empty($img['image_path'])) {
        @unlink("uploads/" . $img['image_path']);
    }
    $pdo->prepare("DELETE FROM $table WHERE id = ?")->execute([$id]);
    header("Location: offer.php?sec=$sec&msg=deleted"); 
    exit();
}

// --- 3. SAVE LOGIC ---
if (isset($_POST['save_btn'])) {
    $id = $_POST['id'];
    $title = htmlspecialchars($_POST['title']);
    $short_desc = htmlspecialchars($_POST['short_desc']);
    $long_desc = htmlspecialchars($_POST['long_desc']);
    $image_name = $_POST['old_img'];

    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . "_" . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $image_name)) {
            if (!empty($_POST['old_img'])) { @unlink("uploads/" . $_POST['old_img']); }
        }
    }

    if (!empty($id)) {
        $sql = "UPDATE $table SET title=?, short_desc=?, long_desc=?, image_path=? WHERE id=?";
        $pdo->prepare($sql)->execute([$title, $short_desc, $long_desc, $image_name, $id]);
        $message = "Item updated successfully!";
    } else {
        $sql = "INSERT INTO $table (title, short_desc, long_desc, image_path) VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$title, $short_desc, $long_desc, $image_name]);
        $message = "New item added!";
    }
}

// --- 4. FETCH DATA FOR EDITING ---
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saral IT - Promotion Manager</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
     
    <style>
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
        
        body { 
            background: var(--bg-body); 
            display: flex; 
            min-height: 100vh; 
            color: var(--text-main); 
            overflow-x: hidden;
        }

        /* --- FIXED SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: white;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: width 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); }

        .sidebar-header {
            padding: 25px;
            background: #1a252f;
            text-align: center;
            border-bottom: 3px solid var(--primary);
            position: sticky; top: 0; z-index: 10;
        }
        .sidebar-header h2 span { color: var(--primary); }

        .sidebar-menu { list-style: none; padding: 10px 0 100px 0; }
        .sidebar-menu li a {
            display: flex; align-items: center; padding: 12px 20px;
            color: #bdc3c7; text-decoration: none; font-size: 14px; transition: 0.2s;
        }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background: var(--primary); color: white; }
        .sidebar-menu i { margin-right: 12px; width: 20px; text-align: center; font-size: 16px; }
        
        .menu-divider { 
            padding: 20px 20px 5px; font-size: 11px; text-transform: uppercase; 
            color: #7f8c8d; font-weight: bold; letter-spacing: 1px;
        }

        /* --- MAIN VIEWPORT --- */
        .main-view { 
            margin-left: var(--sidebar-width); 
            flex: 1; 
            padding: 30px; 
            width: calc(100% - var(--sidebar-width)); 
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }

        /* Managed Tabs */
        .tab-nav { display: flex; gap: 10px; margin-bottom: 25px; }
        .tab-link { 
            padding: 12px 24px; background: var(--white); border-radius: 10px; 
            text-decoration: none; color: #666; font-weight: 600; font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: 0.3s;
        }
        .tab-link.active { background: var(--primary); color: white; }

        /* Layout Grid */
        .flex-layout { display: grid; grid-template-columns: 1fr 1.8fr; gap: 25px; align-items: flex-start; }

        /* Panels */
        .panel { background: var(--white); padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .panel h2 { margin-bottom: 20px; font-size: 18px; color: var(--sidebar-bg); border-bottom: 1px solid #eee; padding-bottom: 10px; display: flex; align-items: center; gap: 10px; }

        /* Form Styling */
        .form-group { margin-bottom: 15px; display: flex; flex-direction: column; }
        label { font-size: 13px; font-weight: 600; color: #555; margin-bottom: 6px; }
        input, textarea { padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; outline: none; transition: 0.3s; }
        input:focus, textarea:focus { border-color: var(--primary); }
        
        .btn-save { 
            background: var(--primary); color: white; border: none; padding: 14px; 
            border-radius: 8px; cursor: pointer; font-weight: bold; margin-top: 10px; transition: 0.3s;
        }
        .btn-save:hover { background: var(--secondary); transform: translateY(-1px); }

        /* Table Styling */
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; background: #f9fafb; color: #7f8c8d; font-size: 12px; text-transform: uppercase; }
        td { padding: 15px 10px; border-bottom: 1px solid #f1f1f1; font-size: 14px; vertical-align: middle; }
        .thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; background: #eee; border: 1px solid #ddd; }
        
        .actions { display: flex; gap: 12px; justify-content: flex-end; }
        .act-btn { text-decoration: none; font-size: 16px; transition: 0.2s; }
        .act-edit { color: var(--primary); }
        .act-del { color: var(--danger); }

        .alert { padding: 15px; background: #d4edda; color: #155724; border-radius: 8px; margin-bottom: 25px; border-left: 5px solid #28a745; display: flex; align-items: center; gap: 10px; }

        /* Responsive */
        @media (max-width: 1100px) {
            .flex-layout { grid-template-columns: 1fr; }
        }
        @media (max-width: 992px) {
            :root { --sidebar-width: 70px; }
            .sidebar h2, .sidebar span, .menu-divider { display: none; }
            .sidebar-menu i { margin: 0; font-size: 20px; width: 100%; }
            .sidebar-menu li a { justify-content: center; padding: 20px 0; }
        }
    </style>
</head>
<body>

    <!-- FIXED SIDEBAR (Standard Menu) -->
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
    <!-- MAIN VIEW -->
    <main class="main-view">
        <header class="header-top">
            <div>
                <h1>UI Sections Manager</h1>
                <p style="color: #888; font-size: 14px;">Manage dynamic categories and promotional grids</p>
            </div>
            <div class="admin-badge" style="background: white; padding: 10px 20px; border-radius: 30px; font-weight: 600; font-size: 13px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <i class="fas fa-user-shield" style="color: var(--primary);"></i> System Admin
            </div>
        </header>

        <!-- Tab Navigation -->
        <nav class="tab-nav">
            <a href="offer.php?sec=2" class="tab-link <?= $sec==2?'active':'' ?>">Latest Categories</a>
            <a href="offer.php?sec=3" class="tab-link <?= $sec==3?'active':'' ?>">Featured Solutions</a>
            <a href="offer.php?sec=4" class="tab-link <?= $sec==4?'active':'' ?>">Special Offers</a>
        </nav>

        <?php if($message || isset($_GET['msg'])): ?>
            <div class='alert'><i class='fas fa-check-circle'></i> Item processed successfully.</div>
        <?php endif; ?>

        <div class="flex-layout">
            
            <!-- FORM PANEL -->
            <section class="panel">
                <h2><i class="fas fa-edit" style="color: var(--primary);"></i> <?= $edit['id'] ? 'Update Item' : 'Create New Item' ?></h2>
                <form action="offer.php?sec=<?= $sec ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $edit['id'] ?>">
                    <input type="hidden" name="old_img" value="<?= $edit['image_path'] ?>">
                    
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($edit['title']) ?>" placeholder="Display Title" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Short Description</label>
                        <textarea name="short_desc" required rows="2" placeholder="Brief tagline..."><?= htmlspecialchars($edit['short_desc']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Detailed Description (Optional)</label>
                        <textarea name="long_desc" rows="3" placeholder="Full details..."><?= htmlspecialchars($edit['long_desc']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Cover Image</label>
                        <?php if($edit['image_path']): ?>
                            <img src="uploads/<?= $edit['image_path'] ?>" class="thumb" style="width:100px; height:60px; margin-bottom:10px;">
                        <?php endif; ?>
                        <input type="file" name="image">
                    </div>
                    
                    <button type="submit" name="save_btn" class="btn-save" style="width: 100%;">SAVE TO SECTION <?= $sec ?></button>
                    
                    <?php if($edit['id']): ?>
                        <a href="offer.php?sec=<?= $sec ?>" style="display:block; text-align:center; margin-top:15px; color:#999; font-size:12px; text-decoration:none;">Discard Changes</a>
                    <?php endif; ?>
                </form>
            </section>

            <!-- DATA TABLE PANEL -->
            <section class="panel">
                <h2><i class="fas fa-list" style="color: var(--primary);"></i> Current Items (Section <?= $sec ?>)</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Details</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM $table ORDER BY id DESC");
                            while($row = $stmt->fetch()){
                                echo "<tr>
                                    <td style='width:60px;'><img src='uploads/{$row['image_path']}' class='thumb' onerror=\"this.src='https://via.placeholder.com/50'\"></td>
                                    <td>
                                        <div style='font-weight:700; color:var(--sidebar-bg);'>{$row['title']}</div>
                                        <div style='font-size:12px; color:#777;'>".mb_strimwidth($row['short_desc'], 0, 60, "...")."</div>
                                    </td>
                                    <td class='actions'>
                                        <a href='?sec=$sec&edit={$row['id']}' class='act-btn act-edit'><i class='fas fa-pen-to-square'></i></a>
                                        <a href='?sec=$sec&del={$row['id']}' class='act-btn act-del' onclick=\"return confirm('Delete this permanently?')\"><i class='fas fa-trash-can'></i></a>
                                    </td>
                                </tr>";
                            }
                            if($stmt->rowCount() == 0) echo "<tr><td colspan='3' style='text-align:center; padding:40px; color:#999;'>No content found in this section.</td></tr>";
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </div>
    </main>

</body>
</html>