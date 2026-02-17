<?php
// admin/admin_newrelease.php
require_once '../db.php';

$msg = "";
$msg_type = "info";

// --- 1. SECURE FILE UPLOAD FUNCTION ---
function uploadPhoto($file, $old_image = "") {
    if (empty($file['name'])) return $old_image;

    $target_dir = "../uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    // Validate if it is an actual image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) return $old_image;

    // Secure naming: time + sanitized filename
    $filename = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "", basename($file["name"]));
    $target_file = $target_dir . $filename;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // Delete old file to save space
        if (!empty($old_image) && file_exists($target_dir . $old_image)) {
            @unlink($target_dir . $old_image);
        }
        return $filename;
    }
    return $old_image;
}

// --- 2. CRUD LOGIC ---

// Delete Item
if (isset($_GET['del_item'])) {
    $stmt = $pdo->prepare("DELETE FROM newrelease_items WHERE id = ?");
    $stmt->execute([$_GET['del_item']]);
    header("Location: admin_newrelease.php?msg=Deleted&type=danger"); exit();
}

// Save/Update Item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_item'])) {
    $id = $_POST['item_id'] ?: null;
    $img_path = uploadPhoto($_FILES['image'], $_POST['old_image']);

    $sql = $id 
        ? "UPDATE newrelease_items SET brand_id=?, sub_id=?, title=?, short_description=?, image_path=?, target_link=? WHERE id=?"
        : "INSERT INTO newrelease_items (brand_id, sub_id, title, short_description, image_path, target_link) VALUES (?,?,?,?,?,?)";

    $params = [$_POST['brand_id'], $_POST['sub_id'], $_POST['title'], $_POST['short_description'], $img_path, $_POST['target_link']];
    if ($id) $params[] = $id;

    $pdo->prepare($sql)->execute($params);
    header("Location: admin_newrelease.php?msg=Saved&type=success"); exit();
}

// Add Brand/Subcategory
if (isset($_POST['add_brand'])) { $pdo->prepare("INSERT INTO newrelease_brands (brand_name) VALUES (?)")->execute([$_POST['brand_name']]); header("Location: admin_newrelease.php"); exit(); }
if (isset($_POST['add_sub'])) { $pdo->prepare("INSERT INTO newrelease_subcategories (sub_name) VALUES (?)")->execute([$_POST['sub_name']]); header("Location: admin_newrelease.php"); exit(); }

// --- 3. FETCH DATA ---
$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM newrelease_items WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch();
}

$brands = $pdo->query("SELECT * FROM newrelease_brands ORDER BY brand_name ASC")->fetchAll();
$subs = $pdo->query("SELECT * FROM newrelease_subcategories ORDER BY sub_name ASC")->fetchAll();
$items = $pdo->query("SELECT n.*, b.brand_name, s.sub_name FROM newrelease_items n 
                      LEFT JOIN newrelease_brands b ON n.brand_id = b.id 
                      LEFT JOIN newrelease_subcategories s ON n.sub_id = s.id 
                      ORDER BY n.id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Proper Admin Panel | New Release</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #0f172a; color: #f8fafc; font-family: 'Inter', sans-serif; }
        .card-custom { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; }
        .form-control, .form-select { background: #0f172a; border: 1px solid #334155; color: white; }
        .form-control:focus { background: #0f172a; color: white; border-color: #3b82f6; }
        .img-table { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; }
    </style>
</head>
<body class="p-4">

<div class="container-fluid">
    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-<?= $_GET['type'] ?> alert-dismissible fade show"><?= $_GET['msg'] ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Brand & Sub Manager -->
        <div class="col-md-3">
            <div class="card-custom mb-3">
                <h6 class="text-primary fw-bold mb-3">Manage Brands</h6>
                <form method="POST" class="d-flex gap-2">
                    <input type="text" name="brand_name" class="form-control form-control-sm" required>
                    <button name="add_brand" class="btn btn-sm btn-primary">Add</button>
                </form>
                <div class="mt-3 d-flex flex-wrap gap-1">
                    <?php foreach($brands as $b): ?>
                        <span class="badge bg-secondary"><?= $b['brand_name'] ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card-custom">
                <h6 class="text-info fw-bold mb-3">Subcategories</h6>
                <form method="POST" class="d-flex gap-2">
                    <input type="text" name="sub_name" class="form-control form-control-sm" required>
                    <button name="add_sub" class="btn btn-sm btn-info text-white">Add</button>
                </form>
                <div class="mt-3 d-flex flex-wrap gap-1">
                    <?php foreach($subs as $s): ?>
                        <span class="badge bg-dark border border-info"><?= $s['sub_name'] ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Main Form -->
        <div class="col-md-4">
            <div class="card-custom">
                <h5 class="mb-4"><?= $edit_data ? 'Edit Product' : 'Create New Release' ?></h5>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="item_id" value="<?= $edit_data['id'] ?? '' ?>">
                    <input type="hidden" name="old_image" value="<?= $edit_data['image_path'] ?? '' ?>">
                    
                    <div class="row g-2">
                        <div class="col-6 mb-2">
                            <label class="small text-secondary">Brand</label>
                            <select name="brand_id" class="form-select" required>
                                <?php foreach($brands as $b): ?>
                                    <option value="<?= $b['id'] ?>" <?= @$edit_data['brand_id']==$b['id']?'selected':'' ?>><?= $b['brand_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 mb-2">
                            <label class="small text-secondary">Subcategory</label>
                            <select name="sub_id" class="form-select" required>
                                <?php foreach($subs as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= @$edit_data['sub_id']==$s['id']?'selected':'' ?>><?= $s['sub_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="small text-secondary">Title</label>
                        <input type="text" name="title" class="form-control" value="<?= $edit_data['title'] ?? '' ?>" required>
                    </div>

                    <div class="mb-2">
                        <label class="small text-secondary">Description</label>
                        <textarea name="short_description" class="form-control" rows="3"><?= $edit_data['short_description'] ?? '' ?></textarea>
                    </div>

                    <div class="mb-2">
                        <label class="small text-secondary">Upload Photo</label>
                        <input type="file" name="image" class="form-control">
                        <?php if(!empty($edit_data['image_path'])): ?>
                            <small class="text-success mt-1 d-block">Existing: <?= $edit_data['image_path'] ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="small text-secondary">WhatsApp Text/Link</label>
                        <input type="text" name="target_link" class="form-control" placeholder="e.g. Inquiry for Laptop" value="<?= $edit_data['target_link'] ?? '' ?>">
                    </div>

                    <button name="save_item" class="btn btn-primary w-100 fw-bold">PUBLISH RELEASE</button>
                    <?php if($edit_data): ?>
                        <a href="admin_newrelease.php" class="btn btn-link w-100 text-secondary text-decoration-none small mt-2">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Inventory Table -->
        <div class="col-md-5">
            <div class="card-custom">
                <h5 class="mb-4">Live Inventory</h5>
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead class="text-secondary small">
                            <tr><th>Preview</th><th>Product Details</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($items as $i): ?>
                            <tr>
                                <td>
                                    <img src="../uploads/<?= $i['image_path'] ?>" class="img-table" onerror="this.src='https://via.placeholder.com/50'">
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($i['title']) ?></div>
                                    <small class="text-muted"><?= $i['brand_name'] ?> | <?= $i['sub_name'] ?></small>
                                </td>
                                <td class="text-nowrap">
                                    <a href="?edit=<?= $i['id'] ?>" class="btn btn-sm btn-outline-info"><i class="fa fa-edit"></i></a>
                                    <a href="?del_item=<?= $i['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete permanently?')"><i class="fa fa-trash"></i></a>
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