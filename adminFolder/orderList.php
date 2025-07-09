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
  $statusValue = $_POST['modalStatus'];
  $statusType = $_POST['statusType']; // 'status', 'claim', or 'payment'
  $transactionID = $_POST['transaction_id'];

  // Update transaction status based on type
  $isUpdated = $con->updateTransactionStatus($statusValue, $_SESSION['adminID'], $transactionID, $statusType);

  // Success message if $isUpdated is true
  if($isUpdated) {
    $statusTypeName = ($statusType === 'claim') ? 'Claim Status' : (($statusType === 'payment') ? 'Payment Status' : 'Order Status');
    $sweetAlertConfig = "
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '$statusTypeName updated successfully.',
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
      /* Status badge styling with color coding - matching admin.css glass-badge style */
      .glass-badge {
        padding: 0.35em 0.75em;
        border-radius: 15px;
        font-size: 0.85em;
        font-weight: 600;
        display: inline-block;
        text-align: center;
        min-width: 75px;
        transition: all 0.2s ease;
      }
      
      /* Red statuses: Cancelled, Unpaid, Unclaimed */
      .glass-badge.cancelled,
      .glass-badge.unpaid,
      .glass-badge.unclaimed {
        background-color: #dc3545;
        color: white;
      }
      
      /* Yellow statuses: In Progress, Pending */
      .glass-badge.in-progress,
      .glass-badge.pending {
        background-color: #ffc107;
        color: #000;
      }
      
      /* Green statuses: Completed, Paid, Claimed */
      .glass-badge.completed,
      .glass-badge.paid,
      .glass-badge.claimed {
        background-color: #198754;
        color: white;
      }
      
      /* Center and fit the order card container */
      .order-card-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
      }
      
      .order-card-container .card {
        width: 100%;
        max-width: fit-content;
        min-width: 800px;
      }
      
      /* Responsive adjustments */
      @media (max-width: 992px) {
        .order-card-container .card {
          min-width: 700px;
        }
      }
      
      @media (max-width: 768px) {
        .order-card-container .card {
          min-width: 600px;
        }
      }
      
      @media (max-width: 576px) {
        .order-card-container .card {
          min-width: 100%;
        }
      }
      
      /* Status Type Button Styling */
      .status-type-btn {
        transition: all 0.2s ease;
        border-width: 2px;
        font-weight: 600;
      }
      
      .status-type-btn.active {
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        transform: translateY(-1px);
      }
      
      .status-type-btn:not(.active):hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      }
      
      /* Make buttons responsive */
      @media (max-width: 576px) {
        .status-type-btn {
          font-size: 0.85rem;
          padding: 0.4rem 0.8rem;
        }
        
        .status-type-btn i {
          display: none;
        }
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

    <!-- Main container for centering -->
    <div class="container-fluid d-flex justify-content-center align-items-start min-vh-100 py-4">
      <div class="w-100" style="max-width: 1400px;">
        <!-- main order content -->
        <div class="order-card-container">
          <!-- Laundry Transaction History Card -->
          <div class="card p-4 no-hover-effect">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="card-title mb-0">
            <i class="fas fa-box me-2"></i>Order List
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
          <table class="transaction-table align-middle" id="ordersTable">
            <thead>
              <tr>
                <th scope="col">ID</th>
                <th scope="col">Customer Name</th>
                <th scope="col">Date</th>
                <th scope="col">Service</th>
                <th scope="col">Status</th>
                <th scope="col">Claim Status</th>
                <th scope="col">Payment Status</th>
                <th scope="col">Total</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $transactions = $con->getAllTransactions();
              foreach ($transactions as $transaction) {
                $status = strtolower($transaction['Status']);
                $badgeClass = 'in-progress'; // default
                
                // Color coding based on status
                if ($status === 'completed') $badgeClass = 'completed';
                else if ($status === 'cancelled') $badgeClass = 'cancelled';
                else if ($status === 'in progress' || $status === 'pending') $badgeClass = 'in-progress';
                
                // For claim and payment status
                $claimStatus = isset($transaction['ClaimStatus']) ? strtolower($transaction['ClaimStatus']) : 'unclaimed';
                $paymentStatus = isset($transaction['PaymentStatus']) ? strtolower($transaction['PaymentStatus']) : 'unpaid';
                
                $claimBadgeClass = ($claimStatus === 'claimed' || $claimStatus === '2') ? 'claimed' : 'unclaimed';
                $paymentBadgeClass = ($paymentStatus === 'paid' || $paymentStatus === '2') ? 'paid' : 'unpaid';
                ?>
                <tr class="order-row" 
                    data-transaction-id="<?php echo $transaction['TransactionID']; ?>" 
                    data-status="<?php echo $transaction['StatusID']; ?>">
                  <td><?php echo $transaction['TransactionID']; ?></td>
                  <td><?php echo $transaction['CustomerName']; ?></td>
                  <td><?php echo $transaction['FormattedDate']; ?></td>
                  <td><?php echo $transaction['Services']; ?></td>
                  <td>
                    <span class="glass-badge <?php echo $badgeClass; ?>">
                      <?php echo $transaction['Status']; ?>
                    </span>
                  </td>
                  <td>
                    <span class="glass-badge <?php echo $claimBadgeClass; ?>">
                      <?php 
                      if (isset($transaction['ClaimStatus'])) {
                        if ($transaction['ClaimStatus'] == '1') {
                          echo 'Unclaimed';
                        } elseif ($transaction['ClaimStatus'] == '2') {
                          echo 'Claimed';
                        } else {
                          echo $transaction['ClaimStatus'];
                        }
                      } else {
                        echo 'Unclaimed';
                      }
                      ?>
                    </span>
                  </td>
                  <td>
                    <span class="glass-badge <?php echo $paymentBadgeClass; ?>">
                      <?php 
                      if (isset($transaction['PaymentStatus'])) {
                        if ($transaction['PaymentStatus'] == '1') {
                          echo 'Unpaid';
                        } elseif ($transaction['PaymentStatus'] == '2') {
                          echo 'Paid';
                        } else {
                          echo $transaction['PaymentStatus'];
                        }
                      } else {
                        echo 'Unpaid';
                      }
                      ?>
                    </span>
                  </td>
                  <td>₱<?php echo number_format($transaction['TransacTotalAmount'], 2); ?></td>
                </tr>
                <?php
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
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
            </div>            <div class="modal-body">
              <!-- Hidden input to store the transaction ID -->
              <input type="hidden" id="modalTransactionId" name="transaction_id">
              <!-- Hidden input to store the status type -->
              <input type="hidden" id="statusType" name="statusType" value="status">
              
              <!-- Status Type Buttons -->
              <div class="mb-3">
                <label class="form-label">Select Status Type to Update</label>
                <div class="d-flex gap-2 flex-wrap">
                  <button type="button" class="btn btn-outline-primary status-type-btn active" data-type="status">
                    <i class="fas fa-tasks me-1"></i>Order Status
                  </button>
                  <button type="button" class="btn btn-outline-success status-type-btn" data-type="claim">
                    <i class="fas fa-hand-holding me-1"></i>Claim Status
                  </button>
                  <button type="button" class="btn btn-outline-warning status-type-btn" data-type="payment">
                    <i class="fas fa-credit-card me-1"></i>Payment Status
                  </button>
                </div>
              </div>
              
              <!-- Status Value Selection -->
              <div class="mb-3">
                <label for="modalStatus" class="form-label">Status</label>
                <select class="form-select" id="modalStatus" name="modalStatus" required>
                  <!-- Options will be populated by JavaScript based on status type -->
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
      // Define status options for each type
      const statusOptions = {
        status: [
          <?php
          $statuses = $con->getAllStatuses();
          foreach ($statuses as $status) {
            echo '{value: "' . $status['StatusID'] . '", text: "' . $status['StatusName'] . '"},';
          }
          ?>
        ],
        claim: [
          {value: "1", text: "Unclaimed"},
          {value: "2", text: "Claimed"}
        ],
        payment: [
          {value: "1", text: "Unpaid"},
          {value: "2", text: "Paid"}
        ]
      };

      // Function to update status options based on selected type
      function updateStatusOptions() {
        const statusType = document.getElementById('statusType').value;
        const modalStatus = document.getElementById('modalStatus');
        const modalTitle = document.querySelector('#statusModal .modal-title');
        
        // Clear existing options
        modalStatus.innerHTML = '';
        
        // Update modal title
        const titles = {
          status: 'Update Order Status',
          claim: 'Update Claim Status',
          payment: 'Update Payment Status'
        };
        modalTitle.textContent = titles[statusType];
        
        // Add new options
        statusOptions[statusType].forEach(option => {
          const optionElement = document.createElement('option');
          optionElement.value = option.value;
          optionElement.textContent = option.text;
          modalStatus.appendChild(optionElement);
        });
      }
      
      // Function to handle status type button clicks
      function setStatusType(type) {
        // Update hidden input
        document.getElementById('statusType').value = type;
        
        // Update button states
        document.querySelectorAll('.status-type-btn').forEach(btn => {
          btn.classList.remove('active');
          if (btn.classList.contains('btn-outline-primary')) {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-primary');
          } else if (btn.classList.contains('btn-outline-success') || btn.classList.contains('btn-success')) {
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-success');
          } else if (btn.classList.contains('btn-outline-warning') || btn.classList.contains('btn-warning')) {
            btn.classList.remove('btn-warning');
            btn.classList.add('btn-outline-warning');
          }
        });
        
        // Activate selected button
        const selectedBtn = document.querySelector(`[data-type="${type}"]`);
        selectedBtn.classList.add('active');
        if (type === 'status') {
          selectedBtn.classList.remove('btn-outline-primary');
          selectedBtn.classList.add('btn-primary');
        } else if (type === 'claim') {
          selectedBtn.classList.remove('btn-outline-success');
          selectedBtn.classList.add('btn-success');
        } else if (type === 'payment') {
          selectedBtn.classList.remove('btn-outline-warning');
          selectedBtn.classList.add('btn-warning');
        }
        
        // Update status options
        updateStatusOptions();
      }

      document.addEventListener('DOMContentLoaded', function () {
        // Initialize status options
        updateStatusOptions();
        
        // Add event listeners to status type buttons
        document.querySelectorAll('.status-type-btn').forEach(btn => {
          btn.addEventListener('click', function() {
            const type = this.getAttribute('data-type');
            setStatusType(type);
          });
        });
        
        document.querySelectorAll('.order-row').forEach(function(row) {
          row.addEventListener('click', function() {
            // Set transaction ID and current status in modal
            document.getElementById('modalTransactionId').value = row.getAttribute('data-transaction-id');
            
            // Reset to default status type (Order Status)
            setStatusType('status');
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
