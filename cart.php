<?php
include 'db.php';
session_start();
$customer_id = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 1;
if (isset($_POST['add_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $qty = (int)$_POST['quantity'];
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id=?");
    $stmt->execute([$product_id]);
    $stock = (int)$stmt->fetchColumn();
    if ($stock > 0) {
        if ($qty > $stock) $qty = $stock;
        $stmt = $conn->prepare("SELECT quantity FROM shopping_cart WHERE customer_id=? AND product_id=?");
        $stmt->execute([$customer_id, $product_id]);
        $existing = $stmt->fetchColumn();
        if ($existing !== false) {
            $newQty = min($existing + $qty, $stock);
            $stmt = $conn->prepare("UPDATE shopping_cart SET quantity=? WHERE customer_id=? AND product_id=?");
            $stmt->execute([$newQty, $customer_id, $product_id]);
        } else {
            $stmt = $conn->prepare("INSERT INTO shopping_cart (customer_id, product_id, quantity) VALUES (?,?,?)");
            $stmt->execute([$customer_id, $product_id, $qty]);
        }
        header("Location: cart.php");
        exit;
    } else {
        echo "<p style='color:red'>Out of stock!</p>";
    }
}
if (isset($_POST['update_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $qty = (int)$_POST['quantity'];
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id=?");
    $stmt->execute([$product_id]);
    $stock = (int)$stmt->fetchColumn();
    if ($stock > 0) {
        if ($qty > $stock) $qty = $stock;
        if ($qty > 0) {
            $stmt = $conn->prepare("UPDATE shopping_cart SET quantity=? WHERE customer_id=? AND product_id=?");
            $stmt->execute([$qty, $customer_id, $product_id]);
        } else {
            $stmt = $conn->prepare("DELETE FROM shopping_cart WHERE customer_id=? AND product_id=?");
            $stmt->execute([$customer_id, $product_id]);
        }
    } else {
        $stmt = $conn->prepare("DELETE FROM shopping_cart WHERE customer_id=? AND product_id=?");
        $stmt->execute([$customer_id, $product_id]);
    }
    header("Location: cart.php");
    exit;
}
if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM shopping_cart WHERE customer_id=? AND product_id=?");
    $stmt->execute([$customer_id, $product_id]);
    header("Location: cart.php");
    exit;
}
$stmt = $conn->prepare("SELECT sc.*, p.name, p.price, p.stock 
                        FROM shopping_cart sc 
                        JOIN products p ON sc.product_id=p.id 
                        WHERE sc.customer_id=?");
$stmt->execute([$customer_id]);
$cart = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your Cart</title>
<style>
body { 
    font-family: Arial, sans-serif; 
    background: #fff; 
    color: #000; 
    margin: 0; 
    padding: 20px 40px; 
}
h2 { 
    text-align: center; 
    margin-bottom: 30px; 
}
.card { 
    background: #f9f9f9; 
    padding: 20px; 
    border: 1px solid #ccc; 
    border-radius: 8px; 
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
table { 
    width: 100%; 
    border-collapse: collapse; 
}
th, td { 
    border: 1px solid #ccc; 
    padding: 8px; 
    text-align: left; 
}
th { 
    background: #e0e0e0; 
}
tr:hover { 
    background-color: #f1f1f1; 
}
input[type=number] { 
    width: 60px; 
    padding: 4px; 
    border: 1px solid #ccc; 
    border-radius: 4px; 
}
button, input[type=submit], a.button { 
    padding: 6px 12px; 
    background: #000; 
    color: #fff; 
    border: none; 
    border-radius: 5px; 
    cursor: pointer; 
    text-decoration: none;
}
button:hover, input[type=submit]:hover, a.button:hover { 
    background: #333; 
}
.delete-button { 
    padding: 4px 8px; 
    background: #900; 
    color: white; 
    border: none; 
    border-radius: 5px; 
    cursor: pointer; 
    text-decoration: none; 
}
.delete-button:hover { 
    background: #600; 
}
@media (max-width: 900px) {
    body { padding: 10px 20px; }
    table, th, td { font-size: 14px; }
}
</style>
</head>
<body>
<div class="card">
<h2>Your Cart</h2>
<?php if (!empty($cart)): ?>
<table>
<tr>
    <th>Product</th>
    <th>Price</th>
    <th>Quantity</th>
    <th>Total</th>
    <th>Action</th>
</tr>
<?php
$total = 0;
foreach ($cart as $c):
    $subtotal = $c['price'] * $c['quantity'];
    $total += $subtotal;
?>
<tr>
    <td><?= htmlspecialchars($c['name']) ?></td>
    <td><?= number_format($c['price'], 2) ?></td>
    <td>
        <form method="POST" style="margin:0;">
            <input type="hidden" name="product_id" value="<?= $c['product_id'] ?>">
            <input type="number" 
                   name="quantity" 
                   value="<?= $c['quantity'] ?>" 
                   min="1" 
                   max="<?= $c['stock'] ?>">
            <button type="submit" name="update_cart">Update</button>
        </form>
        <?php if ($c['quantity'] >= $c['stock']): ?>
            <p style="color:red; font-size:12px;">Only <?= $c['stock'] ?> in stock!</p>
        <?php endif; ?>
    </td>
    <td><?= number_format($subtotal, 2) ?></td>
    <td>
        <a href="?delete=<?= $c['product_id'] ?>" 
           class="delete-button" 
           onclick="return confirm('Remove this product from cart?')">Delete</a>
    </td>
</tr>
<?php endforeach; ?>
<tr>
    <td colspan="3"><b>Grand Total</b></td>
    <td colspan="2"><b><?= number_format($total, 2) ?></b></td>
</tr>
</table>
<?php else: ?>
<p>Your cart is empty.</p>
<?php endif; ?>
</div>
</body>
</html>
