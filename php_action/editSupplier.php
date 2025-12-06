<?php 
require_once 'core.php';
$valid['success'] = false; $valid['messages'] = array();
if ($_POST) {
    $sql = "UPDATE suppliers SET name = ?, contact_no = ?, gstin = ?, address = ? WHERE id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("ssssi", $_POST['editSupplierName'], $_POST['editSupplierContact'], $_POST['editSupplierGstin'], $_POST['editSupplierAddress'], $_POST['supplierId']);
    if ($stmt->execute()) { $valid['success'] = true; $valid['messages'] = "Successfully Updated"; } 
    else { $valid['success'] = false; $valid['messages'] = "Error while updating."; }
    $stmt->close();
    $connect->close();
    echo json_encode($valid);
}
?>