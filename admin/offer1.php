<?php
include '../db.php'; 
$edit_mode = false;
$data = ['id' => '', 'name' => '', 'tag' => '', 'image_path' => ''];

// 1. DELETE
if (isset($_GET['del'])) {
    $stmt = $pdo->prepare("SELECT image_path FROM offers1 WHERE id = ?");
    $stmt->execute([$_GET['del']]);
    $img = $stmt->fetch();
    if ($img && !empty($img['image_path'])) { 
        @unlink("uploads/" . $img['image_path']); 
    }
    $pdo->prepare("DELETE FROM offers1 WHERE id = ?")->execute([$_GET['del']]);
    header("Location: offer1.php"); // Fixed filename
    exit();
}

// 2. FETCH FOR EDIT
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $stmt = $pdo->prepare("SELECT * FROM offers1 WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $data = $stmt->fetch();
    if (!$data) { header("Location: offer1.php"); exit(); }
}

// 3. SAVE (ADD/UPDATE)
if (isset($_POST['save_offer'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $tag = $_POST['tag'];
    $img = $_POST['old_img'];

    // Handle Image Upload
    if (!empty($_FILES['image']['name'])) {
        $img_name = time() . "_" . $_FILES['image']['name'];
        if (move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $img_name)) {
            // Delete old image if updating
            if (!empty($_POST['old_img'])) { @unlink("uploads/" . $_POST['old_img']); }
            $img = $img_name;
        }
    }

    if (!empty($id)) {
        // UPDATE
        $sql = "UPDATE offers1 SET name=?, tag=?, image_path=? WHERE id=?";
        $pdo->prepare($sql)->execute([$name, $tag, $img, $id]);
    } else {
        // INSERT
        $sql = "INSERT INTO offers1 (name, tag, image_path) VALUES (?, ?, ?)";
        $pdo->prepare($sql)->execute([$name, $tag, $img]);
    }
    header("Location: offer1.php"); // Fixed filename
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Daily Offers 1</title>
    <style>
        :root { --primary: #27ae60; --dark: #2c3e50; --bg: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); padding: 40px 20px; color: var(--dark); }
        .container { max-width: 800px; margin: auto; }
        
        /* Box Styling */
        .box { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); margin-bottom: 30px; }
        h2 { margin-top: 0; border-bottom: 2px solid #eee; padding-bottom: 10px; font-size: 22px; }

        /* Form Styling */
        label { display: block; margin-top: 15px; font-weight: 600; font-size: 14px; }
        input[type="text"], input[type="file"] { 
            width: 100%; padding: 12px; margin-top: 5px; border: 1px solid #ddd; 
            border-radius: 6px; box-sizing: border-box; 
        }
        .img-preview { margin-top: 10px; border: 1px solid #eee; padding: 5px; border-radius: 5px; background: #fafafa; }

        /* Button Styling */
        .btn { 
            background: var(--primary); color: white; border: none; padding: 14px; 
            width: 100%; cursor: pointer; border-radius: 6px; font-weight: bold; 
            font-size: 16px; margin-top: 20px; transition: 0.3s;
        }
        .btn:hover { background: #219150; }
        .btn-cancel { 
            display: block; text-align: center; margin-top: 10px; color: #888; 
            text-decoration: none; font-size: 14px; 
        }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        th { background: var(--dark); color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; }
        .action-links a { text-decoration: none; font-weight: bold; font-size: 13px; margin-right: 10px; }
        .edit-lnk { color: #2980b9; }
        .del-lnk { color: #e74c3c; }
    </style>
</head>
<body>

<div class="container">
    <!-- FORM BOX -->
    <div class="box">
        <h2><?= $edit_mode ? '📝 Edit Product' : '➕ Add New Offer' ?></h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $data['id'] ?>">
            <input type="hidden" name="old_img" value="<?= $data['image_path'] ?>">

            <label>Product Name</label>
            <input type="text" name="name" placeholder="e.g. Gaming Laptop G15" value="<?= htmlspecialchars($data['name'] ?? '') ?>" required>

            <label>Tag / Discount Badge</label>
            <input type="text" name="name" placeholder="e.g. 10% OFF or Limited" name="tag" value="<?= htmlspecialchars($data['tag'] ?? '') ?>">

            <label>Product Image</label>
            <input type="file" name="image" <?= $edit_mode ? '' : 'required' ?>>
            
            <?php if ($edit_mode && $data['image_path']): ?>
                <div class="img-preview">
                    <small>Current Image:</small><br>
                    <img src="uploads/<?= $data['image_path'] ?>" width="100">
                </div>
            <?php endif; ?>

            <button type="submit" name="save_offer" class="btn">
                <?= $edit_mode ? 'UPDATE CHANGES' : 'SAVE TO DATABASE' ?>
            </button>
            
            <?php if($edit_mode): ?>
                <a href="offer1.php" class="btn-cancel">Cancel Editing</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- DATA TABLE -->
    <div class="box">
        <h2>Existing Offers (Section 1)</h2>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Tag</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = $pdo->query("SELECT * FROM offers1 ORDER BY id DESC");
                while($r = $res->fetch()): ?>
                <tr>
                    <td>
                        <?php if($r['image_path']): ?>
                            <img src="uploads/<?= $r['image_path'] ?>" class="thumb">
                        <?php else: ?>
                            <div style="font-size:10px; color:#ccc;">No Image</div>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight:600;"><?= htmlspecialchars($r['name']) ?></td>
                    <td><span style="background:#eee; padding:3px 8px; border-radius:4px; font-size:12px;"><?= htmlspecialchars($r['tag']) ?></span></td>
                    <td class="action-links">
                        <a href="offer1.php?edit=<?= $r['id'] ?>" class="edit-lnk">Edit</a>
                        <a href="offer1.php?del=<?= $r['id'] ?>" class="del-lnk" onclick="return confirm('Are you sure you want to delete this?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>