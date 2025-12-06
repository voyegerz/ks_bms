<?php 
require_once 'core.php';

$valid = array('success' => false, 'messages' => array());

if($_POST) {    
    $connect->begin_transaction();
    try {
        // --- Step 1: Get data from the form ---
        $orderId = $_POST['orderId'];
        $payAmount = (float)$_POST['payAmount'];
        $paymentType = $_POST['paymentType'];
        $paymentStatus = $_POST['paymentStatus'];
        $paidAmount = (float)$_POST['paidAmount']; // This is the amount already paid before this new payment
        $grandTotal = (float)$_POST['grandTotal'];

        // --- Step 2: Calculate new paid and due amounts ---
        $updatePaidAmount = $paidAmount + $payAmount;
        $updateDue = $grandTotal - $updatePaidAmount;
        
        // --- Step 3: Update the main orders table ---
        $updateOrderSql = "UPDATE orders SET paid = ?, due = ?, payment_type = ?, payment_status = ? WHERE order_id = ?";
        $stmtOrder = $connect->prepare($updateOrderSql);
        $stmtOrder->bind_param("ddiii", $updatePaidAmount, $updateDue, $paymentType, $paymentStatus, $orderId);
        $stmtOrder->execute();
        $stmtOrder->close();

        // --- Step 4: Add the payment as a CREDIT to the party's ledger ---
        // First, we need to get the party_id associated with this order
        $getPartySql = "SELECT party_id FROM orders WHERE order_id = ?";
        $stmtParty = $connect->prepare($getPartySql);
        $stmtParty->bind_param("i", $orderId);
        $stmtParty->execute();
        $partyId = $stmtParty->get_result()->fetch_assoc()['party_id'];
        $stmtParty->close();
        
        if ($partyId) {
            // Get the last known balance for this party
            $lastBalanceSql = "SELECT balance FROM party_ledger WHERE party_id = ? ORDER BY id DESC LIMIT 1";
            $stmtBalance = $connect->prepare($lastBalanceSql);
            $stmtBalance->bind_param("i", $partyId);
            $stmtBalance->execute();
            $lastBalance = (float)($stmtBalance->get_result()->fetch_assoc()['balance'] ?? 0.00);
            $stmtBalance->close();

            // Calculate the new balance after this payment
            $newBalance = $lastBalance - $payAmount;

            // Insert the CREDIT entry into the ledger
            $ledgerSql = "INSERT INTO party_ledger (party_id, description, transaction_type, order_id, credit, balance) VALUES (?, ?, 'Payment', ?, ?, ?)";
            $stmtLedger = $connect->prepare($ledgerSql);
            $description = "Payment Received for Order #" . $orderId;
            $stmtLedger->bind_param("isidd", $partyId, $description, $orderId, $payAmount, $newBalance);
            $stmtLedger->execute();
            $stmtLedger->close();
        }

        // --- Step 5: Commit the transaction ---
        $connect->commit();
        $valid['success'] = true;
        $valid['messages'] = "Payment Successfully Updated";
        
    } catch (Exception $e) {
        $connect->rollback();
        $valid['success'] = false;
        $valid['messages'] = "Error while updating payment: " . $e->getMessage();
    }

    $connect->close();
    echo json_encode($valid);
}
?>