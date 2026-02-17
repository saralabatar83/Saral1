<?php
// 1. Setup paths and include files
require_once '../config/db.php';
require_once '../includes/functions.php';

// 2. Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['logo'])) {
    $name = $_POST['name'];
    $link = $_POST['link'];
    $logo_name = $_FILES['logo']['name'];
    $target_dir = "../uploads/brands/";

    // Check if the folder exists, if not, create it
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $target_file = $target_dir . basename($logo_name);

    // Try to upload the file
    if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
        // Only save to DB if file move was successful
        $stmt = $pdo->prepare("INSERT INTO brands (name, logo_url, repair_service_link) VALUES (?, ?, ?)");
        $stmt->execute([$name, $logo_name, $link]);
        echo "<div style='color: green;'>Brand and Image added successfully!</div>";
    } else {
        echo "<div style='color: red;'>Error: Could not upload image. Check folder permissions.</div>";
    }
}

// 3. Fetch Data to display list below
$brands = getBrands($pdo); 
?>

<!-- Your HTML Form stays below -->
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Brand Name" required><br><br>
    <input type="text" name="link" placeholder="Repair Link (e.g. repairs.php?brand=xxx)" required><br><br>
    <input type="file" name="logo" required><br><br>
    <button type="submit">Add New Brand</button>
</form>

<hr>

<!-- Optional: Display current brands to confirm they are appearing -->
<h3>Current Brands</h3>
<div style="display: flex; gap: 10px;">
    <?php foreach($brands as $b): ?>
        <div style="border: 1px solid #ccc; padding: 10px; text-align: center;">
            <img src="../uploads/brands/<?= $b['logo_url'] ?>" width="50"><br>
            <?= $b['name'] ?>
        </div>
    <?php endforeach; ?>
</div>