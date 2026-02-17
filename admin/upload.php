<?php
// 1. DATABASE SETTINGS
$host = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'photo_db';

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` text;");
    $pdo->exec("USE `$db_name` text;");

    // Create table if it doesn't exist
    $tableSql = "CREATE TABLE IF NOT EXISTS images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($tableSql);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// 2. FILE UPLOAD LOGIC
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['photo'])) {
    $uploadDir = 'uploads/';
    
    // Create uploads folder if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = $_FILES['photo']['name'];
    $tmpName  = $_FILES['photo']['tmp_name'];
    $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($fileExt, $allowed)) {
        // Generate a unique name to avoid overwriting files
        $newFileName = uniqid('IMG_', true) . '.' . $fileExt;
        $destination = $uploadDir . $newFileName;

        if (move_uploaded_file($tmpName, $destination)) {
            // Save to Database
            $stmt = $pdo->prepare("INSERT INTO images (filename) VALUES (?)");
            $stmt->execute([$newFileName]);
            $message = "<p style='color: green;'>✅ Photo uploaded successfully!</p>";
        } else {
            $message = "<p style='color: red;'>❌ Error: Could not save the file.</p>";
        }
    } else {
        $message = "<p style='color: red;'>❌ Error: Invalid file type. Only JPG, PNG, GIF, WEBP allowed.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Photo Manager</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f4f4f4; }
        .container { background: white; padding: 20px; border-radius: 8px; max-width: 800px; margin: auto; }
        .gallery { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 20px; }
        .gallery-item { border: 1px solid #ddd; padding: 5px; background: #fff; text-align: center; }
        .gallery-item img { width: 150px; height: 150px; object-fit: cover; display: block; }
        form { margin-bottom: 20px; border-bottom: 20px; padding-bottom: 20px; }
    </style>
</head>
<body>

<div class="container">
    <h2>📸 Upload New Photo</h2>
    
    <!-- Show success or error message -->
    <?php echo $message; ?>

    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="photo" required>
        <button type="submit">Upload Now</button>
    </form>

    <hr>

    <h2>🖼️ Photo Gallery</h2>
    <div class="gallery">
        <?php
        // Fetch photos from database
        $stmt = $pdo->query("SELECT * FROM images ORDER BY id DESC");
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($images) > 0) {
            foreach ($images as $row) {
                echo '<div class="gallery-item">';
                echo '<img src="uploads/' . htmlspecialchars($row['filename']) . '">';
                echo '<small>' . $row['uploaded_at'] . '</small>';
                echo '</div>';
            }
        } else {
            echo '<p>No photos uploaded yet.</p>';
        }
        ?>
    </div>
</div>

</body>
</html>