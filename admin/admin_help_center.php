<?php
session_start();
// --- DATABASE CONNECTION ---
include '../db.php'; 

$message = "";
$msg_type = "";
$editMode = false;
$editData = ['id' => '', 'question' => '', 'answer' => '', 'display_order' => 0];

// =========================================================
// 1. HANDLE FORM SUBMISSIONS
// =========================================================

// --- SAVE / UPDATE FAQ ---
if (isset($_POST['save_faq'])) {
    $question = $_POST['question'];
    $answer = $_POST['answer'];
    $order = $_POST['display_order'];
    $id = $_POST['faq_id'];

    if (!empty($id)) {
        $stmt = $pdo->prepare("UPDATE faqs SET question=?, answer=?, display_order=? WHERE id=?");
        $stmt->execute([$question, $answer, $order, $id]);
        $message = "FAQ updated successfully!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO faqs (question, answer, display_order) VALUES (?, ?, ?)");
        $stmt->execute([$question, $answer, $order]);
        $message = "New FAQ added successfully!";
    }
    header("Location: admin_help_center.php?msg=" . urlencode($message) . "&type=success");
    exit;
}

// --- DELETE FAQ ---
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: admin_help_center.php?msg=FAQ deleted&type=success");
    exit;
}

// --- UPDATE HERO SETTINGS ---
if (isset($_POST['update_settings'])) {
    $title = $_POST['hero_title'];
    $image = $_POST['hero_image'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM page_content WHERE section_key = 'help_hero'");
    $stmt->execute();
    if ($stmt->fetchColumn()) {
        $pdo->prepare("UPDATE page_content SET title = ?, image_url = ? WHERE section_key = 'help_hero'")
            ->execute([$title, $image]);
    } else {
        $pdo->prepare("INSERT INTO page_content (section_key, title, image_url) VALUES ('help_hero', ?, ?)")
            ->execute([$title, $image]);
    }
    $message = "Header settings updated!";
    $msg_type = "success";
}

// =========================================================
// 2. FETCH DATA
// =========================================================

// Edit Mode Check
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($fetched) {
        $editMode = true;
        $editData = $fetched;
    }
}

