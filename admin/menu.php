<?php
// --- ERROR REPORTING ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- CONNECT TO DATABASE ---
// FIX: Changed from db.php to db_conn.php to match your file structure
if (file_exists('../db.php')) {
    include '../db.php';
} else {
    die("Error: Could not find db_conn.php in the main folder.");
}

// --- 1. HANDLE SECTION DELETION ---
if (isset($_GET['del_section'])) {
    $id = $_GET['del_section'];
    $conn->query("DELETE FROM category_sections WHERE id=$id");
    $conn->query("DELETE FROM category_items WHERE section_id=$id");
    header("Location: menu.php"); 
    exit();
}

// --- 2. HANDLE ITEM DELETION ---
if (isset($_GET['del_item'])) {
    $id = $_GET['del_item'];
    $conn->query("DELETE FROM category_items WHERE id=$id");
    header("Location: menu.php"); 
    exit();
}

// --- 3. HANDLE ADDING NEW SECTION ---
if (isset($_POST['add_section'])) {
    $name = $_POST['section_name'];
    $conn->query("INSERT INTO category_sections (section_name) VALUES ('$name')");
    header("Location: menu.php"); 
    exit();
}

// --- 4. HANDLE ADDING NEW ITEM ---
if (isset($_POST['add_item'])) {
    $sec_id = $_POST['section_id'];
    $name = $_POST['item_name'];
    $link = $_POST['item_link'];
    $conn->query("INSERT INTO category_items (section_id, item_name, item_link) VALUES ('$sec_id', '$name', '$link')");
    header("Location: menu.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Mega Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container bg-white p-4 shadow">
    <div class="d-flex justify-content-between">
        <h2>Manage Mega Menu Content</h2>
        <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
    <hr>

    <div class="row">
        <!-- LEFT: Add Section -->
        <div class="col-md-4 border-end">
            <h4 class="text-primary">1. Add Section</h4>
            <form method="POST" class="d-flex gap-2 mb-3">
                <input type="text" name="section_name" class="form-control" placeholder="Ex: Components" required>
                <button type="submit" name="add_section" class="btn btn-success">Add</button>
            </form>

            <table class="table table-bordered">
                <tr class="table-dark"><th>Name</th><th>Action</th></tr>
                <?php
                $sec = $conn->query("SELECT * FROM category_sections");
                while($row = $sec->fetch_assoc()){
                    echo "<tr>";
                    echo "<td>".$row['section_name']."</td>";
                    echo "<td><a href='?del_section=".$row['id']."' class='btn btn-danger btn-sm'>Delete</a></td>";
                    echo "</tr>";
                }
                ?>
            </table>
        </div>

        <!-- RIGHT: Add Items -->
        <div class="col-md-8">
            <h4 class="text-success">2. Add Item to Section</h4>
            <form method="POST" class="row g-2 mb-3 bg-light p-3 border">
                <div class="col-md-4">
                    <select name="section_id" class="form-control" required>
                        <option value="">Select Section...</option>
                        <?php
                        $sec = $conn->query("SELECT * FROM category_sections");
                        while($r = $sec->fetch_assoc()){
                            echo "<option value='".$r['id']."'>".$r['section_name']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="item_name" class="form-control" placeholder="Ex: Graphic Cards" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="item_link" class="form-control" value="#" placeholder="Link">
                </div>
                <div class="col-md-1">
                    <button type="submit" name="add_item" class="btn btn-success">Add</button>
                </div>
            </form>

            <h5>Existing Items</h5>
            <table class="table table-striped">
                <thead><tr><th>Section</th><th>Item Name</th><th>Action</th></tr></thead>
                <tbody>
                <?php
                $sql = "SELECT category_items.*, category_sections.section_name 
                        FROM category_items 
                        JOIN category_sections ON category_items.section_id = category_sections.id
                        ORDER BY section_id ASC";
                $items = $conn->query($sql);
                while($row = $items->fetch_assoc()){
                    echo "<tr>";
                    echo "<td><span class='badge bg-secondary'>".$row['section_name']."</span></td>";
                    echo "<td>".$row['item_name']."</td>";
                    echo "<td><a href='?del_item=".$row['id']."' class='text-danger'>Delete</a></td>";
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>