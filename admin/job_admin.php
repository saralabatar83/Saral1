<?php
session_start();
// --- DATABASE CONNECTION ---
include '../db.php'; // Standardized path

// Ensure upload folder exists
if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }

$message = "";
$msg_type = "";
$editMode = false;
$editJob = [];

// ====================================================
// 1. DELETE LOGIC
// ====================================================
if (isset($_GET['del_job'])) {
    $pdo->prepare("DELETE FROM jobs WHERE id=?")->execute([$_GET['del_job']]);
    $message = "Job record deleted successfully!";
    $msg_type = "success";
}
if (isset($_GET['del_cat'])) {
    $pdo->prepare("DELETE FROM job_categories WHERE id=?")->execute([$_GET['del_cat']]);
    $message = "Sector deleted!";
    $msg_type = "success";
}
if (isset($_GET['del_sub'])) {
    $pdo->prepare("DELETE FROM job_sub_categories WHERE id=?")->execute([$_GET['del_sub']]);
    $message = "Sub-Category deleted!";
    $msg_type = "success";
}

// ====================================================
// 2. FORM SUBMISSION LOGIC
// ====================================================

// Add Category
if (isset($_POST['add_category'])) {
    $pdo->prepare("INSERT INTO job_categories (name) VALUES (?)")->execute([$_POST['cat_name']]);
    $message = "New Sector added!";
    $msg_type = "success";
}

// Add Sub-Category
if (isset($_POST['add_sub_category'])) {
    $pdo->prepare("INSERT INTO job_sub_categories (category_id, name) VALUES (?, ?)")
        ->execute([$_POST['parent_cat'], $_POST['sub_name']]);
    $message = "New Sub-Category added!";
    $msg_type = "success";
}