// Fetch All FAQs
$faqs = $pdo->query("SELECT * FROM faqs ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch Page Settings
$heroStmt = $pdo->prepare("SELECT * FROM page_content WHERE section_key = 'help_hero'");
$heroStmt->execute();
$heroData = $heroStmt->fetch(PDO::FETCH_ASSOC);
$currentHeroTitle = $heroData['title'] ?? 'Help Center';
$currentHeroImage = $heroData['image_url'] ?? '';

// Handle redirect messages
if(isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $msg_type = $_GET['type'] ?? 'success';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Help Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f4f7f6; margin: 0; display: flex; }
        
        /* Sidebar Navigation */
        .sidebar { width: 260px; background: #2c3e50; min-height: 100vh; color: white; position: fixed; }
        .sidebar h2 { text-align: center; font-size: 18px; border-bottom: 1px solid #3e4f5f; padding: 20px 0; margin: 0; }
        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar ul li a { display: block; padding: 15px 25px; color: #bdc3c7; text-decoration: none; transition: 0.3s; }
        .sidebar ul li a:hover, .sidebar ul li a.active { background: #34495e; color: white; border-left: 4px solid #3498db; }
        .sidebar i { margin-right: 10px; width: 20px; }

        /* Main Content Area */
        .main-content { flex: 1; margin-left: 260px; padding: 30px; }
        .container { max-width: 1000px; margin: 0 auto; }
        
        .header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .btn-view { background: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; color: #3498db; font-weight: bold; border: 1px solid #3498db; transition: 0.2s; }
        .btn-view:hover { background: #3498db; color: white; }

        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        h3 { margin-top: 0; color: #333; border-left: 4px solid #3498db; padding-left: 10px; margin-bottom: 20px; }
        
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; font-size: 14px; }
        input[type="text"], input[type="number"], textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }

        .btn-save { background: #2E7D32; color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-cancel { background: #c62828; color: white; text-decoration: none; padding: 12px 25px; border-radius: 5px; font-weight: bold; display: inline-block; }
        
        .msg { padding: 15px; border-radius: 5px; margin-bottom: 20px; font-weight: bold; }
        .success { background: #d4edda; color: #155724; border-left: 5px solid #28a745; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9f9f9; color: #666; font-size: 13px; text-transform: uppercase; }
        .actions a { text-decoration: none; font-weight: bold; margin-right: 15px; }
        .edit-btn { color: #1976D2; }
        .delete-btn { color: #C62828; }
    </style>

    <script>
      tinymce.init({
        selector: '#answer-editor', 
        height: 300,
        menubar: false,
        plugins: 'lists link code',
        toolbar: 'undo redo | bold italic underline | bullist numlist | link code',
        setup: function (editor) { editor.on('change', function () { editor.save(); }); }
      });
    </script>
</head>
<body>

<div class="sidebar">
    <h2><i class="fas fa-question-circle"></i> ADMIN PANEL</h2>
    <ul>
        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="about.php"><i class="fas fa-info-circle"></i> About Page</a></li>
        <li><a href="admin_warranty.php"><i class="fas fa-shield-alt"></i> Warranty Policy</a></li>
        <li><a href="admin_help_center.php" class="active"><i class="fas fa-question-circle"></i> Help Center</a></li>
        <li><a href="admin_privacy.php"><i class="fas fa-user-shield"></i> Privacy Policy</a></li>
        <li><a href="admin_terms.php"><i class="fas fa-file-contract"></i> Terms</a></li>
        <li><a href="admin_return.php"><i class="fas fa-undo-alt"></i> Return Policy</a></li>
        <li><a href="job_admin.php"><i class="fas fa-briefcase"></i> Job Admin</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="container">
        
        <div class="header-row">
            <h2 style="margin:0;">Help Center Management</h2>
            <a href="../help_center.php" target="_blank" class="btn-view">
                <i class="fas fa-external-link-alt"></i> View Live Page
            </a>
        </div>

        <?php if($message): ?>
            <div class="msg <?php echo $msg_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- 1. HEADER SETTINGS -->
        <div class="card">
            <h3><i class="fas fa-cog"></i> Header Settings</h3>
            <form method="POST">
                <div style="display:flex; gap:20px; margin-bottom:15px;">
                    <div style="flex:1">
                        <label>Hero Title</label>
                        <input type="text" name="hero_title" value="<?php echo htmlspecialchars($currentHeroTitle); ?>">
                    </div>
                    <div style="flex:2">
                        <label>Hero Background Image URL</label>
                        <input type="text" name="hero_image" value="<?php echo htmlspecialchars($currentHeroImage); ?>">
                    </div>
                </div>
                <button type="submit" name="update_settings" class="btn-save">Update Header</button>
            </form>
        </div>

        <!-- 2. FAQ FORM -->
        <div class="card" id="faqForm">
            <h3><i class="fas fa-plus-circle"></i> <?php echo $editMode ? 'Edit FAQ' : 'Add New FAQ'; ?></h3>
            <form method="POST">
                <input type="hidden" name="faq_id" value="<?php echo $editData['id']; ?>">
                
                <div style="margin-bottom:15px;">
                    <label>The Question</label>
                    <input type="text" name="question" value="<?php echo htmlspecialchars($editData['question']); ?>" required>
                </div>
                
                <div style="margin-bottom:15px;">
                    <label>The Answer</label>
                    <textarea name="answer" id="answer-editor"><?php echo htmlspecialchars($editData['answer']); ?></textarea>
                </div>

                <div style="margin-bottom:20px; width:150px;">
                    <label>Display Order</label>
                    <input type="number" name="display_order" value="<?php echo $editData['display_order']; ?>">
                </div>

                <button type="submit" name="save_faq" class="btn-save"><?php echo $editMode ? 'Update FAQ' : 'Add FAQ'; ?></button>
                <?php if($editMode): ?>
                    <a href="admin_help_center.php" class="btn-cancel">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- 3. LIST OF FAQS -->
        <div class="card">
            <h3><i class="fas fa-list"></i> Existing FAQ Questions</h3>
            <table>
                <thead>
                    <tr>
                        <th width="50">Pos</th>
                        <th>Question</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($faqs as $f): ?>
                        <tr>
                            <td><strong><?php echo $f['display_order']; ?></strong></td>
                            <td><?php echo htmlspecialchars($f['question']); ?></td>
                            <td class="actions">
                                <a href="?edit_id=<?php echo $f['id']; ?>#faqForm" class="edit-btn"><i class="fas fa-edit"></i> Edit</a>
                                <a href="?delete_id=<?php echo $f['id']; ?>" class="delete-btn" onclick="return confirm('Permanently delete this question?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</body>
</html>