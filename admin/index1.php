<?php 
require_once 'config/db.php';
require_once 'includes/functions.php';
include 'includes/header.php'; 
?>

<div class="hero">
    <div class="hero-content">
        <h1>ABC Technology Pvt. Ltd.</h1>
        <p>Innovative • Reliable • Professional</p>
        <button style="background:#007bff; color:white; border:none; padding:10px 20px;">Learn More</button>
    </div>
</div>

<h2 style="padding: 20px 5%;">DAILY OFFERS</h2>
<div class="grid">
    <?php foreach(getProducts($pdo, true) as $p): ?>
    <div class="card">
        <span class="badge"><?= $p['sale_label'] ?></span>
        <img src="uploads/products/<?= $p['image_url'] ?>">
        <p><?= $p['name'] ?></p>
    </div>
    <?php endforeach; ?>
</div>

<h2 style="padding: 20px 5%;">SHOP BY CATEGORY</h2>
<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));">
    <?php foreach(getCategories($pdo) as $c): ?>
    <div class="card">
        <img src="uploads/categories/<?= $c['icon_url'] ?>" style="height:50px;">
        <p><?= $c['name'] ?></p>
    </div>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>