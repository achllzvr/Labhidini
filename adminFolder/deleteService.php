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

// Database connection file
require_once('../classes/database.php');

// Create an instance of the database class
$con = new database();

// SweetAlert Initialization
$sweetAlertConfig = "";

// Fetch service_id from POST
$service_id = $_POST['service_id'] ?? null;

// Fetch service data based on the service_id
$service_data = $con->getServiceByID($service_id);

// Check if the form is submitted
if(isset($_POST['delete'])) {

  // Get the form data
  $service_id = $_POST['service'];

  // Delete service data
  $userID = $con->deleteService($service_id);

  // Success message if $userID is returned
    if($userID) {
      $sweetAlertConfig = "
      <script>
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: 'Service deleted successfully.',
          confirmButtonText: 'Continue'
        }).then(() => {
          window.location.href = 'services.php';
        });
      </script>";
    } else {
      $sweetAlertConfig = "
      <script>
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to delete service. Please try again.',
          confirmButtonText: 'Try Again'
        });
      </script>";
    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Delete Service</title>
  <link rel="stylesheet" href="admin.css">
  <link rel="stylesheet" href="./bootstrap-5.3.3-dist/css/bootstrap.css">
  <link rel="stylesheet" href="./package/dist/sweetalert2.css">
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
  <style>
    body, label, .form-label, .card-title, h2, input, button, .card {
      color: #395C58 !important;
    }
    .form-control, input[type="text"], input[type="number"] {
      background-color: #C4E5E2 !important;
      color: #5A7C78 !important;
      border: none !important;
      box-shadow: none !important;
      border-radius: 12px !important;
    }
    .form-control:focus, input[type="text"]:focus, input[type="number"]:focus {
      background-color: #C4E5E2 !important;
      color: #5A7C78 !important;
      border: none !important;
      box-shadow: none !important;
      outline: none !important;
      border-radius: 12px !important;
    }
    .btn-admin {
      background: #395C58 !important;
      color: #fff !important;
      border: none !important;
      border-radius: 12px !important;
      font-weight: bold;
      font-size: 1.15rem;
      box-shadow: 0 2px 8px rgba(57, 92, 88, 0.07);
      transition: background 0.15s, box-shadow 0.15s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
    }
    .btn-admin:hover, .btn-admin:focus {
      background: #2C4744 !important;
      color: #fff !important;
      box-shadow: 0 4px 16px rgba(57, 92, 88, 0.13);
      outline: none !important;
    }
    .filter-btn {
      background: #395c58 !important;
      color: #fff !important;
      border: none !important;
      font-size: 0.97rem;
      padding: 2px 14px 2px 10px;
      border-radius: 18px !important;
      margin-left: 8px;
      display: flex;
      align-items: center;
      gap: 4px;
      cursor: pointer;
      transition: background 0.18s, box-shadow 0.18s;
      box-shadow: 0 1px 4px rgba(57, 92, 88, 0.04);
    }
    .filter-btn:hover,
    .filter-btn:focus {
      background: #2c4744 !important;
      color: #fff !important;
      box-shadow: 0 2px 12px rgba(57, 92, 88, 0.1);
    }
  </style>
</head>

<body>
  <div class="order-card-container">
    <div class="card p-4 mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0 card-title">Are you sure you want to delete this service?</h2>
        <button
          type="button"
          class="btn btn-sm filter-btn"
            onclick="window.location.href='adminHome.php';"
        >
          <i class="fas fa-arrow-left me-1"></i>Back
        </button>
      </div>
      <form method="POST" action="">
        <!-- Disabled input to store the service ID -->
        <div class="mb-3">
          <label for="service_id" class="form-label fw-bold">Service ID</label>
          <input type="text" value="<?php echo $service_data['laundry_id']?>" id="service_id" class="form-control" disabled required>
          <input type="hidden" name="service" value="<?php echo $service_data['laundry_id']?>">
        </div>
        <!-- Input fields for service name -->
        <div class="mb-3">
          <label for="service_name" class="form-label fw-bold">Service Name</label>
          <input type="text" name="service_name" value="<?php echo $service_data['laundry_name']?>" id="service_name" class="form-control" disabled required>
        </div>
        <!-- Input fields for service description -->
        <div class="mb-3">
          <label for="service_desc" class="form-label fw-bold">Service Description</label>
          <input type="text" name="service_desc" value="<?php echo $service_data['laundry_desc']?>" id="service_desc" class="form-control" disabled required>
        </div>
        <button type="submit" name="delete" class="btn btn-admin w-100 fw-bold">
          Delete
        </button>
      </form>
    </div>
  </div>
  
  <script src="./bootstrap-5.3.3-dist/js/bootstrap.js"></script>
  <script src="./package/dist/sweetalert2.js"></script>
  <?php echo $sweetAlertConfig; ?>
  
</body>
</html>