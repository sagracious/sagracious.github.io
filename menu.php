<?php
session_start();
include 'db_connect.php';

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert predefined products if they do not exist
$products = [
    [1, 'Hot Deals', 'Garlic Cream Cheese Buns - Box of 6 Regular Size Buns', 'gccb1.jpg', 420.00, 'Box of 6'],
    [2, 'Hot Deals', 'Garlic Cream Cheese Buns - Box of 4 Big Size Buns', 'gccb1.jpg', 420.00, 'Box of 4'],
    [3, 'Hot Deals', 'Chocolate Revel Bars - Box of 16 Bars', 'crb2.jpg', 440.00, 'Box of 16'],
    [4, 'Menu', 'Cinnamon Rolls - Box of 4', 'cr3.jpg', 420.00, 'Box of 4'],
    [5, 'Menu', 'Empanadas - Box of 4', 'empa1.jpg', 260.00, 'Box of 4'],
    [6, 'Menu', 'Empanadas - Box of 12', 'empa1.jpg', 780.00, 'Box of 12']
];

$stmt = $conn->prepare("INSERT INTO products (id, category, name, image_url, price, quantity, status, count, username, phone_number, email, address, created_at)
    VALUES (?, ?, ?, ?, ?, ?, 'none', 0, '', '', '', '', NOW())
    ON DUPLICATE KEY UPDATE 
    category=VALUES(category), name=VALUES(name), image_url=VALUES(image_url), 
    price=VALUES(price), quantity=VALUES(quantity)");

foreach ($products as $product) {
    $stmt->bind_param("isssds", ...$product);
    $stmt->execute();
}
$stmt->close();

// Handle Add to Cart request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["product_id"])) {
    $product_id = (int) $_POST["product_id"];
    $username = $_POST['username'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';

    // Securely fetch product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if ($product) {
        // Create a new cart entry with user info
        $insert_stmt = $conn->prepare("INSERT INTO products (category, name, image_url, price, quantity, status, count, username, phone_number, email, address, created_at)
            VALUES (?, ?, ?, ?, ?, 'on cart', 1, ?, ?, ?, ?, NOW())");
        $insert_stmt->bind_param("sssdsssss", $product['category'], $product['name'], $product['image_url'], 
                                $product['price'], $product['quantity'], $username, $phone_number, 
                                $email, $address);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
}

// Count total cart items
$countQuery = $conn->query("SELECT SUM(count) AS cart_count FROM products WHERE status = 'on cart'");
$countRow = $countQuery->fetch_assoc();
$count = $countRow["cart_count"] ?? 0;

// Fetch products from database with status 'none'
$menuResult = $conn->query("SELECT * FROM products WHERE status = 'none'");
$hotDealsResult = $conn->query("SELECT * FROM products WHERE category = 'Hot Deals' AND status = 'none'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tummy Pillow</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <?php include 'header.php'; ?>
        </header>

        <section class="welcome-message">
            <h2>Welcome to Tummy Pillow, where there's comfort in every bite!</h2>
        </section>

        <section class="hot-deals">
            <h2>Hot Deals!</h2>
            <?php 
                while ($deal = $hotDealsResult->fetch_assoc()): 
            ?>
                <div class="deal">
                    <div class="deal-image">
                        <img src="images/<?= htmlspecialchars($deal["image_url"]) ?>" alt="<?= htmlspecialchars($deal["name"]) ?>">
                    </div>
                    <div class="deal-info">
                        <h3><?= htmlspecialchars($deal["name"]) ?></h3>
                        <p><strong><?= htmlspecialchars($deal["quantity"]) ?></strong> - PHP<?= number_format($deal["price"], 2) ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </section>

        <section class="menu">
            <h2>Our Menu</h2>
            <div class="menu-items">
                <?php while ($row = $menuResult->fetch_assoc()): ?>
                    <div class="menu-item">
                        <div class="menu-box">
                            <div class="menu-image">
                                <img src="images/<?= htmlspecialchars($row["image_url"]) ?>" alt="<?= htmlspecialchars($row["name"]) ?>">
                            </div>
                            <p>
                                <strong><?= htmlspecialchars($row["name"]) ?></strong><br>
                                PHP<?= htmlspecialchars(number_format($row["price"], 2)) ?>
                            </p>
                            <form method="post">
                                <input type="hidden" name="product_id" value="<?= $row["id"] ?>">
								
								
                                <button type="submit" class="order-button">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    </div>
</body>
</html>

<?php $conn->close(); ?>