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
    header("Location: loginAdmin.php");
    exit();
  } else {
    // If a customer is logged in, redirect to user home page
    header("Location: error404.php");
    exit();
  }

}

    require_once('classes/database.php');
    $con = new database();

    $sweetAlertConfig = "";

    if (isset($_POST['register'])){
      if ($_POST['password'] !== $_POST['confpassword']) {
        $sweetAlertConfig = "
          <script>
          Swal.fire({
            icon: 'error',
            title: 'Registration Failed',
            text: 'Passwords do not match.',
            confirmButtonText: 'OK'
          });
          </script>
        ";
      } else {

      $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
      $firstname = $_POST['first_name'];
      $lastname = $_POST['last_name'];
      $username = $_POST['username'];
      $role = $_POST['role'];

      $userID = $con->signupUser($username, $password, $firstname, $lastname, $role);
      $adminID = $con->getLastAdminID();

        if ($userID) {
          $sweetAlertConfig = "
          <script>
          Swal.fire({
            icon: 'success',
            title: 'Registration Successful',
            text: 'Your ID is: $adminID',
            confirmButtonText: 'OK'
          }).then(() => {
            window.location.href = 'adminFolder/adminHome.php';
          });
          </script>
          ";
        } else {
          $sweetAlertConfig = "
          <script>
          Swal.fire({
            icon: 'error',
            title: 'Registration Failed',
            text: 'An error occurred during registration. Please try again.',
            confirmButtonText: 'OK'
          });
          </script>"
          
          ;
        }
      }
    }
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register Admin - Labhidini Laundromat</title>
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
    <!-- register form -->
    <div class="glass-card text-center">
      <h2>Register Admin</h2>
      <!-- Registration Form -->
      <!-- Start of Form -->
      <form id="registrationForm" method="POST" action="" >
        <!-- Input fields for registration -->
                <!-- Username -->
        <div class="mb-3">
          <input type="text" name="username" id="username" class="form-control" placeholder="Username" required>
          <div class="invalid-feedback">Username is required.</div>
        </div>
        <!-- First Name -->
        <div class="mb-3 text-start">
          <input name="first_name" type="text" class="form-control" id="firstname" placeholder="Firstname" required />
          <div class="invalid-feedback">Firstname is required.</div>
        </div>
        <!-- Last Name -->
        <div class="mb-3 text-start">
          <input name="last_name" type="text" class="form-control" id="lastname" placeholder="Last name" required />
          <div class="invalid-feedback">Lastname is required.</div>
        </div>
        <!-- Password input with toggle visibility -->
        <div class="mb-3 text-start position-relative">
          <input name="password"  type="password" class="form-control" id="password" placeholder="Password" required />
          <button type="button" id="togglePassword" tabindex="-1" style="position: absolute; top: 50%; right: 12px; transform: translateY(-50%); background: none; border: none; padding: 0; outline: none;">
            <i class="fa-regular fa-eye" id="eyeIconPassword" style="color: #6c8b8b; font-size: 1.2em;"></i>
          </button>
        </div>
        <!-- Confirm Password input with toggle visibility -->
        <div class="mb-3 text-start position-relative">
          <input name="confpassword" type="password" class="form-control" id="confirmPassword" placeholder="Confirm password" required />
          <button type="button" id="toggleConfirmPassword" tabindex="-1"
            style="position: absolute; top: 50%; right: 12px; transform: translateY(-50%); background: none; border: none; padding: 0; outline: none;">
            <i class="fa-regular fa-eye" id="eyeIconConfirm" style="color: #6c8b8b; font-size: 1.2em;"></i>
          </button>
          <div class="invalid-feedback">Passwords do not match.</div>
        </div>
        <!-- Role selection dropdown -->
        <div class="mb-4 text-start">
          <select name="role" class="form-select" aria-label="Default select example" required>
            <option value="" disabled selected>Select Admin Type</option>
            <option value="Owner">Owner</option>
            <option value="Employee">Employee</option>
          </select>
        </div>
        <!-- Submit button -->
        <button name="register" type="submit" class="btn btn-labhidini mt-1" disabled>Register</button>
        <!-- Back button -->
        <button name="back" class="btn btn-labhidini mt-1" onClick="window.history.back();">Back</button>
      <!-- End of Form -->
      </form>
    </div>

    <!-- scripts for frontend functionality -->
    <script src="userscript.js"></script>

    <script>
      // Toggle password visibility for password
      const passwordInput = document.getElementById('password');
      const togglePassword = document.getElementById('togglePassword');
      const eyeIconPassword = document.getElementById('eyeIconPassword');
      togglePassword.addEventListener('click', function () {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        eyeIconPassword.classList.toggle('fa-eye');
        eyeIconPassword.classList.toggle('fa-eye-slash');
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

    <!-- AJAX -->
    <script>

      // Function to check form validity and enable/disable submit button
      function checkFormValidity() {
        const allValid = [firstName, lastName, username, password, confirmPassword].every(field => field.classList.contains('is-valid'));
        document.querySelector('button[name="register"]').disabled = !allValid;
      }
    
      // Function to validate individual fields
      function validateField(field, validationFn) {
        field.addEventListener('input', () => {
          if (validationFn(field.value)) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
          } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
          }
          checkFormValidity(); // Check form validity on input
        });
      }

      // Validation functions for each field
      const isNotEmpty = (value) => value.trim() !== '';

      // Real-time username validation using AJAX
      const checkUsernameAvailability = (usernameField) => {
        usernameField.addEventListener('input', () => {
          const username = usernameField.value.trim();

          if (username === '') {
            usernameField.classList.remove('is-valid');
            usernameField.classList.add('is-invalid');
            usernameField.nextElementSibling.textContent = 'Username is required.';
            return;
          }

          // Send AJAX request to check username availability
          fetch('ajax/check_username.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `username=${encodeURIComponent(username)}`,
          })
            .then((response) => response.json())
            .then((data) => {
              if (data.exists) {
                usernameField.classList.remove('is-valid');
                usernameField.classList.add('is-invalid');
                usernameField.nextElementSibling.textContent = 'Username is already taken.';
              } else {
                usernameField.classList.remove('is-invalid');
                usernameField.classList.add('is-valid');
                usernameField.nextElementSibling.textContent = '';
              }
              checkFormValidity();
            })
            .catch((error) => {
              console.error('Error:', error);
            });
        });
      };

      // Get form fields
      const firstName = document.getElementById('firstname');
      const lastName = document.getElementById('lastname');
      const username = document.getElementById('username');
      const password = document.getElementById('password');
      const confirmPassword = document.getElementById('confirmPassword');

      const isPasswordMatch = () => password.value === confirmPassword.value && password.value.trim() !== '';

      // Attach real-time validation to each field
      validateField(firstName, isNotEmpty);
      validateField(lastName, isNotEmpty);
      validateField(password, isNotEmpty);
      validateField(confirmPassword, isPasswordMatch);
      
      checkUsernameAvailability(username);

      // Form submission validation
      document.getElementById('registrationForm').addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent form submission for validation

        let isValid = true;

        // Validate all fields on submit
        [firstName, lastName, username, password, confirmPassword].forEach((field) => {
          if (!field.classList.contains('is-valid')) {
            field.classList.add('is-invalid');
            isValid = false;
          }
        });

        // If all fields are valid, submit the form
        if (isValid) {
          this.submit();
        }
      });

      checkFormValidity(); // Initial check to disable submit button
    </script>

    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO"
      crossorigin="anonymous"
    ></script>

    <script src="https://cdn.jsdelivr.net/npm/tsparticles@2.11.1/tsparticles.bundle.min.js"></script>
    <script src="/particles.js"></script>

    <!-- SweetAlert2 -->
    <?php echo $sweetAlertConfig; ?>

  </body>
</html>
