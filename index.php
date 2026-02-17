<?php
session_start(); // Start session to track login
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KMECom - Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fff;
            text-align: center;
            padding: 50px;
        }
        h1 {
            color: #333;
        }
        .nav {
            margin-top: 30px;
        }
        .nav a {
            display: inline-block;
            margin: 10px;
            padding: 15px 25px;
            background: #ccc;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.3s;
        }
        .nav a:hover {
            background: #9b9b9bff;
        }
    </style>
</head>
<body>
    <h1>Welcome to KMecom Project</h1>
    <p>Select a module to continue:</p>

    <div class="nav">
        <?php if(isset($_SESSION['customer_id'])): ?>
            <!-- Navigation for logged-in users -->
            <a href="categories.php">📂 Categories</a>
            <a href="products.php">📦 Products</a>
            <a href="cart.php">🛒 Your Cart</a>
            <a href="wishlist.php">❤️ Wishlist</a>
            <a href="profile.php">👤 Profile</a>
            <a href="logout.php">🚪 Logout (<?= $_SESSION['customer_name'] ?>)</a>
        <?php else: ?>
            <!-- Navigation for guests -->
            <a href="user.php">👤 Register New User</a>
            <a href="login.php">🔑 Login</a>
            <a href="categories.php">📂 Categories</a>
            <a href="products.php">📦 Products</a>
        <?php endif; ?>
    </div>
</body>
</html>
