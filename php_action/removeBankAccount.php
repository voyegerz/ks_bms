<?php 
require_once 'core.php';
$valid['success'] = false; 
$valid['messages'] = array();
$accountId = $_POST['accountId']; // This is the bank_accounts.account_id

if ($accountId) {
    // Start a database transaction
    $connect->begin_transaction();
    try {
        // --- Step 1: Find the linked chart_of_account_id ---
        $sqlFetch = "SELECT chart_of_account_id FROM bank_accounts WHERE account_id = ?";
        $stmtFetch = $connect->prepare($sqlFetch);
        $stmtFetch->bind_param("i", $accountId);
        $stmtFetch->execute();
        $result = $stmtFetch->get_result();
        $chartOfAccountId = null;
        if ($result->num_rows > 0) {
            $chartOfAccountId = $result->fetch_assoc()['chart_of_account_id'];
        }
        $stmtFetch->close();

        // --- Step 2: Delete from the 'bank_accounts' table ---
        $sqlDeleteBank = "DELETE FROM bank_accounts WHERE account_id = ?";
        $stmtDeleteBank = $connect->prepare($sqlDeleteBank);
        $stmtDeleteBank->bind_param("i", $accountId);
        if (!$stmtDeleteBank->execute()) {
            throw new Exception("Error deleting bank account record.");
        }
        $stmtDeleteBank->close();

        // --- Step 3: Delete the linked account from 'chart_of_accounts' ---
        if ($chartOfAccountId) {
            $sqlDeleteChart = "DELETE FROM chart_of_accounts WHERE account_id = ?";
            $stmtDeleteChart = $connect->prepare($sqlDeleteChart);
            $stmtDeleteChart->bind_param("i", $chartOfAccountId);
            if (!$stmtDeleteChart->execute()) {
                // This will fail if the account has been used, which is good!
                throw new Exception("Error deleting from Chart of Accounts.");
            }
            $stmtDeleteChart->close();
        }

        // If both deletes were successful, commit
        $connect->commit();
        $valid['success'] = true;
        $valid['messages'] = "Successfully Removed";
        
    } catch (Exception $e) {
        $connect->rollback();
        $valid['success'] = false;
        // Provide a user-friendly error message if the account is in use
        if ($connect->errno == 1451) { // 1451 is the error code for a foreign key constraint failure
             $valid['messages'] = "Cannot delete this bank account. It is already being used in journal entries.";
        } else {
             $valid['messages'] = $e->getMessage();
        }
    }
    $connect->close();
    echo json_encode($valid);
}
?>