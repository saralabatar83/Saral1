<?php
// 1. DATABASE CONNECTION
require_once 'db.php'; 

$msg = "";
$edit_mode = false;
$cat_to_edit = ['id' => '', 'name' => '', 'bg_color' => '#d1e8ff', 'image_path' => '', 'target_link' => ''];

// --- 2. HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $stmt = $pdo->prepare("SELECT image_path FROM category1 WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    
    if ($img && file_exists("uploads/" . $img)) {
        unlink("uploads/" . $img);
    }

    $pdo->prepare("DELETE FROM category1 WHERE id = ?")->execute([$id]);
    header("Location: admin_categories.php?msg=deleted");
    exit();
}

// --- 3. HANDLE EDIT FETCH ---
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM category1 WHERE id = ?");
    $stmt->execute([$edit_id]);
    $cat_to_edit = $stmt->fetch();
    if ($cat_to_edit) {
        $edit_mode = true;
    }
}

// --- 4. HANDLE ADD OR UPDATE ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_category'])) {
    $id = $_POST['cat_id'];
    $name = $_POST['name'];
    $bg_color = $_POST['bg_color'];
    $target_link = $_POST['target_link']; 
    $existing_image = $_POST['existing_image'];

    if (!empty($_FILES['image']['name'])) {
        $imageName = time() . "_" . $_FILES['image']['name'];
        if (move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $imageName)) {
            if ($existing_image && file_exists("uploads/" . $existing_image)) {
                unlink("uploads/" . $existing_image);
            }
        }
    } else {
        $imageName = $existing_image; 
    }

    if ($id) {
        $sql = "UPDATE category1 SET name=?, bg_color=?, image_path=?, target_link=? WHERE id=?";
        $pdo->prepare($sql)->execute([$name, $bg_color, $imageName, $target_link, $id]);
    } else {
        $sql = "INSERT INTO category1 (name, bg_color, image_path, target_link) VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$name, $bg_color, $imageName, $target_link]);
    }
    
    header("Location: admin_categories.php?msg=success");
    exit();
}

$categories = $pdo->query("SELECT * FROM category1 ORDER BY id DESC")->fetchAll();
?>
<!-- Keep your existing HTML/CSS here, it will work with the PHP variables above -->