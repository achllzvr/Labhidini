<?php

// Start the session
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Checks if there is a user logged in
if (!isset($_SESSION['adminID']) || isset($_SESSION['CustomerID'])) {

  // If Customer logged in, redirect to the appropriate home page
  if (isset($_SESSION['CustomerID'])) {

    header("Location: /washette/userFolder/userHome.php");
    exit();

  }

}

// Connect to Database
require_once('classes/database.php');
$con = new database();

// Initialize a variable to hold the SweetAlert configuration
$sweetAlertConfig = "";

// Check if the reset password form is submitted
if (isset($_POST['resetPassword'])) {

  // Get the user ID and new password from the POST request
  $userId = $_POST['userId'];

  // Validate the inputs
  if ($_POST['newPassword'] === $_POST['confirmPassword']) {
    $newPasswordHashed = password_hash($_POST['newPassword'], PASSWORD_BCRYPT);
    $result = $con->updateAdminPassword($newPasswordHashed, $userId);

    // If the password is reset successfully, show success message
    if ($result) {
      $sweetAlertConfig = "
      <script>
        Swal.fire({
          icon: 'success',
          title: 'Password Reset Successful',
          text: 'You can now log in with your new password.',
          confirmButtonText: 'OK'
        }).then(() => {
          window.location.href = 'loginAdmin.php';
        });
      </script>";
    } else {
      $sweetAlertConfig = "
      <script>
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to reset password. Please try again.',
          confirmButtonText: 'OK'
        });
      </script>";
    }
  } else {
    $sweetAlertConfig = "
    <script>
      Swal.fire({
        icon: 'warning',
        title: 'Passwords do not match',
        text: 'Please ensure both passwords are the same.',
        confirmButtonText: 'OK'
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
    <title>Reset Password - Washette Laundromat</title>
    <link rel="icon" type="image/png" href="img/icon.png" />

    <!-- Bootstrap CSS -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT"
      crossorigin="anonymous"
    />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
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
    <link rel="stylesheet" href="style.css" />
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
    <!-- forgot password form -->
    <div class="glass-card text-center">
      <h2>Reset Password</h2>
      <button
            type="button"
            class="btn btn-sm filter-btn"
            style="background: #222; color: #baebe6"
            onclick="window.history.back();"
          >
            <i class="fas fa-arrow-left me-1"></i>Back
          </button>
      <form method="POST" action="" class="mt-4">
        <div class="mb-3 text-start">
          <input
            type="text"
            class="form-control"
            id="userId"
            name="userId"
            placeholder="User ID"
            required
          />
        </div>
        <div class="mb-3 text-start position-relative">
          <input
            type="password"
            class="form-control"
            id="newPassword"
            name="newPassword"
            placeholder="New Password"
            required
          />
          <button type="button" id="toggleNewPassword" tabindex="-1"
            style="position: absolute; top: 50%; right: 12px; transform: translateY(-50%); background: none; border: none; padding: 0; outline: none;">
            <i class="fa-regular fa-eye" id="eyeIconNew" style="color: #6c8b8b; font-size: 1.2em;"></i>
          </button>
        </div>
        <div class="mb-3 text-start position-relative">
          <input
            type="password"
            class="form-control"
            id="confirmPassword"
            name="confirmPassword"
            placeholder="Confirm Password"
            required
          />
          <button type="button" id="toggleConfirmPassword" tabindex="-1"
            style="position: absolute; top: 50%; right: 12px; transform: translateY(-50%); background: none; border: none; padding: 0; outline: none;">
            <i class="fa-regular fa-eye" id="eyeIconConfirm" style="color: #6c8b8b; font-size: 1.2em;"></i>
          </button>
        </div>
        <button
          type="submit"
          name="resetPassword"
          id="resetPasswordBtn"
          class="btn btn-washette mt-1"
        >
          Set New Password
        </button>
        <div class="text-center mt-3">
          Remember your password? <a href="loginAdmin.php" class="link-btn" style="text-decoration: underline;">Login</a>
        </div>
      </form>
    </div>

     <!-- scripts for frontend functionality -->
    <script src="userFolder/userscript.js"></script>
    <script>
      // Toggle password visibility for new password
      const newPasswordInput = document.getElementById('newPassword');
      const toggleNewPassword = document.getElementById('toggleNewPassword');
      const eyeIconNew = document.getElementById('eyeIconNew');
      toggleNewPassword.addEventListener('click', function () {
        const type = newPasswordInput.type === 'password' ? 'text' : 'password';
        newPasswordInput.type = type;
        eyeIconNew.classList.toggle('fa-eye');
        eyeIconNew.classList.toggle('fa-eye-slash');
      });

      // Toggle password visibility for confirm password
      const confirmPasswordInput = document.getElementById('confirmPassword');
      const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
      const eyeIconConfirm = document.getElementById('eyeIconConfirm');
      toggleConfirmPassword.addEventListener('click', function () {
        const type = confirmPasswordInput.type === 'password' ? 'text' : 'password';
        confirmPasswordInput.type = type;
        eyeIconConfirm.classList.toggle('fa-eye');
        eyeIconConfirm.classList.toggle('fa-eye-slash');
      });
    </script>

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
      crossorigin="anonymous"
    ></script>
    <script src="https://cdn.jsdelivr.net/npm/tsparticles@2.11.1/tsparticles.bundle.min.js"></script>
    <script src="/particles.js"></script>
  
      <!-- SweetAlert2 -->
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
      <?php echo $sweetAlertConfig; ?>
  
  </body>
</html>
