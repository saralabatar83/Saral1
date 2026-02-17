<?php
/**
 * SARAL IT SOLUTION - CUSTOMER INQUIRY MANAGEMENT
 */
require_once '../db.php'; 

// --- 1. DATABASE AUTO-SETUP (Ensure inquiries are saved) ---
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS inquiries (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category VARCHAR(100),
        name VARCHAR(100),
        phone VARCHAR(20),
        message TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) { }

// --- 2. BACKEND LOGIC (Save & Email) ---
$message_sent = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_inquiry'])) {
    $to       = "saralabatar83@gmail.com"; 
    $category = htmlspecialchars($_POST['Category']);
    $name     = htmlspecialchars($_POST['Name']);
    $phone    = htmlspecialchars($_POST['Phone']);
    $msg      = htmlspecialchars($_POST['Message']);

    // Save to Database first
    $stmt = $pdo->prepare("INSERT INTO inquiries (category, name, phone, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$category, $name, $phone, $msg]);

    // Send Email
    $subject = "New Website Inquiry from $name";
    $headers = "From: Saral IT Admin <no-reply@saralitsolution.com>\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body    = "Customer: $name\nPhone: $phone\nInterested In: $category\nMessage: $msg";
    
    @mail($to, $subject, $body, $headers);
    $message_sent = true;
}

// --- 3. DELETE INQUIRY ---
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM inquiries WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: formfillup.php?msg=Deleted"); exit();
}

// Fetch all inquiries for the "Customers" list
$customers = $pdo->query("SELECT * FROM inquiries ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Inquiries | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root { --sidebar-width: 260px; --primary-dark: #0f172a; --accent: #838de7; }
        body { background: #f4f7f6; margin: 0; display: flex; font-family: 'Segoe UI', sans-serif; }

        /* --- SIDEBAR STYLES --- */
        .sidebar { width: var(--sidebar-width); height: 100vh; background: var(--primary-dark); color: white; position: fixed; left: 0; top: 0; overflow-y: auto; z-index: 1000; }
        .sidebar-header { padding: 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h2 { font-size: 22px; font-weight: 700; margin: 0; color: white; }
        .sidebar-header span { color: var(--accent); }
        .sidebar-menu { list-style: none; padding: 20px 0; margin: 0; }
        .sidebar-menu li a { display: block; padding: 12px 25px; color: #bdc3c7; text-decoration: none; transition: 0.3s; font-size: 14px; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { color: white; background: rgba(255,255,255,0.1); border-left: 4px solid var(--accent); }
        .sidebar-menu li a i { margin-right: 12px; width: 20px; text-align: center; }
        .menu-divider { padding: 15px 25px 5px; font-size: 11px; text-transform: uppercase; color: #7f8c8d; font-weight: bold; }

        /* --- CONTENT AREA --- */
        .main-content { margin-left: var(--sidebar-width); flex: 1; padding: 40px; min-width: 0; }
        .card { background: white; border-radius: 15px; border: none; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        
        .form-label { font-weight: 600; font-size: 13px; color: #555; }
        .table thead { background: #f8fafc; }
        .table th { font-size: 12px; text-transform: uppercase; color: #64748b; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Saral<span>Admin</span></h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <div class="menu-divider">Catalog Management</div>
            <li><a href="all_categories.php"><i class="fas fa-layer-group"></i> Categories</a></li>
            <li><a href="Laptop.php"><i class="fas fa-laptop"></i> Laptops</a></li>
            <li><a href="printer.php"><i class="fas fa-print"></i> Printers</a></li>
            <li><a href="admin_accessories.php"><i class="fas fa-keyboard"></i> Accessories</a></li>
            
            <div class="menu-divider">System</div>
            <li><a href="formfillup.php" class="active"><i class="fas fa-users"></i> Customers</a></li>
            <li><a href="top_setting.php"><i class="fas fa-tools"></i> Top Header Settings</a></li>
            <li><a href="sociallinks.php"><i class="fas fa-share-alt"></i> Social Links</a></li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <div class="row g-4">
            
            <!-- 1. INQUIRY FORM (LEFT) -->
            <div class="col-lg-4">
                <div class="card p-4">
                    <h4 class="mb-4" style="color: var(--accent);">Quick Inquiry</h4>
                    <?php if ($message_sent): ?>
                        <div class="alert alert-success small">✅ Inquiry saved and emailed!</div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Requirement</label>
                            <select name="Category" class="form-select shadow-sm" required>
                                <option value="Laptop">Laptop & Computer</option>
                                <option value="Monitor">Monitor & Display</option>
                                <option value="Printer">Printer & Scanner</option>
                                <option value="CCTV">CCTV & Security</option>
                                <option value="Service">Repair & Maintenance</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="Name" class="form-control shadow-sm" placeholder="Full Name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="Phone" class="form-control shadow-sm" placeholder="98XXXXXXXX" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="Message" rows="3" class="form-control shadow-sm" placeholder="Requirements..."></textarea>
                        </div>
                        <button type="submit" name="send_inquiry" class="btn w-100 fw-bold py-2" style="background: #ff9800; color: white;">SEND INQUIRY</button>
                    </form>
                </div>
            </div>

            <!-- 2. CUSTOMER LIST (RIGHT) -->
            <div class="col-lg-8">
                <div class="card p-4">
                    <h4 class="mb-4 text-dark">Customer Inquiries</h4>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Customer Info</th>
                                    <th>Service</th>
                                    <th>Message</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $c): ?>
                                <tr>
                                    <td class="small text-muted"><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                                    <td>
                                        <div class="fw-bold"><?= $c['name'] ?></div>
                                        <div class="small text-primary"><?= $c['phone'] ?></div>
                                    </td>
                                    <td><span class="badge bg-info text-dark"><?= $c['category'] ?></span></td>
                                    <td><small><?= mb_strimwidth($c['message'], 0, 50, "...") ?></small></td>
                                    <td>
                                        <a href="?delete=<?= $c['id'] ?>" class="text-danger" onclick="return confirm('Delete this record?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if(empty($customers)) echo "<p class='text-center text-muted py-4'>No inquiries yet.</p>"; ?>
                    </div>
                </div>
            </div>

        </div>
    </main>

</body>
</html>