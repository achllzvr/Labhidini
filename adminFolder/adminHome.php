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

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Home - Labhidini Laundromat</title>
  <link rel="icon" type="image/png" href="../img/icon.png" />
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous" />
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
    rel="stylesheet" />
  <!-- Custom CSS -->
  <link rel="stylesheet" href="admin.css" />
</head>

<body>
  <!-- particles background -->
  <div id="tsparticles" style="
        position: fixed;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        z-index: -1;
      "></div>
  <!-- main home content -->
  <div class="container py-5" style="flex: 1 0 auto">
    <div class="header d-flex align-items-center justify-content-between rounded-4 px-4 py-3 mb-4"
      style="background: #bde7e3">
      <div class="d-flex align-items-center" style="gap: 18px; margin-left: 12px">
        <img src="../img/icon.png" alt="Labhidini Logo" style="
              width: 56px;
              height: 56px;
              border-radius: 50%;
              background: #BFE6E3;
              object-fit: cover;
            " />
        <span style="
              font-size: 1.5rem;
              color: #395c58;
              margin-left: 8px;
            ">Home</span>
      </div>
      <div class="d-flex align-items-center" style="gap: 8px; margin-right: 8px;">
        <!-- Logout Button -->
        <a href="../logout.php" id="logoutCard" class="d-flex align-items-center px-3 py-2" title="Logout"
          style="background-color: #395c58; color: #fff; border-radius: 15px; font-weight: 600; min-width: 100px; box-shadow: 0 4px 24px 0 rgba(31,38,135,0.1); text-decoration: none; transition: background 0.2s, color 0.2s; margin-right: 5px;"
          onmouseover="this.style.backgroundColor='#2C4744'" onmouseout="this.style.backgroundColor='#395c58'"
          onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
          <i class="fa fa-sign-out-alt me-2"></i>
          <span style="font-size: 1.13rem; font-weight: 700;">Logout</span>
          <form id="logoutForm" method="POST" action="../logout.php" style="display: none;">
            <input type="hidden" name="logout" value="1" />
          </form>
        </a>
        <!-- End Logout Button -->
        <!-- Create Customer Account Button -->
        <a href="../registerCustomer.php" id="registerCustomerCard" class="d-flex align-items-center px-3 py-2" title="Create Customer Account"
          style="background-color: #395c58; color: #fff; border-radius: 15px; font-weight: 600; min-width: 100px; box-shadow: 0 4px 24px 0 rgba(31,38,135,0.1); text-decoration: none; transition: background 0.2s, color 0.2s; margin-right: 5px;"
          onmouseover="this.style.backgroundColor='#2C4744'" onmouseout="this.style.backgroundColor='#395c58'"
          onclick="event.preventDefault(); document.getElementById('registerCustomerForm').submit();">
          <i class="fa fa-user-plus me-2"></i>
          <span style="font-size: 1.13rem; font-weight: 700;">Create Customer</span>
          <form id="registerCustomerForm" method="POST" action="../registerCustomer.php" style="display: none;">
            <input type="hidden" name="registerCustomer" value="1" />
          </form>
        </a>
        <!-- End Create Customer Account Button -->
        <!-- Create Admin Account Button -->
        <a href="../registerAdmin.php" id="registerAdminCard" class="d-flex align-items-center px-3 py-2" title="Create Admin Account"
          onmouseover="this.style.backgroundColor='#2C4744'" onmouseout="this.style.backgroundColor='#395c58'"
          onclick="event.preventDefault(); document.getElementById('registerAdminForm').submit();"
          <?php if($_SESSION['adminRole'] == 'Employee') { 
            echo 'style="background-color:rgb(100, 149, 143); color: #fff; pointer-events: none; cursor: default; border-radius: 15px; font-weight: 600; min-width: 100px; box-shadow: 0 4px 24px 0 rgba(31,38,135,0.1); text-decoration: none; transition: background 0.2s, color 0.2s; margin-right: 5px;"';
          } else {
            echo 'style="background-color: #395c58; color: #fff; border-radius: 15px; font-weight: 600; min-width: 100px; box-shadow: 0 4px 24px 0 rgba(31,38,135,0.1); text-decoration: none; transition: background 0.2s, color 0.2s; margin-right: 5px;"';
          } ?>>
          <i class="fa fa-user-plus me-2"></i>
          <span style="font-size: 1.13rem; font-weight: 700;">Create Admin</span>
          <form id="registerAdminForm" method="POST" action="../registerAdmin.php" style="display: none;">
            <input type="hidden" name="registerAdmin" value="1" />
          </form>
        </a>
        <!-- End Admin Customer Account Button -->
        <a href="../fpasswordAdmin.php" id="profileCard" class="d-flex align-items-center px-3 py-2"
          style="text-decoration: none;" title="Change password">
          <div class="d-flex flex-column align-items-end text-end flex-grow-1"
            style="line-height: 1.2; margin-right: 10px">
            <span id="profileName" style="font-size: 1.13rem; font-weight: 700; color: #395c58">

              <?php echo $_SESSION['adminName']; ?>

            </span>
            <span style="font-size: 0.85rem; color: #7a9c98">
              Click to see user settings
            </span>
          </div>
          <div class="profile-picture-wrapper ms-2" style="position: relative">
            <img id="profileIcon" src="../img/profile.jpg" alt="Profile" class="profile-picture" style="
                  width: 44px;
                  height: 44px;
                  border-radius: 50%;
                  object-fit: cover;
                " />
            <!-- No file input, icon only -->
          </div>
        </a>
      </div>
    </div>
    <div class="row g-4 align-items-stretch justify-content-center">
      <div class="col-lg-6 col-md-8 d-flex flex-column gap-4">
        <div class="card about-card p-3 h-100 position-relative" id="newOrderCard" style="cursor: pointer">
          <div class="card-body d-flex align-items-center">
            <span class="card-icon me-3">
              <i class="fa-solid fa-square-plus"></i>
            </span>
            <div class="card-content flex-grow-1">
              <h5 class="card-title mb-1">New Order</h5>
              <p class="card-text mb-0">Log new customer order</p>
            </div>
            <span class="card-arrow">
              <i class="fas fa-chevron-right"></i>
            </span>
          </div>
        </div>
        <div class="card about-card p-3 h-100 position-relative" id="orderListCard" style="cursor: pointer">
          <div class="card-body d-flex align-items-center">
            <span class="card-icon me-3">
              <i class="fas fa-receipt"></i>
            </span>
            <div class="card-content flex-grow-1">
              <h5 class="card-title mb-1">Order List</h5>
              <p class="card-text mb-0">View all your orders</p>
            </div>
            <span class="card-arrow">
              <i class="fas fa-chevron-right"></i>
            </span>
          </div>
        </div>
        <div class="card about-card p-3 h-100 position-relative" id="customerListCard" style="cursor: pointer">
          <div class="card-body d-flex align-items-center">
            <span class="card-icon me-3">
              <i class="fa-solid fa-users"></i>
            </span>
            <div class="card-content flex-grow-1">
              <h5 class="card-title mb-1">Customer List</h5>
              <p class="card-text mb-0">View all customers</p>
            </div>
            <span class="card-arrow">
              <i class="fas fa-chevron-right"></i>
            </span>
          </div>
        </div>
        <div class="card about-card p-3 h-100 position-relative" id="salesCard" style="cursor: pointer">
          <div class="card-body d-flex align-items-center">
            <span class="card-icon me-3">
              <i class="fa-solid fa-money-bills"></i>
            </span>
            <div class="card-content flex-grow-1">
              <h5 class="card-title mb-1">Sales</h5>
              <p class="card-text mb-0">View all sales</p>
            </div>
            <span class="card-arrow">
              <i class="fas fa-chevron-right"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="footer-labhidini" style="flex-shrink: 0">
    &copy; 2025 Labhidini Laundromat
  </div>

  <!-- Profile Modal -->
  <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="profileModalLabel">Change Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="profileForm">
            <div class="mb-3 d-flex flex-column align-items-center">
              <div class="profile-picture-wrapper" style="position: relative">
                <img id="profileIconModal" src="../img/profile.jpg" alt="Profile" class="profile-picture" style="
                      width: 70px;
                      height: 70px;
                      border-radius: 50%;
                      object-fit: cover;
                    " />
                <!-- No file input, icon only -->
              </div>
            </div>
            <div class="mb-3">
              <label for="newPasswordInput" class="form-label" style="color: #395c58">New Password</label>
              <div class="password-wrapper">
                <input type="password" class="form-control" id="newPasswordInput" required />
                <span class="toggle-password" data-target="newPasswordInput">
                  <i class="fa-regular fa-eye"></i>
                </span>
              </div>
            </div>
            <div class="mb-3">
              <label for="confirmPasswordInput" class="form-label" style="color: #395c58">Confirm Password</label>
              <div class="password-wrapper">
                <input type="password" class="form-control" id="confirmPasswordInput" required />
                <span class="toggle-password" data-target="confirmPasswordInput">
                  <i class="fa-regular fa-eye"></i>
                </span>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" id="saveProfileBtn" class="btn filter-btn" style="
                border-radius: 18px;
                font-weight: 500;
                font-size: 1rem;
                padding: 0.6rem 2.2rem;
                background: #395c58;
                color: #fff;
                border: none;
                box-shadow: 0 2px 8px rgba(57, 92, 88, 0.07);
              ">
            Save
          </button>
          <form id="logoutForm" method="POST" action="../logout.php">
            <button name="logout" type="submit" class="btn filter-btn" style="
                  border-radius: 18px;
                  font-weight: 500;
                  font-size: 1rem;
                  padding: 0.6rem 2.2rem;
                  background: #466c69;
                  color: #fff;
                  border: none;
                  box-shadow: 0 2px 8px rgba(57, 92, 88, 0.07);
                " data-bs-dismiss="modal">
              Log out
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- scripts for frontend functionality -->
  <script src="adminscript.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/tsparticles@2.11.1/tsparticles.bundle.min.js"></script>
  <script src="../particles.js"></script>
  <script>
    // Password visibility toggle for profile modal
    document.querySelectorAll('#profileModal .toggle-password').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const input = document.getElementById(btn.getAttribute('data-target'));
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
          input.type = 'text';
          icon.classList.remove('fa-eye');
          icon.classList.add('fa-eye-slash');
        } else {
          input.type = 'password';
          icon.classList.remove('fa-eye-slash');
          icon.classList.add('fa-eye');
        }
      });
    });
  </script>
</body>

</html>