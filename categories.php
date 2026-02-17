<?php
session_start();
include 'db.php';
if (isset($_SESSION['customer_id']) && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $parent_id = $_POST['parent_id'] ?: NULL;
    $check = $conn->prepare("SELECT COUNT(*) FROM categories WHERE name=?");
    $check->execute([$name]);
    if ($check->fetchColumn() > 0) {
        echo "<p style='color:red'>Category already exists!</p>";
    } else {
        $sql = "INSERT INTO categories (name, parent_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $parent_id]);
        echo "<p style='color:green'>Category added!</p>";
    }
}
function deleteCategoryRecursive($conn, $category_id) {
    $stmt = $conn->prepare("SELECT id FROM categories WHERE parent_id=?");
    $stmt->execute([$category_id]);
    $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($subcategories as $subcat) {
        deleteCategoryRecursive($conn, $subcat['id']);
    }
    $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
    $stmt->execute([$category_id]);
}
if (isset($_SESSION['customer_id']) && isset($_GET['delete'])) {
    $id = $_GET['delete'];
    deleteCategoryRecursive($conn, $id);
    echo "<p style='color:red'>Category and its subcategories deleted!</p>";
}
$sql = "SELECT * FROM categories ORDER BY id ASC";
$stmt = $conn->query($sql);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
function displayCategoriesTableNumbered($categories, $parent_id = NULL, $prefix = '', $depth = 0) {
    $counter = 1;
    foreach ($categories as $category) {
        if ($category['parent_id'] == $parent_id) {
            $current_no = $prefix ? $prefix . '.' . $counter : $counter;
            $indent = str_repeat('--', $depth);
            echo "<tr>
                    <td>{$current_no}</td>
                    <td>{$indent}{$category['name']}</td>
                    <td>";
            if (isset($_SESSION['customer_id'])) {
                echo "<a href='?delete={$category['id']}' class='delete-button' onclick=\"return confirm('Are you sure you want to delete this category?')\">Delete</a>";
            } else {
                echo "-";
            }
            echo "</td>
                  </tr>";
            displayCategoriesTableNumbered($categories, $category['id'], $current_no, $depth + 1);
            $counter++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Categories</title>
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
a.button, input[type=submit] { 
    padding: 8px 16px; 
    background: #000; 
    color: white; 
    text-decoration: none; 
    border: none; 
    border-radius: 5px;
    cursor: pointer; 
}
a.button:hover, input[type=submit]:hover { 
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
input, select { 
    padding: 6px; 
    border: 1px solid #ccc; 
    border-radius: 5px;
    margin-bottom: 10px;
    width: 100%;
}
@media (max-width: 900px) {
    .container { flex-direction: column; }
}
</style>
</head>
<body>
<div class="container">
    <div class="card">
        <h2>Add Category</h2>
        <?php if (isset($_SESSION['customer_id'])): ?>
        <form method="POST">
            <input type="text" name="name" placeholder="Category Name" required>
            <select name="parent_id">
                <option value="">-- No Parent (Top-level) --</option>
                <?php
                function displayCategoryOptions($categories, $parent_id = NULL, $prefix = '') {
                    foreach ($categories as $cat) {
                        if ($cat['parent_id'] == $parent_id) {
                            echo "<option value='{$cat['id']}'>{$prefix}{$cat['name']}</option>";
                            displayCategoryOptions($categories, $cat['id'], $prefix . '--');
                        }
                    }
                }
                displayCategoryOptions($categories);
                ?>
            </select>
            <input type="submit" name="add_category" value="Add Category">
        </form>
        <?php else: ?>
            <p style="color:blue;">Please <a href="login.php">login</a> to add categories.</p>
        <?php endif; ?>
    </div>
    <div class="card">
        <h2>Category List</h2>
        <table>
            <tr>
                <th>No.</th>
                <th>Category Name</th>
                <th>Action</th>
            </tr>
            <?php displayCategoriesTableNumbered($categories); ?>
        </table>
    </div>
</div>
</body>
</html>
