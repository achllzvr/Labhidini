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

// Check if the confirm order form is submitted
if (isset($_POST['placeOrderBtn'])) {
     
      // Get the form data
      $customerName = trim($_POST['customerName']);
      $admin_id = $_SESSION['adminID'];
      $paymentMethodID = $_POST['selectedPaymentMethodId'];
      $totalAmount = $_POST['total_amount'];
      $subtotal = $totalAmount; // Set subtotal equal to total since we removed discount
      $discount = 0; // No discount functionality
      
      // Get service counts
      $regularCount = intval($_POST['regularServiceCount'] ?? 0);
      $heavyCount = intval($_POST['heavyServiceCount'] ?? 0);

      // Insert into the orders table using customer name
      $userID = $con->newOrderWithCustomerName($customerName, $admin_id, $paymentMethodID, $subtotal, $discount, $totalAmount);

      // Get the latest transaction ID for the customer (Last inserted order) using customer name
      $transactionID = $con->getLatestTransactionIDByName($customerName);
      
      $userID2 = true; // Initialize as true for success checking
      
      // Insert Regular Service if count > 0
      if ($regularCount > 0) {
        // Assuming service ID 1 for Regular Service, price 120.00
        $userID2 = $con->insertTransactionDetails($transactionID, 1, $regularCount) && $userID2;
      }
      
      // Insert Extra Heavy Load if count > 0  
      if ($heavyCount > 0) {
        // Assuming service ID 2 for Extra Heavy Load, price 175.00
        $userID2 = $con->insertTransactionDetails($transactionID, 2, $heavyCount) && $userID2;
      }

      if ($userID && $userID2) {
        $sweetAlertConfig = "
        <script>
        Swal.fire({
          icon: 'success',
          title: 'Order Placed Successfully',
          text: 'You have successfully placed a new order.',
          confirmButtonText: 'OK'
        }).then(() => {
          window.location.href = 'newOrder.php';
        });
        </script>
        ";
      } else {
        $sweetAlertConfig = "
         <script>
        Swal.fire({
          icon: 'error',
          title: 'Order Placement Failed',
          text: 'An error occurred while placing the order. Please try again.',
          confirmButtonText: 'OK'
        });
        </script>
        ";
      }
    }

