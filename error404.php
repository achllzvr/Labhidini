<?php
// Set the HTTP response code to 404
http_response_code(404);

// Start the session to check if user is logged in
session_start();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>404 - Page Not Found | Labhidini Laundromat</title>
    <link rel="icon" type="image/png" href="img/icon.png" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    
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
    
    <style>
      .error-404 {
        font-size: 8rem;
        font-weight: 700;
        color: rgba(112, 181, 176, 0.8);
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        margin: 0;
        line-height: 1;
      }
      
      .error-icon {
        font-size: 4rem;
        color: rgba(112, 181, 176, 0.7);
        margin-bottom: 1rem;
      }
      
      .error-title {
        font-family: "Montserrat", sans-serif;
        font-weight: 600;
        color: #395c58;
        margin-bottom: 1rem;
      }
      
      .error-message {
        color: #5a7872;
        margin-bottom: 2rem;
        line-height: 1.6;
      }
      
      .glass-card-404 {
        background-color: rgba(255, 255, 255, 0.16) !important;
        border: 3px solid rgba(191, 230, 227, 0.45) !important;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
        border-radius: 18px;
        padding: 3rem 2rem;
        color: #395c58;
        width: 100%;
        max-width: 500px;
        transition: 0.18s ease-in-out;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        text-align: center;
      }
      
      .btn-home {
        background-color: rgba(112, 181, 176, 0.8) !important;
        border: 1px solid rgba(112, 181, 176, 1) !important;
        color: white !important;
        padding: 0.75rem 2rem;
        border-radius: 8px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
        font-weight: 500;
        margin: 0.25rem;
      }
      
      .btn-home:hover {
        background-color: rgba(112, 181, 176, 1) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(112, 181, 176, 0.3);
        color: white !important;
      }
      
      .btn-back {
        background-color: rgba(217, 242, 239, 0.6) !important;
        border: 1px solid rgba(191, 230, 227, 0.8) !important;
        color: #395c58 !important;
        padding: 0.75rem 2rem;
        border-radius: 8px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
        font-weight: 500;
        margin: 0.25rem;
      }
      
      .btn-back:hover {
        background-color: rgba(217, 242, 239, 0.8) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(191, 230, 227, 0.4);
        color: #395c58 !important;
      }
      
      .floating-bubbles {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: -1;
        overflow: hidden;
      }
      
      .bubble {
        position: absolute;
        background: rgba(112, 181, 176, 0.1);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
      }
      
      .bubble:nth-child(1) {
        width: 40px;
        height: 40px;
        left: 10%;
        animation-delay: 0s;
      }
      
      .bubble:nth-child(2) {
        width: 20px;
        height: 20px;
        left: 20%;
        animation-delay: 2s;
      }
      
      .bubble:nth-child(3) {
        width: 50px;
        height: 50px;
        left: 35%;
        animation-delay: 4s;
      }
      
      .bubble:nth-child(4) {
        width: 80px;
        height: 80px;
        left: 50%;
        animation-delay: 0s;
      }
      
      .bubble:nth-child(5) {
        width: 35px;
        height: 35px;
        left: 70%;
        animation-delay: 3s;
      }
      
      .bubble:nth-child(6) {
        width: 45px;
        height: 45px;
        left: 80%;
        animation-delay: 1s;
      }
      
      .bubble:nth-child(7) {
        width: 25px;
        height: 25px;
        left: 90%;
        animation-delay: 5s;
      }
      
      @keyframes float {
        0% {
          transform: translateY(100vh) rotate(0deg);
          opacity: 0;
        }
        10% {
          opacity: 1;
        }
        90% {
          opacity: 1;
        }
        100% {
          transform: translateY(-100px) rotate(360deg);
          opacity: 0;
        }
      }
    </style>
  </head>

  <body>
    <!-- Floating bubbles animation -->
    <div class="floating-bubbles">
      <div class="bubble"></div>
      <div class="bubble"></div>
      <div class="bubble"></div>
      <div class="bubble"></div>
      <div class="bubble"></div>
      <div class="bubble"></div>
      <div class="bubble"></div>
    </div>

    <!-- 404 Error Card -->
    <div class="glass-card-404">
      <!-- Error Icon -->
      <div class="error-icon">
        <i class="fas fa-exclamation-triangle"></i>
      </div>
      
      <!-- 404 Number -->
      <div class="error-404">404</div>
      
      <!-- Error Title -->
      <h2 class="error-title">Oops! Page Not Found</h2>
      
      <!-- Error Message -->
      <p class="error-message">
        The page you're looking for doesn't exist or has been moved. 
        This might happen due to access restrictions or invalid permissions.
      </p>
      
      <!-- Action Buttons -->
      <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-2 mt-4">
        <?php if (isset($_SESSION['adminID'])): ?>
          <!-- If admin is logged in, redirect to admin home -->
          <a href="adminFolder/adminHome.php" class="btn-home">
            <i class="fas fa-home me-2"></i>Go to Admin Dashboard
          </a>
        <?php elseif (isset($_SESSION['CustomerID'])): ?>
          <!-- If customer is logged in, redirect to customer home -->
          <a href="error404.php" class="btn-home">
            <i class="fas fa-home me-2"></i>Go to Dashboard
          </a>
        <?php else: ?>
          <!-- If no one is logged in, redirect to login -->
          <a href="loginAdmin.php" class="btn-home">
            <i class="fas fa-sign-in-alt me-2"></i>Admin Login
          </a>
        <?php endif; ?>
        
        <a href="javascript:history.back()" class="btn-back">
          <i class="fas fa-arrow-left me-2"></i>Go Back
        </a>
      </div>
      
      <!-- Additional Help -->
      <div class="mt-4">
        <small class="text-muted">
          <i class="fas fa-info-circle me-1"></i>
          Need help? Contact your system administrator.
        </small>
      </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  </body>
</html>
