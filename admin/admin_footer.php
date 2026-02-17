<?php
session_start();
// --- DATABASE CONNECTION ---
include '../db.php'; 

// --- CONFIGURATION ---
$max_file_size = 2 * 1024 * 1024; // 2MB
$upload_dir = "../uploads/";
$valid_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

$message = "";
$msg_type = "";

// --- 1. HANDLE OFFICE IMAGE & SETTINGS UPDATE ---
if (isset($_POST['update_settings'])) {
    $link_url = trim($_POST['office_link']);
    
    // Save Link
    $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('office_image_link', ?) ON DUPLICATE KEY UPDATE setting_value = ?")
        ->execute([$link_url, $link_url]);

    // Save Image (if uploaded)
    if (!empty($_FILES['office_img']['name'])) {
        if ($_FILES['office_img']['size'] > $max_file_size) {
            $message = "Error: File too large (Max 2MB)"; $msg_type = "error";
        } elseif (!in_array($_FILES['office_img']['type'], $valid_types)) {
            $message = "Error: Invalid file type"; $msg_type = "error";
        } else {
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            $ext = pathinfo($_FILES['office_img']['name'], PATHINFO_EXTENSION);
            $new_name = "office_" . time() . "." . $ext;
            
            if (move_uploaded_file($_FILES['office_img']['tmp_name'], $upload_dir . $new_name)) {
                $db_path = "uploads/" . $new_name;
                $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('office_image', ?) ON DUPLICATE KEY UPDATE setting_value = ?")
                    ->execute([$db_path, $db_path]);
                $message = "Office settings updated successfully!"; $msg_type = "success";
            }
        }
    } else {
        $message = "Link updated successfully!"; $msg_type = "success";
    }
}

// --- 2. HANDLE ADD OR UPDATE LINK ---
if (isset($_POST['save_link'])) {
    $id = $_POST['link_id'];
    $section = $_POST['section'];
    $text = $_POST['link_text'];
    $url = $_POST['link_url'];

    if ($id) {
        $pdo->prepare("UPDATE footer_links SET column_section=?, link_text=?, link_url=? WHERE id=?")
            ->execute([$section, $text, $url, $id]);
        $message = "Link updated successfully!";
    } else {
        $pdo->prepare("INSERT INTO footer_links (column_section, link_text, link_url) VALUES (?, ?, ?)")
            ->execute([$section, $text, $url]);
        $message = "New link added to footer!";
    }
    $msg_type = "success";
}

// --- 3. HANDLE DELETE ---
if (isset($_GET['delete_id'])) {
    $pdo->prepare("DELETE FROM footer_links WHERE id = ?")->execute([$_GET['delete_id']]);
    header("Location: admin_footer.php?msg=deleted");
    exit();
}

// --- 4. PREPARE EDIT DATA ---
$edit_id = $edit_section = $edit_text = $edit_url = "";
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM footer_links WHERE id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $edit_id = $row['id'];
        $edit_section = $row['column_section'];
        $edit_text = $row['link_text'];
        $edit_url = $row['link_url'];
    }
}

// --- 5. FETCH ALL DATA FOR DISPLAY ---
$settings = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$curr_img = isset($settings['office_image']) ? "../" . $settings['office_image'] : '';
$curr_link = $settings['office_image_link'] ?? '';
$links = $pdo->query("SELECT * FROM footer_links ORDER BY column_section, id")->fetchAll(PDO::FETCH_ASSOC);

