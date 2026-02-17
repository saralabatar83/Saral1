<?php 
// 1. Include the unified connection (Goes up one folder to find db.php)
include '../db.php'; 

// 2. Enable error reporting to find bugs immediately
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 3. LOGIC: ADD PRODUCT (This part was missing from your code)
if (isset($_POST['add_product'])) {
    $brand = $_POST['brand'];
    $desc  = $_POST['desc'];
    $price = $_POST['price'];
    
    // Create uploads folder if it doesn't exist
    if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }

    // File Upload Handling
    $filename = time() . "_" . $_FILES['image']['name'];
    $target = "uploads/" . $filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        // Insert into database using $pdo
        $stmt = $pdo->prepare("INSERT INTO products (brand, description, price, image_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$brand, $desc, $price, $filename]);
        
        echo "<script>alert('Product Added Successfully!'); window.location='Laptop.php';</script>";
        exit();
    } else {
        echo "<script>alert('Failed to upload image. Check folder permissions.');</script>";
    }
}

// 4. LOGIC: DELETE PRODUCT
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Find the image name first to delete it from the folder
    $stmt = $pdo->prepare("SELECT image_path FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    
    if ($img && file_exists("uploads/".$img)) {
        unlink("uploads/".$img); // Deletes the physical file
    }

    // Delete record from database
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    header("Location: Laptop.php?deleted=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nagmani Admin - Manage Laptops</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; padding: 30px; background: #f4f7f6; color: #333; }
        .container { max-width: 900px; margin: auto; }
        .box { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 30px; }
        h2, h3 { color: #222; margin-top: 0; }
        input, textarea { width: 100%; margin-bottom: 15px; padding: 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { background: #fbd604; color: #000; font-weight: bold; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%; transition: 0.3s; }
        button:hover { background: #eac503; }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        th, td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #333; color: white; }
        .product-img { width: 60px; height: 50px; object-fit: contain; background: #f9f9f9; border-radius: 4px; }
        .btn-delete { color: #d00; text-decoration: none; font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Add New Laptop</h2>
    <div class="box">
        <form action="Laptop.php" method="POST" enctype="multipart/form-data">
            <input type="text" name="brand" placeholder="Laptop Brand (e.g. ASUS Vivobook)" required>
            <textarea name="desc" placeholder="Specifications / Description" rows="3" required></textarea>
            <input type="number" name="price" placeholder="Price (Numbers only)" required>
            <label style="font-size:12px; color:#666;">Select Photo:</label>
            <input type="file" name="image" accept="image/*" required>
            <button type="submit" name="add_product">Upload Product to Store</button>
        </form>
    </div>

    <h3>Inventory Overview</h3>
    <table>
        <thead>
            <tr>
                <th>Photo</th>
                <th>Brand & Description</th>
                <th>Price</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch products using $pdo (Consistent with your db.php)
            $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)):
            ?>
            <tr>
                <td><img src="uploads/<?php echo $row['image_path']; ?>" class="product-img" alt="Laptop"></td>
                <td>
                    <strong><?php echo htmlspecialchars($row['brand']); ?></strong><br>
                    <small style="color:#777;"><?php echo htmlspecialchars($row['description']); ?></small>
                </td>
                <td>Rs. <?php echo number_format($row['price']); ?></td>
                <td>
                    <a href="?delete=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Remove this product?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>