// CREATE or UPDATE Job
if (isset($_POST['save_job'])) {
    $photoPath = $_POST['current_photo']; 

    if (!empty($_FILES['job_photo']['name'])) {
        $targetDir = "uploads/";
        $fileName = time() . "_" . basename($_FILES['job_photo']['name']);
        $targetFilePath = $targetDir . $fileName;
        if (move_uploaded_file($_FILES['job_photo']['tmp_name'], $targetFilePath)) {
            $photoPath = $targetFilePath;
        }
    }

    if (!empty($_POST['job_id'])) {
        // UPDATE
        $stmt = $pdo->prepare("UPDATE jobs SET category_id=?, sub_category_id=?, position=?, salary=?, photo_path=? WHERE id=?");
        $stmt->execute([$_POST['job_cat'], $_POST['job_sub'], $_POST['position'], $_POST['salary'], $photoPath, $_POST['job_id']]);
        $message = "Job updated successfully!";
    } else {
        // CREATE
        $stmt = $pdo->prepare("INSERT INTO jobs (category_id, sub_category_id, position, salary, photo_path) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['job_cat'], $_POST['job_sub'], $_POST['position'], $_POST['salary'], $photoPath]);
        $message = "New Job posted!";
    }
    $msg_type = "success";
}

// ====================================================
// 3. FETCH DATA
// ====================================================
if (isset($_GET['edit_job'])) {
    $editMode = true;
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id=?");
    $stmt->execute([$_GET['edit_job']]);
    $editJob = $stmt->fetch(PDO::FETCH_ASSOC);
}

$cats = $pdo->query("SELECT * FROM job_categories")->fetchAll(PDO::FETCH_ASSOC);
$subs = $pdo->query("SELECT * FROM job_sub_categories")->fetchAll(PDO::FETCH_ASSOC);
$jobs = $pdo->query("SELECT j.*, c.name as cat_name, s.name as sub_name FROM jobs j 
                     LEFT JOIN job_categories c ON j.category_id = c.id
                     LEFT JOIN job_sub_categories s ON j.sub_category_id = s.id
                     ORDER BY j.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Job Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .container { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 320px 1fr; gap: 30px; }
        
        .header-row { grid-column: 1 / -1; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        h3 { margin-top: 0; color: #333; border-left: 4px solid #C62828; padding-left: 10px; margin-bottom: 20px; font-size: 1.1rem; }
        
        /* Forms */
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; font-size: 13px; }
        input[type="text"], input[type="number"], select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; margin-bottom: 15px; }
        .btn-action { background: #C62828; color: white; border: none; padding: 10px; width: 100%; cursor: pointer; border-radius: 4px; font-weight: bold; }
        .btn-action:hover { background: #b71c1c; }

        /* Lists & Tables */
        .list-group { list-style: none; padding: 0; margin: 0; font-size: 0.9rem; }
        .list-group li { padding: 8px 10px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .list-group li:last-child { border-bottom: none; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #f9f9f9; color: #666; font-size: 12px; text-transform: uppercase; }
        
        .thumb { width: 45px; height: 45px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
        .msg { grid-column: 1 / -1; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; }
        .success { background: #d4edda; color: #155724; border-left: 5px solid #28a745; }
        
        .badge-sector { background: #e3f2fd; color: #1976D2; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: bold; }
        .del-x { color: #dc3545; text-decoration: none; font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2><i class="fas fa-briefcase"></i> ADMIN PANEL</h2>
    <ul>
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="about.php"><i class="fas fa-info-circle"></i> About Page</a></li>
        <li><a href="admin_warranty.php"><i class="fas fa-shield-alt"></i> Warranty Policy</a></li>
        <li><a href="admin_help_center.php"><i class="fas fa-question-circle"></i> Help Center</a></li>
        <li><a href="admin_privacy.php"><i class="fas fa-user-shield"></i> Privacy Policy</a></li>
        <li><a href="admin_terms.php"><i class="fas fa-file-contract"></i> Terms</a></li>
        <li><a href="admin_return.php"><i class="fas fa-undo-alt"></i> Return Policy</a></li>
        <li><a href="job_admin.php" class="active"><i class="fas fa-briefcase"></i> Job Admin</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="container">
        <div class="header-row">
            <h2 style="margin:0;">Job Portal Management</h2>
            <a href="../jobs.php" target="_blank" style="color: #3498db; text-decoration:none; font-weight:bold;"><i class="fas fa-external-link-alt"></i> View Jobs Site</a>
        </div>

        <?php if($message): ?>
            <div class="msg <?php echo $msg_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- LEFT COLUMN: SETTINGS -->
        <div>
            <div class="card">
                <h3>Add Sector</h3>
                <form method="POST">
                    <input type="text" name="cat_name" placeholder="e.g. Technology" required>
                    <button type="submit" name="add_category" class="btn-action">Add Sector</button>
                </form>
                <ul class="list-group" style="margin-top:15px;">
                    <?php foreach($cats as $c): ?>
                        <li><span><?php echo $c['name']; ?></span> <a href="?del_cat=<?php echo $c['id']; ?>" class="del-x" onclick="return confirm('Delete Sector?');">&times;</a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card">
                <h3>Add Sub-Category</h3>
                <form method="POST">
                    <label>Parent Sector</label>
                    <select name="parent_cat" required>
                        <option value="">Select...</option>
                        <?php foreach($cats as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="sub_name" placeholder="e.g. Web Dev" required>
                    <button type="submit" name="add_sub_category" class="btn-action">Add Sub-Cat</button>
                </form>
                <ul class="list-group" style="margin-top:15px;">
                    <?php foreach($subs as $s): ?>
                        <li><span><?php echo $s['name']; ?></span> <a href="?del_sub=<?php echo $s['id']; ?>" class="del-x" onclick="return confirm('Delete?');">&times;</a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- RIGHT COLUMN: JOB MANAGEMENT -->
        <div>
            <div class="card" style="border-top: 4px solid <?php echo $editMode ? '#2196F3' : '#C62828'; ?>;">
                <h3><i class="fas fa-edit"></i> <?php echo $editMode ? 'Edit Job Posting' : 'Post New Job'; ?></h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="job_id" value="<?php echo $editMode ? $editJob['id'] : ''; ?>">
                    <input type="hidden" name="current_photo" value="<?php echo $editMode ? $editJob['photo_path'] : ''; ?>">

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div>
                            <label>Sector</label>
                            <select name="job_cat" id="categorySelect" required onchange="filterSubCats()">
                                <option value="">Select...</option>
                                <?php foreach($cats as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php if($editMode && $editJob['category_id'] == $c['id']) echo 'selected'; ?>><?php echo $c['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label>Sub-Category</label>
                            <select name="job_sub" id="subSelect" required>
                                <option value="">Select Sector First</option>
                                <?php foreach($subs as $s): ?>
                                    <option value="<?php echo $s['id']; ?>" data-parent="<?php echo $s['category_id']; ?>" 
                                        <?php if($editMode && $editJob['sub_category_id'] == $s['id']) echo 'selected'; ?>><?php echo $s['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:15px;">
                        <div>
                            <label>Position Name</label>
                            <input type="text" name="position" value="<?php echo $editMode ? htmlspecialchars($editJob['position']) : ''; ?>" required>
                        </div>
                        <div>
                            <label>Salary/Budget</label>
                            <input type="text" name="salary" value="<?php echo $editMode ? htmlspecialchars($editJob['salary']) : ''; ?>" placeholder="e.g. $5k - $8k" required>
                        </div>
                    </div>

                    <label>Listing Photo</label>
                    <input type="file" name="job_photo" accept="image/*">
                    
                    <div style="margin-top:20px; display:flex; align-items:center; gap:15px;">
                        <button type="submit" name="save_job" class="btn-action" style="width:auto; padding:12px 40px;">
                            <i class="fas fa-save"></i> <?php echo $editMode ? 'Update Listing' : 'Publish Job'; ?>
                        </button>
                        <?php if($editMode): ?>
                            <a href="job_admin.php" style="color:#666; text-decoration:none; font-size:14px;">Cancel Edit</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card">
                <h3>Active Job Listings</h3>
                <table>
                    <thead><tr><th>Photo</th><th>Position / Salary</th><th>Sector</th><th width="100">Actions</th></tr></thead>
                    <tbody>
                        <?php foreach($jobs as $j): ?>
                        <tr>
                            <td><img src="<?php echo $j['photo_path'] ?: 'https://via.placeholder.com/50'; ?>" class="thumb"></td>
                            <td>
                                <strong><?php echo htmlspecialchars($j['position']); ?></strong><br>
                                <small style="color:#28a745; font-weight:bold;"><?php echo htmlspecialchars($j['salary']); ?></small>
                            </td>
                            <td>
                                <span class="badge-sector"><?php echo htmlspecialchars($j['cat_name']); ?></span><br>
                                <small style="color:#999;"><?php echo htmlspecialchars($j['sub_name']); ?></small>
                            </td>
                            <td>
                                <a href="?edit_job=<?php echo $j['id']; ?>" title="Edit" style="color:#1976D2; margin-right:15px;"><i class="fas fa-edit"></i></a>
                                <a href="?del_job=<?php echo $j['id']; ?>" title="Delete" style="color:#dc3545;" onclick="return confirm('Permanently delete listing?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function filterSubCats() {
    var catId = document.getElementById('categorySelect').value;
    var subSelect = document.getElementById('subSelect');
    var options = subSelect.querySelectorAll('option');

    options.forEach(function(opt) {
        if (opt.value === "") return;
        if (opt.getAttribute('data-parent') == catId) {
            opt.style.display = 'block';
        } else {
            opt.style.display = 'none';
        }
    });
}
window.onload = filterSubCats;
</script>

</body>
</html>