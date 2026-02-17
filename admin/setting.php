<?php
// 1. Database Connection
// Ensure your ../config/db.php uses the 'saral_db' name.
require_once '../config/db.php'; 

// Fallback connection if db.php doesn't define $conn
if (!isset($conn)) {
    $conn = mysqli_connect("localhost", "root", "", "saral_db");
}

if (!$conn) { die("Connection failed: " . mysqli_connect_error()); }

// 2. Handle ADD (With Security Fix)
if (isset($_POST['add_setting'])) {
    // mysqli_real_escape_string prevents SQL Injection (Security)
    $loc = mysqli_real_escape_string($conn, $_POST['delivery_location']);
    $cur = mysqli_real_escape_string($conn, $_POST['currency']);
    $lan = mysqli_real_escape_string($conn, $_POST['lang']);
    
    $query = "INSERT INTO settings (delivery_location, currency, lang) VALUES ('$loc', '$cur', '$lan')";
    if(mysqli_query($conn, $query)) {
        header("Location: setting.php?msg=added");
        exit(); // Always use exit after header
    }
}

// 3. Handle DELETE
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete']; // Cast to integer for security
    mysqli_query($conn, "DELETE FROM settings WHERE id=$id");
    header("Location: setting.php?msg=deleted");
    exit();
}

// 4. Fetch Settings (Order by newest first)
$result = mysqli_query($conn, "SELECT * FROM settings ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Saral IT - Header Settings</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f0f2f5; padding: 30px; }
        .admin-container { max-width: 900px; margin: auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        
        /* The Purple Header Layout (Matching your image) */
        .header-preview { background: #838bdc; color: white; padding: 10px 20px; display: flex; justify-content: space-between; font-size: 13px; align-items: center; border-radius: 5px; margin-bottom: 25px; border: 1px solid #7079d1; }
        .preview-label { font-size: 10px; color: #d1d5ff; text-transform: uppercase; margin-bottom: 5px; display: block; font-weight: bold;}

        /* Form Styling */
        .form-row { display: flex; gap: 10px; margin-bottom: 20px; }
        input[type="text"] { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 6px; outline: none; }
        input[type="text"]:focus { border-color: #838bdc; }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; background: #f8f9fa; padding: 12px; color: #666; font-size: 13px; border-bottom: 2px solid #eee; }
        td { padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        
        .btn { padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; }
        .btn-add { background: #4e56d1; color: white; }
        .btn-delete { background: #ff4757; color: white; font-size: 12px; }
        .btn-delete:hover { background: #ff6b81; }
        
        .empty-msg { text-align: center; padding: 20px; color: #999; }
    </style>
</head>
<body>

<div class="admin-container">
    <h2>Header Configuration</h2>

    <!-- ADD FORM -->
    <form method="POST" class="form-row">
        <input type="text" name="delivery_location" placeholder="Deliver to (e.g. Saral IT)..." required>
        <input type="text" name="currency" placeholder="Currency (RS)" style="max-width: 100px;" required>
        <input type="text" name="lang" placeholder="Language (नेपाली)" style="max-width: 130px;" required>
        <button type="submit" name="add_setting" class="btn btn-add">Save Configuration</button>
    </form>

    <span class="preview-label">Live Preview (Current Settings)</span>
    
    <table>
        <thead>
            <tr>
                <th>Preview Content</th>
                <th width="100">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>
                        <!-- This renders the row exactly like the frontend -->
                        <div class="header-preview">
                            <div class="left">
                                Deliver to: 🇳🇵 <b><?php echo htmlspecialchars($row['delivery_location']); ?></b> 
                                | 💵 Cash on Delivery | 🚚 Express | 🔄 Returns
                            </div>
                            <div class="right">
                                💰 <?php echo htmlspecialchars($row['currency']); ?> ▾ | 🌐 <?php echo htmlspecialchars($row['lang']); ?> ▾
                            </div>
                        </div>
                    </td>
                    <td style="vertical-align: middle; text-align: center;">
                        <a href="setting.php?delete=<?php echo $row['id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this config?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="2" class="empty-msg">No configurations found. Add one above!</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>