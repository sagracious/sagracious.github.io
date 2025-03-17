<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';
include 'header.php';

// Fetch only items marked as "on cart"
$query = $conn->query("SELECT * FROM products WHERE count > 0 AND status = 'on cart'");

$cart_items = [];
while ($row = $query->fetch_assoc()) {
    $cart_items[] = $row;
}

// Check if user is logged in
// $userFound = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - Tummy Pillow</title>
    <link rel="stylesheet" href="style.css">
    <script>
    function updateCart(action, id) {
        fetch('update_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=${encodeURIComponent(action)}&id=${encodeURIComponent(id)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update item quantity and subtotal
                if (data.new_quantity !== undefined && document.getElementById(`quantity-${id}`)) {
                    document.getElementById(`quantity-${id}`).textContent = data.new_quantity;
                }

                if (data.subtotal !== undefined && document.getElementById(`subtotal-${id}`)) {
                    document.getElementById(`subtotal-${id}`).textContent = "PHP" + data.subtotal;
                }

                // Update cart total and count
                if (document.getElementById("cart-total")) {
                    document.getElementById("cart-total").textContent = "PHP" + data.grand_total;
                }
                if (document.getElementById("cart-count")) {
                    document.getElementById("cart-count").textContent = data.cart_count;
                }

                // Remove item row if quantity is 0
                if (data.new_quantity === 0 && document.getElementById(`row-${id}`)) {
                    document.getElementById(`row-${id}`).remove();
                }

                // Handle empty cart UI
                if (data.cart_count === 0) {
                    document.querySelector(".cart-table")?.classList.add("hidden");
                    document.querySelector(".cart-actions")?.classList.add("hidden");
                    document.querySelector(".empty-cart")?.classList.remove("hidden");
                }
            } else {
                console.error("Cart update failed:", data.message);
                alert("Failed to update cart: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error updating cart:", error);
            alert("An error occurred while updating the cart. Please try again.");
        });
    }

    function clearCart() {
        fetch('update_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=clear_cart'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById("cart-count").textContent = "0";
                document.getElementById("cart-total").textContent = "PHP0.00";

                // Remove all rows but keep the table structure
                document.querySelector("tbody").innerHTML = "";

                // Keep the table visible
                document.querySelector(".cart-table").style.display = "table";
                document.querySelector(".cart-actions").style.display = "none"; // Hide actions since there's nothing to clear
                document.querySelector(".empty-cart").classList.remove("hidden"); // Show empty cart message
            }
        })
        .catch(error => console.error("Error clearing cart:", error));
    }

    function purchaseCart() {
        fetch('update_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=purchase'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to delivery page
                window.location.href = "delivery.php";
            } else {
                // Handle purchase failure
                alert("Purchase failed: " + (data.message || "Unknown error"));
            }
        })
        .catch(error => {
            console.error("Error processing purchase:", error);
            alert("An error occurred while processing your purchase. Please try again.");
        });
    }
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h2>Your Cart</h2>
        </header>
        <section class="cart-section">
            <?php if (count($cart_items) > 0): ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        foreach ($cart_items as $item):
                            $subtotal = $item["price"] * $item["count"];
                            $total += $subtotal;
                        ?>
                        <tr id="row-<?= $item['id'] ?>">
                            <td><?= htmlspecialchars($item["name"]) ?></td>
                            <td>PHP<?= number_format($item["price"], 2) ?></td>
                            <td>
                                <button class="qty-btn" onclick="updateCart('decrease', <?= $item['id'] ?>)">−</button>
                                <span id="quantity-<?= $item['id'] ?>"><?= (int) $item["count"] ?></span> 
                                <button class="qty-btn" onclick="updateCart('increase', <?= $item['id'] ?>)">+</button>
                            </td>
                            <td id="subtotal-<?= $item['id'] ?>">PHP<?= number_format($subtotal, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="cart-total-label"><strong>Grand Total:</strong></td>
                            <td class="cart-total-value"><strong id="cart-total">PHP<?= number_format($total, 2) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="cart-actions">
                    <button class="clear-cart-button" onclick="clearCart()">Clear Cart</button>
                    <?php if ($_SESSION['userFound']): ?>
                        <button class="checkout-button" onclick="purchaseCart()">Proceed to Checkout</button>
                    <?php else: ?>
                        <button class="checkout-button" onclick="window.location.href='login.php?redirect=cart'">Login</button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="empty-cart">
                    <p>Your cart is empty.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>

<?php
$conn->close();
?>