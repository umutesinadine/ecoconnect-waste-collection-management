<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();

if (isset($_POST['make'])) {
    // Prevent Posting Blank Values
    if (empty($_POST["order_code"]) || empty($_POST["customer_name"]) || empty($_GET['prod_price'])) {
        $err = "Blank Values Not Accepted";
    } else {
        $order_id = $_POST['order_id'];
        $order_code  = $_POST['order_code'];
        $customer_id = $_SESSION['customer_id'];
        $customer_name = $_POST['customer_name'];
        $prod_id  = $_GET['prod_id'];
        $prod_name = $_GET['prod_name'];
        $prod_price = $_GET['prod_price'];
        $prod_qty = $_POST['prod_qty'];
        $location_link = isset($_POST['location_link']) ? $_POST['location_link'] : '';

        // Insert Captured information to a database table including location_link
        $postQuery = "INSERT INTO rpos_orders (prod_qty, order_id, order_code, customer_id, customer_name, prod_id, prod_name, prod_price, location_link) VALUES(?,?,?,?,?,?,?,?,?)";
        $postStmt = $mysqli->prepare($postQuery);
        // bind parameters (all strings)
        $rc = $postStmt->bind_param('sssssssss', $prod_qty, $order_id, $order_code, $customer_id, $customer_name, $prod_id, $prod_name, $prod_price, $location_link);
        $postStmt->execute();

        // Declare a variable which will be passed to alert function
        if ($postStmt) {
            $success = "Order Submitted" && header("refresh:1; url=payments.php");
        } else {
            $err = "Please Try Again Or Try Later";
        }
    }
}
require_once('partials/_head.php');
?>

<body>
    <!-- Sidenav -->
    <?php require_once('partials/_sidebar.php'); ?>

    <!-- Main content -->
    <div class="main-content">
        <!-- Top navbar -->
        <?php require_once('partials/_topnav.php'); ?>

        <!-- Header -->
        <div style="background-image: url(../admin/assets/img/theme/restro00.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
            <span class="mask bg-gradient-dark opacity-8"></span>
            <div class="container-fluid">
                <div class="header-body">
                </div>
            </div>
        </div>

        <!-- Page content -->
        <div class="container-fluid mt--8">
            <!-- Table -->
            <div class="row">
                <div class="col">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <h3>Please Fill All Fields</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="form-row">

                                    <div class="col-md-6">
                                        <label>Your Name Is</label>
                                        <?php
                                        // Load Customer Name
                                        $customer_id = $_SESSION['customer_id'];
                                        $ret = "SELECT * FROM rpos_customers WHERE customer_id = '$customer_id'";
                                        $stmt = $mysqli->prepare($ret);
                                        $stmt->execute();
                                        $res = $stmt->get_result();
                                        while ($cust = $res->fetch_object()) {
                                        ?>
                                            <input class="form-control" readonly name="customer_name" value="<?php echo $cust->customer_name; ?>">
                                        <?php } ?>
                                        <input type="hidden" name="order_id" value="<?php echo $orderid; ?>" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Request Code</label>
                                        <input type="text" readonly name="order_code" value="<?php echo $alpha; ?>-<?php echo $beta; ?>" class="form-control" value="">
                                    </div>
                                </div>

                                <hr>

                                <?php
                                $prod_id = $_GET['prod_id'];
                                $ret = "SELECT * FROM rpos_products WHERE prod_id = '$prod_id'";
                                $stmt = $mysqli->prepare($ret);
                                $stmt->execute();
                                $res = $stmt->get_result();
                                while ($prod = $res->fetch_object()) {
                                ?>
                                    <div class="form-row">
                                        <div class="col-md-6">
                                            <label>Price</label>
                                            <input type="text" readonly name="prod_price" value="<?php echo $prod->prod_price; ?> Rwf" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Preferred Pickup Date</label>
                                            <input type="date" name="prod_qty" class="form-control" value="">
                                        </div>
                                    </div>

                                    <!-- New Location input -->
                                    <div class="form-row mt-3">
                                        <div class="col-md-6">
                                            <label>Your Location (Click to Auto-Fill)</label>
                                            <input type="text" id="location_link" name="location_link" class="form-control" placeholder="Click here to auto-fill your location" readonly />
                                        </div>
                                    </div>
                                <?php } ?>
                                <br>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <input type="submit" name="make" value="Make Request" class="btn btn-success" value="">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php require_once('partials/_footer.php'); ?>
        </div>
    </div>

    <!-- Argon Scripts -->
    <?php require_once('partials/_scripts.php'); ?>

    <script>
        // Fill location_link input with user's Google Maps location on click
        document.getElementById('location_link').addEventListener('focus', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const mapsLink = `https://www.google.com/maps?q=${lat},${lng}`;
                        document.getElementById('location_link').value = mapsLink;
                    },
                    function(error) {
                        alert('Could not get your location. Please allow location access and try again.');
                        document.getElementById('location_link').blur();
                    }
                );
            } else {
                alert('Geolocation is not supported by your browser.');
                document.getElementById('location_link').blur();
            }
        });
    </script>
</body>

</html>