if(isset($_GET['msg']) && $_GET['msg']=='deleted') { $message = "Link removed successfully."; $msg_type = "success"; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Footer Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f4f7f6; margin: 0; display: flex; }
        
        /* Sidebar Navigation */
        .sidebar { width: 260px; background: #2c3e50; min-height: 100vh; color: white; position: fixed; }
        .sidebar h2 { text-align: center; font-size: 18px; border-bottom: 1px solid #3e4f5f; padding: 20px 0; margin: 0; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar ul li a { display: block; padding: 12px 25px; color: #bdc3c7; text-decoration: none; transition: 0.3s; font-size: 14px; }
        .sidebar ul li a:hover, .sidebar ul li a.active { background: #34495e; color: white; border-left: 4px solid #3498db; }
        .sidebar i { margin-right: 10px; width: 20px; text-align: center; }

        /* Main Content Area */
        .main-content { flex: 1; margin-left: 260px; padding: 30px; }
        .container { max-width: 1000px; margin: 0 auto; }
        
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .btn-view { background: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; color: #007bff; font-weight: bold; border: 1px solid #007bff; transition: 0.2s; }
        .btn-view:hover { background: #007bff; color: white; }

        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        h3 { margin-top: 0; color: #333; border-left: 4px solid #007bff; padding-left: 10px; margin-bottom: 20px; }
        
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; font-size: 14px; }
        input[type="text"], select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }

        .btn-save { background: #007bff; color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 15px; }
        .btn-add { background: #28a745; }
        .btn-save:hover { opacity: 0.9; }

        .msg { padding: 15px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; }
        .success { background: #d4edda; color: #155724; border-left: 5px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; border-left: 5px solid #dc3545; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9f9f9; color: #666; font-size: 12px; text-transform: uppercase; }
        .actions a { text-decoration: none; font-weight: bold; margin-right: 15px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2><i class="fas fa-cogs"></i> SITE MANAGE</h2>
    <ul>
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="about.php"><i class="fas fa-info-circle"></i> About Content</a></li>
        <li><a href="job_admin.php"><i class="fas fa-briefcase"></i> Job Postings</a></li>
        <li><a href="admin_help_center.php"><i class="fas fa-question-circle"></i> FAQ / Help Center</a></li>
        <li><hr style="border:0; border-top:1px solid #3e4f5f; margin:10px 0;"></li>
        <li><a href="admin_warranty.php"><i class="fas fa-shield-alt"></i> Warranty Policy</a></li>
        <li><a href="admin_privacy.php"><i class="fas fa-user-shield"></i> Privacy Policy</a></li>
        <li><a href="admin_terms.php"><i class="fas fa-file-contract"></i> Terms of Service</a></li>
        <li><a href="admin_return.php"><i class="fas fa-undo-alt"></i> Return Policy</a></li>
       
    </ul>
</div>

<div class="main-content">
    <div class="container">
        
        <div class="top-bar">
            <h2 style="margin:0;">Footer & Office Settings</h2>
            <a href="../index.php" target="_blank" class="btn-view"><i class="fas fa-external-link-alt"></i> View Shop</a>
        </div>

        <?php if($message): ?>
            <div class="msg <?php echo $msg_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- 1. OFFICE IMAGE SETTINGS -->
        <div class="card">
            <h3><i class="fas fa-building"></i> Office Banner & Link</h3>
            <form method="POST" enctype="multipart/form-data">
                <label>External Destination URL</label>
                <input type="text" name="office_link" value="<?php echo htmlspecialchars($curr_link); ?>" placeholder="e.g. Google Maps URL">
                
                <label>Update Office Photo</label>
                <input type="file" name="office_img" accept="image/*">
                
                <?php if($curr_img && file_exists($curr_img)): ?>
                    <div style="margin-top:15px; border:1px dashed #ccc; padding:10px; display:inline-block;">
                        <img src="<?php echo htmlspecialchars($curr_img); ?>" style="max-height:80px; border-radius:4px;">
                    </div>
                <?php endif; ?>
                
                <button type="submit" name="update_settings" class="btn-save">Update Office Info</button>
            </form>
        </div>

        <!-- 2. FOOTER LINKS -->
        <div class="card" id="linkForm">
            <h3><i class="fas fa-link"></i> <?php echo $edit_id ? 'Edit Footer Link' : 'Add New Footer Link'; ?></h3>
            <form method="POST">
                <input type="hidden" name="link_id" value="<?php echo $edit_id; ?>">
                
                <div style="display:flex; gap:20px; margin-bottom:15px;">
                    <div style="flex:1">
                        <label>Footer Column</label>
                        <select name="section" required>
                            <option value="ABOUT TECHMART" <?php if($edit_section == "ABOUT TECHMART") echo 'selected'; ?>>ABOUT TECHMART</option>
                            <option value="CUSTOMER SERVICE" <?php if($edit_section == "CUSTOMER SERVICE") echo 'selected'; ?>>CUSTOMER SERVICE</option>
                            <option value="POLICIES" <?php if($edit_section == "POLICIES") echo 'selected'; ?>>POLICIES</option>
                        </select>
                    </div>
                    <div style="flex:1">
                        <label>Link Display Text</label>
                        <input type="text" name="link_text" value="<?php echo htmlspecialchars($edit_text); ?>" required>
                    </div>
                </div>

                <label>Target URL (Local file or Full Link)</label>
                <input type="text" name="link_url" value="<?php echo htmlspecialchars($edit_url); ?>" placeholder="e.g. about.php or https://..." required>

                <button type="submit" name="save_link" class="btn-save btn-add">
                    <?php echo $edit_id ? 'Update Link' : 'Add Link to Footer'; ?>
                </button>
                
                <?php if($edit_id): ?>
                    <div style="text-align:center; margin-top:10px;"><a href="admin_footer.php" style="color:#dc3545; text-decoration:none; font-size:14px;">Cancel Edit</a></div>
                <?php endif; ?>
            </form>
        </div>

        <!-- 3. LIST TABLE -->
        <div class="card">
            <h3><i class="fas fa-list"></i> Current Links</h3>
            <table>
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>Link Title & URL</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($links as $link): ?>
                    <tr>
                        <td><small style="color:#666; font-weight:bold;"><?php echo htmlspecialchars($link['column_section']); ?></small></td>
                        <td>
                            <strong><?php echo htmlspecialchars($link['link_text']); ?></strong><br>
                            <span style="font-size:12px; color:#007bff;"><?php echo htmlspecialchars($link['link_url']); ?></span>
                        </td>
                        <td>
                            <a href="?edit_id=<?php echo $link['id']; ?>#linkForm" style="color:#f39c12;"><i class="fas fa-edit"></i></a>
                            <a href="?delete_id=<?php echo $link['id']; ?>" style="color:#dc3545;" onclick="return confirm('Delete this link?');"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>