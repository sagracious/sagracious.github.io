<?php
header("Content-Type: application/json");

include 'db_connect.php';

// Check for connection errors
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Fetch available products
$sql = "SELECT * FROM products WHERE status = 'none'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = [
            "product_id" => $row["id"],
            "category" => $row["category"],
            "name" => $row["name"],
            "image_url" => $row["image_url"],
            "price" => $row["price"],
            "quantity" => $row["quantity"],
            "status" => $row["status"],
        ];
    }
    echo json_encode(["success" => true, "data" => $products]);
} else {
    echo json_encode(["success" => false, "message" => "No products found."]);
}

$conn->close();
?>