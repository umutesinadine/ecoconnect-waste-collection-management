<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();

require_once('partials/_head.php');
require_once('partials/_analytics.php');
?>
<?php
// Get total money spent from rpos_orders
$customer_id = $_SESSION['customer_id']; // Make sure this session is set

$query = "SELECT SUM(prod_price) AS total_spent FROM rpos_orders WHERE customer_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $customer_id); // Use customer_id instead of customer_name
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$total_spent = $data['total_spent'] ?? 0; // Default to 0 if null
?>
<!-- For more projects: Visit NetGO+  -->
<body>
  <!-- Sidenav -->
  <?php
  require_once('partials/_sidebar.php');
  ?>
  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <?php
    require_once('partials/_topnav.php');
    ?>
    <!-- Header -->
    <div style="background-image: url(../admin/assets/img/theme/robot.jpg); background-size: cover;" class="header  pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
          <!-- Card stats -->
          <div class="row">
            <div class="col-xl-4 col-lg-6">
              <a href="orders.php">
                <div class="card card-stats mb-4 mb-xl-0">
                  <div class="card-body">
                    <div class="row">
                      <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Available Services</h5>
                        <span class="h2 font-weight-bold mb-0"><?php echo $products; ?></span>
                      </div><!-- For more projects: Visit NetGO+  -->
                      <div class="col-auto">
                        <div class="icon icon-shape bg-primary text-white rounded-circle shadow">
                        <i class="fas fa-recycle"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </a>
            </div><!-- For more projects: Visit NetGO+  -->
            <div class="col-xl-4 col-lg-6">
              <a href="orders_reports.php">
                <div class="card card-stats mb-4 mb-xl-0">
                  <div class="card-body">
                    <div class="row">
                      <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Requested a Service</h5>
                        <span class="h2 font-weight-bold mb-0"><?php echo $orders; ?> Times</span>
                      </div>
                      <div class="col-auto">
                        <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                          <i class="fas fa-shopping-cart"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </a><!-- For more projects: Visit NetGO+  -->
            </div>
            <div class="col-xl-4 col-lg-6">
              <a href="payments_reports.php">
                <div class="card card-stats mb-4 mb-xl-0">
                  <div class="card-body">
                    <div class="row">
                      <div class="col">
                        <h5 class="card-title text-uppercase text-muted mb-0">Total Amount Spent</h5>
                        <span class="h2 font-weight-bold mb-0"><span class="h2 font-weight-bold mb-0">
                                                                    <?php echo number_format($total_spent, 0); ?> RWF
                                                              </span>

                      </div>
                      <div class="col-auto">
                        <div class="icon icon-shape bg-green text-white rounded-circle shadow">
                          <i class="fas fa-wallet"></i>
                        </div>
                      </div><!-- For more projects: Visit NetGO+  -->
                    </div>
                  </div>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div><!-- For more projects: Visit NetGO+  -->
    </div>
    <!-- Page content -->
    <div class="container-fluid mt--7">
      <div class="row mt-5">
        <div class="col-xl-12 mb-5 mb-xl-0">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">Recent Requests</h3>
                </div>
                <div class="col text-right">
                  <a href="orders_reports.php" class="btn btn-sm btn-primary">See all</a>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <!-- Projects table -->
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr><!-- For more projects: Visit NetGO+  -->
                    <th class="text-success" scope="col">Request Code</th>
                    <th scope="col">You</th>
                    <th class="text-success" scope="col">Requested</th>
                    <th scope="col">Price</th>
                    <th scop="col">Status</th>
                    <th class="text-success" scope="col">Requested At</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $customer_id = $_SESSION['customer_id'];
                  $ret = "SELECT * FROM  rpos_orders WHERE customer_id = '$customer_id' ORDER BY `rpos_orders`.`created_at` DESC LIMIT 10 ";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($order = $res->fetch_object()) {
                    $total = ($order->prod_qty);

                  ?>
                    <tr>
                      <th class="text-success" scope="row"><?php echo $order->order_code; ?></th>
                      <td><?php echo $order->customer_name; ?></td>
                      <td class="text-success"><?php echo $order->prod_name; ?></td>
                      <td><?php echo $order->prod_price; ?>Rwf</td>
                      <td><?php if ($order->order_status == '') {
                            echo "<span class='badge badge-danger'>Pending</span>";
                          } else {
                            echo "<span class='badge badge-success'>$order->order_status</span>";
                          } ?></td>
                      <td class="text-success"><?php echo date('d/M/Y g:i', strtotime($order->created_at)); ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
		<!-- For more projects: Visit NetGO+  -->
      <div class="row mt-5">
        <div class="col-xl-12">
          <div class="card shadow">
            <div class="card-header border-0">
              <div class="row align-items-center">
                <div class="col">
                  <h3 class="mb-0">Accepted Requests</h3>
                </div>
                <div class="col text-right">
                  <a href="payments_reports.php" class="btn btn-sm btn-primary">See all</a>
                </div>
              </div>
            </div>
            <div class="table-responsive">
              <!-- Projects table -->
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th class="text-success" scope="col">Phone</th>
                    <th scope="col">Prefered Pickup Date</th>
                    <th class='text-success' scope="col">Request Code</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $ret = "SELECT * FROM   rpos_payments WHERE customer_id ='$customer_id'   ORDER BY `rpos_payments`.`created_at` DESC LIMIT 10 ";
                  $stmt = $mysqli->prepare($ret);
                  $stmt->execute();
                  $res = $stmt->get_result();
                  while ($payment = $res->fetch_object()) {
                  ?>
                    <tr>
                      <th class="text-success" scope="row">
                        <?php echo $payment->pay_code; ?>
                      </th>
                      <td>
                        <?php echo $payment->pay_amt; ?>
                      </td>
                      <td class='text-success'>
                        <?php echo $payment->order_code; ?>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <!-- Footer -->
      <?php require_once('partials/_footer.php'); ?>
    </div>
  </div>
  <!-- Argon Scripts -->
  <?php
  require_once('partials/_scripts.php');
  ?>
</body>
<!-- For more projects: Visit NetGO+  -->
</html>