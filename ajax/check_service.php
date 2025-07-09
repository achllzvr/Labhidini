<?php

// Find the file in the same project folder
require_once('../classes/database.php');
// Connect to class in the file
$con = new database();

// If there is a POST request with the email
if(isset($_POST['service_name'])) {
    $service_name = $_POST['service_name'];

    //Check if the service name exists
    if($con->isServiceExists($service_name)) {

        // If the service_name exists, return a JSON response of true
        echo json_encode((['exists'=>true]));

    } else {

        // If the service_name does not exist, return a JSON response of false
        echo json_encode((['exists'=>false]));

    }

// If the service_name is not set, return an error message
} else {

    // If the service_name is not set, return an error message
    echo json_encode(['error'=>'Invalid Request']);

}