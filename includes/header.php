<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "saral_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Your HTML header code starts here
?>
<div style="background: #fff; padding: 20px; border-bottom: 2px solid #eee;">
    <h2 style="color: #6f42c1; margin:0;">Saral IT Solution</h2>
</div>