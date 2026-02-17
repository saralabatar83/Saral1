<?php
require_once 'config/db.php'; // Ensure your DB connection path is correct

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if ($query !== '') {
    try {
        // Search across multiple tables using UNION
        // Note: Adjust table names if yours are different
        $sql = "
            SELECT id, name, price, image_path, 'laptop' as type FROM laptop WHERE name LIKE :q
            UNION
            SELECT id, name, price, image_path, 'cctv' as type FROM cctv WHERE name LIKE :q
            UNION
            SELECT id, name, price, image_path, 'electronics' as type FROM electronics WHERE name LIKE :q
            UNION
            SELECT id, name, price, image_path, 'networking' as type FROM networking WHERE name LIKE :q
            UNION
            SELECT id, name, price, image_path, 'accessories' as type FROM accessories WHERE name LIKE :q
        ";

        $stmt = $pdo->prepare($sql);
        $searchTerm = "%$query%";
        $stmt->execute(['q' => $searchTerm]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Search Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results for "<?php echo htmlspecialchars($query); ?>"</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #838de7; --bg: #f4f7f6; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; padding: 20px; }
        .search-header1 { background: white; padding: 30px; border-radius: 15px; text-align: center; margin-bottom: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .search-header1 h2 { margin: 0; color: #333; }
        .search-header1 span { color: var(--primary); }

        .results-grid1 { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); 
            gap: 25px; 
            max-width: 1200px; 
            margin: auto; 
        }

        .result-card1 { 
            background: white; 
            border-radius: 12px; 
            padding: 20px; 
            text-align: center; 
            transition: 0.3s; 
            border: 1px solid #eee;
            text-decoration: none;
            color: inherit;
        }
        .result-card1:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); border-color: var(--primary); }
        .result-card1 img { width: 100%; height: 150px; object-fit: contain; margin-bottom: 15px; }
        .result-card1 h4 { margin: 10px 0; font-size: 16px; height: 40px; overflow: hidden; }
        .result-card1 .price { color: var(--primary); font-weight: bold; font-size: 18px; }
        .type-badge { font-size: 10px; background: #eee; padding: 3px 8px; border-radius: 10px; text-transform: uppercase; color: #777; }
        
        .no-results { text-align: center; padding: 100px; color: #999; }
        .btn-back { display: inline-block; margin-top: 20px; color: var(--primary); text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

    <div class="search-header1">
        <h2>Search Results for <span>"<?php echo htmlspecialchars($query); ?>"</span></h2>
        <a href="index.php" class="btn-back">← Back to Home</a>
    </div>

    <div class="results-grid1">
        <?php if (!empty($results)): ?>
            <?php foreach ($results as $item): 
                // Determine which detail page to link to based on the "type"
                $detailPage = $item['type'] . "detail.php"; 
                if($item['type'] == 'laptop') $detailPage = "laptopdetail.php";
                if($item['type'] == 'cctv') $detailPage = "CCTV.php"; // Or your cctv detail page
            ?>
                <a href="<?php echo $detailPage; ?>?id=<?php echo $item['id']; ?>" class="result-card1">
                    <img src="admin/uploads/<?php echo $item['image_path']; ?>" onerror="this.src='https://via.placeholder.com/200'">
                    <span class="type-badge"><?php echo $item['type']; ?></span>
                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                    <div class="price">Rs. <?php echo number_format($item['price']); ?></div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">
                <i class="fa fa-search" style="font-size: 50px; margin-bottom: 20px;"></i>
                <p>No products found matching your search.</p>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>