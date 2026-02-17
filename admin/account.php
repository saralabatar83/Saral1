<?php 
session_start(); 
include 'db.php'; 

// Check if database connection works
if (!isset($conn)) {
    die("Database connection failed. Please check db.php");
}
<!DOCTYPE html>
<html lang="en">
<!-- ... rest of your HTML ... --><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - TechMart</title>

    <link rel="stylesheet" href="account.html">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-left">
                <span class="location">
                    <span>Deliver to</span>
                    <img src="https://flagcdn.com/24x18/ae.png" alt="UAE Flag" class="flag">
                    <strong>Abu Dhabi</strong>
                </span>
                <span class="divider">|</span>
                <span><i class="fas fa-money-bill"></i> Cash on Delivery</span>
                <span class="divider">|</span>
                <span><i class="fas fa-truck"></i> Express Delivery</span>
                <span class="divider">|</span>
                <span><i class="fas fa-undo"></i> Free Returns</span>
                <span class="divider">|</span>
                <span><i class="fas fa-map-marker-alt"></i> Our Location</span>
                <span class="divider">|</span>
                <span><i class="fas fa-store"></i> Sell On TechMart</span>
            </div>
            <div class="top-bar-right">
                <span class="currency">
                    <i class="fas fa-coins"></i>
                    <select>
                        <option value="AED">AED</option>
                        <option value="USD">USD</option>
                    </select>
                </span>
                <span class="language">
                    <i class="fas fa-globe"></i>
                    <select>
                        <option value="en">English</option>
                        <option value="ar">العربية</option>
                    </select>
                </span>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <a href="index.html" class="logo">
                <span class="logo-text">Tech<span class="logo-highlight">Mart</span></span>
                <span class="logo-dot">●</span>
            </a>
            
            <div class="search-bar">
                <input type="text" placeholder="What are you looking for?" id="searchInput">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            
            <div class="header-actions">
                <a href="account.html" class="header-action">
                    <i class="fas fa-user"></i>
                    <span>HELLO SK SHAH</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <a href="wishlist.html" class="header-action">
                    <i class="fas fa-heart"></i>
                    <span>WISHLIST</span>
                </a>
                <a href="cart.html" class="header-action cart-action">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">1</span>
                    <span>YOUR CART</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="main-nav">
        <div class="container">
            <div class="nav-categories">
                <button class="all-categories-btn">
                    <i class="fas fa-bars"></i>
                    ALL CATEGORIES
                </button>
                <ul class="nav-menu">
                    <li><a href="#">COMPUTERS & LAPTOPS</a></li>
                    <li><a href="#">OFFICE & NETWORKING</a></li>
                    <li><a href="#">MOBILES & TABLETS</a></li>
                    <li><a href="#">ELECTRONICS</a></li>
                    <li><a href="#">HOME</a></li>
                    <li><a href="#" class="new-releases">NEW RELEASES</a></li>
                    <li><a href="#" class="clearance-sale">CLEARANCE SALE</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.html">Home</a>
            <span>&gt;</span>
            <span>My Account</span>
        </div>
    </div>

    <!-- Account Content -->
    <main class="account-page">
        <div class="container">
            <div class="account-layout">
                <!-- Sidebar -->
                <aside class="account-sidebar">
                    <div class="user-profile">
                        <div class="user-avatar">
                            <img src="/placeholder.svg?height=100&width=100" alt="User Avatar">
                        </div>
                        <h3 class="user-name">SK</h3>
                        <a href="#" class="sign-out-btn">
                            <i class="fas fa-sign-out-alt"></i> Sign Out
                        </a>
                    </div>
                    
                    <nav class="account-nav">
                        <a href="#" class="nav-item active">
                            <i class="fas fa-box"></i>
                            <span>Order History</span>
                        </a>
                        <a href="#" class="nav-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Addresses</span>
                        </a>
                        <a href="#" class="nav-item">
                            <i class="fas fa-tools"></i>
                            <span>Service Requests</span>
                        </a>
                        <a href="#" class="nav-item">
                            <i class="fas fa-heart"></i>
                            <span>My Wishlist</span>
                        </a>
                        <a href="#" class="nav-item">
                            <i class="fas fa-star"></i>
                            <span>My Reviews</span>
                        </a>
                        <a href="#" class="nav-item">
                            <i class="fas fa-user"></i>
                            <span>Profile Details</span>
                        </a>
                        <a href="#" class="nav-item">
                            <i class="fas fa-credit-card"></i>
                            <span>Payment Options</span>
                        </a>
                        <a href="#" class="nav-item">
                            <i class="fas fa-building"></i>
                            <span>My Business</span>
                        </a>
                    </nav>
                </aside>

                <!-- Main Content -->
                <div class="account-content">
                    <h1 class="welcome-title">Hello, SK Shah</h1>
                    
                    <div class="welcome-message">
                        <p>This is your dashboard. From here you can update your profile details, account security settings and see the details of your orders, inquiries etc.</p>
                    </div>

                    <div class="dashboard-cards">
                        <a href="#" class="dashboard-card">
                            <div class="card-content">
                                <h3>Order History</h3>
                                <p>Track your order, check order status, return your product or buy the product again.</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-box"></i>
                            </div>
                        </a>
                        
                        <a href="#" class="dashboard-card">
                            <div class="card-content">
                                <h3>Addresses</h3>
                                <p>Modify your addresses or add the new address for orders and gifts.</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                        </a>
                        
                        <a href="#" class="dashboard-card">
                            <div class="card-content">
                                <h3>Service Requests</h3>
                                <p>Track your product repair status with service order number.</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-tools"></i>
                            </div>
                        </a>
                        
                        <a href="#" class="dashboard-card">
                            <div class="card-content">
                                <h3>Wishlist</h3>
                                <p>See the items saved to your wishlist. Move them to cart or remove from wishlist.</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                        </a>
                        
                        <a href="#" class="dashboard-card">
                            <div class="card-content">
                                <h3>Reviews</h3>
                                <p>View the previous reviews you have submitted.</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-star"></i>
                            </div>
                        </a>
                        
                        <a href="#" class="dashboard-card">
                            <div class="card-content">
                                <h3>Profile Details</h3>
                                <p>View, update account information. Change account password.</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-user"></i>
                            </div>
                        </a>
                        
                        <a href="#" class="dashboard-card">
                            <div class="card-content">
                                <h3>Payment Options</h3>
                                <p>Manage your saved payment methods and cards.</p>
                            </div>
                            <div class="card-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-column">
                    <h3>About TechMart</h3>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Customer Service</h3>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Returns</a></li>
                        <li><a href="#">Shipping Info</a></li>
                        <li><a href="#">Track Order</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Policies</h3>
                    <ul>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Warranty Policy</a></li>
                        <li><a href="#">Return Policy</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Connect With Us</h3>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                    <h3>Download App</h3>
                    <div class="app-links">
                        <a href="#"><img src="/placeholder.svg?height=40&width=135" alt="App Store"></a>
                        <a href="#"><img src="/placeholder.svg?height=40&width=135" alt="Google Play"></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 TechMart. All Rights Reserved.</p>
                <div class="payment-methods">
                    <img src="/placeholder.svg?height=30&width=50" alt="Visa">
                    <img src="/placeholder.svg?height=30&width=50" alt="Mastercard">
                    <img src="/placeholder.svg?height=30&width=50" alt="PayPal">
                    <img src="/placeholder.svg?height=30&width=50" alt="Apple Pay">
                </div>
            </div>
        </div>
    </footer>

    <!-- Help Button -->
    <a href="#" class="help-btn">
        <i class="fas fa-question-circle"></i> Help
    </a>

    <script src="js/main.js"></script>
</body>
</html>
