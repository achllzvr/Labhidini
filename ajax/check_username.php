<?php

// Find the file in the same project folder
require_once('../classes/database.php');
// Connect to class in the file
$con = new database();

// If there is a POST request with the username
if(isset($_POST['username'])) {
    $username = $_POST['username'];
    
    //Check if the usrname exitsts
    if($con->isUsernameExists($username)) {

        // If the username exists, return a JSON response of true
        echo json_encode((['exists'=>true]));

    } else {

        // If the username does not exist, return a JSON response of false
        echo json_encode((['exists'=>false]));

    }

// If the username is not set, return an error message
} else {

    // If the username is not set, return an error message
    echo json_encode(['error'=>'Invalid Request']);

}