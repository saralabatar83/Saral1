<?php
// 1. Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Include database connection
if (file_exists('../db.php')) {
    include '../db.php';
}

// 3. EMERGENCY FIX: Connection fallback
if (!isset($conn)) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "saral_db"; 
    $conn = new mysqli($servername, $username, $password, $dbname);
}

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// 4. HANDLE UPLOAD
if (isset($_POST['upload'])) {
    $target_dir = "../images/"; 
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = time() . "_" . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        $conn->query("INSERT INTO slider_images (file_name) VALUES ('$file_name')");
        header("Location: sliderimg.php?msg=Image Uploaded Successfully");
        exit();
    }
}

// 5. HANDLE DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $res = $conn->query("SELECT file_name FROM slider_images WHERE id=$id");
    if($res && $row = $res->fetch_assoc()){
        $file_path = "../images/" . $row['file_name'];
        if (file_exists($file_path)) {
            unlink($file_path); 
        }
        $conn->query("DELETE FROM slider_images WHERE id=$id");
    }
    header("Location: sliderimg.php?msg=Image Deleted");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Slider Manager | Saral IT Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root { 
            --sidebar-width: 260px; 
            --bg-dark: #0f172a; 
            --accent-blue: #3498db; 
            --card-dark: #1e293b;
        }

        body { background: #020617; color: white; font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; }

        /* --- SIDEBAR STYLES --- */
        .sidebar { width: var(--sidebar-width); height: 100vh; background: var(--bg-dark); color: white; position: fixed; left: 0; top: 0; overflow-y: auto; z-index: 1000; border-right: 1px solid rgba(255,255,255,0.05); }
        .sidebar-header { padding: 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h2 { font-size: 22px; font-weight: 700; margin: 0; }
        .sidebar-header span { color: var(--accent-blue); }
        .sidebar-menu { list-style: none; padding: 20px 0; margin: 0; }
        .sidebar-menu li a { display: block; padding: 12px 25px; color: #bdc3c7; text-decoration: none; transition: 0.3s; font-size: 14px; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { color: white; background: rgba(255,255,255,0.1); border-left: 4px solid var(--accent-blue); }
        .sidebar-menu li a i { margin-right: 12px; width: 20px; text-align: center; }
        .menu-divider { padding: 15px 25px 5px; font-size: 11px; text-transform: uppercase; color: #7f8c8d; font-weight: bold; }

        /* --- CONTENT AREA --- */
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; min-width: 0; }
        .admin-card { background: var(--card-dark); border-radius: 15px; padding: 30px; border: 1px solid #334155; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        
        /* Upload Section */
        .upload-zone { border: 2px dashed #475569; padding: 30px; border-radius: 12px; text-align: center; margin-bottom: 30px; background: rgba(255,255,255,0.02); }
        .upload-zone:hover { border-color: var(--accent-blue); background: rgba(52, 152, 219, 0.05); }
        
        input[type="file"] { background: #0f172a; padding: 10px; border-radius: 8px; width: 100%; max-width: 400px; border: 1px solid #334155; color: #94a3b8; }
        .btn-upload { background: var(--accent-blue); color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: bold; transition: 0.3s; margin-top: 15px; }
        .btn-upload:hover { opacity: 0.9; transform: translateY(-2px); }

        /* Gallery Grid */
        .img-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .img-item { background: #0f172a; border-radius: 12px; overflow: hidden; border: 1px solid #334155; position: relative; transition: 0.3s; }
        .img-item:hover { transform: scale(1.03); border-color: #ef4444; }
        .img-item img { width: 100%; height: 140px; object-fit: cover; }
        .btn-del { display: block; padding: 10px; color: #ef4444; text-decoration: none; text-align: center; font-size: 12px; font-weight: bold; background: rgba(239, 68, 68, 0.1); }
        .btn-del:hover { background: #ef4444; color: white; }
        
        .status-msg { background: #10b981; color: white; padding: 10px 20px; border-radius: 8px; margin-bottom: 20px; display: inline-block; font-size: 14px; }
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
         
            <li><a href="sliderimg.php" class="active"><i class="fas fa-images"></i> Slider Manager</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="m-0"><i class="fas fa-images me-2 text-info"></i> Slider Image Management</h2>
                <a href="../index.php" target="_blank" class="btn btn-sm btn-outline-info">View Site <i class="fas fa-external-link-alt"></i></a>
            </div>

            <?php if(isset($_GET['msg'])): ?>
                <div class="status-msg"><i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($_GET['msg']) ?></div>
            <?php endif; ?>

            <!-- UPLOAD ZONE -->
            <div class="upload-zone">
                <form method="POST" enctype="multipart/form-data">
                    <p class="text-secondary small mb-3">Upload high-resolution images (Suggested: 1920x600px)</p>
                    <input type="file" name="image" required><br>
                    <button type="submit" name="upload" class="btn-upload">
                        <i class="fas fa-cloud-upload-alt me-2"></i> UPDATE NEW SLIDER IMAGE
                    </button>
                </form>
            </div>

            <h5 class="mb-4 text-secondary"><i class="fas fa-th me-2"></i> Current Live Slides</h5>
            
            <!-- GALLERY GRID -->
            <div class="img-grid">
                <?php
                $result = $conn->query("SELECT * FROM slider_images ORDER BY id DESC");
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<div class='img-item'>
                                <img src='../images/{$row['file_name']}' alt='Slider'>
                                <a href='sliderimg.php?delete={$row['id']}' class='btn-del' onclick='return confirm(\"Are you sure you want to remove this slide?\")'>
                                    <i class='fas fa-trash-alt me-1'></i> REMOVE
                                </a>
                              </div>";
                    }
                } else {
                    echo "<div class='w-100 text-center py-5 text-secondary'><i class='fas fa-folder-open fa-3x mb-3'></i><p>No slider images found.</p></div>";
                }
                ?>
            </div>
        </div>
    </main>

</body>
</html>