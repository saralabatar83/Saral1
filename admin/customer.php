<?php 
// 1. FIXED PATH: Go up one folder to find db.php
include_once '../db.php'; 

// Check if connection exists
if (!isset($conn) || !$conn) {
    die("Fatal Error: Database connection failed. Check your db.php file.");
}

$update_mode = false;
$id = $name = $title = $image_url = $content = "";

// DELETE
if (isset($_GET['delete'])) {
    $id_del = intval($_GET['delete']);
    $conn->query("DELETE FROM testimonials WHERE id = $id_del");
    header("Location: customer.php"); exit();
}

// EDIT - Load Data
if (isset($_GET['edit'])) {
    $update_mode = true;
    $id_edit = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM testimonials WHERE id = $id_edit");
    if($row = $res->fetch_assoc()){
        $id = $row['id']; $name = $row['name']; $title = $row['title']; 
        $image_url = $row['image_url']; $content = $row['content'];
    }
}

// SAVE / UPDATE
if (isset($_POST['save_testimonial'])) {
    $f_id = $_POST['id'];
    $f_name = mysqli_real_escape_string($conn, $_POST['name']);
    $f_title = mysqli_real_escape_string($conn, $_POST['title']);
    $f_img = mysqli_real_escape_string($conn, $_POST['image_url']);
    $f_con = mysqli_real_escape_string($conn, $_POST['content']);

    if (empty($f_id)) {
        $sql = "INSERT INTO testimonials (name, title, image_url, content) VALUES ('$f_name', '$f_title', '$f_img', '$f_con')";
    } else {
        $sql = "UPDATE testimonials SET name='$f_name', title='$f_title', image_url='$f_img', content='$f_con' WHERE id=$f_id";
    }

    // This is where your error was happening. We check if the query works:
    if ($conn->query($sql)) {
        header("Location: customer.php"); exit();
    } else {
        // This will tell you EXACTLY why it failed (e.g., "Table testimonials doesn't exist")
        die("SQL Error: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Testimonials</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 20px; }
        .box { background: white; padding: 20px; border-radius: 8px; max-width: 500px; margin: auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        input, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; box-sizing: border-box; }
        button { background: #28a745; color: white; border: none; padding: 10px 20px; cursor: pointer; width: 100%; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; background: white; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
    </style>
</head>
<body>

<div class="box">
    <h2><?php echo $update_mode ? "Edit" : "Add"; ?> Testimonial</h2>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="text" name="name" placeholder="Name" value="<?php echo $name; ?>" required>
        <input type="text" name="title" placeholder="Job Title" value="<?php echo $title; ?>" required>
        <input type="text" name="image_url" placeholder="Image URL" value="<?php echo $image_url; ?>" required>
        <textarea name="content" placeholder="Quote" rows="4" required><?php echo $content; ?></textarea>
        <button type="submit" name="save_testimonial">Save Testimonial</button>
    </form>
</div>

<table>
    <tr><th>Name</th><th>Title</th><th>Actions</th></tr>
    <?php 
    $res = $conn->query("SELECT * FROM testimonials ORDER BY id DESC");
    while($row = $res->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['name']; ?></td>
        <td><?php echo $row['title']; ?></td>
        <td>
            <a href="customer.php?edit=<?php echo $row['id']; ?>">Edit</a> | 
            <a href="customer.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete?')">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>