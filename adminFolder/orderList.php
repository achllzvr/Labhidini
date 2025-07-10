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


// Check if this is an export request
if(isset($_GET['export']) && $_GET['export'] == 'excel') {
  try {
    $timePeriod = $_GET['timePeriod'] ?? 'day';
    $orderStatus = $_GET['orderStatus'] ?? '';
    $claimStatus = $_GET['claimStatus'] ?? '';
    $paymentStatus = $_GET['paymentStatus'] ?? '';
    $searchTerm = $_GET['searchTerm'] ?? '';
    
    // Get main filtered transactions
    $transactions = $con->getFilteredTransactions($timePeriod, $orderStatus, $claimStatus, $paymentStatus, $searchTerm, true);
    
    // Get additional data for today's specific status reports
    $unpaidToday = $con->getUnpaidTransactionsToday();
    $paidToday = $con->getPaidTransactionsToday();
    $claimedToday = $con->getClaimedTransactionsToday();
    
    // Generate filename with timestamp and filters
    $filterDesc = [];
    if ($timePeriod !== 'all') $filterDesc[] = ucfirst($timePeriod);
    if (!empty($searchTerm)) $filterDesc[] = "Search-" . preg_replace('/[^a-zA-Z0-9]/', '', $searchTerm);
    if (!empty($orderStatus)) $filterDesc[] = "Order-" . $orderStatus;
    if (!empty($claimStatus)) $filterDesc[] = "Claim-" . $claimStatus;
    if (!empty($paymentStatus)) $filterDesc[] = "Payment-" . $paymentStatus;
    
    $filterString = empty($filterDesc) ? 'All' : implode('-', $filterDesc);
    $filename = 'Labhidini_Multi_Report_' . $filterString . '_' . date('Y-m-d_H-i-s') . '.csv';
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for proper UTF-8 encoding in Excel
    fwrite($output, "\xEF\xBB\xBF");
    
    // Helper function to write transaction data
    function writeTransactionData($output, $transactions, $sectionTitle = '') {
      if (!empty($sectionTitle)) {
        fputcsv($output, [$sectionTitle]);
        fputcsv($output, []); // Empty row after title
      }
      
      // Write CSV header
      fputcsv($output, [
        'Transaction ID',
        'Customer Name', 
        'Transaction Date',
        'Regular',
        'Extra Heavy Load',
        'Order Status',
        'Claim Status',
        'Claim Date',
        'Payment Status',
        'Payment Date',
        'Total Amount (PHP)'
      ]);
      
      // Write data rows
      foreach ($transactions as $transaction) {
        // Format claim and payment status
        $claimStatusText = ($transaction['ClaimStatus'] == '2') ? 'Claimed' : 'Unclaimed';
        $paymentStatusText = ($transaction['PaymentStatus'] == '2') ? 'Paid' : 'Unpaid';
        
        // Get status change dates if available
        $claimDate = isset($transaction['ClaimDate']) ? $transaction['ClaimDate'] : '';
        $paymentDate = isset($transaction['PaymentDate']) ? $transaction['PaymentDate'] : '';
        
        fputcsv($output, [
          $transaction['TransactionID'],
          $transaction['CustomerName'],
          $transaction['FormattedDate'],
          $transaction['RegularCount'] > 0 ? $transaction['RegularCount'] : '',
          $transaction['ExtraHeavyCount'] > 0 ? $transaction['ExtraHeavyCount'] : '',
          $transaction['Status'],
          $claimStatusText,
          $claimDate,
          $paymentStatusText,
          $paymentDate,
          number_format($transaction['TransacTotalAmount'], 2)
        ]);
      }
      
      // Add summary for this section
      $totalAmount = array_sum(array_column($transactions, 'TransacTotalAmount'));
      $totalCount = count($transactions);
      
      fputcsv($output, []); // Empty row
      fputcsv($output, ['SECTION SUMMARY']);
      fputcsv($output, ['Total Transactions', $totalCount]);
      fputcsv($output, ['Total Amount', '', '', '', '', '', '', '', '', '', number_format($totalAmount, 2)]);
      fputcsv($output, []); // Empty row after summary
      fputcsv($output, []); // Additional empty row for separation
    }
    
    // Write main filtered transactions section
    writeTransactionData($output, $transactions, 'MAIN REPORT - ' . strtoupper($filterString) . ' TRANSACTIONS');
    
    // Write unpaid transactions for today
    writeTransactionData($output, $unpaidToday, 'CURRENTLY UNPAID TRANSACTIONS (' . count($unpaidToday) . ' total)');
    
    // Write paid transactions for today
    writeTransactionData($output, $paidToday, 'PAID TODAY - ' . date('Y-m-d') . ' (' . count($paidToday) . ' transactions)');
    
    // Write claimed transactions for today
    writeTransactionData($output, $claimedToday, 'CLAIMED TODAY - ' . date('Y-m-d') . ' (' . count($claimedToday) . ' transactions)');
    
    // Overall summary
    fputcsv($output, ['===== OVERALL EXPORT SUMMARY =====']);
    fputcsv($output, ['Export Date', date('Y-m-d H:i:s')]);
    fputcsv($output, ['Exported By', $_SESSION['adminName']]);
    fputcsv($output, ['Main Report Count', count($transactions)]);
    fputcsv($output, ['Unpaid Today Count', count($unpaidToday)]);
    fputcsv($output, ['Paid Today Count', count($paidToday)]);
    fputcsv($output, ['Claimed Today Count', count($claimedToday)]);
    
    fclose($output);
    exit();
    
  } catch (Exception $e) {
    error_log("Error in orderList.php export endpoint: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo "Export failed. Please try again.";
    exit();
  }
}

// Check if this is an AJAX request for filtered data
if(isset($_GET['ajax']) && $_GET['ajax'] == 'filter') {
  header('Content-Type: application/json');
  
  try {
    $timePeriod = $_GET['timePeriod'] ?? 'day';
    $orderStatus = $_GET['orderStatus'] ?? '';
    $claimStatus = $_GET['claimStatus'] ?? '';
    $paymentStatus = $_GET['paymentStatus'] ?? '';
    $searchTerm = $_GET['searchTerm'] ?? '';
    
    $transactions = $con->getFilteredTransactions($timePeriod, $orderStatus, $claimStatus, $paymentStatus, $searchTerm);
    
    // Format the data for JSON response
    $response = [];
    foreach ($transactions as $transaction) {
      $status = strtolower($transaction['Status']);
      $badgeClass = 'in-progress'; // default
      
      // Color coding based on status
      if ($status === 'completed') $badgeClass = 'completed';
      else if ($status === 'cancelled') $badgeClass = 'cancelled';
      else if ($status === 'in progress' || $status === 'pending') $badgeClass = 'in-progress';
      
      // For claim and payment status
      $claimStatusValue = isset($transaction['ClaimStatus']) ? $transaction['ClaimStatus'] : '1';
      $paymentStatusValue = isset($transaction['PaymentStatus']) ? $transaction['PaymentStatus'] : '1';
      
      $claimBadgeClass = ($claimStatusValue == '2') ? 'claimed' : 'unclaimed';
      $paymentBadgeClass = ($paymentStatusValue == '2') ? 'paid' : 'unpaid';
      
      $response[] = [
        'TransactionID' => $transaction['TransactionID'],
        'CustomerName' => $transaction['CustomerName'],
        'FormattedDate' => $transaction['FormattedDate'],
        'RegularCount' => $transaction['RegularCount'],
        'ExtraHeavyCount' => $transaction['ExtraHeavyCount'],
        'Status' => $transaction['Status'],
        'StatusID' => $transaction['StatusID'],
        'ClaimStatus' => $claimStatusValue,
        'PaymentStatus' => $paymentStatusValue,
        'TransacTotalAmount' => $transaction['TransacTotalAmount'],
        'badgeClass' => $badgeClass,
        'claimBadgeClass' => $claimBadgeClass,
        'paymentBadgeClass' => $paymentBadgeClass
      ];
    }
    
    echo json_encode(['success' => true, 'data' => $response]);
  } catch (Exception $e) {
    error_log("Error in orderList.php AJAX endpoint: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
  }
  exit();
}

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
        transition: all 0.3s ease;
        border-width: 2px;
        font-weight: 600;
        position: relative;
        overflow: hidden;
      }
      
      /* Default state - outline with subtle background */
      .status-type-btn:not(.active) {
        background: rgba(255, 255, 255, 0.05) !important;
        border-color: rgba(255, 255, 255, 0.3) !important;
        color: rgba(255, 255, 255, 0.7) !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }
      
      /* Hover state for inactive buttons */
      .status-type-btn:not(.active):hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        background: rgba(255, 255, 255, 0.1) !important;
        color: rgba(255, 255, 255, 0.9) !important;
      }
      
      /* Active state - filled with vibrant colors */
      .status-type-btn.active {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        border-color: transparent !important;
        color: white !important;
      }
      
      /* Order Status Button - Blue */
      .status-type-btn.active[data-type="status"] {
        background: linear-gradient(135deg, #0d6efd, #0a58ca) !important;
        box-shadow: 0 6px 12px rgba(13, 110, 253, 0.4);
      }
      
      /* Claim Status Button - Green */
      .status-type-btn.active[data-type="claim"] {
        background: linear-gradient(135deg, #198754, #146c43) !important;
        box-shadow: 0 6px 12px rgba(25, 135, 84, 0.4);
      }
      
      /* Payment Status Button - Orange/Yellow */
      .status-type-btn.active[data-type="payment"] {
        background: linear-gradient(135deg, #fd7e14, #e55a0e) !important;
        box-shadow: 0 6px 12px rgba(253, 126, 20, 0.4);
      }
      
      /* Button press animation */
      .status-type-btn:active {
        transform: translateY(-1px);
        transition: transform 0.1s ease;
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
        
        .status-type-btn.active {
          transform: translateY(-1px);
          box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
      }
      
      /* Filter controls styling - Translucent/Glassy White */
      .form-control, .form-select {
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: #ffffff !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
      }
      
      .form-control:focus, .form-select:focus {
        background: rgba(255, 255, 255, 0.15) !important;
        border-color: rgba(255, 255, 255, 0.4) !important;
        box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.15), 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        color: #ffffff !important;
      }
      
      .form-control::placeholder {
        color: rgba(255, 255, 255, 0.7) !important;
        opacity: 1 !important;
      }
      
      .form-select option {
        background: rgba(40, 44, 52, 0.95) !important;
        color: #ffffff !important;
        backdrop-filter: blur(10px) !important;
      }
      
      .form-select option:hover, .form-select option:checked {
        background: rgba(186, 235, 230, 0.2) !important;
        color: #ffffff !important;
      }
      
      .input-group-text {
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: #ffffff !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
      }
      
      /* Loading and state styling */
      .spinner-border {
        width: 2rem;
        height: 2rem;
      }
      
      .empty-state, .error-state {
        color: rgba(255, 255, 255, 0.8);
      }
      
      .empty-state i, .error-state i {
        opacity: 0.6;
      }
      
      /* Smooth transitions for table updates */
      #transactionTableBody {
        transition: opacity 0.2s ease;
      }
      
      #transactionTableBody.loading {
        opacity: 0.7;
      }
      
      /* Export button styling */
      #exportBtn {
        transition: all 0.3s ease;
      }
      
      #exportBtn:hover:not(:disabled) {
        background: #146c43 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(25, 135, 84, 0.3);
      }
      
      #exportBtn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
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
          <div class="d-flex gap-2">
            <button
              type="button"
              class="btn btn-sm"
              id="exportBtn"
              style="background: #198754; color: white; border: none;"
              title="Export filtered data to Excel"
            >
              <i class="fas fa-download me-1"></i>Export Excel
            </button>
            <button
              type="button"
              class="btn btn-sm filter-btn"
              style="background: #222; color: #baebe6"
              onclick="window.location.href='adminHome.php';"
            >
              <i class="fas fa-arrow-left me-1"></i>Back
            </button>
          </div>
        </div>
        
        <!-- Search and Filter Controls -->
        <div class="row mb-4">
          <!-- Search Bar -->
          <div class="col-md-4 mb-3">
            <div class="input-group">
              <span class="input-group-text">
                <i class="fas fa-search"></i>
              </span>
              <input 
                type="text" 
                class="form-control" 
                id="searchInput" 
                placeholder="Search by Customer Name..."
              >
            </div>
          </div>
          
          <!-- Time Period Filter -->
          <div class="col-md-3 mb-3">
            <select class="form-select" id="timePeriodFilter">
              <option value="all">All Transactions</option>
              <option value="year">This Year</option>
              <option value="month">This Month</option>
              <option value="week">This Week</option>
              <option value="day" selected>Today</option>
            </select>
          </div>
          
          <!-- Order Status Filter -->
          <div class="col-md-2 mb-3">
            <select class="form-select" id="orderStatusFilter">
              <option value="" selected>All Laundry Statuses</option>
              <option value="1">In Progress</option>
              <option value="2">Completed</option>
              <option value="3">Cancelled</option>
            </select>
          </div>
          
          <!-- Claim Status Filter -->
          <div class="col-md-2 mb-3">
            <select class="form-select" id="claimStatusFilter">
              <option value="" selected>All Claim Statuses</option>
              <option value="1">Unclaimed</option>
              <option value="2">Claimed</option>
            </select>
          </div>
          
          <!-- Payment Status Filter -->
          <div class="col-md-1 mb-3">
            <select class="form-select" id="paymentStatusFilter">
              <option value="" selected>All Payment Statuses</option>
              <option value="1">Unpaid</option>
              <option value="2">Paid</option>
            </select>
          </div>
        </div>
        
        <div class="orders table-responsive">
          <table class="transaction-table align-middle" id="orderTable">
            <thead>
              <tr>
                <th scope="col">ID</th>
                <th scope="col">Customer Name</th>
                <th scope="col">Date</th>
                <th scope="col">Regular</th>
                <th scope="col">Extra Heavy Load</th>
                <th scope="col">Status</th>
                <th scope="col">Claim Status</th>
                <th scope="col">Payment Status</th>
                <th scope="col">Total</th>
              </tr>
            </thead>
            <tbody id="transactionTableBody">
              <?php
              // Load initial data with default filter (all transactions to show something)
              $transactions = $con->getFilteredTransactions('all', '', '', '', '');
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
                  <td><?php echo ($transaction['RegularCount'] > 0) ? $transaction['RegularCount'] : ''; ?></td>
                  <td><?php echo ($transaction['ExtraHeavyCount'] > 0) ? $transaction['ExtraHeavyCount'] : ''; ?></td>
                  <td>
                    <span class="glass-badge <?php echo $badgeClass; ?>" data-status="<?php echo $transaction['StatusID']; ?>">
                      <?php echo $transaction['Status']; ?>
                    </span>
                  </td>
                  <td>
                    <span class="glass-badge <?php echo $claimBadgeClass; ?>" data-status="<?php echo isset($transaction['ClaimStatus']) ? $transaction['ClaimStatus'] : '1'; ?>">
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
                    <span class="glass-badge <?php echo $paymentBadgeClass; ?>" data-status="<?php echo isset($transaction['PaymentStatus']) ? $transaction['PaymentStatus'] : '1'; ?>">
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
        
        // Remove active class from all buttons
        document.querySelectorAll('.status-type-btn').forEach(btn => {
          btn.classList.remove('active');
        });
        
        // Add active class to selected button
        const selectedBtn = document.querySelector(`[data-type="${type}"]`);
        if (selectedBtn) {
          selectedBtn.classList.add('active');
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
        
        // AJAX-based filter and search functionality
        let filterTimeout;
        let isLoading = false;
        
        function showLoadingState() {
          if (isLoading) return;
          isLoading = true;
          
          const tableBody = document.getElementById('transactionTableBody');
          tableBody.innerHTML = `
            <tr>
              <td colspan="8" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2">Loading transactions...</div>
              </td>
            </tr>
          `;
        }
        
        function showEmptyState() {
          const tableBody = document.getElementById('transactionTableBody');
          tableBody.innerHTML = `
            <tr>
              <td colspan="8" class="text-center py-4">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <div class="h5 text-muted">No transactions found</div>
                <div class="text-muted">Try adjusting your filters or search criteria</div>
              </td>
            </tr>
          `;
        }
        
        function showErrorState() {
          const tableBody = document.getElementById('transactionTableBody');
          tableBody.innerHTML = `
            <tr>
              <td colspan="8" class="text-center py-4">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <div class="h5 text-warning">Error loading transactions</div>
                <div class="text-muted">Please try again or contact support</div>
              </td>
            </tr>
          `;
        }
        
        function buildTransactionRow(transaction) {
          return `
            <tr class="order-row" 
                data-transaction-id="${transaction.TransactionID}" 
                data-status="${transaction.StatusID}">
              <td>${transaction.TransactionID}</td>
              <td>${transaction.CustomerName}</td>
              <td>${transaction.FormattedDate}</td>
              <td>${transaction.RegularCount > 0 ? transaction.RegularCount : ''}</td>
              <td>${transaction.ExtraHeavyCount > 0 ? transaction.ExtraHeavyCount : ''}</td>
              <td>
                <span class="glass-badge ${transaction.badgeClass}" data-status="${transaction.StatusID}">
                  ${transaction.Status}
                </span>
              </td>
              <td>
                <span class="glass-badge ${transaction.claimBadgeClass}" data-status="${transaction.ClaimStatus || '1'}">
                  ${transaction.ClaimStatus == '2' ? 'Claimed' : 'Unclaimed'}
                </span>
              </td>
              <td>
                <span class="glass-badge ${transaction.paymentBadgeClass}" data-status="${transaction.PaymentStatus || '1'}">
                  ${transaction.PaymentStatus == '2' ? 'Paid' : 'Unpaid'}
                </span>
              </td>
              <td>₱${parseFloat(transaction.TransacTotalAmount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
            </tr>
          `;
        }
        
        function loadTransactions() {
          // Clear any existing timeout
          clearTimeout(filterTimeout);
          
          // Set a debounce timeout for search input
          filterTimeout = setTimeout(() => {
            showLoadingState();
            
            const searchInput = document.getElementById('searchInput').value;
            const timePeriodFilter = document.getElementById('timePeriodFilter').value;
            const orderStatusFilter = document.getElementById('orderStatusFilter').value;
            const claimStatusFilter = document.getElementById('claimStatusFilter').value;
            const paymentStatusFilter = document.getElementById('paymentStatusFilter').value;
            
            // Build query parameters
            const params = new URLSearchParams({
              ajax: 'filter',
              timePeriod: timePeriodFilter,
              orderStatus: orderStatusFilter,
              claimStatus: claimStatusFilter,
              paymentStatus: paymentStatusFilter,
              searchTerm: searchInput
            });
            
            // Make AJAX request
            fetch(`orderList.php?${params.toString()}`)
              .then(response => {
                if (!response.ok) {
                  throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
              })
              .then(response => {
                const tableBody = document.getElementById('transactionTableBody');
                
                if (!response.success) {
                  throw new Error(response.error || 'Unknown error occurred');
                }
                
                const data = response.data;
                
                if (data.length === 0) {
                  showEmptyState();
                } else {
                  // Build table rows
                  const rowsHTML = data.map(transaction => buildTransactionRow(transaction)).join('');
                  tableBody.innerHTML = rowsHTML;
                  
                  // Re-attach event listeners to new rows
                  attachRowEventListeners();
                }
                
                isLoading = false;
              })
              .catch(error => {
                console.error('Error loading transactions:', error);
                showErrorState();
                isLoading = false;
              });
          }, 300); // 300ms debounce for search input
        }
        
        function attachRowEventListeners() {
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
        }
        
        // Add event listeners for filters with different debounce times
        document.getElementById('searchInput').addEventListener('input', loadTransactions); // Debounced
        document.getElementById('timePeriodFilter').addEventListener('change', () => {
          showLoadingState();
          setTimeout(loadTransactions, 50); // Immediate for dropdowns
        });
        document.getElementById('orderStatusFilter').addEventListener('change', () => {
          showLoadingState();
          setTimeout(loadTransactions, 50);
        });
        document.getElementById('claimStatusFilter').addEventListener('change', () => {
          showLoadingState();
          setTimeout(loadTransactions, 50);
        });
        document.getElementById('paymentStatusFilter').addEventListener('change', () => {
          showLoadingState();
          setTimeout(loadTransactions, 50);
        });
        
        // Initial load with default filters already applied by PHP
        attachRowEventListeners();
        
        // Export functionality
        document.getElementById('exportBtn').addEventListener('click', function() {
          const searchInput = document.getElementById('searchInput').value;
          const timePeriodFilter = document.getElementById('timePeriodFilter').value;
          const orderStatusFilter = document.getElementById('orderStatusFilter').value;
          const claimStatusFilter = document.getElementById('claimStatusFilter').value;
          const paymentStatusFilter = document.getElementById('paymentStatusFilter').value;
          
          // Build export URL with current filters
          const params = new URLSearchParams({
            export: 'excel',
            timePeriod: timePeriodFilter,
            orderStatus: orderStatusFilter,
            claimStatus: claimStatusFilter,
            paymentStatus: paymentStatusFilter,
            searchTerm: searchInput
          });
          
          // Show loading state on button
          const exportBtn = document.getElementById('exportBtn');
          const originalContent = exportBtn.innerHTML;
          exportBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Exporting...';
          exportBtn.disabled = true;
          
          // Create hidden link and trigger download
          const link = document.createElement('a');
          link.href = `orderList.php?${params.toString()}`;
          link.download = ''; // Let the server determine filename
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          
          // Reset button after a short delay
          setTimeout(() => {
            exportBtn.innerHTML = originalContent;
            exportBtn.disabled = false;
            
            // Show success message
            Swal.fire({
              icon: 'success',
              title: 'Export Complete',
              text: 'Your Excel file has been downloaded successfully!',
              timer: 2000,
              showConfirmButton: false
            });
          }, 1500);
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
