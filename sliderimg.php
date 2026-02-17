<?php
include 'db.php';
// Fetch images for the background live slider
$res = $conn->query("SELECT file_name FROM slider_images ORDER BY id DESC");
$slides = [];
while($row = $res->fetch_assoc()){ $slides[] = "images/" . $row['file_name']; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Saral It solution | Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; overflow-x: hidden; }

        /* --- TOP BLUE BAR --- */
        .top-bar { background: #6f6af8; color: white; padding: 8px 10%; font-size: 12px; display: flex; justify-content: space-between; }
        .top-bar span { margin-right: 15px; cursor: pointer; }

        /* --- HEADER LOGO & SEARCH --- */
        header { padding: 20px 10%; display: flex; align-items: center; justify-content: space-between; background: white; }
        .logo { font-size: 26px; font-weight: bold; color: #333; flex: 1; }
        .logo span { color: #6f6af8; }
        .search-container { flex: 2; display: flex; margin: 0 30px; }
        .search-container input { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 4px 0 0 4px; outline: none; }
        .search-container button { background: #6f6af8; color: white; border: none; padding: 0 20px; border-radius: 0 4px 4px 0; cursor: pointer; }
        .user-icons { flex: 1; display: flex; justify-content: flex-end; gap: 25px; align-items: center; font-size: 14px; }
        .icon-box { text-align: center; position: relative; cursor: pointer; color: #444; }
        .badge { position: absolute; top: -5px; right: -5px; background: #ff4d4d; color: white; border-radius: 50%; padding: 2px 6px; font-size: 10px; }

        /* --- NAVIGATION --- */
        nav { display: flex; align-items: center; padding: 0 10%; border-bottom: 1px solid #eee; background: white; }
        .btn-categories { background: #ffc107; font-weight: bold; padding: 15px 25px; border: none; cursor: pointer; text-transform: uppercase; }
        .nav-links { display: flex; gap: 30px; margin-left: 30px; font-size: 13px; font-weight: bold; }
        .nav-links a { text-decoration: none; color: #333; }
        .nav-links a.active { color: #d9534f; }

        /* --- LIVE SLIDER SECTION --- */
        .hero-section { position: relative; width: 100%; height: 500px; background: #5c54a4; overflow: hidden; }
        .slider-bg { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; }
        .slider-bg img { position: absolute; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 1.5s; }
        .slider-bg img.active { opacity: 0.6; } /* Faded background */

        /* ABC CARD OVERLAY */
        .overlay-card { 
            position: relative; z-index: 2; display: flex; align-items: center; justify-content: center; height: 100%;
        }
        .card { 
            background: #23213b; color: white; padding: 60px 80px; text-align: center; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .card h1 { font-size: 44px; margin: 10px 0; }
        .card p { color: #ccc; margin-bottom: 30px; }
        .btn-learn { background: #007bff; color: white; padding: 12px 35px; text-decoration: none; border-radius: 5px; font-weight: bold; }

        /* --- SHOP BY CATEGORY --- */
        .shop-cat { padding: 40px 10%; display: flex; justify-content: space-between; align-items: center; }
        .shop-cat h2 { margin: 0; color: #333; text-transform: uppercase; font-size: 20px; }
    </style>
</head>
<body>

    <!-- Top Bar -->
    <div class="top-bar">
        <div>
            <span>Deliver to NP Saral It solution</span> | <span><i class="fa fa-truck"></i> Cash on Delivery</span> | <span>Express Delivery</span>
        </div>
        <div>
            <span>RS <i class="fa fa-caret-down"></i></span> | <span>🇳🇵 नेपाली <i class="fa fa-caret-down"></i></span>
        </div>
    </div>

    <!-- Header -->
    <header>
        <div class="logo">Saral <span>It solution</span> <i class="fa fa-circle" style="font-size:10px; vertical-align:middle; color:#6f6af8"></i></div>
        <form action="index.php" method="GET" class="search-container">
            <input type="text" name="q" placeholder="about" value="<?= @$_GET['q'] ?>">
            <button type="submit"><i class="fa fa-search"></i></button>
        </form>
        <div class="user-icons">
            <div class="icon-box"><i class="fa fa-user" style="font-size:20px;"></i><br>PERSON</div>
            <div class="icon-box"><i class="fa fa-heart" style="font-size:20px;"></i><br>WISHLIST</div>
            <div class="icon-box">
                <i class="fa fa-shopping-cart" style="font-size:20px;"></i><br>YOUR CART
                <span class="badge">1</span>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav>
        <button class="btn-categories"><i class="fa fa-bars"></i> All Categories</button>
        <div class="nav-links">
            <a href="#">HOME</a>
            <a href="#">HOME</a>
            <a href="#">COMPUTER</a>
            <a href="#" class="active">NEW RELEASES</a>
        </div>
    </nav>

    <!-- LIVE HERO SLIDER -->
    <div class="hero-section">
        <div class="slider-bg" id="bgSlider">
            <?php if(!empty($slides)): ?>
                <?php foreach($slides as $index => $path): ?>
                    <img src="<?= $path ?>" class="<?= ($index==0)?'active':'' ?>">
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="overlay-card">
            <div class="card">
                <p style="text-transform:uppercase; font-size:12px; color:#aaa;">Edit Slider Images</p>
                <h1>ABC Technology Pvt. Ltd.</h1>
                <p>Innovative • Reliable • Professional</p>
                <a href="#" class="btn-learn">Learn More</a>
            </div>
        </div>
    </div>

    <!-- Shop by Category Footer -->
    <div class="shop-cat">
        <h2>Shop By Category</h2>
        <a href="#" style="color:#888; text-decoration:none;">See more</a>
    </div>

    <script>
        // Automatic Slider Logic
        let current = 0;
        const slides = document.querySelectorAll('#bgSlider img');
        if(slides.length > 1) {
            setInterval(() => {
                slides[current].classList.remove('active');
                current = (current + 1) % slides.length;
                slides[current].classList.add('active');
            }, 4000);
        }
    </script>
</body>
</html>