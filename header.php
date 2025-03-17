<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db_connect.php';

// Get updated cart count only for items with status "on cart"
$countQuery = $conn->query("SELECT SUM(count) AS cart_count FROM products WHERE status = 'on cart'");
$countRow = $countQuery->fetch_assoc();
$count = $countRow["cart_count"] ?? 0;

?>

<div class="container">
    <header>
        <div class="logo-container">
            <a href="index.php">
                <img src="images/logo.png" alt="Tummy Pillow Logo">
            </a>
        </div>
        <nav>
            <a href="who-we-are.php">Who We Are</a>
            <a href="menu.php">Menu</a>
            <a href="profile.php">Profile</a>
            <a href="cart.php">
                <button class="cart-button">Cart (<span id="cart-count"><?= $count ?></span>)</button>
            </a>
        </nav>
    </header>
    <section class="hero">
        <img src="images/gccb2.jpg" alt="Bread and Flowers">
    </section>
</div>