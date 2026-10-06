<?php
session_start();
include('config/config.php');
include('config/checklogin.php');

check_login();

if (isset($_GET['delete'])) {
  $delete_id = $_GET['delete'];

  // Optional: Delete product image from server
  $query_img = "SELECT prod_img FROM rpos_products WHERE prod_id = ?";
  $stmt_img = $mysqli->prepare($query_img);
  $stmt_img->bind_param('s', $delete_id);
  $stmt_img->execute();
  $result_img = $stmt_img->get_result();
  $img_data = $result_img->fetch_assoc();

  if ($img_data && !empty($img_data['prod_img'])) {
    $img_path = "assets/img/products/" . $img_data['prod_img'];
    if (file_exists($img_path)) {
      unlink($img_path); // Remove image
    }
  }

  // Delete the product
  $query = "DELETE FROM rpos_products WHERE prod_id = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->bind_param('s', $delete_id);
  $stmt->execute();

  if ($stmt) {
    $success = "Product Deleted Successfully";
    header("refresh:1; url=products.php");
  } else {
    $err = "Failed to delete product. Please try again.";
  }
}

require_once('partials/_head.php');
?>

<body>
  <!-- Sidebar -->
  <?php require_once('partials/_sidebar.php'); ?>
  <div class="main-content">
    <!-- Top navbar -->
    <?php require_once('partials/_topnav.php'); ?>

    <!-- Main content area -->
    <div class="container-fluid mt--8">
      <div class="row justify-content-center">
        <div class="col-md-8 mt-5">

          <?php if (isset($success)) : ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <strong>Success!</strong> <?php echo $success; ?>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          <?php endif; ?>

          <?php if (isset($err)) : ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <strong>Error!</strong> <?php echo $err; ?>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>

    <!-- Footer -->
    <?php require_once('partials/_footer.php'); ?>
  </div>

  <!-- Scripts -->
  <?php require_once('partials/_scripts.php'); ?>
</body>
</html>
