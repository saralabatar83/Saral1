<?php
session_start();
// --- DATABASE CONNECTION ---
require_once '../db.php'; 

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$msg = "";
$msg_type = "";
$user_id = $_SESSION['user_id'];

// 1. Fetch current user data
try {
    $stmt = $pdo->prepare("SELECT email, username FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    die("Database Error");
}

// 2. Handle the Account Update
if (isset($_POST['update_account'])) {
    $new_email = trim($_POST['new_email']);
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    $current_pass_verify = $_POST['current_password'];

    try {
        // Step A: Fetch current password hash to verify the user
        $passStmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $passStmt->execute([$user_id]);
        $userData = $passStmt->fetch();

        // Step B: Verify the current password
        if (password_verify($current_pass_verify, $userData['password'])) {
            
            $errors = [];

            // --- EMAIL CHANGE LOGIC ---
            if ($new_email !== $user['email']) {
                $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $checkEmail->execute([$new_email, $user_id]);
                if ($checkEmail->rowCount() > 0) {
                    $errors[] = "The new email is already in use by another account.";
                } else {
                    $updateEmail = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                    $updateEmail->execute([$new_email, $user_id]);
                    $user['email'] = $new_email; 
                }
            }

            // --- PASSWORD CHANGE LOGIC ---
            if (!empty($new_pass)) {
                if ($new_pass === $confirm_pass) {
                    if (strlen($new_pass) >= 6) {
                        $hashed_new_pass = password_hash($new_pass, PASSWORD_DEFAULT);
                        $updatePass = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $updatePass->execute([$hashed_new_pass, $user_id]);
                    } else {
                        $errors[] = "New password must be at least 6 characters.";
                    }
                } else {
                    $errors[] = "New passwords do not match.";
                }
            }

            if (empty($errors)) {
                $msg = "Account settings updated successfully!";
                $msg_type = "success";
            } else {
                $msg = implode("<br>", $errors);
                $msg_type = "error";
            }

        } else {
            $msg = "Verification failed: Incorrect current password.";
            $msg_type = "error";
        }
    } catch (PDOException $e) {
        $msg = "Database Error: " . $e->getMessage();
        $msg_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saral IT - Security Settings</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #7986cb;
            --secondary: #5c6bc0;
            --bg-body: #f4f7f6;
            --sidebar-bg: #2c3e50;
            --sidebar-hover: #34495e;
            --text-main: #333;
            --white: #ffffff;
            --danger: #e74c3c;
            --success-color: #27ae60;
            --sidebar-width: 260px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        
        body { 
            background: var(--bg-body); 
            display: flex; 
            min-height: 100vh; 
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* --- FIXED SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: white;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 25px;
            background: #1a252f;
            text-align: center;
            border-bottom: 3px solid var(--primary);
            position: sticky; top: 0; z-index: 10;
        }
        .sidebar-header h2 span { color: var(--primary); }

        .sidebar-menu { list-style: none; padding: 10px 0 100px 0; }
        .sidebar-menu li a {
            display: flex; align-items: center; padding: 12px 20px;
            color: #bdc3c7; text-decoration: none; font-size: 14px; transition: 0.2s;
        }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background: var(--primary); color: white; }
        .sidebar-menu i { margin-right: 12px; width: 20px; text-align: center; font-size: 16px; }
        
        .menu-divider { 
            padding: 20px 20px 5px; font-size: 11px; text-transform: uppercase; 
            color: #7f8c8d; font-weight: bold; letter-spacing: 1px;
        }

        /* --- MAIN VIEWPORT --- */
        .main-view { 
            margin-left: var(--sidebar-width); 
            flex: 1; 
            padding: 30px; 
            width: calc(100% - var(--sidebar-width)); 
            min-height: 100vh;
        }

        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }

        /* --- UI COMPONENTS --- */
        .panel { background: var(--white); padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto; }
        .panel h2 { margin-bottom: 20px; font-size: 18px; color: var(--sidebar-bg); border-bottom: 1px solid #eee; padding-bottom: 10px; display: flex; align-items: center; gap: 10px; }

        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 700; color: #555; font-size: 13px; text-transform: uppercase; }
        input { 
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; 
            box-sizing: border-box; font-size: 15px; outline: none; transition: 0.3s;
        }
        input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(121, 134, 203, 0.1); }
        
        .divider { height: 1px; background: #eee; margin: 25px 0; position: relative; }
        .divider::after { content: 'Identity Verification'; position: absolute; top: -10px; left: 20px; background: white; padding: 0 10px; font-size: 11px; color: var(--danger); font-weight: bold; text-transform: uppercase; }

        .btn-save { 
            background: var(--primary); color: white; border: none; padding: 15px; 
            border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; 
            font-size: 16px; transition: 0.3s;
        }
        .btn-save:hover { background: var(--secondary); transform: translateY(-1px); }

        .msg { padding: 15px; border-radius: 8px; margin-bottom: 25px; font-weight: bold; display: flex; align-items: center; gap: 10px; font-size: 14px; }
        .success { background: #d4edda; color: #155724; border-left: 5px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; border-left: 5px solid #dc3545; }

        small { color: #888; font-size: 11px; margin-top: 5px; display: block; }

        @media (max-width: 992px) {
            :root { --sidebar-width: 70px; }
            .sidebar h2, .sidebar span, .menu-divider { display: none; }
            .sidebar-menu i { margin: 0; font-size: 20px; width: 100%; }
            .sidebar-menu li a { justify-content: center; padding: 20px 0; }
        }
    </style>
</head>
<body>

    <!-- FIXED SIDEBAR -->
         <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Saral<span>Admin</span></h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-th-large"></i> <span>Dashboard</span></a></li>
            
            <div class="menu-divider">Catalog</div>
            <li><a href="all categories.php"><i class="fas fa-folder"></i> <span>Categories</span></a></li>
            <li><a href="admin_categories.php"><i class="fas fa-list-ul"></i> <span>Manage Cats</span></a></li>
            <li><a href="brands.php"><i class="fas fa-images"></i> <span>Top Brands</span></a></li>

            <div class="menu-divider">Marketing</div>
            <li><a href="offer.php"><i class="fas fa-fire"></i> <span>Daily Offers</span></a></li>
            
            
            <div class="menu-divider">System</div>
           
            
            <li><a href="sociallinks.php"><i class="fas fa-hashtag"></i> <span>Social Links</span></a></li>
            
            <li><a href="admin_footer.php"><i class="fas fa-shoe-prints"></i> <span>Footer Manager</span></a></li>
            
            <div class="menu-divider">Account</div>
            <li><a href="change_password.php"><i class="fas fa-key"></i> <span>Security</span></a></li>
             <li><a href="admin_header.php"><i class="fas fa-user-shield"></i> <span>Admin Home</span></a></li>
            <li><a href="logout.php" style="color: #ff7675;"><i class="fas fa-power-off"></i> <span>Logout</span></a></li>
           
        </ul>
    </aside>

    <main class="main-view">
        <header class="header-top">
            <h1>Account Security</h1>
            <div class="admin-badge" style="background: white; padding: 10px 20px; border-radius: 30px; font-weight: 600; font-size: 13px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                <i class="fas fa-shield-halved" style="color: var(--primary);"></i> Settings for @<?php echo htmlspecialchars($user['username']); ?>
            </div>
        </header>

        <!-- Feedback Messages -->
        <?php if ($msg): ?>
            <div class="msg <?php echo $msg_type; ?>">
                <i class="fas <?php echo ($msg_type == 'success') ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                <div><?php echo $msg; ?></div>
            </div>
        <?php endif; ?>

        <div class="panel">
            <h2><i class="fas fa-user-cog" style="color: var(--primary);"></i> Update Credentials</h2>
            
            <form method="POST">
                <!-- EMAIL SECTION -->
                <div class="form-group">
                    <label>Administrator Email</label>
                    <input type="email" name="new_email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="divider"></div>

                <!-- PASSWORD SECTION -->
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" placeholder="••••••••">
                    <small>Leave blank to keep your current password (Min 6 chars).</small>
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" placeholder="••••••••">
                </div>

                <div class="divider" style="background: #ffcccc;"></div>

                <!-- VERIFICATION SECTION -->
                <div class="form-group">
                    <label style="color: var(--danger);">Confirm Current Password</label>
                    <input type="password" name="current_password" required placeholder="Verify your identity to save">
                </div>

                <button type="submit" name="update_account" class="btn-save">
                    <i class="fas fa-save" style="margin-right: 8px;"></i> Save All Changes
                </button>
            </form>
        </div>
    </main>

</body>
</html>