<?php 
require_once 'core.php';
$valid['success'] = false; $valid['messages'] = array();
$supplierId = $_POST['supplierId'];
if ($supplierId) { 
    $sql = "DELETE FROM suppliers WHERE id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $supplierId);
    if ($stmt->execute()) { $valid['success'] = true; $valid['messages'] = "Successfully Removed"; } 
    else { $valid['success'] = false; $valid['messages'] = "Error while removing."; }
    $stmt->close();
    $connect->close();
    echo json_encode($valid);
}
?>