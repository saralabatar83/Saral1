<?php
session_start();
// --- DATABASE CONNECTION ---
include '../db.php'; 

$message = "";
$msg_type = "";

// --- 1. HANDLE FORM SUBMIT ---
if (isset($_POST['update_terms'])) {
    $title = $_POST['hero_title'];
    $image = $_POST['hero_image'];
    $date  = $_POST['last_updated'];
    $body  = $_POST['content'];

    // Update the single row (ID 1)
    $sql = "UPDATE terms SET hero_title=?, hero_image=?, last_updated=?, content=? WHERE id=1";
    $stmt = $pdo->prepare($sql);
    
    if($stmt->execute([$title, $image, $date, $body])) {
        $message = "Terms of Service updated successfully!";
        $msg_type = "success";
    } else {
        $message = "Error: Could not update the database.";
        $msg_type = "error";
    }
}

// --- 2. FETCH DATA ---
$stmt = $pdo->query("SELECT * FROM terms WHERE id = 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Defaults
$heroTitle = $row['hero_title'] ?? 'Terms of Service';
$heroImage = $row['hero_image'] ?? '';
$lastUpdated = $row['last_updated'] ?? '';
$content = $row['content'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Terms of Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- TinyMCE for easier editing -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f4f7f6; margin: 0; display: flex; }
        
        /* Sidebar Navigation */
        .sidebar { width: 260px; background: #2c3e50; min-height: 100vh; color: white; position: fixed; }
        .sidebar h2 { text-align: center; font-size: 18px; border-bottom: 1px solid #3e4f5f; padding: 20px 0; margin: 0; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar ul li a { display: block; padding: 15px 25px; color: #bdc3c7; text-decoration: none; transition: 0.3s; }
        .sidebar ul li a:hover, .sidebar ul li a.active { background: #34495e; color: white; border-left: 4px solid #3498db; }
        .sidebar i { margin-right: 10px; width: 20px; }

        /* Main Content Area */
        .main-content { flex: 1; margin-left: 260px; padding: 30px; }
        .container { max-width: 1000px; margin: 0 auto; }
        
        .header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .btn-view { background: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; color: #007bff; font-weight: bold; border: 1px solid #007bff; transition: 0.2s; }
        .btn-view:hover { background: #007bff; color: white; }

        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        h3 { margin-top: 0; color: #333; border-left: 4px solid #001f3f; padding-left: 10px; margin-bottom: 20px; }
        
        .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
        .form-group { flex: 1; margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; font-size: 14px; }
        input[type="text"] { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }

        .btn-save { 
            background: #001f3f; color: white; border: none; padding: 15px 30px; 
            font-size: 1rem; border-radius: 5px; cursor: pointer; width: 100%; 
            font-weight: bold; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn-save:hover { background: #00152a; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }

        .msg { padding: 15px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; }
        .success { background: #d4edda; color: #155724; border-left: 5px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; border-left: 5px solid #dc3545; }

        .tips { background: #e3f2fd; padding: 15px; border-left: 4px solid #2196F3; margin-bottom: 20px; font-size: 0.9rem; border-radius: 4px; }
    </style>

    <script>
      tinymce.init({
        selector: '#terms-editor', 
        height: 500,
        menubar: true,
        plugins: 'lists link code table emoticons',
        toolbar: 'undo redo | fontfamily fontsize | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table emoticons | code',
        setup: function (editor) { editor.on('change', function () { editor.save(); }); },
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
      });
    </script>
</head>
<body>

<div class="sidebar">
    <h2><i class="fas fa-file-contract"></i> ADMIN PANEL</h2>
    <ul>
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="about.php"><i class="fas fa-info-circle"></i> About Page</a></li>
        <li><a href="admin_warranty.php"><i class="fas fa-shield-alt"></i> Warranty Policy</a></li>
        <li><a href="admin_help_center.php"><i class="fas fa-question-circle"></i> Help Center</a></li>
        <li><a href="admin_privacy.php"><i class="fas fa-user-shield"></i> Privacy Policy</a></li>
        <li><a href="admin_terms.php" class="active"><i class="fas fa-file-contract"></i> Terms</a></li>
 <li><a href="admin_return.php"><i class="fas fa-undo-alt"></i> Return Policy</a></li>
        <li><a href="job_admin.php"><i class="fas fa-briefcase"></i> Job Admin</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="container">
        
        <div class="header-row">
            <h2 style="margin:0;">Terms of Service Manager</h2>
            <a href="../terms_services.php" target="_blank" class="btn-view">
                <i class="fas fa-external-link-alt"></i> View Live Page
            </a>
        </div>

        <?php if($message): ?>
            <div class="msg <?php echo $msg_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <!-- HEADER SETTINGS -->
            <div class="card">
                <h3><i class="fas fa-cog"></i> Header Configuration</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Main Page Title</label>
                        <input type="text" name="hero_title" value="<?php echo htmlspecialchars($heroTitle); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Last Updated Label</label>
                        <input type="text" name="last_updated" value="<?php echo htmlspecialchars($lastUpdated); ?>" placeholder="e.g. Revised February 2024">
                    </div>
                </div>

                <div class="form-group">
                    <label>Hero Background Image URL</label>
                    <input type="text" name="hero_image" value="<?php echo htmlspecialchars($heroImage); ?>" placeholder="https://example.com/legal-banner.jpg">
                </div>
            </div>

            <!-- TERMS CONTENT -->
            <div class="card">
                <h3><i class="fas fa-file-invoice"></i> Terms Content</h3>
                
                <div class="tips">
                    <i class="fas fa-info-circle"></i> <strong>Pro Tip:</strong> Use "Heading 3" for your numbered sections (1, 2, 3...) to keep the design consistent with your frontend.
                </div>

                <div class="form-group">
                    <label>Detailed Terms (Rich Editor)</label>
                    <textarea name="content" id="terms-editor"><?php echo $content; ?></textarea>
                </div>

                <button type="submit" name="update_terms" class="btn-save">
                    <i class="fas fa-save"></i> Save Terms of Service Changes
                </button>
            </div>
        </form>

    </div>
</div>

</body>
</html>