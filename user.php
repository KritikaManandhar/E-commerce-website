<?php
include 'db.php';
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    $sql = "INSERT INTO customers (name, email, password, address, phone) VALUES (?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$name, $email, $password, $address, $phone]);
    
    header("Location: login.php");
    exit();
}
$sql = "SELECT id, name, email, phone, address FROM customers";
$stmt = $conn->query($sql);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register Users</title>
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
    margin-bottom: 20px; 
}
form, .card { 
    background: #f9f9f9; 
    padding: 20px; 
    border: 1px solid #ccc; 
    border-radius: 8px; 
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    max-width: 500px; 
    margin: 20px auto; 
}
input[type=text], input[type=email], input[type=password] { 
    width: 100%; 
    padding: 8px; 
    margin: 6px 0 12px; 
    border: 1px solid #ccc; 
    border-radius: 5px; 
    white-space: nowrap; 
}

button { 
    padding: 8px 16px; 
    background: #000; 
    color: #fff; 
    border: none; 
    border-radius: 5px; 
    cursor: pointer; 
}
button:hover { 
    background: #333; 
}
table { 
    width: 100%; 
    border-collapse: collapse; 
    margin-top: 20px; 
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
@media (max-width: 900px) {
    body { padding: 10px 20px; }
    form, .card { width: 100%; padding: 15px; }
    table, th, td { font-size: 14px; }
}
</style>
</head>
<body>
<div class="card">
<h2>Register User</h2>
<form method="POST" style="display:flex; flex-direction:column; gap:10px;">
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="text" name="address" placeholder="Address">
    <input type="text" name="phone" placeholder="Phone">
    <button type="submit" name="register">Register</button>
</form>
</div>
<div class="card">
<h2>User List</h2>
<table>
    <tr>
        <th>No.</th>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Address</th>
    </tr>
    <?php 
    $no = 1;
    foreach($users as $u): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($u['name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['phone']) ?></td>
            <td><?= htmlspecialchars($u['address']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>
</div>
</body>
</html>
