<?php
require_once 'db.php';

// Using a variable for the filename so redirects never break
$current_file = basename(__FILE__);

// --- 1. HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM components WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: $current_file?msg=deleted");
    exit();
}

// --- 2. HANDLE ADD ---
if (isset($_POST['add_comp'])) {
    $stmt = $pdo->prepare("INSERT INTO components (category, component_name) VALUES (?, ?)");
    $stmt->execute([$_POST['category'], $_POST['component_name']]);
    header("Location: $current_file?msg=added");
    exit();
}

// --- 3. HANDLE UPDATE (EDIT) ---
if (isset($_POST['update_comp'])) {
    $stmt = $pdo->prepare("UPDATE components SET category = ?, component_name = ? WHERE id = ?");
    $stmt->execute([$_POST['category'], $_POST['component_name'], $_POST['id']]);
    header("Location: $current_file?msg=updated");
    exit();
}

// --- 4. FETCH DATA FOR EDIT MODE ---
$edit_mode = false;
$edit_data = ['category' => '', 'component_name' => '', 'id' => ''];
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM components WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $res = $stmt->fetch();
    if ($res) {
        $edit_mode = true;
        $edit_data = $res;
    }
}

// --- 5. FETCH ALL FOR LIST ---
$components = $pdo->query("SELECT * FROM components ORDER BY category ASC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Components</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f4f4; padding: 30px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 900px; margin: auto; }
        .form-section { background: #f9f9f9; padding: 20px; border-radius: 6px; margin-bottom: 30px; border-left: 5px solid #187bcd; }
        .edit-border { border-left-color: #ffc107; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        .btn { padding: 8px 15px; border-radius: 4px; text-decoration: none; color: white; border: none; cursor: pointer; display: inline-block; font-size: 14px; }
        .btn-blue { background: #187bcd; }
        .btn-yellow { background: #ffc107; color: #000; }
        .btn-red { background: #dc3545; }
        input { padding: 10px; width: 250px; margin-right: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .msg { padding: 10px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="card">
    <h2>Component Manager</h2>
    <?php if(isset($_GET['msg'])): ?>
        <div class="msg">Success: Item <?= $_GET['msg'] ?>!</div>
    <?php endif; ?>

    <div class="form-section <?= $edit_mode ? 'edit-border' : '' ?>">
        <h3><?= $edit_mode ? 'Edit Component' : 'Add New Component' ?></h3>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
            <input type="text" name="category" placeholder="Category" value="<?= htmlspecialchars($edit_data['category']) ?>" required>
            <input type="text" name="component_name" placeholder="Name" value="<?= htmlspecialchars($edit_data['component_name']) ?>" required>
            <?php if ($edit_mode): ?>
                <button type="submit" name="update_comp" class="btn btn-yellow">Update</button>
                <a href="<?= $current_file ?>" class="btn" style="background:#666">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_comp" class="btn btn-blue">Add Component</button>
            <?php endif; ?>
        </form>
    </div>

    <table>
        <thead><tr><th>Category</th><th>Name</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach($components as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['category']) ?></td>
                <td><?= htmlspecialchars($c['component_name']) ?></td>
                <td>
                    <a href="<?= $current_file ?>?edit=<?= $c['id'] ?>" class="btn btn-yellow">Edit</a>
                    <a href="<?= $current_file ?>?delete=<?= $c['id'] ?>" class="btn btn-red" onclick="return confirm('Delete?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <br><a href="../index.php">View Live Website</a>
</div>
</body>
</html>
