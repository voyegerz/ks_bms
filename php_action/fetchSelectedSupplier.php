<?php 
require_once 'core.php';
$supplierId = $_POST['supplierId'];
$sql = "SELECT * FROM suppliers WHERE id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $supplierId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$connect->close();
echo json_encode($row);
?>