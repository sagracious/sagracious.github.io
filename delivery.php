<?php 
session_start();
include 'db_connect.php';

$deliveredProducts = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $deliveryAddress = $conn->real_escape_string($_POST['delivery_address']);

    // Select products that are purchased and have matching users who are online
    $query = $conn->query(" 
        SELECT p.id 
        FROM products p
        JOIN users u ON p.username = u.name
        WHERE p.status = 'purchased' 
            AND u.status = 'online'
    ");

    $productIds = [];
    while ($row = $query->fetch_assoc()) {
        $productIds[] = $row['id'];
    }

    if (!empty($productIds)) {
        $ids = implode(',', array_map('intval', $productIds)); 
        $conn->query("UPDATE products SET status = 'on delivery', address = '$deliveryAddress' WHERE id IN ($ids)");
        $_SESSION['delivered_products'] = $productIds;
    }

    header("Location: delivery.php");
    exit;
}

if (isset($_SESSION['delivered_products'])) {
    $productIds = $_SESSION['delivered_products'];
    unset($_SESSION['delivered_products']);

    if (!empty($productIds)) {
        $ids = implode(',', array_map('intval', $productIds));
        // Add count > 0 condition to the query
        $query = $conn->query("SELECT * FROM products WHERE id IN ($ids) AND count > 0");
        if ($query) {
            while ($row = $query->fetch_assoc()) {
                $deliveredProducts[] = $row;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery - Tummy Pillow</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <?php include 'header.php'; ?>
</header>

<main style="display: flex; justify-content: center; align-items: center; flex-direction: column; text-align: center; padding: 20px;">
    <section class="delivery-intro">
        <h2>Deliver to Your Doorstep, Quick and Easy!</h2>
        <p>Experience seamless delivery with Tummy Pillow. Just enter your address, and we’ll handle the rest!</p>
    </section>

    <section class="delivery-form">
        <form method="POST" action="">
            <label for="delivery_address">Delivery Address:</label>
            <input type="text" id="delivery_address" name="delivery_address" required placeholder="Enter your delivery address">
            
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d965.325771218575!2d121.04394339835835!3d14.581797803306008!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sph!4v1740623693265!5m2!1sen!2sph" 
                width="100%" 
                height="300" 
                style="border: 1px solid #ccc; border-radius: 8px;" 
                allowfullscreen 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>

            <button class="delivery-button" type="submit">Update Delivery Status</button>
        </form>
    </section>

    <?php
    // Update the total price calculation to only include items with quantity > 0
    $totalPrice = 0;
    if (!empty($deliveredProducts)) {
        foreach ($deliveredProducts as $product) {
            if (intval($product['count']) > 0) {
                $totalPrice += floatval($product['price'] * intval($product['count']));
            }
        }
    }
    ?>

    <section class="receipt">
        <?php if (!empty($deliveredProducts)): ?>
            <h2>Official Receipt</h2>
            <p><strong>Company Name:</strong> Tummy Pillow</p>
            <p><strong>Date:</strong> <?php echo date('l, F j, Y'); ?></p>
            <h3>Delivered Items:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($deliveredProducts as $product): ?>
                        <?php if (intval($product['count']) > 0): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['count']); ?></td>
                                <td>PHP <?php echo htmlspecialchars(number_format($product['price'], 2)); ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h4><strong>Total Price: PHP <?php echo htmlspecialchars(number_format($totalPrice, 2)); ?></strong></h4>
            <p><strong>Thank you for your purchase!</strong></p>
        <?php else: ?>
            <p>No items were delivered at this time.</p>
        <?php endif; ?>
    </section>
	<section class="directory-links" style="margin-top: 20px;">
    <a href="order_management.php" class="directory-button" style="display: inline-block; padding: 10px 20px; background-color: #FF5722; color: white; text-decoration: none; border-radius: 8px; font-weight: bold; transition: background 0.3s ease;">
        Order Management
    </a>
</section>
</main>

</body>
</html>
