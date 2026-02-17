<?php
/**
 * Saral IT - Banner Manager
 */
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "saral_db";

$msg = ""; 

// --- 1. HANDLE FORM SUBMISSION ---
if (isset($_POST['upload_btn'])) {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

    $fileName = $_FILES['banner_img']['name'];
    $tmpName  = $_FILES['banner_img']['tmp_name'];
    $folder   = "uploads/"; 

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    // Sanitize filename to avoid issues
    $cleanFileName = time() . "_" . basename($fileName);
    $targetFile = $folder . $cleanFileName;

    if (move_uploaded_file($tmpName, $targetFile)) {
        $sql = "INSERT INTO banners1 (image_path) VALUES ('$targetFile')";
        if ($conn->query($sql) === TRUE) {
            $msg = "Banner uploaded successfully!";
        } else {
            $msg = "Error: " . $conn->error;
        }
    } else {
        $msg = "Failed to upload image to folder.";
    }
    $conn->close();
}

// --- 2. HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $id = (int)$_GET['delete'];
    
    // Get file path to delete from folder
    $res = $conn->query("SELECT image_path FROM banners1 WHERE id = $id");
    if($row = $res->fetch_assoc()) {
        if(file_exists($row['image_path'])) { @unlink($row['image_path']); }
    }
    
    $conn->query("DELETE FROM banners1 WHERE id = $id");
    header("Location: Banners.php?msg=deleted");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saral IT - Banner Manager</title>
    
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
        }

        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }

        /* Panels */
        .panel { background: var(--white); padding: 25px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .panel h2 { margin-bottom: 20px; font-size: 18px; color: var(--sidebar-bg); border-bottom: 1px solid #eee; padding-bottom: 10px; display: flex; align-items: center; gap: 10px; }

        /* Form */
        .upload-area {
            border: 2px dashed #ddd;
            padding: 40px;
            text-align: center;
            border-radius: 12px;
            background: #fafafa;
            transition: 0.3s;
        }
        .upload-area:hover { border-color: var(--primary); background: #f0f2f5; }
        
        input[type="file"] { margin: 15px 0; display: block; width: 100%; max-width: 300px; margin: 20px auto; }
        
        .btn-upload { 
            background: var(--primary); color: white; border: none; padding: 12px 30px; 
            border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 14px; transition: 0.3s;
        }
        .btn-upload:hover { background: var(--secondary); transform: translateY(-1px); }

        /* Gallery/Table */
        .banner-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .banner-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: relative;
        }
        .banner-card img { width: 100%; height: 150px; object-fit: cover; display: block; }
        .banner-card .actions {
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .del-link { color: var(--danger); text-decoration: none; font-size: 14px; font-weight: 600; }
        .del-link:hover { text-decoration: underline; }

        .alert { 
            padding: 15px; background: #d4edda; color: #155724; border-radius: 8px; 
            margin-bottom: 25px; border-left: 5px solid #28a745; display: flex; align-items: center; gap: 10px;
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

    <!-- FIXED SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Saral<span>Admin</span></h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-th-large"></i> <span>Dashboard</span></a></li>
            
            <div class="menu-divider">Catalog</div>
            <li><a href="all categories.php"><i class="fas fa-folder"></i> <span>Categories</span></a></li>
            <li><a href="admin_categories.php"><i class="fas fa-list-ul"></i> <span>Manage Cats</span></a></li>

            <div class="menu-divider">Marketing</div>
            <li><a href="offer.php"><i class="fas fa-fire"></i> <span>Daily Offers</span></a></li>
          
            <li><a href="Banners.php" class="active"><i class="fas fa-ad"></i> <span>Banners</span></a></li>
            
            <div class="menu-divider">System & Settings</div>
            <li><a href="formfillup.php"><i class="fas fa-user-friends"></i> <span>Customers</span></a></li>
            <li><a href="services.php"><i class="fas fa-concierge-bell"></i> <span>Services</span></a></li>
            <li><a href="sociallinks.php"><i class="fas fa-hashtag"></i> <span>Social Links</span></a></li>
            <li><a href="top_setting.php"><i class="fas fa-cog"></i> <span>Header Settings</span></a></li>
            <li><a href="admin_footer.php"><i class="fas fa-shoe-prints"></i> <span>Footer Manager</span></a></li>
            
            <div class="menu-divider">Account</div>
            <li><a href="change_password.php"><i class="fas fa-key"></i> <span>Security</span></a></li>
            <li><a href="logout.php" style="color: #ff7675;"><i class="fas fa-power-off"></i> <span>Logout</span></a></li>
        </ul>
    </aside>

    <main class="main-view">
        <header class="header-top">
            <h1>Marketing Banners</h1>
            <div class="admin-badge" style="background: white; padding: 10px 20px; border-radius: 30px; font-weight: 600; font-size: 13px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <i class="fas fa-ad" style="color: var(--primary);"></i> Promotional Space
            </div>
        </header>

        <!-- Feedback Alert -->
        <?php if($msg): ?>
            <div class='alert'>
                <i class='fas fa-check-circle'></i> <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <!-- Upload Panel -->
        <div class="panel">
            <h2><i class="fas fa-plus-circle" style="color: var(--primary);"></i> Add New Banner</h2>
            <div class="upload-area">
                <form action="" method="POST" enctype="multipart/form-data">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 40px; color: #ddd; margin-bottom: 10px;"></i>
                    <p style="color: #666; font-size: 14px;">Recommended size: 1200x400 pixels</p>
                    <input type="file" name="banner_img" required>
                    <br>
                    <button type="submit" name="upload_btn" class="btn-upload">Upload to Website</button>
                </form>
            </div>
        </div>

        <!-- Inventory Panel -->
        <div class="panel">
            <h2><i class="fas fa-images" style="color: var(--primary);"></i> Live Banners</h2>
            <div class="banner-grid">
                <?php
                $conn = new mysqli($servername, $username, $password, $dbname);
                $result = $conn->query("SELECT * FROM banners1 ORDER BY id DESC");
                if ($result->num_rows > 0):
                    while($row = $result->fetch_assoc()):
                ?>
                <div class="banner-card">
                    <img src="<?php echo $row['image_path']; ?>" alt="Banner">
                    <div class="actions">
                        <span style="font-size: 12px; color: #888;">ID: #<?php echo $row['id']; ?></span>
                        <a href="Banners.php?delete=<?php echo $row['id']; ?>" class="del-link" onclick="return confirm('Delete this banner?')">
                            <i class="fas fa-trash"></i> Remove
                        </a>
                    </div>
                </div>
                <?php endwhile; else: ?>
                    <p style="grid-column: span 3; text-align: center; color: #999; padding: 40px;">No banners uploaded yet.</p>
                <?php endif; $conn->close(); ?>
            </div>
        </div>
    </main>

</body>
</html>