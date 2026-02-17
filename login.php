<?php
session_start();
include 'db.php';

// Check if user is already logged in
if (isset($_SESSION['customer_id'])) {
    header("Location: index.php"); // Redirect to home page
    exit;
}

// Handle login form submission
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch user by email
    $stmt = $conn->prepare("SELECT id, password, name FROM customers WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Password matches, log in the user
        $_SESSION['customer_id'] = $user['id'];
        $_SESSION['customer_name'] = $user['name'];
        header("Location: index.php"); // Redirect to home page
        exit;
    } else {
        $error = "Invalid email or password!";
    }
}
?>

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
    color: #000;
}

form {
    max-width: 400px;
    margin: 0 auto;
    background: #f9f9f9;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    gap: 15px;
}

input[type=email], input[type=password] {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
}

button {
    padding: 10px 16px;
    background: #000;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    background: #333;
}

p {
    text-align: center;
}

a {
    color: #000;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
    color: #333;
}
</style>

<h2>Login</h2>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="login">Login</button>
</form>

<p>Don't have an account? <a href="user.php">Register here</a></p>

