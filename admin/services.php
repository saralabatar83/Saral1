<?php 
include 'db.php'; 

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$id]);
    header("Location: admin.php");
}

// Handle Add/Update
if (isset($_POST['save_service'])) {
    $name = $_POST['service_name'];
    $desc = $_POST['description'];
    $img = $_POST['image_url'];
    $color = $_POST['accent_color'];
    $id = $_POST['id'];

    if ($id) { // Update
        $sql = "UPDATE services SET service_name=?, description=?, image_url=?, accent_color=? WHERE id=?";
        $pdo->prepare($sql)->execute([$name, $desc, $img, $color, $id]);
    } else { // Insert
        $sql = "INSERT INTO services (service_name, description, image_url, accent_color) VALUES (?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([$name, $desc, $img, $color]);
    }
    header("Location: admin.php");
}

// Fetch single service for editing
$edit_data = ['id'=>'', 'service_name'=>'', 'description'=>'', 'image_url'=>'', 'accent_color'=>'#7c83e8'];
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Service Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">
    <h2>Manage Services</h2>
    
    <!-- Form -->
    <form method="POST" class="card p-3 mb-5 shadow-sm">
        <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
        <div class="row g-3">
            <div class="col-md-3"><input type="text" name="service_name" class="form-control" placeholder="Service Name" value="<?= $edit_data['service_name'] ?>" required></div>
            <div class="col-md-3"><input type="text" name="image_url" class="form-control" placeholder="Image URL" value="<?= $edit_data['image_url'] ?>"></div>
            <div class="col-md-2"><input type="color" name="accent_color" class="form-control" value="<?= $edit_data['accent_color'] ?>"></div>
            <div class="col-md-4"><input type="text" name="description" class="form-control" placeholder="Description" value="<?= $edit_data['description'] ?>"></div>
            <div class="col-12"><button name="save_service" class="btn btn-primary">Save Service</button></div>
        </div>
    </form>

    <!-- Table -->
    <table class="table table-bordered">
        <thead>
            <tr><th>Name</th><th>Description</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("SELECT * FROM services");
            while ($row = $stmt->fetch()) {
                echo "<tr>
                    <td>{$row['service_name']}</td>
                    <td>{$row['description']}</td>
                    <td>
                        <a href='?edit={$row['id']}' class='btn btn-sm btn-warning'>Edit</a>
                        <a href='?delete={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Delete?\")'>Delete</a>
                    </td>
                </tr>";
            }
            ?>
        </tbody>
    </table>
    <p><a href="index.php">View Frontend</a></p>
</body>
</html>