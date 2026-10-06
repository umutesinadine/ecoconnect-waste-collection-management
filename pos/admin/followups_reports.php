<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
require_once('partials/_head.php');
?>
<body>
<?php require_once('partials/_sidebar.php'); ?>
<div class="main-content">
    <?php require_once('partials/_topnav.php'); ?>
    <div style="background-image: url(../admin/assets/img/theme/report-bg.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
        <span class="mask bg-gradient-dark opacity-8"></span>
        <div class="container-fluid">
            <div class="header-body">
                <div class="row">
                    <div class="col">
                        <h1 class="text-white">Customer Follow-Up Reports</h1>
                        <p class="text-white">Track the feedback and status of services provided</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid mt--8">
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <h3 class="mb-0">Follow-up Details</h3>
                    </div>
                    <div class="table-responsive">
                        <table id="followupsTable" class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Request Code</th>
                                    <th scope="col">Customer Name</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Feedback</th>
                                    <th scope="col">Submitted On</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $ret = "SELECT * FROM rpos_followups ORDER BY created_at DESC";
                            $stmt = $mysqli->prepare($ret);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            while ($row = $res->fetch_object()) {
                            ?>
                                <tr>
                                    <td><?php echo $row->order_code; ?></td>
                                    <td><?php echo $row->customer_name; ?></td>
                                    <td>
                                        <?php
                                            if ($row->status == 'On Time') {
                                                echo "<span class='badge badge-success'>On Time</span>";
                                            } elseif ($row->status == 'Late') {
                                                echo "<span class='badge badge-warning'>Late</span>";
                                            } else {
                                                echo "<span class='badge badge-danger'>Not Picked</span>";
                                            }
                                        ?>
                                    </td>
                                    <td><?php echo $row->feedback; ?></td>
                                    <td><?php echo date('d/M/Y g:i A', strtotime($row->created_at)); ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php require_once('partials/_footer.php'); ?>
    </div>
</div>
<?php require_once('partials/_scripts.php'); ?>
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css" />

<!-- jQuery (required) -->
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<!-- DataTables Buttons and dependencies -->
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
<script>
$(document).ready(function() {
    $('#followupsTable').DataTable({
        dom: 'Bfrtip', // shows buttons, filter box, etc
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        order: [[4, 'desc']], // default sort by "Submitted On" descending
        // Optional: you can add column search inputs or filters here if needed
    });
});
</script>

</body>
</html>
