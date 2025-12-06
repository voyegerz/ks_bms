<?php 
require_once 'core.php';
$accountId = $_POST['accountId'];
$sql = "SELECT * FROM bank_accounts WHERE account_id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $accountId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$connect->close();
echo json_encode($row);
?>