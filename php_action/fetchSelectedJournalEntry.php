<?php 
require_once 'core.php';
$voucherId = $_POST['voucherId'];

// 1. Get Voucher Details
$sql = "SELECT voucher_id, voucher_no, voucher_date, description FROM journal_vouchers WHERE voucher_id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $voucherId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

// 2. Get Line Item Details
$sqlLines = "SELECT account_id, debit, credit FROM journal_entry_lines WHERE voucher_id = ?";
$stmtLines = $connect->prepare($sqlLines);
$stmtLines->bind_param("i", $voucherId);
$stmtLines->execute();
$lines = $stmtLines->get_result();

$debit_account_id = 0;
$credit_account_id = 0;
$amount = 0;
while ($row = $lines->fetch_assoc()) {
    if ($row['debit'] > 0) {
        $debit_account_id = $row['account_id'];
        $amount = $row['debit'];
    }
    if ($row['credit'] > 0) {
        $credit_account_id = $row['account_id'];
    }
}

$data['debit_account_id'] = $debit_account_id;
$data['credit_account_id'] = $credit_account_id;
$data['amount'] = $amount;

$connect->close();
echo json_encode($data);
?>