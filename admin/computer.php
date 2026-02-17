<?php
include 'db.php';

// --- A. ADD NEW BRAND ---
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $image = $_FILES['image']['name'];
    $target = "uploads/" . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $conn->query("INSERT INTO brands (brand_name, brand_image) VALUES ('$name', '$image')");
        header("Location: admin_brands.php");
    }
}

// --- B. DELETE BRAND ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $res = $conn->query("SELECT brand_image FROM brands WHERE id=$id");
    $row = $res->fetch_assoc();
    unlink("uploads/" . $row['brand_image']); // Delete file from folder
    $conn->query("DELETE FROM brands WHERE id=$id"); // Delete from DB
    header("Location: admin_brands.php");
}

// --- C. UPDATE (EDIT) BRAND ---
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $old_image = $_POST['old_image'];
    
    // If user selected a NEW image
    if (!empty($_FILES['new_image']['name'])) {
        $image = $_FILES['new_image']['name'];
        $target = "uploads/" . basename($image);
        move_uploaded_file($_FILES['new_image']['tmp_name'], $target);
        unlink("uploads/" . $old_image); // Remove old file
        $sql = "UPDATE brands SET brand_name='$name', brand_image='$image' WHERE id=$id";
    } else {
        // Just update name, keep old image
        $sql = "UPDATE brands SET brand_name='$name' WHERE id=$id";
    }
    $conn->query($sql);
    header("Location: admin_brands.php");
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">Saral IT - Admin Panel</a>
            <a href="index.php" class="btn btn-outline-light btn-sm" target="_blank">View Website</a>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm text-center p-5">
                    <h3>Manage Mega Menu</h3>
                    <p>Add/Edit Sections (Components) and Items (Graphic Cards)</p>
                    <a href="menu.php" class="btn btn-primary">Go to Mega Menu</a>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm text-center p-5">
                    <h3>Manage Brands</h3>
                    <p>Add/Edit Brand Logos (ASUS, MSI, etc.)</p>
                    <a href="brands.php" class="btn btn-warning">Go to Brands</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
