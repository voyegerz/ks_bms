<?php 
require_once 'core.php';

$response = array('success' => false, 'next_voucher_no' => 1);

$sql = "SELECT MAX(voucher_no) as last_no FROM journal_vouchers";
$result = $connect->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $lastNo = $row['last_no'];
    if ($lastNo) {
        $response['next_voucher_no'] = $lastNo + 1;
    }
    $response['success'] = true;
}

$connect->close();
echo json_encode($response);
?>