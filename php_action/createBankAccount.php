<?php 
require_once 'core.php';
$valid['success'] = false; $valid['messages'] = array();

if ($_POST) {
    $bankName = $_POST['bankName'];
    $accountHolderName = $_POST['accountHolderName'];
    $accountNumber = $_POST['accountNumber'];
    $ifscCode = $_POST['ifscCode'];
    $branchAddress = $_POST['branchAddress'];
    $isDefault = $_POST['isDefault'];

    $connect->begin_transaction();
    try {
        // --- Step 1: Create the new account in the Chart of Accounts ---
        $accountType = "Asset";
        $accountName = $bankName . ' (' . substr($accountNumber, -4) . ')';
        
        $accountSql = "INSERT INTO chart_of_accounts (account_name, account_type) VALUES (?, ?)";
        $stmtAccount = $connect->prepare($accountSql);
        $stmtAccount->bind_param("ss", $accountName, $accountType);
        
        if(!$stmtAccount->execute()) {
            throw new Exception("Error creating linked account: " . $stmtAccount->error);
        }
        
        // Get the new chart_of_account_id we just created
        $new_chart_of_account_id = $stmtAccount->insert_id;
        $stmtAccount->close();

        // --- Step 2: Insert into the 'bank_accounts' table ---
        // This query now saves the new foreign key in the 'chart_of_account_id' column
        $sql = "INSERT INTO bank_accounts (bank_name, account_holder_name, account_number, ifsc_code, branch_address, is_default, chart_of_account_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("sssssii", $bankName, $accountHolderName, $accountNumber, $ifscCode, $branchAddress, $isDefault, $new_chart_of_account_id);
        
        if(!$stmt->execute()) {
            throw new Exception("Error adding bank account: " . $stmt->error);
        }
        $stmt->close();

        $connect->commit();
        $valid['success'] = true;
        $valid['messages'] = "Successfully Added";
        
    } catch (Exception $e) {
        $connect->rollback();
        $valid['success'] = false;
        $valid['messages'] = $e->getMessage();
    }

    $connect->close();
    echo json_encode($valid);
}
?>