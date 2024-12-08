<?php
session_start();
include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Product</title>
    <link href="<?php echo $base_url ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $base_url ?>/fontawesome/css/fontawesome.css" rel="stylesheet">
    <link href="<?php echo $base_url ?>/fontawesome/css/brands.css" rel="stylesheet">
    <link href="<?php echo $base_url ?>/fontawesome/css/solid.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
    <?php include 'include/menu.php'; ?>
    <div class="container" style="margin-top: 30px;">
        <h4>Home - Manage Product</h4>
        <div class="row g-5">
            <div class="col-md-8 col-sm-12">
                <form action="<?php echo $base_url; ?>/product-form.php" method="post" enctype="multipart/form-data">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="product_name" class="form-control" value="" required>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label">Price</label>
                            <input type="text" name="price" class="form-control" value="" required>
                        </div>

                        <div class="col-sm-6">
                            <label for="formFile" class="form-label">Image</label>
                            <input type="file" name="profile_image" class="form-control" accept="image/png, image/jpg, image/jpeg">
                        </div>

                        <div class="col-sm-12">
                            <label class="form-label">Detail</label>
                            <textarea name="detail" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <button class="btn btn-primary" type="submit">
                    <i class="fa-solid fa-hippo me-1"></i>Create</button>

                </form>
            </div>
        </div>
    </div>

    <script src="<?php echo $base_url; ?>/assets/js/bootstrap.min.js"></script>
    <hr class="my-4">
</body>
</html>
