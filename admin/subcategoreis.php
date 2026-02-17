<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #f0f2f5; }
        .sidebar { width: 250px; background: #1a73e8; color: white; height: 100vh; padding: 20px; position: fixed; }
        .main-content { margin-left: 280px; padding: 40px; width: 100%; }
        .sidebar h2 { border-bottom: 1px solid #ffffff55; padding-bottom: 10px; }
        .nav-btn { display: block; color: white; text-decoration: none; padding: 12px; margin: 10px 0; border-radius: 5px; transition: 0.3s; }
        .nav-btn:hover { background: #1557b0; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); display: inline-block; min-width: 200px; margin-right: 20px; }
        .stat-card h3 { margin: 0; color: #1a73e8; font-size: 24px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php" class="nav-btn">📊 Dashboard</a>
    <a href="add_product.php" class="nav-btn">➕ Add Product</a>
    <a href="add_category.php" class="nav-btn">📁 Add Category</a>
    <a href="categories.php" class="nav-btn" target="_blank">🌐 View Store</a>
</div>

<div class="main-content">
    <h1>Welcome, Admin</h1>
    <div class="stats">
        <div class="stat-card">
            <p>Total Products</p>
            <h3><?php echo $conn->query("SELECT id FROM products")->num_rows; ?></h3>
        </div>
        <div class="stat-card">
            <p>Total Categories</p>
            <h3><?php echo $conn->query("SELECT id FROM categories")->num_rows; ?></h3>
        </div>
    </div>

    <h2 style="margin-top:40px;">Recent Products</h2>
    <table border="1" cellpadding="10" style="width:100%; border-collapse: collapse; background: white; border: none; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
        <tr style="background: #1a73e8; color: white;">
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Category</th>
        </tr>
        <?php
        $latest = $conn->query("SELECT p.*, c.cat_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT 5");
        while($row = $latest->fetch_assoc()) {
            echo "<tr><td>{$row['id']}</td><td>{$row['product_name']}</td><td>Rs. ".number_format($row['price'])."</td><td>{$row['cat_name']}</td></tr>";
        }
        ?>
    </table>
</div>

</body>
</html>