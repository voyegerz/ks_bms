<?php 

require_once 'core.php';

$productId = $_POST['productId'];

// CORRECTED: Added the 'hsn' column to the SELECT statement
$sql = "SELECT * FROM product WHERE product_id = ?";

$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

$connect->close();

echo json_encode($row);