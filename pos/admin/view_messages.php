<?php
session_start();
include('config/config.php'); // POS DB connection for auth
include('config/checklogin.php');
check_login();

require_once('partials/_head.php');

// EcoConnect DB connection
$eco_conn = new mysqli("localhost", "root", "", "ecoconnect");
if ($eco_conn->connect_error) {
    die("Connection to EcoConnect DB failed: " . $eco_conn->connect_error);
}

// Fetch messages
$sql = "SELECT id, name, email, phone, message, created_at FROM contact_messages ORDER BY created_at DESC";
$result = $eco_conn->query($sql);
?>

<body>
  <!-- Sidebar -->
  <?php require_once('partials/_sidebar.php'); ?>

  <!-- Main content -->
  <div class="main-content">
    <!-- Top navbar -->
    <?php require_once('partials/_topnav.php'); ?>

    <!-- Header -->
    <div style="background-image: url(assets/img/theme/mountain.jpg); background-size: cover;" class="header pb-8 pt-5 pt-md-8">
      <span class="mask bg-gradient-dark opacity-8"></span>
      <div class="container-fluid">
        <div class="header-body">
          <h2 class="text-white">Inbox – Contact Messages</h2>
        </div>
      </div>
    </div>

    <!-- Page content -->
    <div class="container-fluid mt--7">
      <div class="row mt-5">
        <div class="col-xl-12">
          <div class="card shadow">
            <div class="card-header border-0">
              <h3 class="mb-0">Recent Contact Messages</h3>
            </div>

            <div class="container mt-3">
              <!-- Alerts -->
              <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>
              <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              <?php endif; ?>
            </div>

            <div class="table-responsive">
              <table class="table align-items-center table-flush">
                <thead class="thead-light">
                  <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Message</th>
                    <th>Sent At</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $count = 1;
                  if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                      ?>
                      <tr>
                        <td><?= $count++; ?></td>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td><?= htmlspecialchars($row['email']); ?></td>
                        <td><?= htmlspecialchars($row['phone']); ?></td>
                        <td><?= nl2br(htmlspecialchars($row['message'])); ?></td>
                        <td><?= date("d/M/Y h:i A", strtotime($row['created_at'])); ?></td>
                        <td>
                          <button 
                            class="btn btn-sm btn-primary reply-btn" 
                            data-email="<?= htmlspecialchars($row['email']); ?>"
                            data-name="<?= htmlspecialchars($row['name']); ?>"
                            data-id="<?= $row['id']; ?>"
                          >Reply</button>

              
                        </td>
                      </tr>
                      <?php
                    }
                  } else {
                    echo "<tr><td colspan='7' class='text-center text-muted'>No messages found.</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Reply Modal -->
      <div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <form id="replyForm" method="POST" action="send_reply.php">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="replyModalLabel">Reply to Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" name="message_id" id="message_id" />
                <div class="mb-3">
                  <label for="recipient_name" class="form-label">Recipient Name</label>
                  <input type="text" class="form-control" id="recipient_name" name="recipient_name" readonly />
                </div>
                <div class="mb-3">
                  <label for="recipient_email" class="form-label">Recipient Email</label>
                  <input type="email" class="form-control" id="recipient_email" name="recipient_email" readonly />
                </div>
                <div class="mb-3">
                  <label for="reply_message" class="form-label">Message</label>
                  <textarea class="form-control" id="reply_message" name="reply_message" rows="5" required></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-success">Send Reply</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Show Replies Modal -->
      <div class="modal fade" id="showRepliesModal" tabindex="-1" aria-labelledby="showRepliesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="showRepliesModalLabel">Reply History</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="replyHistoryContent">
              <p class="text-center">Loading replies...</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>

      <?php require_once('partials/_footer.php'); ?>
    </div>
  </div>

  <?php require_once('partials/_scripts.php'); ?>

  <script>
    // Reply button logic
    document.querySelectorAll('.reply-btn').forEach(button => {
      button.addEventListener('click', () => {
        const email = button.getAttribute('data-email');
        const name = button.getAttribute('data-name');
        const id = button.getAttribute('data-id');

        document.getElementById('recipient_email').value = email;
        document.getElementById('recipient_name').value = name;
        document.getElementById('message_id').value = id;

        const replyModal = new bootstrap.Modal(document.getElementById('replyModal'));
        replyModal.show();
      });
    });

    // Show Replies modal logic
    const showRepliesModal = document.getElementById('showRepliesModal');
    const replyHistoryContent = document.getElementById('replyHistoryContent');

    showRepliesModal.addEventListener('show.bs.modal', event => {
      const button = event.relatedTarget;
      const messageId = button.getAttribute('data-id');

      // Show loading text
      replyHistoryContent.innerHTML = '<p class="text-center">Loading replies...</p>';

      // Fetch replies via AJAX
      fetch(`fetch_replies.php?message_id=${messageId}`)
        .then(response => {
          if (!response.ok) throw new Error('Network response was not OK');
          return response.text();
        })
        .then(data => {
          replyHistoryContent.innerHTML = data;
        })
        .catch(error => {
          replyHistoryContent.innerHTML = '<p class="text-danger text-center">Failed to load replies.</p>';
          console.error('Error fetching replies:', error);
        });
    });
  </script>
</body>
</html>

<?php $eco_conn->close(); ?>
