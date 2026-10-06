<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
include('config/code-generator.php');

check_login();
if (isset($_POST['submit_followup'])) {
    if (empty($_POST["order_code"]) || empty($_POST["status"]) || empty($_POST["feedback"])) {
        $err = "Blank Values Not Accepted";
    } else {
        $followup_id = $orderid;
        $order_code  = $_POST['order_code'];
        $customer_id = $_SESSION['customer_id'];
        $customer_name = $_POST['customer_name'];
        $status = $_POST['status'];
        $feedback = $_POST['feedback'];

        $postQuery = "INSERT INTO rpos_followups (followup_id, order_code, customer_id, customer_name, status, feedback) VALUES(?,?,?,?,?,?)";
        $postStmt = $mysqli->prepare($postQuery);
        $rc = $postStmt->bind_param('ssssss', $followup_id, $order_code, $customer_id, $customer_name, $status, $feedback);
        $postStmt->execute();
        if ($postStmt) {
            $success = "Follow-up Submitted" && header("refresh:1; url=dashboard.php");
        } else {
            $err = "Please Try Again Or Try Later";
        }
    }
}
require_once('partials/_head.php');
?>
<body>
    <?php require_once('partials/_sidebar.php'); ?>
    <div class="main-content">
        <?php require_once('partials/_topnav.php'); ?>
        <div style="background-image: url(../admin/assets/img/theme/restro00.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
            <span class="mask bg-gradient-dark opacity-8"></span>
            <div class="container-fluid">
                <div class="header-body"></div>
            </div>
        </div>
        <div class="container-fluid mt--8">
            <div class="row">
                <div class="col">
                    <div class="card shadow">
                        <div class="card-header border-0">
                            <h3>Follow Up On Your Request</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <label>Your Name</label>
                                        <?php
                                        $customer_id = $_SESSION['customer_id'];
                                        $ret = "SELECT * FROM rpos_customers WHERE customer_id = '$customer_id' ";
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
                                        <?php
                                              $new_order_code = $alpha . '-' . $beta; // Example from your code-generator.php
                                        ?>
                                        <input type="text" name="order_code" class="form-control" value="<?php echo $new_order_code; ?>" readonly>

                                    </div>
                                </div>
                                <hr>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <label>Pickup Status</label>
                                        <select name="status" class="form-control" required>
                                            <option value="">--Select--</option>
                                            <option value="On Time">On Time</option>
                                            <option value="Late">Late</option>
                                            <option value="Not Picked">Not Picked</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Feedback / Comments</label>
                                        <textarea name="feedback" class="form-control" rows="4" required></textarea>
                                    </div>
                                </div>
                                <br>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <input type="submit" name="submit_followup" value="Submit Follow-up" class="btn btn-success">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php require_once('partials/_footer.php'); ?>
        </div>
    </div>
    <?php require_once('partials/_scripts.php'); ?>
</body>
</html>
