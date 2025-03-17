<?php 
session_start();
?>
<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Tummy Pillow</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <?php include 'header.php'; ?>
        </header>
        
        <section class="register-section">
            <div class="register-container">
                <h2>Register</h2>
                
                <?php
                if (isset($_SESSION['message'])) {
                    echo "<div class='notification' style='display:block;'>" . $_SESSION['message'] . "</div>";
                    unset($_SESSION['message']);
                }
                ?>

                <?php
                if ($_SERVER["REQUEST_METHOD"] === "POST") {
                    include 'db_connect.php'; 

                    function getMacAddress() {
                        ob_start();
                        system('ipconfig /all');
                        $content = ob_get_clean();
                        preg_match('/Physical Address[ .]+: ([\w-]+)/', $content, $macMatch);
                        return isset($macMatch[1]) ? strtoupper(str_replace('-', ':', $macMatch[1])) : '00:00:00:00:00:00';
                    }

                    $name = trim(htmlspecialchars($_POST['name']));
                    $number = trim(htmlspecialchars($_POST['number']));
                    $email = trim(htmlspecialchars($_POST['email']));
                    $address = trim(htmlspecialchars($_POST['address']));
                    $password = $_POST['password'];
                    $status = 'offline';
                    $mac_address = getMacAddress();

                    // Validate name (only letters and spaces)
                    if (!preg_match("/^[a-zA-Z ]+$/", $name)) {
                        $_SESSION['message'] = 'Invalid name! Only letters and spaces are allowed.';
                        header("Location: /register.php");
                        exit();
                    }

                    // Validate Philippine phone number
                    if (!preg_match('/^(09\d{9}|\+639\d{9}|\(0\d{2}\)\d{7})$/', $number)) {
                        $_SESSION['message'] = 'Invalid Philippine phone number format!';
                        header("Location: /register.php");
                        exit();
                    }
                    
                    // Validate email format
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $_SESSION['message'] = 'Invalid email format!';
                        header("Location: /register.php");
                        exit();
                    }
                    
                    // Validate password (at least 8 chars, 1 letter, 1 number, 1 special char)
                    if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
                        $_SESSION['message'] = 'Password must be at least 8 characters long, include a letter, a number, and a special character.';
                        header("Location: /register.php");
                        exit();
                    }
                    
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Check if email or mac_address already exists
                    $check_query = "SELECT id FROM users WHERE email = ? OR (mac_address IS NOT NULL AND mac_address = ? AND status = 'online')";
                    $stmt = $conn->prepare($check_query);
                    $stmt->bind_param("ss", $email, $mac_address);
                    $stmt->execute();
                    $stmt->store_result();
                    
                    if ($stmt->num_rows > 0) {
                        $_SESSION['message'] = 'Email already registered or device is currently in use!';
                    } else {
                        $stmt = $conn->prepare("INSERT INTO users (name, phone_number, email, address, mac_address, password_hash, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                        $stmt->bind_param("sssssss", $name, $number, $email, $address, $mac_address, $password_hash, $status);
                        
                        if ($stmt->execute()) {
                            $_SESSION['message'] = 'Registration successful!';
                            header("Location: login.php");
                            exit();
                        } else {
                            $_SESSION['message'] = 'Error: ' . $stmt->error;
                        }
                    }
                    
                    $stmt->close();
                    $conn->close();
                    header("Location: /register.php");
                    exit();
                }
                ?>
                
                <form action="register.php" method="POST">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                    
                    <label for="number">Phone number:</label>
                    <input type="text" id="number" name="number" required>
                    
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                    
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" required>
                    
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                    
                    <button type="submit" class="register-button">Register</button>
                </form>
                
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </section>
    </div>
</body>
</html>

