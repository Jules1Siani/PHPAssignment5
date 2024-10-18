<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require('../model/database.php');

$productCode = $name = $version = $releaseDate = "";
$error = "";

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Secure inputs with filter_input()
    $productCode = filter_input(INPUT_POST, 'productCode', FILTER_SANITIZE_STRING);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $version = filter_input(INPUT_POST, 'version', FILTER_VALIDATE_FLOAT);
    $releaseDate = filter_input(INPUT_POST, 'releaseDate', FILTER_SANITIZE_STRING);

    try {
        // Validate that all fields are filled
        if (empty($productCode) || empty($name) || empty($version) || empty($releaseDate)) {
            throw new TypeError("All fields are required."); // Throw a TypeError if any field is empty
        }

        // Validate release date format
        $date = DateTime::createFromFormat('Y-m-d', $releaseDate);
        if (!$date) {
            throw new TypeError("Invalid date format. Please use 'YYYY-MM-DD'."); // Throw a TypeError if the date format is invalid
        }

        // Using prepared statements to avoid SQL injection
        $sql = "INSERT INTO products (productCode, name, version, releaseDate) 
                VALUES (:productCode, :name, :version, :releaseDate)";
        $statement = $db->prepare($sql);
        $statement->bindValue(':productCode', $productCode);
        $statement->bindValue(':name', $name);
        $statement->bindValue(':version', $version);
        $statement->bindValue(':releaseDate', $releaseDate);

        // Execute the query
        if ($statement->execute()) {
            header('Location: view_product.php?success=Product added successfully'); // Redirect on success
            exit();
        } else {
            throw new TypeError("Error adding product."); // Throw a TypeError if execution fails
        }
    } catch (TypeError $e) {
        // Set error message and include the error page
        $error = $e->getMessage(); // Get the error message from the exception
        include('../view/error.php'); // Include the error page to display the message
        exit();
    }
}
?>

<?php include '../view/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add a Product</title>
    <link rel="stylesheet" type="text/css" href="css/main.css" />
</head>
<body>
    <h1>Add a Product</h1>

    <!-- Show error messages-->
    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p> <!-- Display error message if exists -->
    <?php endif; ?>

    <form action="add_product.php" method="POST">
        <label for="productCode">Code:</label><br>
        <input type="text" id="productCode" name="productCode" value="<?php echo htmlspecialchars($productCode); ?>"><br>

        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>"><br>

        <label for="version">Version:</label><br>
        <input type="text" id="version" name="version" value="<?php echo htmlspecialchars($version); ?>"><br>

        <label for="releaseDate">Release Date:</label><br>
        <input type="text" id="releaseDate" name="releaseDate" value="<?php echo htmlspecialchars($releaseDate); ?>"><br><br>

        <input type="submit" value="Add Product"> <!-- Submit button to add product -->
    </form>

    <br>
    <a href="view_product.php">Product List</a> <!-- Link to view the product list -->
</body>
</html>

<?php include '../view/footer.php'; ?>
