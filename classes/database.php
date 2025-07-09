<?php

class database{

    // Function to open connection with database
    function opencon(){
        return new PDO( 
        'mysql:host=127.0.0.1; 
        dbname=labhidini',   
        username: 'root', 
        password: '');
    }

    // *------------------------------------------ START OF ADMIN FUNCTIONS ----------------------------------------------------*

    // Function to signup user (registration.php)
    function signupUser($username, $password, $firstname, $lastname, $role){
        $con = $this->opencon();

        try{
            $con->beginTransaction();

            $stmt = $con->prepare("INSERT INTO useraccounts (UAUsername, UAFirstName, UALastName, UAPassword, UARole) VALUES (?,?,?,?,?)");
            $stmt->execute([$username, $firstname, $lastname, $password, $role]);

            $userID = $con->lastInsertId();
            $con->commit();

            return $userID;   
        }catch (PDOException $e){
            $con->rollBack();
            return false;
        }

    }

        // Function to check if username exists upon registration
        function isUsernameExists($username){
            // Open connection with database
            $con = $this->opencon();

            // Prepare SQL statement to check if username exists
            $stmt = $con->prepare("SELECT COUNT(*) FROM useraccounts WHERE UAUsername = ?");
            // Executes the statement
            $stmt->execute([$username]);

            // Fetch the count of rows and its values and assign to $count
            $count = $stmt->fetchColumn();

            // Check if the count is greater than 0 and return true if greater than zero, else false
            return $count > 0;
        }

    // Function to login user admin/ employee
    function loginUser($username, $password){

        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to check if username exists
        $stmt = $con->prepare("SELECT * FROM useraccounts WHERE UA_ID = ?");
        // Executes the statement
        $stmt->execute([$username]);

        // Fetch the user data
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password if user exists
        // If user exists and password matches, return user data
        if($user && password_verify($password, $user['UAPassword'])) {
            // If password matches, return user data
            return $user;
        } else {
            // If user does not exist or password does not match, return false
            return false;
        }

    }

    // Function to update admin password
    function updateAdminPassword($password, $admin_id){
        $con = $this->opencon();

        try{
            $con->beginTransaction();

            $stmt = $con->prepare("UPDATE useraccounts SET UAPassword = ? WHERE UA_ID = ?");
            $stmt->execute([$password, $admin_id]);

            $con->commit();

            return true;   
        }catch (PDOException $e){
            $con->rollBack();
            return false;
        }

    }

    // Function for Admin/ Employee to Create a Customer Account
    function addCustomer($firstname, $lastname, $password, $admin_id){
        $con = $this->opencon();

        try{
            $con->beginTransaction();

            $stmt = $con->prepare("INSERT INTO customer (CustomerFN, CustomerLN, CustomerPassword, CreatedBy) VALUES (?,?,?,?)");
            $stmt->execute([$firstname, $lastname, $password, $admin_id]);

            $userID = $con->lastInsertId();
            $con->commit();

            return $userID;   
        }catch (PDOException $e){
            $con->rollBack();
            return false;
        }

    }

    // Function to add a new Service (addService.php)
    function addService($serviceName, $serviceDesc, $statusID, $serviceType){
        $con = $this->opencon();
    
        try{
            $con->beginTransaction();
    
            $stmt = $con->prepare("INSERT INTO laundryservice (LaundryService_Name, LaundryService_Desc, StatusID, LaundryService_Type) VALUES (?,?,?,?)");
            $stmt->execute([$serviceName, $serviceDesc, $statusID, $serviceType]);
    
            $userID = $con->lastInsertId();
            $con->commit();
    
            return $userID;   
        }catch (PDOException $e){
            $con->rollBack();
            return false;
        }
    }

        // Check if Service exists
        function isServiceExists($serviceName){
            // Open connection with database
            $con = $this->opencon();

            // Prepare SQL statement to check if Service exists
            $stmt = $con->prepare("SELECT COUNT(*) FROM laundryservice WHERE LaundryService_Name = ?");
            // Executes the statement
            $stmt->execute([$serviceName]);

            // Fetch the count of rows and its values and assign to $count
            $count = $stmt->fetchColumn();

            // Check if the count is greater than 0 and return true if greater than zero, else false
            return $count > 0;
        }
    
    // Function to get Transaction data (not Transaction Details) by ID (*file used*)
    function getTransactionByID($TransactionID){

        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get Transaction data by ID
        $stmt = $con->prepare("SELECT * FROM transaction WHERE TransactionID = ?");
        // Execute the statement with the student ID
        $stmt->execute([$TransactionID]);

        // Fetch the student data as an associative array
        $transaction_data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return the student data
        return $transaction_data;
    }

