<?php
// 1. Correct the path to find db.php outside the admin folder
if (file_exists('../db.php')) {
    include '../db.php';
} else {
    die("Error: db.php not found. Please check the file path.");
}

$msg = "";

if (isset($_POST['register'])) {
    $name = trim($_POST['username']);
    $email = trim($_POST['email']);
    // Encrypt password
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT); 

    try {
        // 2. Check if the email already exists using PDO
        $checkStmt = $pdo->prepare("SELECT email FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        
        if ($checkStmt->rowCount() > 0) {
            $msg = "Error: This email is already registered!";
        } else {
            // 3. Email is unique, proceed to insert (Default role is 'user')
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            
            if ($stmt->execute([$name, $email, $pass])) {
                // Success - Redirect to login page
                header("Location: login.php?signup=success"); 
                exit();
            } else {
                $msg = "Error: Could not register. Please try again.";
            }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Saral IT</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background-color: #f0f2f5; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
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
        <h2>Create Account</h2>
        
        <?php if ($msg != ""): ?>
            <p class="error-msg"><?php echo $msg; ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="register">Sign Up</button>
        </form>
        
        <p style="margin-top:20px; font-size: 14px; color: #666;">
            Already have an account? <a href="login.php">Login</a>
        </p>
    </div>
</body>
</html>