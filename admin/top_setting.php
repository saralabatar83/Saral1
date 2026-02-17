<?php
ob_start();
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php'; // Must use PDO

// --- AUTO-INSTALLER ---
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `header_settings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `section` VARCHAR(50) NOT NULL, -- 'branding' or 'top_bar'
        `icon_class` VARCHAR(100) DEFAULT NULL,
        `text_label` VARCHAR(255) DEFAULT NULL,
        `link_url` VARCHAR(255) DEFAULT NULL,
        `sort_order` INT DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (Exception $e) { die("DB Error: " . $e->getMessage()); }

$current_page = basename($_SERVER['PHP_SELF']);

// --- ACTION: UPDATE BRANDING (Logo & Title) ---
if (isset($_POST['update_branding'])) {
    $brand_text = $_POST['brand_text'];
    $new_logo = $_POST['old_logo'];

    if (!empty($_FILES['logo_image']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $filename = "logo_" . time() . "_" . basename($_FILES["logo_image"]["name"]);
        if (move_uploaded_file($_FILES["logo_image"]["tmp_name"], $target_dir . $filename)) {
            $new_logo = $target_dir . $filename;
        }
    }

    $check = $pdo->prepare("SELECT id FROM header_settings WHERE section = 'branding'");
    $check->execute();
    if ($check->fetch()) {
        $pdo->prepare("UPDATE header_settings SET text_label=?, link_url=? WHERE section='branding'")->execute([$brand_text, $new_logo]);
    } else {
        $pdo->prepare("INSERT INTO header_settings (section, text_label, link_url) VALUES ('branding', ?, ?)")->execute([$brand_text, $new_logo]);
    }
    header("Location: $current_page?msg=Branding Updated"); exit();
}

// --- ACTION: ADD/UPDATE TOP BAR ITEM ---
if (isset($_POST['save_top_item'])) {
    if(!empty($_POST['id'])) {
        $pdo->prepare("UPDATE header_settings SET icon_class=?, text_label=?, link_url=?, sort_order=? WHERE id=?")
            ->execute([$_POST['icon'], $_POST['text'], $_POST['link'], $_POST['order'], $_POST['id']]);
    } else {
        $pdo->prepare("INSERT INTO header_settings (section, icon_class, text_label, link_url, sort_order) VALUES ('top_bar', ?, ?, ?, ?)")
            ->execute([$_POST['icon'], $_POST['text'], $_POST['link'], $_POST['order']]);
    }
    header("Location: $current_page?msg=Top Bar Saved"); exit();
}

// --- ACTION: DELETE ---
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM header_settings WHERE id=?")->execute([$_GET['delete']]);
    header("Location: $current_page?msg=Deleted"); exit();
}

// Fetch Data
$brand = $pdo->query("SELECT * FROM header_settings WHERE section = 'branding' LIMIT 1")->fetch();
$top_items = $pdo->query("SELECT * FROM header_settings WHERE section = 'top_bar' ORDER BY sort_order ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Top Setting Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root { --purple: #9291e9; --dark: #1e293b; --bg: #f3f4f6; --blue: #3b82f6; --red: #ef4444; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; display: flex; }
        
        .sidebar { width: 250px; background: var(--dark); color: white; height: 100vh; position: fixed; }
        .main { margin-left: 250px; padding: 30px; width: calc(100% - 250px); }

        .panel { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 25px; }
        h2 { font-size: 18px; color: #111827; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }

        /* Purple Bar Style */
        .purple-preview { background: var(--purple); color: white; padding: 12px; border-radius: 6px; display: flex; gap: 20px; font-size: 14px; margin-bottom: 20px; }

        /* Inventory-style List (From your Laptop Image) */
        .item-row { display: flex; align-items: center; padding: 15px 0; border-bottom: 1px solid #f3f4f6; }
        .item-icon-box { width: 50px; height: 50px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: var(--purple); margin-right: 15px; }
        .item-info { flex: 1; }
        .item-title { font-weight: 700; color: #1f2937; margin-bottom: 3px; }
        .item-sub { font-size: 13px; color: #6b7280; }
        .item-actions { display: flex; gap: 15px; }
        .btn-edit { color: var(--blue); cursor: pointer; font-size: 18px; }
        .btn-delete { color: var(--red); cursor: pointer; font-size: 18px; }

        /* Form Styling */
        .grid-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; align-items: flex-end; }
        input, select { padding: 10px; border: 1px solid #ddd; border-radius: 6px; width: 100%; }
        label { font-size: 12px; font-weight: bold; color: #666; display: block; margin-bottom: 5px; }
        .btn-main { background: var(--purple); color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        
        .logo-circle { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd; }
    </style>
</head>
<body>

<div class="sidebar">
    <div style="padding:25px; text-align:center; background:#0f172a"><h3>Admin Panel</h3></div>
    <div style="padding:20px"><a href="#" style="color:white; text-decoration:none"><i class="fas fa-cog"></i> Header Top Setting</a></div>
</div>

<div class="main">
    <h1>Header Configuration</h1>

    <!-- 1. BRANDING PANEL (White Bar with Circular Logo) -->
    <div class="panel">
        <h2><i class="fas fa-id-card"></i> Main Branding (White Bar)</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="old_logo" value="<?= $brand['link_url'] ?? '' ?>">
            <div class="grid-form">
                <div>
                    <label>Circular Logo</label>
                    <img src="<?= !empty($brand['link_url']) ? $brand['link_url'] : '1.jpeg' ?>" class="logo-circle">
                    <input type="file" name="logo_image" style="margin-top: 10px;">
                </div>
                <div>
                    <label>Brand Name</label>
                    <input type="text" name="brand_text" value="<?= htmlspecialchars($brand['text_label'] ?? 'SARAL IT SOLUTION') ?>" style="font-weight: bold;">
                </div>
                <button type="submit" name="update_branding" class="btn-main">Save Branding</button>
            </div>
        </form>
    </div>

    <!-- 2. TOP BAR ADD PANEL (Purple Bar Items) -->
    <div class="panel">
        <h2><i class="fas fa-plus-circle"></i> Add Top Bar Info (Purple Bar)</h2>
        <form method="POST">
            <div class="grid-form">
                <div><label>Icon (fa-phone, fa-envelope)</label><input type="text" name="icon" placeholder="fa-phone" required></div>
                <div><label>Text Label</label><input type="text" name="text" placeholder="info@gmail.com" required></div>
                <div><label>Link (URL)</label><input type="text" name="link" value="#"></div>
                <div><label>Sort Order</label><input type="number" name="order" value="1"></div>
                <button type="submit" name="save_top_item" class="btn-main">Add to Top Bar</button>
            </div>
        </form>
    </div>

    <!-- 3. MANAGEMENT LIST (Matching your Laptop Inventory Style) -->
    <div class="panel">
        <h2>Header Top Bar Inventory</h2>
        
        <!-- Live Preview of Purple Bar -->
        <div class="purple-preview">
            <?php foreach($top_items as $item): ?>
                <span><i class="fa <?= $item['icon_class'] ?>"></i> <?= $item['text_label'] ?></span>
                <?php if(next($top_items)) echo "|"; ?>
            <?php endforeach; ?>
        </div>

        <div class="inventory-list">
            <?php foreach($top_items as $row): ?>
            <div class="item-row">
                <div class="item-icon-box">
                    <i class="fa <?= $row['icon_class'] ?>"></i>
                </div>
                
                <div class="item-info">
                    <div class="item-title"><?= htmlspecialchars($row['text_label']) ?></div>
                    <div class="item-sub">Icon: <?= $row['icon_class'] ?> | Sort Order: <?= $row['sort_order'] ?></div>
                </div>

                <div class="item-actions">
                    <!-- Edit form trigger (Simplified for this example) -->
                    <a href="#" class="btn-edit" title="Edit"><i class="far fa-edit"></i></a>
                    <a href="?delete=<?= $row['id'] ?>" class="btn-delete" title="Delete" onclick="return confirm('Delete this item?')">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if(empty($top_items)): ?>
                <p style="text-align: center; color: #999;">No top bar items found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>