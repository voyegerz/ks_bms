<?php 
require_once 'core.php';
$valid['success'] = false; $valid['messages'] = array();
if ($_POST) {
    $sql = "UPDATE bank_accounts SET bank_name = ?, account_holder_name = ?, account_number = ?, ifsc_code = ?, branch_address = ?, is_default = ? WHERE account_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("sssssii", $_POST['editBankName'], $_POST['editAccountHolderName'], $_POST['editAccountNumber'], $_POST['editIfscCode'], $_POST['editBranchAddress'], $_POST['editIsDefault'], $_POST['accountId']);
    if ($stmt->execute()) { $valid['success'] = true; $valid['messages'] = "Successfully Updated"; }
    $stmt->close(); $connect->close();
    echo json_encode($valid);
}
?>