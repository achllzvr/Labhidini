<?php

// Start the session
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Checks if there is a user logged in
if (!isset($_SESSION['adminID']) || isset($_SESSION['CustomerID'])) {
  
  // If not logged in, redirect to login page
  if (!isset($_SESSION['adminID'])) {
    header("Location: ../loginAdmin.php");
    exit();
  } else {
    // If a customer is logged in, redirect to user home page
    header("Location: ../error404.php");
    exit();
  }

}

// Connect to Database
require_once('../classes/database.php');
$con = new database();

// Set Customer Name
$_SESSION['adminName'] = $_SESSION['adminFN'] . " " . $_SESSION['adminLN'];

// Initialize a variable to hold the SweetAlert configuration
$sweetAlertConfig = "";


// Check if the form is submitted
if(isset($_POST['updateStatusButton'])) {

  // Get the form data
  $status = $_POST['modalStatus'];
  $transactionID = $_POST['transaction_id'];

  // Update transaction status
  $isUpdated = $con->updateTransactionStatus($status, $_SESSION['adminID'], $transactionID);

  // Success message if $isUpdated is true
  if($isUpdated) {
    $sweetAlertConfig = "
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: ' Status updated successfully.',
        confirmButtonText: 'Continue'
      }).then(() => {
        window.location.href = 'orderList.php';
        });
      </script>";
    } else {
      $sweetAlertConfig = "
      <script>
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to update status. Please try again.',
          confirmButtonText: 'Try Again'
        });
      </script>";
    }

}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Order List - Labhidini Laundromat</title>
    <link rel="icon" type="image/png" href="img/icon.png" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- FontAwesome Icons -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
      integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
      rel="stylesheet"
    />  
    <!-- Custom CSS -->
    <link rel="stylesheet" href="admin.css" />
    <style>
      .transaction-table th, .transaction-table td {
        vertical-align: middle;
        text-align: left;
        white-space: nowrap;
      }
      .glass-badge {
        padding: 4px 14px;
        border-radius: 16px;
        font-size: 0.95em;
        font-weight: 500;
        display: inline-block;
      }
      .glass-badge.in-progress {
        background: #d2f5f1;
        color: #1a7f73;
      }
      .glass-badge.completed {
        background: #d2f5e3;
        color: #1a7f2b;
      }
      .glass-badge.cancelled {
        background: #f5d2d2;
        color: #a71a1a;
      }
      .order-card-container {
        margin-bottom: 2rem;
      }
      .transaction-table {
        min-width: 100%;
      }
    </style>
  </head>
  <body>
    <!-- particles background -->
    <div
      id="tsparticles"
      style="
        position: fixed;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        z-index: -1;
      "
    ></div>

    <!-- Recent Order Card -->
    <div class="order-card-container mb-4">
      <div class="card p-4 no-hover-effect">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="card-title mb-0">
            <i class="fas fa-clock me-2"></i>Recent Order
          </div>
          <button
            type="button"
            class="btn btn-sm filter-btn"
            style="background: #222; color: #baebe6"
            onclick="window.location.href='adminHome.php';"
          >
            <i class="fas fa-arrow-left me-1"></i>Back
          </button>
        </div>
        <div class="orders table-responsive">
          <table class="transaction-table align-middle" id="recentOrderTable">
            <thead>
              <tr>
                <th scope="col">ID</th>
                <th scope="col">Customer Name</th>
                <th scope="col">Date</th>
                <th scope="col">Service</th>
                <th scope="col">Status</th>
                <th scope="col">Total</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $transactions = $con->getLatestOrder();
              foreach ($transactions as $transaction) {
                ?>
                <tr>
                    <?php
                      $status = strtolower($transaction['Status']);
                      $badgeClass = 'in-progress';
                      if ($status === 'completed') $badgeClass = 'completed';
                      else if ($status === 'cancelled') $badgeClass = 'cancelled';
                    ?>

                    <tr>
                      <td>

                        <?php echo $transaction['TransactionID']; ?>
                      </td>
                      <td>

                        <?php echo $transaction['CustomerName']; ?>

                      </td>
                      <td>

                        <?php echo $transaction['FormattedDate']; ?>

                      </td>
                      <td>

                        <?php echo $transaction['Services']; ?>

                      </td>
                      <td>

                        <span class="glass-badge completed"><?php echo $transaction['Status']; ?></span>

                      </td>
                      <td>

                        <?php echo $transaction['TransacTotalAmount']; ?>

                      </td>
                    </tr>
                  </tbody>

                  <!-- Close the foreach loop -->
                  <?php

                  }

                  ?>
          </table>
        </div>
      </div>
    </div>

    <!-- main order content -->
    <div class="order-card-container">
      <!-- Laundry Transaction History Card -->
      <div class="card p-4 no-hover-effect">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="card-title mb-0">
            <i class="fas fa-box me-2"></i>Order List
          </div>
        </div>
        <div class="orders table-responsive">
          <table class="transaction-table align-middle" id="ordersTable">
            <thead>
              <tr>
                <th scope="col">ID</th>
                <th scope="col">Customer Name</th>
                <th scope="col">Date</th>
                <th scope="col">Service</th>
                <th scope="col">Status</th>
                <th scope="col">Total</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $transactions = $con->getAllTransactions();
              foreach ($transactions as $transaction) {
                ?>
                <tr>
                    <?php
                      $status = strtolower($transaction['Status']);
                      $badgeClass = 'in-progress';
                      if ($status === 'completed') $badgeClass = 'completed';
                      else if ($status === 'cancelled') $badgeClass = 'cancelled';
                    ?>

                    <tr class="order-row" 
                        data-transaction-id="<?php echo $transaction['TransactionID']; ?>" 
                        data-status="<?php echo $transaction['StatusID']; ?>">
                      <td>

                        <?php echo $transaction['TransactionID']; ?>
                      </td>
                      <td>

                        <?php echo $transaction['CustomerName']; ?>

                      </td>
                      <td>

                        <?php echo $transaction['FormattedDate']; ?>

                      </td>
                      <td>

                        <?php echo $transaction['Services']; ?>

                      </td>
                      <td>

                        <span class="glass-badge completed"><?php echo $transaction['Status']; ?></span>

                      </td>
                      <td>

                        <?php echo $transaction['TransacTotalAmount']; ?>

                      </td>
                    </tr>
                  </tbody>

                  <!-- Close the foreach loop -->
                  <?php

                  }

                  ?>
          </table>
        </div>
      </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
      <div class="modal-dialog">
        <form id="statusForm" method="POST" action="">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Update Order Status</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <!-- Hidden input to store the transaction ID -->
              <input type="hidden" id="modalTransactionId" name="transaction_id">
              <div class="mb-3">
                <label for="modalStatus" class="form-label">Status</label>
                <select class="form-select" id="modalStatus" name="modalStatus" required>

                  <?php
                  // Define the status options
                  $status= $con->getAllStatuses();
                  foreach ($status as $option) {

                  ?>
                  <option value="<?php echo $option['StatusID']; ?>">
                      <?php echo $option['StatusName']; ?>
                  </option>

                  <?php
                  }
                  ?>

                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" id="updateStatusButton" name="updateStatusButton" class="btn btn-primary">Update Status</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- scripts for frontend functionality -->
    <script src="userscript.js"></script>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
      crossorigin="anonymous"></script>

    <!-- Script to handle order row click and show modal -->
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.order-row').forEach(function(row) {
          row.addEventListener('click', function() {
            // Set transaction ID and current status in modal
            document.getElementById('modalTransactionId').value = row.getAttribute('data-transaction-id');
            document.getElementById('modalStatus').value = row.getAttribute('data-status');
            // Show modal
            var modal = new bootstrap.Modal(document.getElementById('statusModal'));
            modal.show();
          });
        });
      });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/tsparticles@2.11.1/tsparticles.bundle.min.js"></script>
    <script src="/particles.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php echo $sweetAlertConfig; ?>

  </body>
</html>
