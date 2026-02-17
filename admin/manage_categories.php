<?php
require_once 'db.php';

// ==========================================
// 1. ADD / DELETE MAIN CATEGORIES
// ==========================================
if (isset($_POST['add_main_cat'])) {
    $name = trim($_POST['cat_name']);
    if (!empty($name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
            header("Location: manage_categories.php");
            exit;
        } catch (PDOException $e) {
            $error = "Error adding category: " . $e->getMessage();
        }
    }
}

if (isset($_GET['del_cat'])) {
    $id = $_GET['del_cat'];
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    header("Location: manage_categories.php");
    exit;
}

// ==========================================
// 2. ADD / DELETE SUB-CATEGORIES
// ==========================================
if (isset($_POST['add_sub_cat'])) {
    $parent_id = $_POST['parent_id'];
    $sub_name = trim($_POST['sub_name']);
    
    if (!empty($parent_id) && !empty($sub_name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO sub_categories (category_id, name) VALUES (?, ?)");
            $stmt->execute([$parent_id, $sub_name]);
            header("Location: manage_categories.php");
            exit;
        } catch (PDOException $e) {
            $error = "Error adding sub-category: " . $e->getMessage();
        }
    }
}

if (isset($_GET['del_sub'])) {
    $id = $_GET['del_sub'];
    $pdo->prepare("DELETE FROM sub_categories WHERE id = ?")->execute([$id]);
    header("Location: manage_categories.php");
    exit;
}

// ==========================================
// 3. FETCH DATA (WITH ERROR HANDLING)
// ==========================================
try {
    // Fetch Main Categories
    $cats = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Sub Categories (This is where your previous error happened)
    // We select c.name (category name) and s.name (sub category name)
    $sql_subs = "SELECT s.id, s.name AS sub_name, c.name AS parent_name 
                 FROM sub_categories s 
                 JOIN categories c ON s.category_id = c.id 
                 ORDER BY s.id DESC";
    $subs = $pdo->query($sql_subs)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("<div style='color:red; padding:20px; border:1px solid red;'>
            <h3>Database Error!</h3>
            <p>The system cannot find the correct columns. Please run the SQL command provided in Step 1.</p>
            <p><strong>Detailed Error:</strong> " . $e->getMessage() . "</p>
         </div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    
    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📂 Category Manager</h2>
        <a href="printer.php" class="btn btn-dark">⬅ Back to Printer Form</a>
    </div>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="row">
        
        <!-- LEFT SIDE: MAIN CATEGORIES -->
        <div class="col-md-5">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">1. Main Categories</h5>
                </div>
                <div class="card-body">
                    <!-- Form -->
                    <form method="POST" class="input-group mb-3">
                        <input type="text" name="cat_name" class="form-control" placeholder="New Category (e.g. HP)" required>
                        <button type="submit" name="add_main_cat" class="btn btn-primary">Add</button>
                    </form>

                    <!-- List -->
                    <ul class="list-group">
                        <?php foreach($cats as $c): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($c['name']) ?>
                                <a href="?del_cat=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete? This will delete all sub-categories too.');">✕</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE: SUB CATEGORIES -->
        <div class="col-md-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">2. Sub Categories</h5>
                </div>
                <div class="card-body">
                    <!-- Form -->
                    <form method="POST" class="row g-2 mb-3 bg-light p-2 border rounded">
                        <div class="col-md-5">
                            <select name="parent_id" class="form-select" required>
                                <option value="">Select Parent...</option>
                                <?php foreach($cats as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="sub_name" class="form-control" placeholder="New Sub (e.g. Inkjet)" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="add_sub_cat" class="btn btn-success w-100">Add</button>
                        </div>
                    </form>

                    <!-- Table -->
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Parent</th>
                                <th>Sub-Category</th>
                                <th style="width:50px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($subs as $s): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($s['parent_name']) ?></span></td>
                                    <td><?= htmlspecialchars($s['sub_name']) ?></td>
                                    <td>
                                        <a href="?del_sub=<?= $s['id'] ?>" class="btn btn-sm btn-danger py-0" onclick="return confirm('Delete this sub-category?');">✕</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>