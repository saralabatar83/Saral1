<?php
require_once 'db.php'; 

// EXACT PATH from your screenshot
$upload_dir = "uploads/features/"; 

// Handle Save
if (isset($_POST['save'])) {
    $img = $_POST['old_img'] ?? '';
    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        // Clean filename (Removes spaces)
        $clean_name = preg_replace("/[^a-zA-Z0-9]/", "_", pathinfo($_FILES['image']['name'], PATHINFO_FILENAME));
        $img = time() . "_" . $clean_name . "." . $ext;
        
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $img);
    }
    
    $data = [$_POST['title'], $_POST['subtitle'], $img];
    if (!empty($_POST['id'])) {
        $pdo->prepare("UPDATE features1 SET title=?, subtitle=?, image_path=? WHERE id=?")->execute(array_merge($data, [$_POST['id']]));
    } else {
        $pdo->prepare("INSERT INTO features1 (title, subtitle, image_path) VALUES (?,?,?)")->execute($data);
    }
    header("Location: admin_features.php"); exit;
}
?>