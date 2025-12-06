<?php 
require_once 'core.php';

$party_id = $_POST['party_id'];

$sql = "SELECT * FROM partys WHERE id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $party_id);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();
$stmt->close();
$connect->close();
echo json_encode($row);