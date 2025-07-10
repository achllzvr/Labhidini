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

        // Prepare SQL statement to get Transaction details by ID
        $stmt = $con->prepare("SELECT * FROM transactiondetails WHERE TransactionID = ?");
        // Execute the statement with the student ID
        $stmt->execute([$TransactionID]);

        // Fetch the student data as an associative array
        $transaction_details = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return the student data
        return $transaction_details;
    }

    // Function to update Transaction Status data (supports multiple status types)
    function updateTransactionStatus($statusValue, $admin_id, $transactionID, $statusType = 'status'){

        // Establish Connection with Database
        $con = $this->opencon();

        try{
            $con->beginTransaction();

            // Determine which column to update based on status type
            switch($statusType) {
                case 'claim':
                    if ($statusValue == '2') {
                        // Setting to claimed - update both status and date
                        $query = $con->prepare("UPDATE transaction SET ClaimStatus = ?, ClaimStatusDate = NOW(), UA_ID = ? WHERE TransactionID = ?");
                    } else {
                        // Setting to unclaimed - clear the date
                        $query = $con->prepare("UPDATE transaction SET ClaimStatus = ?, ClaimStatusDate = NULL, UA_ID = ? WHERE TransactionID = ?");
                    }
                    break;
                case 'payment':
                    if ($statusValue == '2') {
                        // Setting to paid - update both status and date
                        $query = $con->prepare("UPDATE transaction SET PaymentStatus = ?, PaymentStatusDate = NOW(), UA_ID = ? WHERE TransactionID = ?");
                    } else {
                        // Setting to unpaid - clear the date
                        $query = $con->prepare("UPDATE transaction SET PaymentStatus = ?, PaymentStatusDate = NULL, UA_ID = ? WHERE TransactionID = ?");
                    }
                    break;
                case 'status':
                default:
                    $query = $con->prepare("UPDATE transaction SET StatusID = ?, UA_ID = ? WHERE TransactionID = ?");
                    break;
            }
            
            $query->execute([$statusValue, $admin_id, $transactionID]);

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

        // Prepare SQL statement to get Transaction data - uses CustomerName from transaction table with separate service counts
        $stmt = $con->prepare("SELECT 
                                t.TransactionID,
                                t.CustomerName AS CustomerName,
                                DATE_FORMAT(t.TransactionTimestamp, '%M %d, %Y') AS FormattedDate,
                                COALESCE(SUM(CASE WHEN td.LaundryID = 1 THEN td.TDQuantity ELSE 0 END), 0) AS RegularCount,
                                COALESCE(SUM(CASE WHEN td.LaundryID = 2 THEN td.TDQuantity ELSE 0 END), 0) AS ExtraHeavyCount,
                                s.StatusName AS Status,
                                t.StatusID AS StatusID,
                                t.ClaimStatus AS ClaimStatus,
                                t.PaymentStatus AS PaymentStatus,
                                t.TransacTotalAmount
                            FROM transaction t
                            LEFT JOIN transactiondetails td ON t.TransactionID = td.TransactionID
                            LEFT JOIN status s ON t.StatusID = s.StatusID
                            GROUP BY t.TransactionID, t.CustomerName, t.TransactionTimestamp, s.StatusName, t.StatusID, t.ClaimStatus, t.PaymentStatus, t.TransacTotalAmount
                            ORDER BY t.TransactionTimestamp DESC;
                            ");
        
        // Execute the statement
        $stmt->execute();

        // Fetch the student data as an associative array
        $transaction_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the student data
        return $transaction_data;
    }

    // Function to get ALL active services with current prices for newOrder.php
    function getAllServices(){
        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get all active Services with their latest prices
        $stmt = $con->prepare("SELECT
                ls.LaundryID,
                ls.LaundryService_Type as ServiceType,
                ls.LaundryService_Name as ServiceName,
                ls.LaundryService_Desc as ServiceDesc,
                pc.Price as Price
            FROM laundryservice ls
            JOIN pricechangelog pc ON ls.LaundryID = pc.LaundryID
        ");

        // Execute the statement
        $stmt->execute();

        // Fetch all services as an associative array
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the services data
        return $services;
    }

    // Fetch all customer names and ID for dropdown
    // Fetch all customer names from transaction table
    function getAllCustomers(){
        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get unique customer names from transactions
        $stmt = $con->prepare("SELECT DISTINCT CustomerName AS FullName FROM transaction WHERE CustomerName IS NOT NULL ORDER BY CustomerName");
        
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

    // Function to insert a new Transaction with customer name
    function newOrderWithCustomerName($customerName, $admin_id, $paymentMethodID, $subtotal, $discount, $totalAmount){

        // Open connection with database
        $con = $this->opencon();

        try{
            $con->beginTransaction();

            // Prepare SQL statement to insert a new Transaction with customer name
            $stmt = $con->prepare("INSERT INTO transaction 
                                    (CustomerName, UA_ID, PaymentMethodID, StatusID, TransacSubTotal, TransacDiscount, TransacTotalAmount) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$customerName, $admin_id, $paymentMethodID, 1, $subtotal, $discount, $totalAmount]);

            // Get the last inserted Transaction ID
            $transactionID = $con->lastInsertId();
            $con->commit();

            return $transactionID;   
        }catch (PDOException $e){
            $con->rollBack();
            return false;
        }

    }

    // Function to insert a new Transaction (original with CustomerID)
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

    // Get the latest Transaction ID for a specific Customer by name
    function getLatestTransactionIDByName($customerName){

        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get the latest Transaction ID for a specific Customer by name
        $stmt = $con->prepare("SELECT TransactionID FROM transaction WHERE CustomerName = ? ORDER BY TransactionTimestamp DESC LIMIT 1");
        
        // Execute the statement
        $stmt->execute([$customerName]);

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

    // Function to get Customer List from transaction table
    function getCustomerList(){

        // Open connection with database
        $con = $this->opencon();

        // Prepare SQL statement to get unique customer names from transactions
        $stmt = $con->prepare("SELECT DISTINCT CustomerName AS FullName FROM transaction WHERE CustomerName IS NOT NULL ORDER BY CustomerName");
        
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

    // Function to get all Services and their prices for newOrder.php
    function getAllServicesWithPrices(){
        // Open connection with database
        $con = $this->opencon();
        // Prepare SQL statement to get all active Services with their latest prices
        $stmt = $con->prepare("SELECT
                ls.LaundryID,
                ls.LaundryService_Type as ServiceType,
                ls.LaundryService_Name as ServiceName,
                ls.LaundryService_Desc as ServiceDesc,
                COALESCE(pcl.Price, 0.00) as Price
            FROM laundryservice ls
            LEFT JOIN (
                SELECT LaundryID, Price, 
                       ROW_NUMBER() OVER (PARTITION BY LaundryID ORDER BY PriceChangeID DESC) as rn
                FROM pricechangelog
            ) pcl ON ls.LaundryID = pcl.LaundryID AND pcl.rn = 1
            WHERE ls.StatusID = 6
            ORDER BY ls.LaundryService_Type, ls.LaundryService_Name
        ");
        // Execute the statement
        $stmt->execute();
        // Fetch all services as an associative array
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Return the services data
        return $services;
    }

    // *------------------------------------------ END OF ADMIN FUNCTIONS ----------------------------------------------------*

    // Function to get filtered transactions based on criteria (Performance optimized)
    function getFilteredTransactions($timePeriod = 'day', $orderStatus = '', $claimStatus = '', $paymentStatus = '', $searchTerm = '', $forExport = false){

        try {
            // Open connection with database
            $con = $this->opencon();

            // Base query - uses CustomerName from transaction table with separate service counts
            $query = "SELECT 
                        t.TransactionID,
                        t.CustomerName AS CustomerName,
                        DATE_FORMAT(t.TransactionTimestamp, '%M %d, %Y') AS FormattedDate,
                        COALESCE(SUM(CASE WHEN td.LaundryID = 1 THEN td.TDQuantity ELSE 0 END), 0) AS RegularCount,
                        COALESCE(SUM(CASE WHEN td.LaundryID = 2 THEN td.TDQuantity ELSE 0 END), 0) AS ExtraHeavyCount,
                        s.StatusName AS Status,
                        t.StatusID AS StatusID,
                        t.ClaimStatus AS ClaimStatus,
                        t.PaymentStatus AS PaymentStatus,
                        t.TransacTotalAmount
                    FROM transaction t
                    LEFT JOIN transactiondetails td ON t.TransactionID = td.TransactionID
                    LEFT JOIN status s ON t.StatusID = s.StatusID
                    WHERE 1=1";

            $params = [];

            // Time period filter
            switch($timePeriod) {
                case 'day':
                    $query .= " AND DATE(t.TransactionTimestamp) = CURDATE()";
                    break;
                case 'week':
                    $query .= " AND t.TransactionTimestamp >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                    break;
                case 'month':
                    $query .= " AND MONTH(t.TransactionTimestamp) = MONTH(CURDATE()) AND YEAR(t.TransactionTimestamp) = YEAR(CURDATE())";
                    break;
                case 'year':
                    $query .= " AND YEAR(t.TransactionTimestamp) = YEAR(CURDATE())";
                    break;
                case 'all':
                default:
                    // No additional time filter
                    break;
            }

            // Order status filter
            if (!empty($orderStatus)) {
                $query .= " AND t.StatusID = ?";
                $params[] = $orderStatus;
            }

            // Claim status filter
            if (!empty($claimStatus)) {
                $query .= " AND t.ClaimStatus = ?";
                $params[] = $claimStatus;
            }

            // Payment status filter
            if (!empty($paymentStatus)) {
                $query .= " AND t.PaymentStatus = ?";
                $params[] = $paymentStatus;
            }

            // Search term filter (Customer name or Transaction ID)
            if (!empty($searchTerm)) {
                $query .= " AND (t.CustomerName LIKE ? OR t.TransactionID LIKE ?)";
                $searchParam = '%' . $searchTerm . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            $query .= " GROUP BY t.TransactionID ORDER BY t.TransactionTimestamp DESC";
            
            // Add limit only for web display, not for exports
            if (!$forExport) {
                $query .= " LIMIT 1000";
            }

            // Prepare and execute the statement
            $stmt = $con->prepare($query);
            if (!$stmt) {
                throw new Exception("Failed to prepare statement");
            }
            
            $success = $stmt->execute($params);
            if (!$success) {
                throw new Exception("Failed to execute statement");
            }

            // Fetch the transaction data as an associative array
            $transaction_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Return the transaction data
            return $transaction_data;
        } catch (Exception $e) {
            error_log("Error in getFilteredTransactions: " . $e->getMessage());
            return [];
        }
    }

    // Fallback function to get basic services (simplified query)
    function getServicesBasic(){
        try {
            // Open connection with database
            $con = $this->opencon();

            // Simple query to get services
            $stmt = $con->prepare("SELECT
                    LaundryID,
                    LaundryService_Type as ServiceType,
                    LaundryService_Name as ServiceName,
                    LaundryService_Desc as ServiceDesc,
                    50.00 as Price
                FROM laundryservice
                WHERE StatusID = 6 OR StatusID IS NULL
                ORDER BY LaundryService_Type, LaundryService_Name
            ");

            // Execute the statement
            $stmt->execute();

            // Fetch all services as an associative array
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Return the services data
            return $services;
        } catch (Exception $e) {
            error_log("Error in getServicesBasic: " . $e->getMessage());
            return [];
        }
    }

    // Debug function to check database tables and data
    function debugDatabaseState(){
        try {
            $con = $this->opencon();
            
            $tables = ['transaction', 'transactiondetails', 'laundryservice', 'status'];
            
            foreach($tables as $table) {
                $stmt = $con->prepare("SELECT COUNT(*) as count FROM $table");
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("Table $table has " . $result['count'] . " records");
            }
            
            // Test a simple transaction query
            $stmt = $con->prepare("SELECT COUNT(*) as count FROM transaction");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Direct transaction count: " . $result['count']);
            
        } catch (Exception $e) {
            error_log("Debug error: " . $e->getMessage());
        }
    }

    // Function to get unpaid transactions for today
    function getUnpaidTransactionsToday(){
        try {
            $con = $this->opencon();
            
            $query = "SELECT 
                        t.TransactionID,
                        t.CustomerName AS CustomerName,
                        DATE_FORMAT(t.TransactionTimestamp, '%M %d, %Y') AS FormattedDate,
                        COALESCE(SUM(CASE WHEN td.LaundryID = 1 THEN td.TDQuantity ELSE 0 END), 0) AS RegularCount,
                        COALESCE(SUM(CASE WHEN td.LaundryID = 2 THEN td.TDQuantity ELSE 0 END), 0) AS ExtraHeavyCount,
                        s.StatusName AS Status,
                        t.StatusID AS StatusID,
                        t.ClaimStatus AS ClaimStatus,
                        t.PaymentStatus AS PaymentStatus,
                        t.TransacTotalAmount
                    FROM transaction t
                    LEFT JOIN transactiondetails td ON t.TransactionID = td.TransactionID
                    LEFT JOIN status s ON t.StatusID = s.StatusID
                    WHERE t.PaymentStatus = 1
                    GROUP BY t.TransactionID 
                    ORDER BY t.TransactionTimestamp DESC";
            
            $stmt = $con->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getUnpaidTransactionsToday: " . $e->getMessage());
            return [];
        }
    }

    // Function to get paid transactions for today
    function getPaidTransactionsToday(){
        try {
            $con = $this->opencon();
            
            $query = "SELECT 
                        t.TransactionID,
                        t.CustomerName AS CustomerName,
                        DATE_FORMAT(t.TransactionTimestamp, '%M %d, %Y') AS FormattedDate,
                        COALESCE(SUM(CASE WHEN td.LaundryID = 1 THEN td.TDQuantity ELSE 0 END), 0) AS RegularCount,
                        COALESCE(SUM(CASE WHEN td.LaundryID = 2 THEN td.TDQuantity ELSE 0 END), 0) AS ExtraHeavyCount,
                        s.StatusName AS Status,
                        t.StatusID AS StatusID,
                        t.ClaimStatus AS ClaimStatus,
                        t.PaymentStatus AS PaymentStatus,
                        t.TransacTotalAmount,
                        DATE_FORMAT(t.PaymentStatusDate, '%M %d, %Y at %h:%i %p') AS PaymentDate
                    FROM transaction t
                    LEFT JOIN transactiondetails td ON t.TransactionID = td.TransactionID
                    LEFT JOIN status s ON t.StatusID = s.StatusID
                    WHERE DATE(t.PaymentStatusDate) = CURDATE() 
                    AND t.PaymentStatus = 2
                    GROUP BY t.TransactionID 
                    ORDER BY t.PaymentStatusDate DESC";
            
            $stmt = $con->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getPaidTransactionsToday: " . $e->getMessage());
            return [];
        }
    }

    // Function to get claimed transactions for today
    function getClaimedTransactionsToday(){
        try {
            $con = $this->opencon();
            
            $query = "SELECT 
                        t.TransactionID,
                        t.CustomerName AS CustomerName,
                        DATE_FORMAT(t.TransactionTimestamp, '%M %d, %Y') AS FormattedDate,
                        COALESCE(SUM(CASE WHEN td.LaundryID = 1 THEN td.TDQuantity ELSE 0 END), 0) AS RegularCount,
                        COALESCE(SUM(CASE WHEN td.LaundryID = 2 THEN td.TDQuantity ELSE 0 END), 0) AS ExtraHeavyCount,
                        s.StatusName AS Status,
                        t.StatusID AS StatusID,
                        t.ClaimStatus AS ClaimStatus,
                        t.PaymentStatus AS PaymentStatus,
                        t.TransacTotalAmount,
                        DATE_FORMAT(t.ClaimStatusDate, '%M %d, %Y at %h:%i %p') AS ClaimDate
                    FROM transaction t
                    LEFT JOIN transactiondetails td ON t.TransactionID = td.TransactionID
                    LEFT JOIN status s ON t.StatusID = s.StatusID
                    WHERE DATE(t.ClaimStatusDate) = CURDATE() 
                    AND t.ClaimStatus = 2
                    GROUP BY t.TransactionID 
                    ORDER BY t.ClaimStatusDate DESC";
            
            $stmt = $con->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getClaimedTransactionsToday: " . $e->getMessage());
            return [];
        }
    }

}