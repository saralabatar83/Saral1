<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Customer Testimonials</title>
    <style>
        /* Renamed CSS Classes */
        .testimonial-page-bg { 
            background: #1a2a4e; 
            color: white; 
            font-family: 'Arial', sans-serif; 
            text-align: center; 
            padding: 60px 20px; 
        }

        .main-title span { color: #ff6600; }
        
        .testimonial-wrapper {
            max-width: 850px;
            margin: 40px auto;
        }

        /* The White Box */
        .quote-container-box { 
            background: white; 
            color: #333; 
            padding: 50px; 
            border-radius: 12px; 
            position: relative; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            font-size: 1.2rem;
            line-height: 1.6;
        }

        /* The Profile Photo */
        .client-avatar-circle { 
            width: 110px; 
            height: 110px; 
            border-radius: 50%; 
            border: 6px solid #1a2a4e; 
            margin-top: -55px; /* Makes it overlap the box */
            position: relative;
            z-index: 5;
            object-fit: cover;
        }

        .client-name { 
            text-transform: uppercase; 
            letter-spacing: 2px; 
            margin-top: 15px; 
            font-size: 1.4rem;
        }

        .client-job-title { 
            color: #ff6600; 
            font-weight: bold; 
            font-size: 1rem;
            margin-bottom: 40px;
        }

        .navigation-arrow {
            background: #ff6600;
            color: white;
            border: none;
            padding: 15px 12px;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
        .arrow-left { left: 0; border-radius: 0 5px 5px 0; }
        .arrow-right { right: 0; border-radius: 5px 0 0 5px; }
    </style>
</head>
<body class="testimonial-page-bg">

    <h1 class="main-title">Customer <span>Testimonials</span></h1>
    <p>What Our Customers Have To Say.</p>

    <?php
    $res = $conn->query("SELECT * FROM testimonials ORDER BY id DESC");
    while($row = $res->fetch_assoc()): ?>
        
        <div class="testimonial-wrapper">
            <div class="quote-container-box">
                <button class="navigation-arrow arrow-left"><</button>
                <p>"<?php echo $row['content']; ?>"</p>
                <button class="navigation-arrow arrow-right">></button>
            </div>
            
            <img src="<?php echo $row['image_url']; ?>" class="client-avatar-circle">
            <h3 class="client-name"><?php echo $row['name']; ?></h3>
            <p class="client-job-title"><?php echo $row['title']; ?></p>
        </div>

    <?php endwhile; ?>

</body>
</html>