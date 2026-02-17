<?php
require_once 'db.php'; 

// --- 1. CONFIGURATION ---
$msg = "";
$edit = null;
$upload_dir = "uploads/new_arrivals/";

// Create directory if it doesn't exist
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// --- 2. HANDLE DELETE ---
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    
    // Get image path to delete file from server
    $st = $pdo->prepare("SELECT image_path FROM new_arrivals1 WHERE id = ?");
    $st->execute([$id]);
    $img_name = $st->fetchColumn();
    
    if ($img_name && file_exists($upload_dir . $img_name)) {
        unlink($upload_dir . $img_name); // Remove photo from folder
    }

    $pdo->prepare("DELETE FROM new_arrivals1 WHERE id = ?")->execute([$id]);
    header("Location: admin_newarrivals.php?msg=Deleted Successfully");
    exit;
}

// --- 3. FETCH FOR EDIT ---
if (isset($_GET['edit'])) {
    $st = $pdo->prepare("SELECT * FROM new_arrivals1 WHERE id = ?");
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch();
}

// --- 4. HANDLE SAVE (ADD / UPDATE) ---
if (isset($_POST['save'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $subtitle = $_POST['subtitle'];
    $link = $_POST['link'];
    $status = $_POST['status'];
    $img = $_POST['old_img'] ?? '';

    // Handle New Image Upload
    if (!empty($_FILES['image']['name'])) {
        $filename = $_FILES['image']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        // CLEAN FILENAME: Remove spaces and special chars
        $clean_name = preg_replace("/[^a-zA-Z0-9]/", "_", pathinfo($filename, PATHINFO_FILENAME));
        $img = time() . "_" . $clean_name . "." . $ext;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $img)) {
            // Delete old file if updating
            if (!empty($_POST['old_img']) && file_exists($upload_dir . $_POST['old_img'])) {
                unlink($upload_dir . $_POST['old_img']);
            }
        }
    }
    
    if ($id) {
        // UPDATE record
        $sql = "UPDATE new_arrivals1 SET title=?, subtitle=?, target_link=?, status=?, image_path=? WHERE id=?";
        $pdo->prepare($sql)->execute([$title, $subtitle, $link, $status, $img, $id]);
    } else {
        // INSERT new record
        $sql = "INSERT INTO new_arrivals1 (title, subtitle, target_link, status, image_path) VALUES (?,?,?,?,?)";
        $pdo->prepare($sql)->execute([$title, $subtitle, $link, $status, $img]);
    }
    header("Location: admin_newarrivals.php?msg=Saved Successfully");
    exit;
}

// Fetch all arrivals for the list
$list = $pdo->query("SELECT * FROM new_arrivals1 ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin - New Arrivals</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 40px; }
        .admin-box { background: white; padding: 25px; border-radius: 10px; max-width: 600px; margin: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #333; }
        input, select { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn-save { background: #2d2d5b; color: white; border: none; padding: 15px; width: 100%; cursor: pointer; font-weight: bold; border-radius: 5px; }
        table { width: 100%; margin-top: 30px; border-collapse: collapse; background: white; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        .thumb { width: 60px; height: 45px; object-fit: contain; background: #f9f9f9; border: 1px solid #eee; }
        .msg { padding: 10px; background: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="admin-box">
    <h2>Manage New Arrivals</h2>
    <?php if(isset($_GET['msg'])) echo "<div class='msg'>".$_GET['msg']."</div>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
        <input type="hidden" name="old_img" value="<?= $edit['image_path'] ?? '' ?>">
        
        <label>Product Name</label>
        <input type="text" name="title" value="<?= $edit['title'] ?? '' ?>" required>
        
        <label>Price / Subtitle</label>
        <input type="text" name="subtitle" value="<?= $edit['subtitle'] ?? '' ?>">
        
        <label>Link (Optional)</label>
        <input type="text" name="link" value="<?= $edit['target_link'] ?? '' ?>">
        
        <label>Status</label>
        <select name="status">
            <option value="1" <?= (isset($edit['status']) && $edit['status']==1)?'selected':'' ?>>Active</option>
            <option value="0" <?= (isset($edit['status']) && $edit['status']==0)?'selected':'' ?>>Hidden</option>
        </select>
        
        <label>Select Photo</label>
        <input type="file" name="image">
        
        <button type="submit" name="save" class="btn-save">SAVE TO SLIDER</button>
    </form>

    <table>
        <tr><th>Image</th><th>Name</th><th>Action</th></tr>
        <?php foreach($list as $row): ?>
        <tr>
            <td><img src="uploads/new_arrivals/<?= $row['image_path'] ?>" class="thumb"></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td>
                <a href="?edit=<?= $row['id'] ?>">Edit</a> | 
                <a href="?del=<?= $row['id'] ?>" style="color:red" onclick="return confirm('Delete this?')">Del</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>