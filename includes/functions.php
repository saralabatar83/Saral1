<?php
function getProducts($pdo, $onlyOffers = false) {
    $sql = $onlyOffers ? "SELECT * FROM products WHERE is_daily_offer = 1" : "SELECT * FROM products";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function getCategories($pdo) {
    return $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
}



function getBrands($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM brands");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}