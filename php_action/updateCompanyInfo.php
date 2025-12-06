<?php 
require_once 'core.php';

// Check if the form was submitted via POST
if($_POST) {
    // Prepare a response array
    $valid['success'] = array('success' => false, 'messages' => array());
    
    // Retrieve all the form data
    $userId = $_POST['user_id'];
    $companyName = $_POST['company_name'];
    $gstin = $_POST['gstin'];
    $companyAddr = $_POST['company_addr'];
    $companyEmail = $_POST['company_email'];
    $companyBankDetails = $_POST['company_bank_details'];
    $cgst = $_POST['cgst'];
    $sgst = $_POST['sgst'];
    $stateName = $_POST['state_name'];
    $stateCode = $_POST['state_code'];

    // SQL statement to update only the company fields
    $sql = "UPDATE users SET 
                company_name = ?, 
                gstin = ?, 
                cgst = ?, 
                sgst = ?, 
                company_addr = ?, 
                company_email = ?,
                company_bank_details = ?, 
                state_name = ?, 
                state_code = ? 
            WHERE user_id = ?";
    
    // Use a prepared statement to prevent SQL injection
    $stmt = $connect->prepare($sql);
    
    // Bind the parameters to the statement
    $stmt->bind_param("ssddsssssi", 
        $companyName, 
        $gstin, 
        $cgst, 
        $sgst, 
        $companyAddr, 
        $companyEmail,
        $companyBankDetails, 
        $stateName, 
        $stateCode, 
        $userId
    );

    // Execute the query and check for success
    if($stmt->execute()) {
        $valid['success'] = true;
        $valid['messages'] = "Company information successfully updated.";     
    } else {
        $valid['success'] = false;
        $valid['messages'] = "Error: Could not update company information.";
    }
    
    // Close the statement and the database connection
    $stmt->close();
    $connect->close();
    
    // Return the response as a JSON object
    echo json_encode($valid);
}
?>