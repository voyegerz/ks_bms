<?php 
require_once 'core.php';
$valid['success'] = false; $valid['messages'] = array();
if ($_POST) {
    $sql = "INSERT INTO suppliers (name, contact_no, gstin, address) VALUES (?, ?, ?, ?)";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("ssss", $_POST['supplierName'], $_POST['supplierContact'], $_POST['supplierGstin'], $_POST['supplierAddress']);
    if ($stmt->execute()) { $valid['success'] = true; $valid['messages'] = "Successfully Added"; } 
    else { $valid['success'] = false; $valid['messages'] = "Error while adding."; }
    $stmt->close();
    $connect->close();
    echo json_encode($valid);
}
?>