    // Function to get Transaction data (not Transaction Details) by ID (*file used*)
    function getTransactionDetails($TransactionID){

        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get Transaction data by ID
        $stmt = $con->prepare("SELECT * FROM transactionDetails WHERE TransactionID = ?");
        // Execute the statement with the student ID
        $stmt->execute([$TransactionID]);

        // Fetch the student data as an associative array
        $transaction_details = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return the student data
        return $transaction_details;
    }

    // Function to update Transaction Status data
    function updateTransactionStatus($status, $admin_id, $transactionID){

        // Establish Connection with Database
        $con = $this->opencon();

        try{
            $con->beginTransaction();

            $query = $con->prepare("UPDATE transaction SET StatusID = ?, UA_ID = ? WHERE TransactionID = ?");
            $query->execute([$status, $admin_id, $transactionID]);

            $con->commit();

            return true;   
        }catch (PDOException $e){
            $con->rollBack();
            return false;
        }

    }

    // Function to get Transaction data (not Transaction Details) by ID
    function getAllTransactions(){

        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get Transaction data by ID
        $stmt = $con->prepare("SELECT 
                                t.TransactionID,
                                CONCAT(c.CustomerFN, ' ', c.CustomerLN) AS CustomerName,
                                DATE_FORMAT(t.TransactionTimestamp, '%M %d, %Y') AS FormattedDate,
                                GROUP_CONCAT(ls.LaundryService_Name SEPARATOR ', ') AS Services,
                                s.StatusName AS Status,
                                t.StatusID AS StatusID,
                                t.TransacTotalAmount
                            FROM transaction t
                            JOIN transactiondetails td ON t.TransactionID = td.TransactionID
                            JOIN customer c ON t.CustomerID = c.CustomerID
                            JOIN laundryservice ls ON td.LaundryID = ls.LaundryID
                            JOIN status s ON t.StatusID = s.StatusID
                            GROUP BY t.TransactionID
                            ORDER BY t.TransactionID DESC;
                            ");
        
        // Execute the statement
        $stmt->execute();

        // Fetch the student data as an associative array
        $transaction_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the student data
        return $transaction_data;
    }

    // Function to get recent Transaction data (not Transaction Details) by ID
    function getRecentTransactions(){

        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get Transaction data by ID
        $stmt = $con->prepare("SELECT 
                                t.TransactionID,
                                CONCAT(c.CustomerFN, ' ', c.CustomerLN) AS CustomerName,
                                DATE_FORMAT(t.TransactionTimestamp, '%M %d, %Y') AS FormattedDate,
                                GROUP_CONCAT(ls.LaundryService_Name SEPARATOR ', ') AS Services,
                                s.StatusName AS Status,
                                t.TransacTotalAmount
                            FROM transaction t
                            JOIN transactiondetails td ON t.TransactionID = td.TransactionID
                            JOIN customer c ON t.CustomerID = c.CustomerID
                            JOIN laundryservice ls ON td.LaundryID = ls.LaundryID
                            JOIN status s ON t.StatusID = s.StatusID
                            GROUP BY t.TransactionID
                            ORDER BY t.TransactionTimestamp DESC
                            LIMIT 10;
                            ");
        
        // Execute the statement
        $stmt->execute();

        // Fetch the student data as an associative array
        $transaction_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the student data
        return $transaction_data;
    }

    // Function to get Transaction data (not Transaction Details) by ID
    function getLatestOrder(){

        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get Transaction data by ID
        $stmt = $con->prepare("SELECT 
                                t.TransactionID,
                                CONCAT(c.CustomerFN, ' ', c.CustomerLN) AS CustomerName,
                                DATE_FORMAT(t.TransactionTimestamp, '%M %d, %Y') AS FormattedDate,
                                GROUP_CONCAT(ls.LaundryService_Name SEPARATOR ', ') AS Services,
                                s.StatusName AS Status,
                                t.TransacTotalAmount
                            FROM transaction t
                            JOIN transactiondetails td ON t.TransactionID = td.TransactionID
                            JOIN customer c ON t.CustomerID = c.CustomerID
                            JOIN laundryservice ls ON td.LaundryID = ls.LaundryID
                            JOIN status s ON t.StatusID = s.StatusID
                            GROUP BY t.TransactionID
                            ORDER BY t.TransactionTimestamp DESC
                            LIMIT 1;
                            ");
        
        // Execute the statement
        $stmt->execute();

        // Fetch the student data as an associative array
        $transaction_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the student data
        return $transaction_data;
    }

    // Function to get ALL services
    function getAllServices(){
        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get all Services
        $stmt = $con->prepare("SELECT
                ls.LaundryID,
                ls.LaundryService_Type as ServiceType,
                CONCAT(ls.LaundryService_Name, ' - ', ls.LaundryService_Desc) AS ServiceName,
                pcl.Price as Price
            FROM laundryservice ls
            LEFT JOIN pricechangelog pcl ON ls.LaundryID = pcl.LaundryID
        ");

        // Execute the statement
        $stmt->execute();

        // Fetch all services as an associative array
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the services data
        return $services;
    }

    // Fetch all customer names and ID for dropdown
    function getAllCustomers(){
        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get all Customers
        $stmt = $con->prepare("SELECT CustomerID, 
        CONCAT(CustomerFN, ' ', CustomerLN) AS FullName FROM customer");
        
        // Execute the statement
        $stmt->execute();

        // Fetch all customers as an associative array
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the customers data
        return $customers;
    }

    // Function to get all Payment Methods
    function getAllPaymentMethods(){
        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get all Payment Methods
        $stmt = $con->prepare("SELECT PaymentMethodID,
                                PMethodName as PaymentMethodName  
                                FROM paymentmethod");

        // Execute the statement
        $stmt->execute();

        // Fetch all payment methods as an associative array
        $payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the payment methods data
        return $payment_methods;
    }

    // Function to insert a new Transaction
    function newOrder($customerID, $admin_id, $paymentMethodID, $subtotal, $discount, $totalAmount){

        // Open connection with database
        $con = $this->opencon();

        try{
            $con->beginTransaction();

            // Prepare SQL statement to insert a new Transaction
            $stmt = $con->prepare("INSERT INTO transaction 
                                    (CustomerID, UA_ID, PaymentMethodID, StatusID, TransacSubtotal, TransacDiscount, TransacTotalAmount) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$customerID, $admin_id, $paymentMethodID, 1, $subtotal, $discount, $totalAmount]);

            // Get the last inserted Transaction ID
            $transactionID = $con->lastInsertId();
            $con->commit();

            return $transactionID;   
        }catch (PDOException $e){
            $con->rollBack();
            return false;
        }

    }

    // Get the latest Transaction ID for a specific Customer
    function getLatestTransactionID($customerID){

        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get the latest Transaction ID for a specific Customer
        $stmt = $con->prepare("SELECT TransactionID FROM transaction WHERE CustomerID = ? ORDER BY TransactionTimestamp DESC LIMIT 1");
        
        // Execute the statement
        $stmt->execute([$customerID]);

        // Fetch the latest Transaction ID
        $transactionID = $stmt->fetchColumn();

        // Return the Transaction ID
        return $transactionID;
    }

    // Function to insert Transaction Details
    function insertTransactionDetails($transactionID, $laundryID, $quantity){

        // Open connection with database
        $con = $this->opencon();

        try{
            $con->beginTransaction();

            // Prepare SQL statement to insert Transaction Details
            $stmt = $con->prepare("INSERT INTO transactiondetails (TransactionID, LaundryID, TDQuantity) VALUES (?, ?, ?)");
            $stmt->execute([$transactionID, $laundryID, $quantity]);

            $con->commit();

            return true;   
        }catch (PDOException $e){
            $con->rollBack();
            return false;
        }

    }

    // Function to get Customer List
    function getCustomerList(){

        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get all Customers
        $stmt = $con->prepare("SELECT CustomerID, 
                                CONCAT(CustomerFN, ' ', CustomerLN) AS FullName FROM customer");
        
        // Execute the statement
        $stmt->execute();

        // Fetch all customers as an associative array
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the customers data
        return $customers;
    }

    // Function to get sales data for the chart
    function getSalesData(){
        // Open connection with database
        $con = $this->opencon();

        // Initialize arrays for sales data and labels
        $salesData = [];
        $labels = [];

        // Example: Get total sales per month for the current year
        $result = $con->query("SELECT DATE_FORMAT(TransactionTimestamp, '%b %Y') as month, SUM(TransacTotalAmount) as total 
                       FROM transaction 
                       WHERE YEAR(TransactionTimestamp) = YEAR(CURDATE())
                       GROUP BY month
                       ORDER BY MIN(TransactionTimestamp) ASC");
        if ($result) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $labels[] = $row['month'];
                $salesData[] = (float)$row['total'];
            }
        }

        // Return the sales data and labels
        return [
            'labels' => $labels,
            'data' => $salesData
        ];
    }

    // Function to get all Full Service & Drop-Off Service
    function getAllServicesList1(){
        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get all Services
        $stmt = $con->prepare("SELECT 
                                LaundryID as laundry_id,
                                LaundryService_Name as laundry_name,
                                LaundryService_Desc as laundry_desc
        FROM laundryservice WHERE StatusID = 6 and LaundryService_Type = 1");
        
        // Execute the statement
        $stmt->execute();

        // Fetch all services as an associative array
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the services data
        return $services;
    }

    // Function to get all Self Service Services
    function getAllServicesList2(){
        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get all Services
        $stmt = $con->prepare("SELECT 
                                LaundryID as laundry_id,
                                LaundryService_Name as laundry_name,
                                LaundryService_Desc as laundry_desc
        FROM laundryservice WHERE StatusID = 6 and LaundryService_Type = 2");

        // Execute the statement
        $stmt->execute();

        // Fetch all services as an associative array
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the services data
        return $services;
    }

    // Function to get service by ID
    function getServiceByID($serviceID){
        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get Service data by ID
        $stmt = $con->prepare("SELECT 
                                LaundryID as laundry_id,
                                LaundryService_Name as laundry_name,
                                LaundryService_Desc as laundry_desc,
                                StatusID as status_id
                                FROM laundryservice WHERE LaundryID = ?");
        
        // Execute the statement with the service ID
        $stmt->execute([$serviceID]);

        // Fetch the service data as an associative array
        $service_data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return the service data
        return $service_data;
    }

    // Function to update Service data
    function updateService($serviceID, $serviceName, $serviceDesc){
        // Open connection with database
        $con = $this->opencon();

        try{
            $con->beginTransaction();

            $stmt = $con->prepare("UPDATE laundryservice SET LaundryService_Name = ?, LaundryService_Desc = ? WHERE LaundryID = ?");
            $stmt->execute([$serviceName, $serviceDesc, $serviceID]);

            $con->commit();

            return true;   
        }catch (PDOException $e){
            $con->rollBack();
            return false;
        }

    }

    // Function to delete Service data
    function deleteService($serviceID){
        // Open connection with database
        $con = $this->opencon();

        try{
            $con->beginTransaction();

            $stmt = $con->prepare("UPDATE laundryservice SET StatusID = 7 WHERE LaundryID = ?");
            $stmt->execute([$serviceID]);

            $con->commit();

            return true;   
        }catch (PDOException $e){
            $con->rollBack();
            return false;
        }

    }

    // Function to get all Statuses
    function getAllStatuses(){
        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get all Statuses
        $stmt = $con->prepare("SELECT StatusID,
                                StatusName as StatusName  
                                FROM status
                                LIMIT 3
                                ");

        // Execute the statement
        $stmt->execute();

        // Fetch all statuses as an associative array
        $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the statuses data
        return $statuses;
    }

        // Function to get last admin ID
        function getLastAdminID(){
            // Open connection with database
            $con = $this->opencon();

            // Prepare SQL statement to get the last Admin ID
            $stmt = $con->prepare("SELECT AdminID FROM admin ORDER BY AdminID DESC LIMIT 1");

            // Execute the statement
            $stmt->execute();

            // Fetch the last Admin ID
            $lastAdminID = $stmt->fetchColumn();

            // Return the last Admin ID
            return $lastAdminID;
        }

    // Function to get Services list (used by admin for display purposes)
    function getPremiumServicesList(){
        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get all Services
        $stmt = $con->prepare("SELECT * FROM laundryservice WHERE LaundryService_Type = 1 AND StatusID = 6");
        
        // Execute the statement
        $stmt->execute();

        // Fetch all services as an associative array
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the services data
        return $services;
    }

    // Function to get Services list (used by admin for display purposes)
    function getRegularServicesList(){
        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get all Services
        $stmt = $con->prepare("SELECT * FROM laundryservice WHERE LaundryService_Type = 2 AND StatusID = 6");
        
        // Execute the statement
        $stmt->execute();

        // Fetch all services as an associative array
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the services data
        return $services;
    }

    // *------------------------------------------ END OF ADMIN FUNCTIONS ----------------------------------------------------*
    
}