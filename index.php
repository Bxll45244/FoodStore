<?php
session_start();
include 'config.php';

// Initialize result array
$result = [
    'id' => '',
    'product_name' => '',
    'price' => '',
    'detail' => '',
    'profile_image' => ''
];

// Add or update product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $detail = $_POST['detail'];
    $image_name = '';

    // Check if an image is uploaded
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "images/";
        $image_name = basename($_FILES['profile_image']['name']);
        $target_file = $target_dir . $image_name;

        // Attempt to upload the image
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            // Image uploaded successfully
        } else {
            $_SESSION['message'] = 'Image upload failed';
            header('Location: index.php');
            exit();
        }
    }

    // Check if we are updating an existing product or adding a new one
    if (!empty($_POST['id'])) {
        $id = $_POST['id'];
        $query = "UPDATE products SET 
            product_name='$product_name', 
            price='$price', 
            detail='$detail', 
            profile_image='$image_name' 
            WHERE id='$id'";
    } else {
        // Insert a new product
        $query = "INSERT INTO products (product_name, price, detail, profile_image) VALUES 
            ('$product_name', '$price', '$detail', '$image_name')";
    }

    // Execute the query
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = 'Product saved successfully';
    } else {
        $_SESSION['message'] = 'Error: ' . mysqli_error($conn);
    }
    header('Location: index.php');
    exit();
}

// Delete product
if (!empty($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Get product details to delete the image if necessary
    $query_delete = mysqli_query($conn, "SELECT profile_image FROM products WHERE id='$delete_id'");
    $data = mysqli_fetch_assoc($query_delete);
    if (!empty($data['profile_image']) && file_exists("images/" . $data['profile_image'])) {
        unlink("images/" . $data['profile_image']);
    }

    // Delete the product from the database
    mysqli_query($conn, "DELETE FROM products WHERE id='$delete_id'");
    $_SESSION['message'] = 'Product deleted successfully';
    header('Location: index.php');
    exit();
}

// Edit product (for pre-filling the form)
if (!empty($_GET['id'])) {
    $id = $_GET['id'];
    $query_product = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
    $result = mysqli_fetch_assoc($query_product);
}

// Fetch all products for display
$query = mysqli_query($conn, "SELECT * FROM products");

// Fetch statistics: total number of products and total price
$query_stats = mysqli_query($conn, "SELECT COUNT(*) AS total_products, SUM(price) AS total_price, AVG(price) AS avg_price FROM products");
$stats = mysqli_fetch_assoc($query_stats);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #121212;
            color: #f0f0f0;
            font-family: 'Arial', sans-serif;
        }

        .container {
            background-color: #1e1e1e;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);
            border: 1px solid #333;
        }

        .form-control, .table {
            background-color: #333;
            border: 1px solid #555;
            color: #f0f0f0;
        }

        .table th, .table td {
            border-color: #444;
        }

        .table th {
            background-color: #222;
        }

        .table-hover tbody tr:hover {
            background-color: #444;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            font-size: 14px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }

        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
        }

        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
        }

        .stat-card {
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            padding: 20px;
            background-color: #2c2c2c;
            text-align: center;
        }

        .stat-card h5 {
            font-size: 24px;
            margin-bottom: 15px;
        }

        .stat-card p {
            font-size: 22px;
            color: #ccc;
        }

        .summary-card {
            background-color: #2c2c2c;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .summary-card h5 {
            font-size: 24px;
            margin-bottom: 15px;
        }

        .summary-card p {
            font-size: 22px;
            color: #ccc;
        }

        footer {
            background-color: #121212;
            padding: 20px;
            color: #f0f0f0;
        }

        .img-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }

        .alert {
            background-color: #333;
            color: #f0f0f0;
        }

        .alert-success {
            background-color: #28a745;
            color: white;
        }

        .alert-error {
            background-color: #dc3545;
            color: white;
        }

        .text-muted {
            color: #aaa;
        }
    </style>
</head>

<body>

    <div class="container mt-5">

        <!-- Display success or error message -->
        <?php if (!empty($_SESSION['message'])) : ?>
            <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <!-- Title of the page -->
        <h2 class="mb-4 text-center">Manage Products</h2>

        <!-- Product Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <h5>Total Products</h5>
                    <p><?php echo $stats['total_products']; ?> products</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <h5>Total Price</h5>
                    <p><?php echo number_format($stats['total_price'], 2); ?> THB</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <h5>Average Price</h5>
                    <p><?php echo number_format($stats['avg_price'], 2); ?> THB</p>
                </div>
            </div>
        </div>

        <!-- Product Form: Add/Edit Product -->
        <form method="POST" enctype="multipart/form-data" class="mb-5 p-4 bg-dark rounded">
            <input type="hidden" name="id" value="<?php echo $result['id']; ?>">
            <div class="mb-3">
                <label>Product Name</label>
                <input type="text" name="product_name" class="form-control" value="<?php echo $result['product_name']; ?>" required>
            </div>
            <div class="mb-3">
                <label>Price</label>
                <input type="number" name="price" class="form-control" value="<?php echo $result['price']; ?>" required>
            </div>
            <div class="mb-3">
                <label>Detail</label>
                <textarea name="detail" class="form-control" required><?php echo $result['detail']; ?></textarea>
            </div>
            <div class="mb-3">
                <label>Image</label>
                <input type="file" name="profile_image" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Save Product</button>
        </form>

        <!-- Table displaying all products -->
        <table class="table table-bordered table-hover table-striped text-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($product = mysqli_fetch_assoc($query)) : ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td>
                            <img src="images/<?php echo $product['profile_image']; ?>" class="img-preview" alt="Product Image">
                        </td>
                        <td><?php echo $product['product_name']; ?></td>
                        <td><?php echo number_format($product['price'], 2); ?> THB</td>
                        <td>
                            <a href="index.php?id=<?php echo $product['id']; ?>" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
                            <a href="index.php?delete_id=<?php echo $product['id']; ?>" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </div>

    <!-- Footer -->
    <footer class="text-center">
        <p>&copy; 2024 Product Management System. All rights reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
