<?php
include 'db_connect.php';

$dateFilter = '';
$totalRevenue = 0;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['report_date']) && !empty($_GET['report_date'])) {
        $reportDate = $conn->real_escape_string($_GET['report_date']);
        $dateFilter = " WHERE delivery_date = '$reportDate' AND status = 'delivered'";
    } else {
        $dateFilter = " WHERE status = 'delivered'";
    }
}

$salesSql = "SELECT name, SUM(count) as total_count, SUM(price * count) as total_revenue FROM products" . $dateFilter . " GROUP BY name";
$salesResult = $conn->query($salesSql);

if ($salesResult) {
    while ($row = $salesResult->fetch_assoc()) {
        $totalRevenue += $row['total_revenue'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            background: #fffaf0;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #FF5722;
        }
        .filter-section {
            text-align: center;
            margin-bottom: 20px;
        }
        .filter-section input, .filter-section button {
            padding: 10px;
            border: 2px solid #FF9800;
            border-radius: 8px;
            outline: none;
        }
        .filter-section button {
            background-color: #FF5722;
            color: white;
            cursor: pointer;
        }
        .filter-section button:hover {
            background-color: #E64A19;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #FFCCBC;
        }
        th {
            background: #FF5722;
            color: white;
        }
        .total-revenue {
            text-align: right;
            margin-top: 20px;
            font-size: 1.5rem;
            color: #FF5722;
        }
    </style>
</head>
<body>
    <h1>Sales Report</h1>
    
    <div class="filter-section">
        <form method="get" action="">
            <input type="date" name="report_date">
            <button type="submit">Filter by Date</button>
            <button type="submit" name="show_all">Show All</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Total Sold</th>
                <th>Total Revenue</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($salesResult && $salesResult->num_rows > 0): ?>
                <?php mysqli_data_seek($salesResult, 0); ?>
                <?php while ($row = $salesResult->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['total_count']; ?></td>
                        <td><?php echo number_format($row['total_revenue'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3" style="text-align: center;">No sales data found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="total-revenue">
        Total Revenue: PHP<?php echo number_format($totalRevenue, 2); ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>
