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

require_once('../classes/database.php');
$con = new database();

$sales = $con->getSalesData();
$labels = $sales['labels'];
$salesData = $sales['data'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sales - Labhidini Laundromat</title>
  <link rel="icon" type="image/png" href="../img/icon.png" />

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />

  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Arimo&family=Montserrat:wght@400;600&display=swap"
    rel="stylesheet" />

  <!-- Custom CSS -->
  <link rel="stylesheet" href="admin.css" />
</head>

<body>
  <!-- particles background -->
  <div id="tsparticles" style="position: fixed; width: 100%; height: 100%; top: 0; left: 0; z-index: -1;"></div>

  <!-- Sales Card with Chart -->
  <div class="order-card-container">
    <div class="card p-4 no-hover-effect">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="card-title mb-0">
          <i class="fa-solid fa-chart-simple"></i>&nbsp;Sales
        </div>
        <div class="d-flex align-items-center" style="gap: 10px">
          <button type="button" class="btn btn-sm filter-btn" style="background: #222; color: #baebe6"
            onclick="window.location.href='adminHome.php';">
            <i class="fas fa-arrow-left me-1"></i>Back
          </button>
        </div>
      </div>

      <!-- chart.js Canvas -->
      <canvas id="salesChart"></canvas>

    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/tsparticles@2.11.1/tsparticles.bundle.min.js"></script>
  <script src="/particles.js"></script>
  <script src="userscript.js"></script>

  <script>
    // Pass PHP arrays to JS
    const salesLabels = <?php echo json_encode($labels); ?>;
    const salesTotals = <?php echo json_encode($salesData); ?>;
  </script>

  <!-- chart.js config -->
  <script src="chart.js"></script>

</body>

</html>