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

// Check if add service has been clicked
if (isset($_POST['fAdd_service'])){

      // Get the form data for Full Service
      $service_name = $_POST['fService_name'];
      $service_desc = $_POST['fService_desc'];
      $service_type = "1"; // Specify the service type

      $userID = $con->addService($service_name, $service_desc, 6, $service_type);

      if ($userID) {
        $sweetAlertConfig = "
        <script>
        Swal.fire({
          icon: 'success',
          title: 'Service Added Successfully',
          text: 'You have successfully added a service.',
          confirmButtonText: 'OK'
        }).then(() => {
          window.location.href = 'services.php';
        });
        </script>
        ";
      } else {
        $sweetAlertConfig = "
         <script>
        Swal.fire({
          icon: 'error',
          title: 'Service wasn't added',
          text: 'An error occurred while adding the service. Please try again.',
          confirmButtonText: 'OK'
        });
        </script>"
        
        ;
      }
    }

// Check if add service has been clicked
if (isset($_POST['sAdd_service'])){

      // Get the form data for Full Service
      $service_name = $_POST['sService_name'];
      $service_desc = $_POST['sService_desc'];
      $service_type = "2";

      $userID = $con->addService($service_name, $service_desc, 6, $service_type);

      if ($userID) {
        $sweetAlertConfig = "
        <script>
        Swal.fire({
          icon: 'success',
          title: 'Service Added Successfully',
          text: 'You have successfully added a service.',
          confirmButtonText: 'OK'
        }).then(() => {
          window.location.href = 'services.php';
        });
        </script>
        ";
      } else {
        $sweetAlertConfig = "
         <script>
        Swal.fire({
          icon: 'error',
          title: 'Service wasn't added',
          text: 'An error occurred while adding the service. Please try again.',
          confirmButtonText: 'OK'
        });
        </script>"
        ;
      }
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Services - Labhidini Laundromat</title>
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
      /* Set hover color on tables to #C4E4E2 and remove any filter/darkening */
      .table-hover > tbody > tr:hover {
        --bs-table-accent-bg: #C4E4E2 !important;
        background-color: #C4E4E2 !important;
        filter: none !important;
      }
      /* Remove border from icon buttons when clicked */
      .btn:focus, .btn:active {
        box-shadow: none !important;
        outline: none !important;
        border: none !important;
      }
      /* Custom input styles for modal textboxes */
      .modal-content input.form-control {
        background-color: rgba(197,229,226,0.16) !important;
        color: #395C58 !important;
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
      }
      .modal-content input.form-control:focus {
        background-color: rgba(197,229,226,0.16) !important;
        color: #395C58 !important;
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
      }
      .modal-content input.form-control::placeholder {
        color: #395C58 !important;
        opacity: 0.7;
      }
    </style>
</head>
<body>
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
  <div class="order-card-container">
    <div class="card p-4 mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
          <span class="card-title fw-bold fs-4 mb-0">Full Service & Drop-Off</span>
          <button class="btn p-0 ms-2 service-action-btn" style="color:#395c58; font-size:1.7rem;" title="Add Regular Service" id="addFullServiceBtn" data-bs-toggle="modal" data-bs-target="#addFullServiceModal">
            <i class="fa fa-plus-circle" style="color:#395c58;"></i>
          </button>
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
      <div class="table-responsive position-relative">
        <table class="transaction-table table-hover w-100" style="table-layout:fixed;">
          <thead class="table-light">
            <tr>
              <th style="width:28%;" class="text-nowrap">Service Name</th>
              <th style="width:44%;">Details</th>
              <th style="width:14%;" class="text-nowrap text-center">Edit</th>
              <th style="width:14%;" class="text-nowrap text-center">Delete</th>
            </tr>
          </thead>
          <tbody>

            <!-- Fetch services from the database -->
            <?php

            // Prepare the SQL statement to fetch services
            $services = $con->getAllServicesList1();

            // For each services, create a table row
            // Assuming $services is an array of associative arrays
            // Each associative array contains laundry_id, laundry_name, laundry_desc, and laundry_type
            foreach($services as $servicesFS) {
            ?>

            <!-- Full Service & Drop-Off -->
            <tr class="service-row">
              
              <!-- Service Name -->
              <td class="fw-bold align-middle" style="color:#395c58; border-radius:12px 0 0 12px;">

                <?php echo $servicesFS['laundry_name']; ?>

              </td>

              <!-- Description of the service -->
              <td class="align-middle text-truncate" style="color:#395c58; max-width:1px;">

                <?php echo $servicesFS['laundry_desc']; ?>

              </td>

              <td class="text-center align-middle text-nowrap">
                <span class="service-action-icon" title="Edit" style="color:#395c58;">
                  
                  <!-- Form to handle editing the service -->
                  <form action="editService.php" method="POST">

                    <!-- Laundry ID hidden input -->
                    <input type="hidden" name="service_id" value="<?php echo $servicesFS['laundry_id']; ?>">

                      <!-- button to redirect to update student page -->
                      <button type="submit" class="btn">
                        <i class="fa-solid fa-pen-to-square" style="color:#395c58;"></i>
                      </button>

                  <!-- End of Form to handle editing the service -->
                  </form>

                </span>
              </td>

              <td class="text-center align-middle text-nowrap">
                <span class="service-action-icon" title="Delete" style="color:#395c58;">

                  <!-- Form to handle deletion of the service -->
                  <form action="deleteService.php" method="POST">

                    <!-- Service ID hidden input -->
                    <input type="hidden" name="service_id" value="<?php echo $servicesFS['laundry_id']; ?>">

                      <!-- button to delete the service -->
                      <button type="submit" class="btn">
                          <i class="fa-solid fa-trash" style="color:#395c58;"></i>
                        </button>

                  <!-- End of Form to handle deletion of the service -->
                  </form>

                </span>
              </td>
            </tr>
            <!-- End of Self-Service -->

            <!-- Close the foreach loop -->
            <?php

            }

            ?>
            
          </tbody>
        </table>
      </div>
    </div>
    <div class="card p-4 mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
          <span class="card-title fw-bold fs-4 mb-0">Self-Service</span>
          <button class="btn p-0 ms-2 service-action-btn" style="color:#395c58; font-size:1.7rem;" title="Add Regular Service" id="addSelfServiceBtn" data-bs-toggle="modal" data-bs-target="#addSelfServiceModal">
            <i class="fa fa-plus-circle" style="color:#395c58;"></i>
          </button>
        </div>
      </div>
      <div class="table-responsive">
        <table class="transaction-table table-hover w-100" style="table-layout:fixed;">
          <thead class="table-light">
            <tr>
              <th style="width:28%;" class="text-nowrap">Service Name</th>
              <th style="width:44%;">Details</th>
              <th style="width:14%;" class="text-nowrap text-center">Edit</th>
              <th style="width:14%;" class="text-nowrap text-center">Delete</th>
            </tr>
          </thead>
          <tbody>

            <!-- Fetch services from the database -->
            <?php

            // Prepare the SQL statement to fetch services
            $services = $con->getAllServicesList2();

            // For each services, create a table row
            // Assuming $services is an array of associative arrays
            // Each associative array contains laundry_id, laundry_name, laundry_desc, and laundry_type
            foreach($services as $servicesSS) {
            ?>

            <!-- Self-Service -->
            <tr class="service-row">
              
              <!-- Service Name -->
              <td class="fw-bold align-middle" style="color:#395c58; border-radius:12px 0 0 12px;">
                
                <?php echo $servicesSS['laundry_name']; ?>

              </td>

              <!-- Description of the service -->
              <td class="align-middle text-truncate" style="color:#395c58; max-width:1px;">

                <?php echo $servicesSS['laundry_desc']; ?>

              </td>

              <td class="text-center align-middle text-nowrap">
                <span class="service-action-icon" title="Edit" style="color:#395c58;">
                  
                  <!-- Form to handle editing the service -->
                  <form action="editService.php" method="POST">

                    <!-- Laundry ID hidden input -->
                    <input type="hidden" name="service_id" value="<?php echo $servicesSS['laundry_id']; ?>">

                      <!-- button to redirect to update student page -->
                      <button type="submit" class="btn">
                        <i class="fa-solid fa-pen-to-square" style="color:#395c58;"></i>
                      </button>

                  <!-- End of Form to handle editing the service -->
                  </form>

                </span>
              </td>

              <td class="text-center align-middle text-nowrap">
                <span class="service-action-icon" title="Delete" style="color:#395c58;">

                  <!-- Form to handle deletion of the service -->
                  <form action="deleteService.php" method="POST">

                    <!-- Service ID hidden input -->
                    <input type="hidden" name="service_id" value="<?php echo $servicesSS['laundry_id']; ?>">

                      <!-- button to delete the service -->
                      <button type="submit" class="btn">
                          <i class="fa-solid fa-trash" style="color:#395c58;"></i>
                        </button>

                  <!-- End of Form to handle deletion of the service -->
                  </form>

                </span>
              </td>
            </tr>
            <!-- End of Self-Service -->

            <!-- Close the foreach loop -->
            <?php

            }

            ?>

          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Add Full Service Modal -->
  <div class="modal fade" id="addFullServiceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" id="courseForm" method="POST" action="">
        <div class="modal-header">
          <h5 class="modal-title">Add New Service to 'Full Service & Drop-Off'</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="text" name="fService_name" id="fService_name" class="form-control mb-2" placeholder="Service Name" required>
          <div class="invalid-feedback" id="fService_name_feedback">Service name is required.</div>
        </div>
        <div class="modal-body">
          <input type="text" name="fService_desc" id="fService_desc" class="form-control mb-2" placeholder="Service Description" required>
          <div class="invalid-feedback" id="fService_desc_feedback">Service description is required.</div>
        </div>
        <div class="modal-footer">
          <button type="submit" id="fService_button" name="fAdd_service" class="btn btn-success" style="background-color:#395C58; border-color:#395C58;">Add Service</button>
        </div>

      </form>
    </div>
  </div>

  <!-- Add Self Service Modal -->
  <div class="modal fade" id="addSelfServiceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" id="selfServiceForm" method="POST" action="">
        <div class="modal-header">
          <h5 class="modal-title">Add New Service to 'Self Service'</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="text" name="sService_name" id="sService_name" class="form-control mb-2" placeholder="Service Name" required>
          <div class="invalid-feedback" id="sService_name_feedback">Service name is required.</div>
        </div>
        <div class="modal-body">
          <input type="text" name="sService_desc" id="sService_desc" class="form-control mb-2" placeholder="Service Description" required>
          <div class="invalid-feedback" id="sService_desc_feedback">Service description is required.</div>
        </div>
        <div class="modal-footer">
          <button type="submit" id="sService_button" name="sAdd_service" class="btn btn-success" style="background-color:#395C58; border-color:#395C58;">Add Service</button>
        </div>

      </form>
    </div>
  </div>

  <script src="userscript.js"></script>
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
    crossorigin="anonymous"
  ></script>
  <script src="https://cdn.jsdelivr.net/npm/tsparticles@2.11.1/tsparticles.bundle.min.js"></script>
  <script src="/particles.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      document.body.classList.remove("modal-blur-fadeout");
      var modals = document.querySelectorAll(".modal");
      modals.forEach(function (modal) {
        modal.addEventListener("show.bs.modal", function () {
          document.body.classList.remove("modal-blur-fadeout");
          document.body.classList.add("modal-blur");
        });
        modal.addEventListener("hide.bs.modal", function () {
          setTimeout(function () {
            if (!document.querySelectorAll(".modal.show").length) {
              document.body.classList.remove("modal-blur");
              document.body.classList.add("modal-blur-fadeout");
              setTimeout(function () {
                document.body.classList.remove("modal-blur-fadeout");
              }, 50);
            }
          }, 10);
        });
        modal.addEventListener("hidden.bs.modal", function () {
          if (!document.querySelector(".modal.show")) {
            document.body.classList.remove("modal-blur");
          }
        });
      });

      const observer = new MutationObserver(function () {
        if (document.body.classList.contains("modal-open")) {
          document.body.classList.remove("modal-open");
        }
        if (document.body.style.overflow === "hidden") {
          document.body.style.overflow = "";
        }
        if (
          document.body.style.paddingRight &&
          document.body.style.paddingRight !== "0px"
        ) {
          document.body.style.paddingRight = "";
        }
      });
      observer.observe(document.body, {
        attributes: true,
        attributeFilter: ["class", "style"],
      });
        // --- Full Service Modal ---
        const fullServiceModal = document.getElementById('addFullServiceModal');
        const fService_name = document.getElementById('fService_name');
        const fService_button = document.getElementById('fService_button');
        const fService_feedback = document.getElementById('fService_name_feedback');
        fService_button.disabled = true;
      
        function checkFullServiceNameAvailability() {
          const service_name = fService_name.value.trim();
          if (service_name === '') {
            fService_name.classList.remove('is-valid');
            fService_name.classList.add('is-invalid');
            fService_feedback.textContent = 'Service name is required.';
            fService_button.disabled = true;
            return;
          }
          fetch('../ajax/check_service.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `service_name=${encodeURIComponent(service_name)}`,
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.exists) {
                fService_name.classList.remove('is-valid');
                fService_name.classList.add('is-invalid');
                fService_feedback.textContent = 'Service already exists.';
                fService_button.disabled = true;
              } else {
                fService_name.classList.remove('is-invalid');
                fService_name.classList.add('is-valid');
                fService_feedback.textContent = '';
                fService_button.disabled = false;
              }
            })
            .catch((error) => {
              console.error('Error:', error);
            });
        }
      
        fService_name.addEventListener('input', checkFullServiceNameAvailability);
      
        // Reset modal state on open
        fullServiceModal.addEventListener('show.bs.modal', function () {
          fService_name.value = '';
          fService_name.classList.remove('is-valid', 'is-invalid');
          fService_feedback.textContent = '';
          fService_button.disabled = true;
        });
      
        document.getElementById('courseForm').addEventListener('submit', function (e) {
          if (!fService_name.classList.contains('is-valid')) {
            fService_name.classList.add('is-invalid');
            e.preventDefault();
          }
        });
      
        // --- Self Service Modal ---
        const selfServiceModal = document.getElementById('addSelfServiceModal');
        const sService_name = document.getElementById('sService_name');
        const sService_button = document.getElementById('sService_button');
        const sService_feedback = document.getElementById('sService_name_feedback');
        sService_button.disabled = true;
      
        function checkSelfServiceNameAvailability() {
          const service_name = sService_name.value.trim();
          if (service_name === '') {
            sService_name.classList.remove('is-valid');
            sService_name.classList.add('is-invalid');
            sService_feedback.textContent = 'Service name is required.';
            sService_button.disabled = true;
            return;
          }
          fetch('../ajax/check_service.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `service_name=${encodeURIComponent(service_name)}`,
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.exists) {
                sService_name.classList.remove('is-valid');
                sService_name.classList.add('is-invalid');
                sService_feedback.textContent = 'Service already exists.';
                sService_button.disabled = true;
              } else {
                sService_name.classList.remove('is-invalid');
                sService_name.classList.add('is-valid');
                sService_feedback.textContent = '';
                sService_button.disabled = false;
              }
            })
            .catch((error) => {
              console.error('Error:', error);
            });
        }
      
        sService_name.addEventListener('input', checkSelfServiceNameAvailability);
      
        // Reset modal state on open
        selfServiceModal.addEventListener('show.bs.modal', function () {
          sService_name.value = '';
          sService_name.classList.remove('is-valid', 'is-invalid');
          sService_feedback.textContent = '';
          sService_button.disabled = true;
        });
      
        document.getElementById('selfServiceForm').addEventListener('submit', function (e) {
          if (!sService_name.classList.contains('is-valid')) {
            sService_name.classList.add('is-invalid');
            e.preventDefault();
          }
        });
      });

  </script>

  <script src="./bootstrap-5.3.3-dist/js/bootstrap.js"></script>
  <script src="./package/dist/sweetalert2.js"></script>
  <?php echo $sweetAlertConfig; ?>

</body>
</html>