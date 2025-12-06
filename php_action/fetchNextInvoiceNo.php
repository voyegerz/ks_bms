<?php 
require_once 'core.php';

// This query finds the highest numeric value in the invoice_no column
// CAST(invoice_no AS UNSIGNED) safely converts the text '1', '2' into numbers 1, 2
$sql = "SELECT MAX(CAST(invoice_no AS UNSIGNED)) as last_invoice_number FROM orders";
$result = $connect->query($sql);

$nextInvoiceNumber = 0; // Default to 1 if the table is empty

if ($result) {
    $row = $result->fetch_assoc();
    $lastNumber = $row['last_invoice_number'];
    if ($lastNumber) {
        $nextInvoiceNumber = (int)$lastNumber + 1;
    }
}

$connect->close();

// Send back the next number
echo json_encode(array('invoice_no' => $nextInvoiceNumber));
?>