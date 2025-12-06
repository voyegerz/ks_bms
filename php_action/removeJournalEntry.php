<?php 
require_once 'core.php';
$valid['success'] = false; $valid['messages'] = array();
$voucherId = $_POST['voucherId'];

if ($voucherId) {
    $sql = "DELETE FROM journal_vouchers WHERE voucher_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $voucherId);
    if ($stmt->execute()) {
        // ON DELETE CASCADE will automatically delete the lines
        $valid['success'] = true;
        $valid['messages'] = "Successfully Removed";
    } else {
        $valid['success'] = false;
        $valid['messages'] = "Error while removing.";
    }
    $stmt->close();
    $connect->close();
    echo json_encode($valid);
}
?>