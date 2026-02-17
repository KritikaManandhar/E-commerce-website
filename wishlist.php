<?php
include 'db.php';
session_start();

$customer_id = 1; 
if (isset($_GET['delete_wishlist'])) {
    $wishlist_id = $_GET['delete_wishlist'];
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE id=? AND customer_id=?");
    $stmt->execute([$wishlist_id, $customer_id]);
    echo "<p style='color:red'>Removed from wishlist!</p>";
}
$sql = "SELECT w.id as wishlist_id, p.id as product_id, p.name, p.price 
        FROM wishlist w 
        JOIN products p ON w.product_id = p.id 
        WHERE w.customer_id=?";
$stmt = $conn->prepare($sql);
$stmt->execute([$customer_id]);
$wishlist = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your Wishlist</title>
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
a.button, .delete-button { 
    padding: 6px 12px; 
    background: #000; 
    color: #fff; 
    border: none; 
    border-radius: 5px; 
    cursor: pointer; 
    text-decoration: none;
}
a.button:hover, .delete-button:hover { 
    background: #333; 
}
.delete-button { 
    background: #900; 
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
<h2>Your Wishlist</h2>
<?php if (!empty($wishlist)): ?>
<table>
    <tr>
        <th>Product</th>
        <th>Price</th>
        <th>Action</th>
    </tr>
    <?php foreach($wishlist as $w): ?>
    <tr>
        <td><?= htmlspecialchars($w['name']) ?></td>
        <td><?= number_format($w['price'], 2) ?></td>
        <td>
            <a href="?delete_wishlist=<?= $w['wishlist_id'] ?>" class="delete-button" onclick="return confirm('Remove from wishlist?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
    <p>Your wishlist is empty.</p>
<?php endif; ?>
</div>
</body>
</html>
