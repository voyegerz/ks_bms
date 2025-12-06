<?php 
require_once 'core.php';

$valid = array('success' => false, 'messages' => array());

if($_POST) {    
    $partyName    = $_POST['partyName'];
    $gstin        = $_POST['gstin'];
    $contactNo    = $_POST['contactNo'];
    $email        = $_POST['email'];
    $billingAddr  = $_POST['billingAddr'];
    $shippingAddr = $_POST['shippingAddr'];

    // Start a transaction to ensure both inserts succeed or fail together
    $connect->begin_transaction();

    try {
        // --- Step 1: Insert into the 'partys' table ---
        $sql = "INSERT INTO partys (name, gstin, contact_no, email, billing_addr, shipping_addr) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("ssssss", $partyName, $gstin, $contactNo, $email, $billingAddr, $shippingAddr);
        
        if(!$stmt->execute()) {
            throw new Exception("Error adding party: " . $stmt->error);
        }
        
        // Get the ID of the new party we just created
        $party_id = $stmt->insert_id;
        $stmt->close();

        // --- Step 2: Create a corresponding account in the Chart of Accounts ---
        $accountType = "Asset"; // All parties are 'Accounts Receivable', which is an Asset
        $accountSql = "INSERT INTO chart_of_accounts (account_name, account_type, party_id) VALUES (?, ?, ?)";
        $stmtAccount = $connect->prepare($accountSql);
        $stmtAccount->bind_param("ssi", $partyName, $accountType, $party_id);
        
        if(!$stmtAccount->execute()) {
            throw new Exception("Error creating linked account: " . $stmtAccount->error);
        }
        $stmtAccount->close();

        // If both inserts were successful, commit the transaction
        $connect->commit();
        $valid['success'] = true;
        $valid['messages'] = "Successfully Added";
        
    } catch (Exception $e) {
        // If anything went wrong, roll back all changes
        $connect->rollback();
        $valid['success'] = false;
        $valid['messages'] = $e->getMessage();
    }

    $connect->close();
    echo json_encode($valid);
}
?>