<?php
require_once 'db.php'; 

$msg = "";
$edit_mode = false;
$row = ['id'=>'','name'=>'','title'=>'','sub_description'=>'','long_description'=>'','image_path'=>'','target_link'=>'','meta_title'=>'','meta_desc'=>'','meta_keywords'=>''];

// DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT image_path FROM thermal_printers WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists("uploads/" . $img)) { unlink("uploads/" . $img); }
    $pdo->prepare("DELETE FROM thermal_printers WHERE id = ?")->execute([$id]);
    header("Location: admin_thermal.php?msg=deleted"); exit();
}

// EDIT FETCH
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM thermal_printers WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($res) { $row = $res; $edit_mode = true; }
}

// SAVE ACTION
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_printer'])) {
    $id = $_POST['id'];
    $img = $_POST['existing_image'];

    if (!empty($_FILES['image']['name'])) {
        $img = time() . "_thermal_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $img);
        if ($_POST['existing_image'] && file_exists("uploads/" . $_POST['existing_image'])) { unlink("uploads/" . $_POST['existing_image']); }
    }

    $data = [$_POST['name'], $_POST['title'], $_POST['sub_desc'], $_POST['long_desc'], $img, $_POST['link'], $_POST['m_title'], $_POST['m_desc'], $_POST['m_key']];

    if ($id) {
        $sql = "UPDATE thermal_printers SET name=?, title=?, sub_description=?, long_description=?, image_path=?, target_link=?, meta_title=?, meta_desc=?, meta_keywords=? WHERE id=?";
        $data[] = $id;
    } else {
        $sql = "INSERT INTO thermal_printers (name, title, sub_description, long_description, image_path, target_link, meta_title, meta_desc, meta_keywords) VALUES (?,?,?,?,?,?,?,?,?)";
    }
    $pdo->prepare($sql)->execute($data);
    header("Location: admin_thermal.php?msg=success"); exit();
}
$printers = $pdo->query("SELECT * FROM thermal_printers ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Thermal Printers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f4f9; padding: 30px 0; font-family: 'Segoe UI', sans-serif; }
        .admin-card { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; }
        .form-area { padding: 40px; border-right: 1px solid #eee; }
        .list-area { padding: 40px; background: #fafbfc; }
        label { font-weight: 600; font-size: 13px; color: #475569; margin-top: 15px; display: block; text-transform: uppercase; }
        .img-preview { width: 60px; height: 60px; object-fit: contain; border: 1px solid #eee; background: #fff; border-radius: 8px; }
        .nav-links a { text-decoration: none; color: #64748b; font-weight: 600; margin-right: 20px; font-size: 14px; }
    </style>
</head>
<body>
<div class="container-fluid px-5">
    <div class="mb-4 d-flex justify-content-between">
        <div class="nav-links">
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="admin_laser.php">Laser</a>
            <a href="admin_inkjet.php">Inkjet</a>
            <a href="admin_thermal.php" class="text-primary border-bottom border-primary">Thermal</a>
        </div>
        <a href="../thermal_list.php" target="_blank" class="btn btn-sm btn-outline-primary">View Website ↗</a>
    </div>

    <div class="admin-card row g-0">
        <div class="col-lg-5 form-area">
            <h4 class="fw-bold mb-4"><?= $edit_mode ? "📝 Edit Printer" : "➕ Add Thermal Printer" ?></h4>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <input type="hidden" name="existing_image" value="<?= $row['image_path'] ?>">

                <div class="row">
                    <div class="col-6"><label>Brand</label><input type="text" name="name" class="form-control" value="<?= $row['name'] ?>" placeholder="e.g. Xprinter" required></div>
                    <div class="col-6"><label>Model</label><input type="text" name="title" class="form-control" value="<?= $row['title'] ?>" placeholder="e.g. XP-420B" required></div>
                </div>

                <label>Summary (Sub-desc)</label>
                <input type="text" name="sub_desc" class="form-control" value="<?= $row['sub_description'] ?>" placeholder="Fast 152mm/s Receipt Printer">

                <label>Technical Details (Long-desc)</label>
                <textarea name="long_desc" class="form-control" rows="4" placeholder="Print Resolution: 203 DPI... Interface: USB/Bluetooth..."><?= $row['long_description'] ?></textarea>

                <label>Target Link (URL)</label>
                <input type="text" name="link" class="form-control" value="<?= $row['target_link'] ?>" placeholder="thermal_details.php?id=X">

                <div class="mt-4 p-3 bg-light rounded-3 border">
                    <h6 class="fw-bold">SEO Optimization</h6>
                    <input type="text" name="m_title" class="form-control mb-2" placeholder="Meta Title" value="<?= $row['meta_title'] ?>">
                    <textarea name="m_desc" class="form-control mb-2" rows="2" placeholder="Meta Description"><?= $row['meta_desc'] ?></textarea>
                    <input type="text" name="m_key" class="form-control" placeholder="Keywords (e.g. Receipt, Barcode)" value="<?= $row['meta_keywords'] ?>">
                </div>

                <label class="mt-3">Photo</label>
                <input type="file" name="image" class="form-control">

                <button type="submit" name="save_printer" class="btn btn-primary w-100 py-3 mt-4 fw-bold shadow">SAVE THERMAL PRINTER</button>
            </form>
        </div>

        <div class="col-lg-7 list-area">
            <h4 class="fw-bold mb-4">Inventory</h4>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Preview</th><th>Details</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                        <?php foreach($printers as $p): ?>
                        <tr>
                            <td><img src="uploads/<?= $p['image_path'] ?>" class="img-preview" onerror="this.src='https://via.placeholder.com/100?text=No+Img'"></td>
                            <td><strong><?= $p['name'] ?></strong><br><small><?= $p['title'] ?></small></td>
                            <td class="text-end">
                                <a href="?edit=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">X</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>