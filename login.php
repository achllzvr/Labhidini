<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session
session_start();

// Checks if there is a user logged in
if (isset($_SESSION['adminID']) || isset($_SESSION['CustomerID'])) {
  
  // If not logged in, redirect to login page
  if (isset($_SESSION['adminID'])) {

    // If an admin is logged in, redirect to admin home page
    header("Location: /washette/adminFolder/adminHome.php");
    exit();

  } else {

    // If a customer is logged in, redirect to user home page
    header("Location: /washette/userFolder/userHome.php");
    exit();
  }

}

// Include the database class file
require_once('classes/database.php');

// Create an instance of the database class
$con = new database();

// Initialize a variable to hold the SweetAlert configuration
$sweetAlertConfig = "";

  // Check if the login form is submitted
  if(isset($_POST['login'])) {

    // Get the username and password from the POST request
    $id = $_POST['userID'];
    $password = $_POST['password'];
  
    // Validate the inputs
    $user = $con->loginCustomer($id, $password);

    // If the user is found, set session variables and show success message
    if ($user) {
      $_SESSION['CustomerID'] = $user['CustomerID'];
      $_SESSION['CustomerFN'] = $user['CustomerFN'];
      $_SESSION['CustomerLN'] = $user['CustomerLN'];
      $_SESSION['RememberMe'] = isset($_POST['rememberMe']) ? true : false;

      $sweetAlertConfig = "
      <script>

      Swal.fire({
        icon: 'success',
        title: 'Login Successful',
        text: 'Welcome, " . addslashes(htmlspecialchars($user['CustomerFN'])) . "!',
        confirmButtonText: 'Continue'
      }).then(() => {
        window.location.href = 'userFolder/userHome.php';
      });

      </script>";
    } else {

      $sweetAlertConfig = "
      <script>

      Swal.fire({
        icon: 'error',
        title: 'Login Failed',
        text: 'Invalid username or password.'
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
    <title>Login - Washette Laundromat</title>
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
    <link rel="stylesheet" href="style.css" />
  </head>

  <body>
    
    <!-- particles background -->
    
    <!-- login form -->
    <div class="glass-card text-center">
      <h2>Customer Login</h2>

      <!-- Start of Form -->
      <form method="POST" action="">

        <!-- User ID input field -->
        <div class="mb-3 text-start">
          <input name="userID" type="text" class="form-control" id="userID" placeholder="Enter your User ID" required />
        </div>

        <!-- Password input with toggle visibility -->
        <div class="mb-4 text-start position-relative">
          <!-- Password input field -->
          <input name="password" type="password" class="form-control" id="password" placeholder="Password" required />
          <!-- Toggle button for password visibility -->
          <button type="button" id="togglePassword" tabindex="-1" style="position: absolute; top: 50%; right: 12px; transform: translateY(-50%); background: none; border: none; padding: 0; outline: none;">
            <i class="fa-regular fa-eye" id="eyeIcon" style="color: #6c8b8b; font-size: 1.2em;"></i>
          </button>
        </div>

        <!-- forgot password links -->
        <div class="d-flex justify-content-between align-items-center mb-3">

          <!-- Link to forgot password page -->
          <a href="fpassword.php" class="link-btn">Forgot password?</a>
        </div>

        <!-- Login button -->
        <button name="login" type="submit" class="btn btn-washette mt-1"> Login </button>

        <!-- Link to Admin login page -->
        <div class="mt-3">
          <p>Are you an admin? <a href="loginAdmin.php" class="link-btn" style="text-decoration: underline; font-weight: bold;" >Login as Admin</a></p>
        </div>

      <!-- End of Form -->
      </form>
    </div>

    <!-- scripts for frontend functionality -->
    <script src="userFolder/userscript.js"></script>

    <script>
      // Toggle password visibility
      const passwordInput = document.getElementById('password');
      const togglePassword = document.getElementById('togglePassword');
      const eyeIcon = document.getElementById('eyeIcon');
      togglePassword.addEventListener('click', function () {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        eyeIcon.classList.toggle('fa-eye');
        eyeIcon.classList.toggle('fa-eye-slash');
      });
    </script>

      <!-- SweetAlert2 -->
      <?php echo $sweetAlertConfig; ?>
      
  </body>
</html>
