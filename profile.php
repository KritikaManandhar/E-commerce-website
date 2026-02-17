<?php
session_start();
include 'db.php';

// Redirect if user is not logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];

// Handle cart item deletion
if (isset($_GET['delete_cart_id'])) {
    $delete_id = $_GET['delete_cart_id'];
    $stmt = $conn->prepare("DELETE FROM shopping_cart WHERE customer_id=? AND product_id=?");
    $stmt->execute([$customer_id, $delete_id]);
    header("Location: profile.php");
    exit;
}

// Handle cart quantity update with stock check
if (isset($_POST['update_cart'])) {
    $product_id = $_POST['product_id'];
    $new_quantity = max(1, intval($_POST['quantity'])); // minimum 1

    // Fetch available stock
    $stock_stmt = $conn->prepare("SELECT stock FROM products WHERE id=?");
    $stock_stmt->execute([$product_id]);
    $stock = $stock_stmt->fetchColumn();

    if ($stock !== false) {
        if ($new_quantity > $stock) {
            $new_quantity = $stock; // limit to available stock
            $_SESSION['error'] = "Only $stock items are available for this product.";
        }

        // Update cart with validated quantity
        $stmt = $conn->prepare("UPDATE shopping_cart SET quantity=? WHERE customer_id=? AND product_id=?");
        $stmt->execute([$new_quantity, $customer_id, $product_id]);
    }

    header("Location: profile.php");
    exit;
}

// Handle wishlist item deletion
if (isset($_GET['delete_wishlist_id'])) {
    $delete_id = $_GET['delete_wishlist_id'];
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE customer_id=? AND product_id=?");
    $stmt->execute([$customer_id, $delete_id]);
    header("Location: profile.php");
    exit;
}

// Fetch user info
$stmt = $conn->prepare("SELECT name, email, address, phone FROM customers WHERE id=?");
$stmt->execute([$customer_id]);
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user's cart items with stock info
$cart_stmt = $conn->prepare("
    SELECT p.id as product_id, p.name, p.price, p.stock, sc.quantity 
    FROM shopping_cart sc
    JOIN products p ON sc.product_id = p.id
    WHERE sc.customer_id=?
");
$cart_stmt->execute([$customer_id]);
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's wishlist items
$wishlist_stmt = $conn->prepare("
    SELECT p.id as product_id, p.name, p.price 
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    WHERE w.customer_id=?
");
$wishlist_stmt->execute([$customer_id]);
$wishlist_items = $wishlist_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your Profile</title>
<style>
/* Same styles as before */
body { font-family: Arial, sans-serif; background: #fff; color: #000; margin: 0; padding: 20px 40px ; }
h1 { text-align: center; margin-bottom: 30px; color: #000;}
.top-right { position: absolute; top: 20px; right: 40px; }
a.button, input[type=submit] { padding: 8px 16px; background: #000; color: white; text-decoration: none; border: none; border-radius: 5px; cursor: pointer; }
a.button:hover, input[type=submit]:hover { background: #333; }
.container { display: flex; gap: 20px; }
.left-column { width: 33%; }
.right-column { width: 67%; display: flex; flex-direction: column; gap: 20px; }
.card { background: #f9f9f9; padding: 15px; border: 1px solid #ccc; border-radius: 8px; box-shadow: 0 3px 8px rgba(0,0,0,0.1); text-align: left; }
.card h3 { margin-top: 0; color: #000; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
table { border-collapse: collapse; width: 100%; margin-top: 10px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
th { background: #e0e0e0; color: #000; }
tr:hover { background-color: #f1f1f1; }
.delete-button { padding: 4px 8px; background: #900; color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; }
.delete-button:hover { background: #600; }
input.quantity { width: 50px; padding: 4px; border: 1px solid #ccc; border-radius: 4px; }
.error-msg { color: red; margin-bottom: 10px; font-weight: bold; }
@media (max-width: 900px) {
    .container { flex-direction: column; }
    .left-column, .right-column { width: 100%; }
    .top-right { position: static; text-align: center; margin-bottom: 20px; }
}
</style>
</head>
<body>
<div class="top-right">
    <a href="index.php" class="button">🏠 Back to Home</a>
</div>

<h1>Your Profile</h1>

<div class="container">
    <!-- Left column: User info -->
    <div class="left-column">
        <div class="card">
            <h3>User Information</h3>
            <p><strong>Name:</strong> <?= htmlspecialchars($user_info['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user_info['email']) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($user_info['address']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($user_info['phone']) ?></p>
        </div>
    </div>

    <!-- Right column: Cart and Wishlist -->
    <div class="right-column">
        <!-- Cart Section -->
        <div class="card">
            <h3>Your Cart</h3>
            <?php if(isset($_SESSION['error'])): ?>
                <p class="error-msg"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <?php if($cart_items): ?>
                <table>
                    <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Stock</th><th>Subtotal</th><th>Action</th></tr>
                    <?php
                    $grand_total = 0;
                    foreach($cart_items as $item):
                        $subtotal = $item['price'] * $item['quantity'];
                        $grand_total += $subtotal;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= number_format($item['price'], 2) ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" class="quantity">
                                <input type="submit" name="update_cart" value="Update">
                            </form>
                        </td>
                        <td><?= $item['stock'] ?></td>
                        <td><?= number_format($subtotal, 2) ?></td>
                        <td>
                            <a href="profile.php?delete_cart_id=<?= $item['product_id'] ?>" class="delete-button" onclick="return confirm('Are you sure you want to remove this item?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <th colspan="4" style="text-align:right;">Grand Total:</th>
                        <th colspan="2"><?= number_format($grand_total, 2) ?></th>
                    </tr>
                </table>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>

        <!-- Wishlist Section -->
        <div class="card">
            <h3>Your Wishlist</h3>
            <?php if($wishlist_items): ?>
                <table>
                    <tr><th>Product</th><th>Price</th><th>Action</th></tr>
                    <?php foreach($wishlist_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= number_format($item['price'], 2) ?></td>
                        <td>
                            <a href="profile.php?delete_wishlist_id=<?= $item['product_id'] ?>" class="delete-button" onclick="return confirm('Are you sure you want to remove this item from your wishlist?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>Your wishlist is empty.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
