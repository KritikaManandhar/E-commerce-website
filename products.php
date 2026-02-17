<?php
include 'db.php';
session_start();
$customer_id = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : null;
if ($customer_id && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM categories WHERE id=?");
    $stmt->execute([$category_id]);
    if ($stmt->fetchColumn() == 0) {
        echo "<p style='color:red'>Invalid category selected!</p>";
    } else {
        $sql = "INSERT INTO products (name, description, price, stock, category_id) VALUES (?,?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $desc, $price, $stock, $category_id]);
        echo "<p style='color:green'>Product added!</p>";
    }
}
if ($customer_id && isset($_GET['delete_product'])) {
    $product_id = $_GET['delete_product'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->execute([$product_id]);
    if ($customer_id) {
        $stmt = $conn->prepare("DELETE FROM shopping_cart WHERE product_id=? AND customer_id=?");
        $stmt->execute([$product_id, $customer_id]);

        $stmt = $conn->prepare("DELETE FROM wishlist WHERE product_id=? AND customer_id=?");
        $stmt->execute([$product_id, $customer_id]);
    }
    echo "<p style='color:green'>Product deleted successfully!</p>";
}
if ($customer_id && isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity   = (int)$_POST['quantity'];
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id=?");
    $stmt->execute([$product_id]);
    $stock = (int)$stmt->fetchColumn();
    if ($stock > 0) {
        if ($quantity > $stock) {
            $quantity = $stock;
        }
        $stmt = $conn->prepare("SELECT id, quantity FROM shopping_cart WHERE customer_id=? AND product_id=?");
        $stmt->execute([$customer_id, $product_id]);
        $row = $stmt->fetch();
        if ($row) {
            if ($row['quantity'] >= $stock) {
                echo "<p style='color:red'>Exceeding quantity! You already have the maximum available stock ($stock) of this product in your cart.</p>";
            } else {
                $newQty = min($row['quantity'] + $quantity, $stock);
                $update = $conn->prepare("UPDATE shopping_cart SET quantity=? WHERE id=?");
                $update->execute([$newQty, $row['id']]);
                if ($newQty == $stock) {
                    echo "<p style='color:orange'>Cart updated. You now have the maximum stock available for this product.</p>";
                } else {
                    echo "<p style='color:green'>Product quantity updated in cart!</p>";
                }
            }
        } else {
            $insert = $conn->prepare("INSERT INTO shopping_cart (customer_id, product_id, quantity) VALUES (?,?,?)");
            $insert->execute([$customer_id, $product_id, $quantity]);
            echo "<p style='color:green'>Product added to cart!</p>";
        }
    } else {
        echo "<p style='color:red'>Out of stock!</p>";
    }
}
if ($customer_id && isset($_POST['add_to_wishlist'])) {
    $product_id = $_POST['product_id'];
    $stmt = $conn->prepare("SELECT COUNT(*) FROM wishlist WHERE customer_id=? AND product_id=?");
    $stmt->execute([$customer_id, $product_id]);
    if ($stmt->fetchColumn() == 0) {
        $insert = $conn->prepare("INSERT INTO wishlist (customer_id, product_id) VALUES (?,?)");
        $insert->execute([$customer_id, $product_id]);
        echo "<p>Product added to wishlist!</p>";
    } else {
        echo "<p>Already in wishlist!</p>";
    }
} elseif (!$customer_id && isset($_POST['add_to_wishlist'])) {
    echo "<p style='color:red'>Please <a href='login.php'>login</a> to add products to wishlist.</p>";
}
$cats = $conn->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
$products = $conn->query("SELECT p.*, c.name as category FROM products p 
                          LEFT JOIN categories c ON p.category_id=c.id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Products</title>
<style>
body { 
    font-family: Arial, sans-serif; 
    background: #fff; 
    color: #000; 
    margin: 0; 
    padding: 20px 40px; 
}
h1, h2 { 
    text-align: center; 
    margin-bottom: 30px; 
    color: #000;
}
a.button, input[type=submit], button { 
    padding: 8px 16px; 
    background: #000; 
    color: white; 
    text-decoration: none; 
    border: none; 
    border-radius: 5px;
    cursor: pointer; 
}
a.button:hover, input[type=submit]:hover, button:hover { 
    background: #333; 
}
.container { 
    display: flex; 
    flex-direction: column;
    gap: 20px; 
}
.card { 
    background: #f9f9f9; 
    padding: 15px; 
    border: 1px solid #ccc; 
    border-radius: 8px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
}
table { 
    border-collapse: collapse; 
    width: 100%; 
    margin-top: 10px; 
}
th, td { 
    border: 1px solid #ccc; 
    padding: 8px; 
    text-align: left; 
}
th { 
    background: #e0e0e0; 
    color: #000; 
}
tr:hover { 
    background-color: #f1f1f1; 
}
input, select, textarea { 
    padding: 6px; 
    border: 1px solid #ccc; 
    border-radius: 5px;
    margin-bottom: 10px;
    width: 100%;
}
input[type=number] { width: 60px; }
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
    .container { flex-direction: column; }
}
.flex-row {
    display: flex;
    gap: 10px; /* space between inputs */
}
.flex-row input {
    flex: 1; /* each input takes equal width */
}
</style>
</head>
<body>
<div class="container">
    <?php if ($customer_id): ?>
    <div class="card">
        <h2>Add Product</h2>
        <form method="POST">
            <input type="text" name="name" placeholder="Product Name" required>
            <textarea name="description" placeholder="Description"></textarea>
            <div class="flex-row">
                <input type="number" step="0.01" name="price" placeholder="Price" required>
                <input type="number" name="stock" placeholder="Stock" required>
            </div>
            <select name="category_id" required>
                <option value="">Select Category</option>
                <?php foreach($cats as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <input type="submit" name="add_product" value="Add Product">
        </form>
    </div>
    <?php else: ?>
        <p style="color:blue;">Login to add products.</p>
    <?php endif; ?>
    <div class="card">
        <h2>Products</h2>
        <table>
        <tr>
            <th>No.</th><th>Name</th><th>Price</th><th>Stock</th><th>Category</th><th>Action</th>
        </tr>
        <?php 
        $no = 1;
        foreach($products as $p): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $p['name'] ?></td>
            <td><?= $p['price'] ?></td>
            <td><?= $p['stock'] ?></td>
            <td><?= $p['category'] ?></td>
            <td>
                <?php if ($customer_id): ?>
                    <form method="POST" style="display:inline-block;margin:0;">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <input type="number" name="quantity" value="1" min="1" max="<?= $p['stock'] ?>">
                        <button type="submit" name="add_to_cart">Add to Cart</button>
                    </form>
                    <form method="POST" style="display:inline-block;margin:0;">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <button type="submit" name="add_to_wishlist">♡ Wishlist</button>
                    </form>
                    <a href="?delete_product=<?= $p['id'] ?>" class="delete-button" onclick="return confirm('Delete this product permanently?')">🗑 Delete</a>
                <?php else: ?>
                    <button onclick="alert('Please login to add to cart or wishlist');">Add to Cart</button>
                    <button onclick="alert('Please login to add to cart or wishlist');">♡ Wishlist</button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </table>
    </div>
</div>
</body>
</html>
