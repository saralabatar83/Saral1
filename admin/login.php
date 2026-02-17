<?php
session_start();

// 1. Path Correction: ../ goes out of the admin folder to find db.php
// If your db file is in a folder called config, use: include '../config/db.php';
if (file_exists('db.php')) {
    include 'db.php';
} else {
    die("Error: db.php not found. Please check the file path.");
}

$msg = "";

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // 2. Use $pdo (PDO) which matches your other project files
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // 3. Verify the hashed password
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];

                // 4. Redirect based on Role
                if ($row['role'] == 'admin') {
                    // Already in admin folder, so just go to dashboard.php
                    header("Location: dashboard.php");
                } else {
                    // Go out of admin folder to find the main index.php
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                $msg = "Invalid Password!";
            }
        } else {
            $msg = "User not found!";
        }
    } catch (PDOException $e) {
        $msg = "Database Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Saral IT</title>
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .auth-box { width: 350px; padding: 40px; background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; }
        h2 { color: #333; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; outline: none; }
        input:focus { border-color: #838de7; }
        button { width: 100%; padding: 12px; background: #838de7; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 16px; margin-top: 10px; }
        button:hover { background: #6c7ae0; }
        .error-msg { color: #ff4757; font-size: 14px; margin-bottom: 10px; font-weight: bold; }
        a { color: #838de7; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="auth-box">
        <h2>Login</h2>
        
        <?php if($msg): ?>
            <p class="error-msg"><?php echo $msg; ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Sign In</button>
        </form>
    
        </p>
    </div>
</body>
</html>