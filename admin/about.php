<?php
session_start();
// --- DATABASE CONNECTION ---
include 'db.php'; 

$message = "";
$msg_type = "";

// --- 1. HANDLE CONTENT SUBMISSION (Hero & About) ---
if (isset($_POST['update_content'])) {
    $imagePath = $_POST['hero_image_url']; 
    
    // File Upload Logic
    if (isset($_FILES['hero_image_file']) && $_FILES['hero_image_file']['name'] != '') {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES["hero_image_file"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        if(move_uploaded_file($_FILES["hero_image_file"]["tmp_name"], $targetFilePath)){
            $imagePath = $targetFilePath;
        }
    }

    // Update Hero Section
    $stmt = $pdo->prepare("UPDATE page_content SET title=?, image_url=? WHERE section_key='hero'");
    $stmt->execute([$_POST['hero_title'], $imagePath]);
    
    // Update About Section (Rich Text)
    $stmt = $pdo->prepare("UPDATE page_content SET title=?, body_text=? WHERE section_key='about'");
    $stmt->execute([$_POST['about_title'], $_POST['about_text']]); 
    
    $message = "Content updated successfully!";
    $msg_type = "success";
}

// --- 2. HANDLE VALUES UPDATE ---
if (isset($_POST['update_values'])) {
    foreach ($_POST['val'] as $id => $data) {
        $stmt = $pdo->prepare("UPDATE store_values SET title=?, description=?, icon_name=? WHERE id=?");
        $stmt->execute([$data['title'], $data['desc'], $data['icon'], $id]);
    }
    $message = "Store values updated!";
    $msg_type = "success";
}

// --- 3. FETCH DATA ---
$stmt = $pdo->query("SELECT * FROM page_content");
$content = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { 
    $content[$row['section_key']] = $row; 
}

$stmt = $pdo->query("SELECT * FROM store_values ORDER BY id ASC");
$values = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Content Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
    
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; display: flex; }
        
        /* Sidebar Navigation */
        .sidebar { width: 260px; background: #2c3e50; min-height: 100vh; color: white; position: fixed; }
        .sidebar h2 { text-align: center; font-size: 18px; border-bottom: 1px solid #3e4f5f; padding: 20px 0; margin: 0; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar ul li a { display: block; padding: 15px 25px; color: #bdc3c7; text-decoration: none; transition: 0.3s; }
        .sidebar ul li a:hover, .sidebar ul li a.active { background: #34495e; color: white; border-left: 4px solid #3498db; }
        .sidebar i { margin-right: 10px; width: 20px; }

        /* Main Content Area */
        .main-content { flex: 1; margin-left: 260px; padding: 30px; }
        .container { max-width: 900px; margin: 0 auto; }
        
        .header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        
        h3 { margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee; color: #333; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        
        input[type="text"], textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn-save { background: #C62828; color: white; border: none; padding: 12px 30px; cursor: pointer; border-radius: 4px; font-weight: bold; transition: 0.3s; }
        .btn-save:hover { background: #b71c1c; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        
        .msg { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 5px solid #28a745; }
        
        /* Values Grid */
        .value-item { display: flex; gap: 10px; margin-bottom: 15px; align-items: center; }
        .value-item strong { width: 120px; color: #666; font-size: 13px; }
    </style>

    <script>
      tinymce.init({
        selector: '.editor', 
        height: 350,
        menubar: false,
        branding: false,
        plugins: 'lists link code',
        toolbar: 'undo redo | fontfamily fontsize | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | code',
        setup: function (editor) {
            editor.on('change', function () { editor.save(); });
        },
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
      });
    </script>
</head>
<body>

<div class="sidebar">
    <h2><i class="fas fa-tools"></i> ADMIN DASHBOARD</h2>
    <ul>
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="about.php" class="active"><i class="fas fa-info-circle"></i> About Page</a></li>
        <li><a href="admin_warranty.php"><i class="fas fa-shield-alt"></i> Warranty Policy</a></li>
        <li><a href="admin_help_center.php"><i class="fas fa-question-circle"></i> Help Center</a></li>
        <li><a href="admin_privacy.php"><i class="fas fa-user-shield"></i> Privacy Policy</a></li>
        <li><a href="admin_terms.php"><i class="fas fa-file-contract"></i> Terms</a></li>
       <li><a href="admin_return.php"><i class="fas fa-undo-alt"></i> Return Policy</a></li>
        <li><a href="job_admin.php"><i class="fas fa-briefcase"></i> Job Admin</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="container">
        
        <div class="header-row">
            <h2 style="margin:0;">About Page Manager</h2>
            <a href="index.php" style="text-decoration:none; color:#3498db; font-weight:bold;"><i class="fas fa-external-link-alt"></i> View Live Site</a>
        </div>

        <?php if($message): ?>
            <div class="msg"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <!-- HERO SECTION -->
            <div class="card">
                <h3><i class="fas fa-image"></i> Hero Section</h3>
                <div class="form-group">
                    <label>Main Headline</label>
                    <input type="text" name="hero_title" value="<?php echo htmlspecialchars($content['hero']['title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Hero Background Image</label>
                    <input type="file" name="hero_image_file" style="margin-bottom:10px;">
                    <input type="hidden" name="hero_image_url" value="<?php echo htmlspecialchars($content['hero']['image_url'] ?? ''); ?>">
                    <?php if(!empty($content['hero']['image_url'])): ?>
                        <div style="font-size:12px; color:#666;">Current: <?php echo $content['hero']['image_url']; ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ABOUT SECTION -->
            <div class="card">
                <h3><i class="fas fa-edit"></i> About Us Content</h3>
                <div class="form-group">
                    <label>Sub-heading</label>
                    <input type="text" name="about_title" value="<?php echo htmlspecialchars($content['about']['title'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label>Detailed Description</label>
                    <textarea name="about_text" class="editor"><?php echo $content['about']['body_text'] ?? ''; ?></textarea>
                </div>

                <button type="submit" name="update_content" class="btn-save">Save Page Content</button>
            </div>
        </form>
        
        <!-- VALUES SECTION -->
        <div class="card">
            <h3><i class="fas fa-star"></i> Our Values / Features</h3>
            <form method="POST">
                <?php foreach($values as $v): ?>
                    <div class="value-item">
                        <strong><?php echo $v['category']; ?>:</strong>
                        <input type="text" name="val[<?php echo $v['id']; ?>][title]" value="<?php echo htmlspecialchars($v['title']); ?>" placeholder="Title" style="flex:1">
                        <input type="text" name="val[<?php echo $v['id']; ?>][desc]" value="<?php echo htmlspecialchars($v['description']); ?>" placeholder="Short description" style="flex:2">
                        <input type="text" name="val[<?php echo $v['id']; ?>][icon]" value="<?php echo htmlspecialchars($v['icon_name']); ?>" placeholder="Icon (e.g. fas fa-truck)" style="flex:1">
                    </div>
                <?php endforeach; ?>
                <button type="submit" name="update_values" class="btn-save" style="margin-top:10px; width:100%;">Update Store Values</button>
            </form>
        </div>

    </div>
</div>

</body>
</html>