?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>New Order - Labhidini Laundromat</title>
    <link rel="icon" type="image/png" href="../img/icon.png" />
    <!-- Bootstrap CSS -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT"
      crossorigin="anonymous"
    />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="./bootstrap-5.3.3-dist/css/bootstrap.css">
    <!-- SweetAlert2 CSS -->
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
      .dropdown-arrow-wrapper {
        position: relative;
        display: flex;
        align-items: center;
      }
      .dropdown-arrow-wrapper input[type="text"], 
      .dropdown-arrow-wrapper input.form-control {
        padding-right: 2.5rem;
      }
      .dropdown-btn {
        background: none;
        border: none;
        position: absolute;
        right: 0.5rem;
        top: 50%;
        transform: translateY(-50%);
        z-index: 2;
        color: #395C58;
        font-size: 1.1em;
        padding: 0;
        cursor: pointer;
      }
      .custom-dropdown-list {
        display: none;
        position: absolute;
        left: 0;
        right: 0;
        top: 110%;
        z-index: 10;
        background: #D1EAE7;
        border: 1px solid #ccc;
        border-radius: 0.5rem;
        max-height: 180px;
        overflow-y: auto;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      }
      .custom-dropdown-list.show {
        display: block;
      }
      .custom-dropdown-list .dropdown-item {
        padding: 0.5rem 1rem;
        cursor: pointer;
        white-space: nowrap;
      }
      .custom-dropdown-list .dropdown-item:hover {
        background: #e6f7f5;
      }
   
      #placeOrderBtn {
        transition: background 0.2s;
      }
      #placeOrderBtn:hover {
        background: #2C4744 !important;
      }
    
      .form-control,
      .form-select,
      .custom-dropdown,
      .dropdown-arrow-wrapper input[type="text"],
      .dropdown-arrow-wrapper input.form-control,
      input[type="text"],
      input[type="number"],
      input[type="email"],
      input[type="password"],
      textarea,
      select {
        background-color: #D1EAE7 !important;
        color: #75908E !important;
        border: none !important;
        box-shadow: none !important;
      }

      #customerNameInput::placeholder,
      #paymentMethodInput::placeholder {
        color: #75908E !important;
        opacity: 1;
      }
      .form-control:focus,
      .form-select:focus,
      input[type="text"]:focus,
      input[type="number"]:focus,
      input[type="email"]:focus,
      input[type="password"]:focus,
      textarea:focus,
      select:focus {
        background-color: #D1EAE7 !important;
        color: #75908E !important;
        border: none !important;
        box-shadow: 0 0 0 0.2rem rgba(57,92,88,0.10);
      }
     
      .custom-dropdown-list .dropdown-item {
        color: #395C58;
      }
      .custom-dropdown-list .dropdown-item:hover {
        background: #e6f7f5;
        color: #2C4744;
      }

      select.form-select.service-select {
        color-scheme: light;
      }

      /* Card Container Styling */
      .order-card-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 1rem;
      }

      .order-card-container .card {
        background: linear-gradient(135deg, #ffffff 0%, #f8fffe 100%);
        border: none;
        border-radius: 1rem;
        box-shadow: 0 10px 30px rgba(57, 92, 88, 0.1);
        transition: all 0.3s ease;
        overflow: hidden;
      }

      .order-card-container .card:hover {
        box-shadow: 0 15px 40px rgba(57, 92, 88, 0.15);
        transform: translateY(-2px);
      }

      .card-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #395C58;
        letter-spacing: -0.5px;
      }

      .card-title i {
        color: #395C58;
        font-size: 1.3rem;
      }

      /* Header styling */
      .card .d-flex.justify-content-between {
        padding-bottom: 1rem;
        border-bottom: 2px solid #E6F7F5;
        margin-bottom: 2rem !important;
      }

      /* Form section styling */
      .form-label {
        color: #2C4744 !important;
        font-weight: 600 !important;
        margin-bottom: 0.75rem !important;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }

      /* Enhanced button styling */
      .btn.filter-btn {
        border-radius: 0.75rem !important;
        padding: 0.5rem 1.25rem !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
      }

      .btn.filter-btn:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
      }

      #placeOrderBtn {
        border-radius: 0.75rem !important;
        padding: 1rem 2rem !important;
        font-weight: 700 !important;
        font-size: 1.1rem !important;
        transition: all 0.3s ease !important;
        box-shadow: 0 4px 15px rgba(57, 92, 88, 0.3) !important;
        text-transform: uppercase;
        letter-spacing: 1px;
      }

      #placeOrderBtn:hover:not(:disabled) {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 25px rgba(57, 92, 88, 0.4) !important;
      }

      #placeOrderBtn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: 0 2px 8px rgba(57, 92, 88, 0.1) !important;
      }

      /* Subtotal and Total styling */
      #totalAmount {
        color: #395C58 !important;
        font-weight: 700 !important;
        font-size: 1.25rem !important;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
      }

      /* Input focus enhancement */
      .form-control:focus,
      .form-select:focus {
        box-shadow: 0 0 0 0.25rem rgba(57, 92, 88, 0.15) !important;
        transform: translateY(-1px);
        transition: all 0.2s ease;
      }

      /* Service selector group styling */
      .service-selector-group {
        background: rgba(209, 234, 231, 0.3);
        padding: 1rem;
        border-radius: 0.75rem;
        margin-bottom: 1rem !important;
        border: 1px solid rgba(57, 92, 88, 0.1);
        transition: all 0.2s ease;
      }

      .service-selector-group:hover {
        background: rgba(209, 234, 231, 0.5);
        border-color: rgba(57, 92, 88, 0.2);
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(57, 92, 88, 0.1);
      }

      /* Service counter styling */
      .service-counter-group {
        background: rgba(209, 234, 231, 0.3);
        padding: 1.25rem;
        border-radius: 0.75rem;
        border: 1px solid rgba(57, 92, 88, 0.1);
        transition: all 0.2s ease;
      }

      .service-counter-group:hover {
        background: rgba(209, 234, 231, 0.5);
        border-color: rgba(57, 92, 88, 0.2);
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(57, 92, 88, 0.1);
      }

      .counter-btn {
        width: 40px;
        height: 40px;
        border-radius: 50% !important;
        border: 2px solid #395C58 !important;
        color: #395C58 !important;
        background: white !important;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease !important;
      }

      .counter-btn:hover {
        background: #395C58 !important;
        color: white !important;
        transform: scale(1.1);
        box-shadow: 0 2px 8px rgba(57, 92, 88, 0.3) !important;
      }

      .service-counter {
        border: 2px solid #395C58 !important;
        background: white !important;
        color: #395C58 !important;
        font-weight: 700 !important;
        font-size: 1.1rem !important;
      }

      .service-counter:focus {
        box-shadow: 0 0 0 0.25rem rgba(57, 92, 88, 0.15) !important;
        border-color: #395C58 !important;
      }

      .service-counter-group h6 {
        color: #2C4744 !important;
        margin-bottom: 0.25rem !important;
      }

      .service-counter-group .text-muted {
        color: #75908E !important;
        font-weight: 500;
      }

      /* Responsive design */
      @media (max-width: 768px) {
        .order-card-container {
          margin: 1rem auto;
          padding: 0 0.5rem;
        }
        
        .order-card-container .card {
          border-radius: 0.75rem;
          padding: 1.5rem !important;
        }
        
        .card-title {
          font-size: 1.25rem;
        }
        
        .service-counter-group {
          padding: 1rem;
        }
        
        .counter-btn {
          width: 35px;
          height: 35px;
        }
      }
    </style>
  </head>
  <body>
    <div class="order-card-container">
      <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="card-title mb-0">
            <i class="fas fa-plus-circle me-2"></i>New Laundry Order
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

        <!-- New Order Form -->
        <form id="orderForm" method="POST" action="">
          <div class="mb-3">

            <!-- Customer Name Input -->
            <label for="customerNameInput" class="form-label fw-bold">Customer Name</label>
            <input type="text" class="form-control" id="customerNameInput" name="customerName" placeholder="Enter customer name" required autocomplete="off">
            <!-- End of Customer Name Input -->
          </div>

          <!-- Service Selection -->
          <div class="mb-3">
            <label class="form-label fw-bold">Services</label>
            
            <!-- Regular Service Counter -->
            <div class="service-counter-group mb-3">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <h6 class="mb-1 fw-bold">Regular Service</h6>
                  <small class="text-muted">₱120.00 per load</small>
                </div>
                <div class="d-flex align-items-center">
                  <button type="button" class="btn btn-outline-secondary btn-sm counter-btn" data-action="decrease" data-service="regular">
                    <i class="fa fa-minus"></i>
                  </button>
                  <input type="number" class="form-control mx-2 text-center service-counter" 
                         id="regularServiceCount" min="0" value="0" style="width: 80px;" readonly>
                  <button type="button" class="btn btn-outline-secondary btn-sm counter-btn" data-action="increase" data-service="regular">
                    <i class="fa fa-plus"></i>
                  </button>
                </div>
              </div>
            </div>

            <!-- Extra Heavy Load Counter -->
            <div class="service-counter-group mb-3">
              <div class="d-flex align-items-center justify-content-between">
                <div>
                  <h6 class="mb-1 fw-bold">Extra Heavy Load</h6>
                  <small class="text-muted">₱175.00 per load</small>
                </div>
                <div class="d-flex align-items-center">
                  <button type="button" class="btn btn-outline-secondary btn-sm counter-btn" data-action="decrease" data-service="heavy">
                    <i class="fa fa-minus"></i>
                  </button>
                  <input type="number" class="form-control mx-2 text-center service-counter" 
                         id="heavyServiceCount" min="0" value="0" style="width: 80px;" readonly>
                  <button type="button" class="btn btn-outline-secondary btn-sm counter-btn" data-action="increase" data-service="heavy">
                    <i class="fa fa-plus"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Hidden inputs to store service counts -->
          <input type="hidden" name="regularServiceCount" id="regularServiceCountInput">
          <input type="hidden" name="heavyServiceCount" id="heavyServiceCountInput">

          <div class="mb-3">

            <!-- Payment Method Selection -->
            <label for="paymentMethodInput" class="form-label fw-bold">Payment Method</label>
            <div class="dropdown-arrow-wrapper" style="position:relative;">
              <input class="form-control custom-dropdown" list="paymentMethodList" id="paymentMethodInput" placeholder="Type to search payment method" autocomplete="off">
              <button type="button" class="dropdown-btn" tabindex="-1" id="paymentMethodDropdownBtn">
                <i class="fa fa-chevron-down"></i>
              </button>
              <div class="custom-dropdown-list" id="paymentMethodDropdownList">

                <?php
                // Fetch payment methods from the database
                $paymentMethods = $con->getAllPaymentMethods();
                if ($paymentMethods) {
                  foreach ($paymentMethods as $method) {
                    echo '<div class="dropdown-item" data-payment-method-id="' . htmlspecialchars($method['PaymentMethodID']) . '">' 
                      . htmlspecialchars($method['PaymentMethodName']) 
                      . '</div>';
                  }
                } else {
                  echo '<div class="dropdown-item">No payment methods available.</div>';
                }
                ?>
              </div>
            </div>

            <!-- Hidden input to store selected payment method ID -->
            <input type="hidden" name="selectedPaymentMethodId" id="selectedPaymentMethodIdInput">

            <!-- End of Payment Method Selection -->
          </div>

          <!-- Total Amount Display -->
          <div class="mb-3">
            <label class="form-label fw-bold">Total Amount</label>
            <div>
              <span id="totalAmount" name="total_amount" class="fs-5 fw-semibold">₱0.00</span>
            </div>
          </div>

          <!-- Hidden input to store total amount -->
          <input type="hidden" name="total_amount" id="totalAmountInput">
          <!-- End of Total Amount Display -->

          <!-- Place Order Button -->
          <button type="submit" id="placeOrderBtn" name="placeOrderBtn" class="btn btn-success w-100 fw-bold"
            style="font-size:1.15rem; background: #395C58; color: #fff; border-color: #395C58;" disabled>
            Place Order
          </button>
          <!-- End of Place Order Button -->

        </form>
        <!-- End of New Order Form -->

      </div>
    </div>
    
    <script src="userscript.js"></script>

    <script>

      // Customer name input validation
      const customerNameInput = document.getElementById('customerNameInput');

      // Service pricing
      const servicePrices = {
        regular: 120.00,
        heavy: 175.00
      };

      // Counter functionality
      const regularCounter = document.getElementById('regularServiceCount');
      const heavyCounter = document.getElementById('heavyServiceCount');
      const totalAmount = document.getElementById('totalAmount');
      
      // Handle counter button clicks
      document.addEventListener('click', function(e) {
        if (e.target.closest('.counter-btn')) {
          const btn = e.target.closest('.counter-btn');
          const action = btn.getAttribute('data-action');
          const service = btn.getAttribute('data-service');
          
          let counter;
          if (service === 'regular') {
            counter = regularCounter;
          } else if (service === 'heavy') {
            counter = heavyCounter;
          }
          
          let currentValue = parseInt(counter.value) || 0;
          
          if (action === 'increase') {
            counter.value = currentValue + 1;
          } else if (action === 'decrease' && currentValue > 0) {
            counter.value = currentValue - 1;
          }
          
          updateTotal();
          validateOrderForm();
        }
      });
      
      // Function to update total based on service counts
      function updateTotal() {
        const regularCount = parseInt(regularCounter.value) || 0;
        const heavyCount = parseInt(heavyCounter.value) || 0;
        
        const total = (regularCount * servicePrices.regular) + (heavyCount * servicePrices.heavy);
        
        totalAmount.textContent = '₱' + total.toFixed(2);
        document.getElementById('totalAmountInput').value = total.toFixed(2);
        
        // Update hidden inputs
        document.getElementById('regularServiceCountInput').value = regularCount;
        document.getElementById('heavyServiceCountInput').value = heavyCount;
      }

      // Payment method functionality
      const paymentMethodInput = document.getElementById('paymentMethodInput');
      const paymentMethodDropdownList = document.getElementById('paymentMethodDropdownList');
      const selectedPaymentMethodIdInput = document.getElementById('selectedPaymentMethodIdInput');

      // Show dropdown when button is clicked
      paymentMethodDropdownBtn.addEventListener('click', function(e) {
        paymentMethodDropdownList.classList.toggle('show');
      });

      // Show dropdown when input is focused or typed in
      paymentMethodInput.addEventListener('focus', function() {
        paymentMethodDropdownList.classList.add('show');
      });
      paymentMethodInput.addEventListener('input', function() {
        paymentMethodDropdownList.classList.add('show');
      });

      // Hide dropdown when clicking outside
      document.addEventListener('mousedown', function(e) {
        if (!paymentMethodDropdownList.contains(e.target) && e.target !== paymentMethodDropdownBtn && e.target !== paymentMethodInput) {
          paymentMethodDropdownList.classList.remove('show');
        }
      });

      // Store selected payment method ID when a dropdown item is clicked
      paymentMethodDropdownList.addEventListener('mousedown', function(e) {
        if (e.target.classList.contains('dropdown-item')) {
          paymentMethodInput.value = e.target.textContent;
          selectedPaymentMethodIdInput.value = e.target.getAttribute('data-payment-method-id');
          paymentMethodDropdownList.classList.remove('show');
          paymentMethodInput.dispatchEvent(new Event('input'));
          validateOrderForm();
        }
      });

      // Filter payment methods based on input
      paymentMethodInput.addEventListener('input', function() {
        const val = this.value.toLowerCase();
        Array.from(paymentMethodDropdownList.children).forEach(function(item) {
          item.style.display = item.textContent.toLowerCase().includes(val) ? '' : 'none';
        });
        paymentMethodDropdownList.classList.add('show');
      });

      // Form validation
      const placeOrderBtn = document.getElementById('placeOrderBtn');
      
      function validateOrderForm() {
        // Check customer name
        const customerValid = customerNameInput.value.trim() !== '';
      
        // Check at least one service is selected
        const regularCount = parseInt(regularCounter.value) || 0;
        const heavyCount = parseInt(heavyCounter.value) || 0;
        const serviceValid = (regularCount > 0) || (heavyCount > 0);
      
        const paymentMethodValid = selectedPaymentMethodIdInput.value.trim() !== '';
        placeOrderBtn.disabled = !(customerValid && serviceValid && paymentMethodValid);
      }

      // Listen for changes
      customerNameInput.addEventListener('input', validateOrderForm);
      regularCounter.addEventListener('input', validateOrderForm);
      heavyCounter.addEventListener('input', validateOrderForm);
      
      // Initial validation
      validateOrderForm();

      // Handle form submission
      document.getElementById('orderForm').addEventListener('submit', function(e) {
        // Update total amount input before submission
        updateTotal();
      });

    </script>

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
      crossorigin="anonymous"
    ></script>
    <script src="https://cdn.jsdelivr.net/npm/tsparticles@2.11.1/tsparticles.bundle.min.js"></script>
    <script src="/unused/particles.js"></script>
          
  <!-- Include Bootstrap JS and SweetAlert2 -->
  <script src="./bootstrap-5.3.3-dist/js/bootstrap.js"></script>
  <script src="./package/dist/sweetalert2.js"></script>
  <?php echo $sweetAlertConfig; ?>

  </body>
</html>
