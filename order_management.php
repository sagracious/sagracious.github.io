<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ids'])) {
    $ids = $_POST['ids'];
    if (isset($_POST['action_delivery'])) {
        $status = 'on delivery';
    } elseif (isset($_POST['action_delivered'])) {
        $status = 'delivered';
    }

    if (!empty($status)) {
        $idList = implode(',', array_map('intval', $ids));
        $updateSql = "UPDATE products SET status = '$status' WHERE id IN ($idList)";
        $conn->query($updateSql);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['one_action_delivery'])) {
    $id = intval($_POST['one_action_delivery']);
    $conn->query("UPDATE products SET status = 'on delivery' WHERE id = $id");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['one_action_delivered'])) {
    $id = intval($_POST['one_action_delivered']);
    $conn->query("UPDATE products SET status = 'delivered' WHERE id = $id");
}

$filter = '';
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filterQuery = " WHERE status != 'none'";
    if (isset($_GET['filter_field']) && isset($_GET['filter_value']) && !empty($_GET['filter_value'])) {
        $filterField = $conn->real_escape_string($_GET['filter_field']);
        $filterValue = $conn->real_escape_string($_GET['filter_value']);
        $filterQuery .= " AND $filterField LIKE '%$filterValue%'";
        $filter = $filterValue;
    }
    
    $sql = "SELECT id, username, phone_number, address, count, price, status FROM products $filterQuery";
    $result = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: #fffaf0;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #FF5722;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        .filter-section {
            text-align: center;
            margin-bottom: 20px;
        }
        .filter-section select, .filter-section input, .filter-section button {
            padding: 10px;
            border: 2px solid #FF9800;
            border-radius: 8px;
            outline: none;
        }
        .filter-section button {
            background-color: #FF5722;
            color: white;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .filter-section button:hover {
            background-color: #E64A19;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #FFCCBC;
        }
        th {
            background: #FF5722;
            color: white;
        }
        .status {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: bold;
        }
        .status.on-delivery {
            background: #FF9800;
            color: #fff;
        }
        .status.delivered {
            background: #4CAF50;
            color: #fff;
        }
        .btn-delivery, .btn-delivered {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .btn-delivery {
            background: #FF9800;
            color: white;
        }
        .btn-delivery:hover {
            background: #FB8C00;
        }
        .btn-delivered {
            background: #4CAF50;
            color: white;
        }
        .btn-delivered:hover {
            background: #43A047;
        }
        .bulk-update {
            text-align: center;
            margin-top: 20px;
        }
		.btn-report {
    background: #2196F3;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s ease;
}
.btn-report:hover {
    background: #1976D2;
}
    </style>
</head>
<body>
    <h1>Order Management</h1>
    
    <div class="filter-section">
        <form method="get" action="">
            <select name="filter_field">
                <option value="username">Username</option>
                <option value="address">Address</option>
				<option value="phone_number">Phone Number</option>
            </select>
            <input type="text" name="filter_value" placeholder="Enter filter value" value="<?php echo $filter; ?>">
            <button type="submit">Filter</button>
        </form>
    </div>

    <form method="post" action="">
        <table id="orderTable">
            <thead>
                <tr>
                    <th>Select</th>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Address</th>
					<th>Phone Number</th>
                    <th>Count</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="<?php echo $row['id']; ?>"></td>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['address']; ?></td>
							<td><?php echo $row['phone_number']; ?></td>
                            <td><?php echo $row['count']; ?></td>
                            <td><?php echo "PHP" . number_format($row['price'], 2); ?></td>
                            <td class="status <?php echo str_replace(' ', '-', strtolower($row['status'])); ?>">
                                <?php echo $row['status']; ?>
                            </td>
                            <td>
                                <button type="submit" class="btn-delivery" name="one_action_delivery" value="<?php echo $row['id']; ?>">On Delivery</button>
                                <button type="submit" class="btn-delivered" name="one_action_delivered" value="<?php echo $row['id']; ?>">Delivered</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="10" style="text-align: center;">No orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
		  <div class="bulk-update">
        <button type="submit" class="btn-delivery" name="action_delivery">Set as On Delivery</button>
        <button type="submit" class="btn-delivered" name="action_delivered">Set as Delivered</button>
		<button type="button" class="btn-report" onclick="window.location.href='sales_report.php'">Sales Report</button>
        </div>
    </form>
	
</body>
</html>

<?php $conn->close(); ?>
