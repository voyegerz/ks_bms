<?php 
require_once 'core.php';

$valid = array('success' => false, 'messages' => array());

if($_POST) {
    $connect->begin_transaction();
    try {
        // --- Step 1: Get data from the form and the original order ---
        $orderId = $_POST['orderId'];
        $newStatus = $_POST['orderStatus'];
        $otherPoNo = $_POST['editOtherPoNo'] ?? null;
        $otherPoDate = !empty($_POST['editOtherPoDate']) ? date('Y-m-d', strtotime($_POST['editOtherPoDate'])) : null;
        $orderDate = date('Y-m-d', strtotime($_POST['orderDate']));
        $partyId = $_POST['partyId'];
        $subTotal = $_POST['subTotalValue'];
        $vat = $_POST['vatValue'];
        $totalAmount = $_POST['totalAmountValue'];
        $discount = $_POST['discount'];
        $grandTotal = (float)$_POST['grandTotalValue'];
        $paid = (float)$_POST['paid'];
        $due = $_POST['dueValue'];
        $paymentType = $_POST['paymentType'];
        $paymentStatus = $_POST['paymentStatus'];
        $paymentPlace = $_POST['paymentPlace'];

        $newGrandTotal = (float)$_POST['grandTotalValue'];
        $newPaidAmount = (float)$_POST['paid'];

        $transportName = $_POST['editTransportName'] ?? null;
        $transportGstNo = $_POST['editTransportGstNo'] ?? null;

        // Get the OLD status and totals of the order before making any changes
        $oldOrderSql = "SELECT order_status, grand_total, paid FROM orders WHERE order_id = ?";
        $stmtOld = $connect->prepare($oldOrderSql);
        $stmtOld->bind_param("i", $orderId);
        $stmtOld->execute();
        $oldOrderData = $stmtOld->get_result()->fetch_assoc();
        $oldStatus = $oldOrderData['order_status'];
        $stmtOld->close();

        // --- Conditional Ledger and Stock Logic ---

        if ($oldStatus == 3 && $newStatus == 1) { // ## FINALIZING A DRAFT ##
            // This is the first time the order is being finalized.
            // Create the initial ledger entries.
            
            // Get last balance
            $lastBalanceSql = "SELECT balance FROM party_ledger WHERE party_id = ? ORDER BY id DESC LIMIT 1";
            $stmtBalance = $connect->prepare($lastBalanceSql); $stmtBalance->bind_param("i", $partyId); $stmtBalance->execute();
            $lastBalance = (float)($stmtBalance->get_result()->fetch_assoc()['balance'] ?? 0.00); $stmtBalance->close();

            // Create DEBIT for the new invoice total
            $balanceAfterDebit = $lastBalance + $newGrandTotal;
            $debitDesc = "Invoice #" . $_POST['invoiceNo'];
            $ledgerSqlDebit = "INSERT INTO party_ledger (party_id, description, transaction_type, order_id, debit, balance) VALUES (?, ?, 'Invoice', ?, ?, ?)";
            $stmtLedgerDebit = $connect->prepare($ledgerSqlDebit);
            $stmtLedgerDebit->bind_param("isidd", $partyId, $debitDesc, $orderId, $newGrandTotal, $balanceAfterDebit);
            $stmtLedgerDebit->execute(); $stmtLedgerDebit->close();

            // Create CREDIT if a payment was made
            if ($newPaidAmount > 0) {
                $balanceAfterCredit = $balanceAfterDebit - $newPaidAmount;
                $creditDesc = "Payment for Order #" . $orderId;
                $ledgerSqlCredit = "INSERT INTO party_ledger (party_id, description, transaction_type, order_id, credit, balance) VALUES (?, ?, 'Payment', ?, ?, ?)";
                $stmtLedgerCredit = $connect->prepare($ledgerSqlCredit);
                $stmtLedgerCredit->bind_param("isidd", $partyId, $creditDesc, $orderId, $newPaidAmount, $balanceAfterCredit);
                $stmtLedgerCredit->execute(); $stmtLedgerCredit->close();
            }

        } else if ($oldStatus == 1) { // ## EDITING A FINALIZED ORDER ##
            // Fetch original grand_total for ledger reversal
            $getOldTotalSql = "SELECT grand_total FROM orders WHERE order_id = ?";
            $stmtOldTotal = $connect->prepare($getOldTotalSql);
            $stmtOldTotal->bind_param("i", $orderId);
            $stmtOldTotal->execute();
            $oldGrandTotal = (float)$stmtOldTotal->get_result()->fetch_assoc()['grand_total'];
            $stmtOldTotal->close();
            
            // --- Step 2: Create Ledger Entries ---
            // First, get the last balance for this party
            $lastBalanceSql = "SELECT balance FROM party_ledger WHERE party_id = ? ORDER BY id DESC LIMIT 1";
            $stmtBalance = $connect->prepare($lastBalanceSql);
            $stmtBalance->bind_param("i", $partyId);
            $stmtBalance->execute();
            $lastBalance = (float)($stmtBalance->get_result()->fetch_assoc()['balance'] ?? 0.00);
            $stmtBalance->close();

            // 2a: Create a CREDIT entry to reverse the original invoice amount
            // get invoice no
            $invoiceSql = "SELECT invoice_no FROM orders WHERE order_id = ?";
            $stmtInvoiceNo = $connect->prepare($invoiceSql);
            $stmtInvoiceNo->bind_param("i", $orderId);
            $stmtInvoiceNo->execute();
            $invoiceNo = $stmtInvoiceNo->get_result()->fetch_assoc()['invoice_no'];
            $stmtInvoiceNo->close();

            $balanceAfterReversal = $lastBalance - $oldGrandTotal;
            $reversalDesc = "Reversal for Invoice #" . $invoiceNo . " due to edit";
            $ledgerSqlCredit = "INSERT INTO party_ledger (party_id, description, transaction_type, order_id, credit, balance) VALUES (?, ?, 'Credit Note', ?, ?, ?)";
            $stmtLedgerCredit = $connect->prepare($ledgerSqlCredit);
            $stmtLedgerCredit->bind_param("isidd", $partyId, $reversalDesc, $orderId, $oldGrandTotal, $balanceAfterReversal);
            $stmtLedgerCredit->execute();
            $stmtLedgerCredit->close();

            // 2b: Create a new DEBIT entry for the updated invoice amount
            $newBalance = $balanceAfterReversal + $grandTotal;
            $debitDesc = "Updated Invoice #" . $invoiceNo;
            $ledgerSqlDebit = "INSERT INTO party_ledger (party_id, description, transaction_type, order_id, debit, balance) VALUES (?, ?, 'Invoice', ?, ?, ?)";
            $stmtLedgerDebit = $connect->prepare($ledgerSqlDebit);
            $stmtLedgerDebit->bind_param("isidd", $partyId, $debitDesc, $orderId, $grandTotal, $newBalance);
            $stmtLedgerDebit->execute();
            $stmtLedgerDebit->close();
        }
        

        $oldItems = [];
        $getOldItemsSql = "SELECT product_id, quantity FROM order_item WHERE order_id = ?";
        $stmtOld = $connect->prepare($getOldItemsSql);
        $stmtOld->bind_param("i", $orderId);
        $stmtOld->execute();
        $resultOld = $stmtOld->get_result();
        while ($row = $resultOld->fetch_assoc()) {
            $oldItems[$row['product_id']] = $row['quantity'];
        }
        $stmtOld->close();

        // --- Step 3: Add the old quantities back to the product stock (reversal) ---
        foreach ($oldItems as $productId => $quantity) {
            $updateStockSql = "UPDATE product SET quantity = quantity + ? WHERE product_id = ?";
            $stmtStock = $connect->prepare($updateStockSql);
            $stmtStock->bind_param("ii", $quantity, $productId);
            $stmtStock->execute();
            $stmtStock->close();
        }

        // --- Step 4: Update the main 'orders' record ---
        $updateOrderSql = "UPDATE orders SET 
                            other_po_no = ?, other_po_date = ?, transport_name = ?, transport_gst_no = ?, order_date = ?, party_id = ?, sub_total = ?, vat = ?, total_amount = ?, 
                            discount = ?, grand_total = ?, paid = ?, due = ?, payment_type = ?, 
                            payment_status = ?, order_status = ?, payment_place = ? 
                           WHERE order_id = ?";
        $stmtOrder = $connect->prepare($updateOrderSql);
        $stmtOrder->bind_param("sssssisssssssiiiis", 
            $otherPoNo, $otherPoDate, $transportName, $transportGstNo, $orderDate, $partyId, $subTotal, $vat, $totalAmount, $discount, 
            $grandTotal, $paid, $due, $paymentType, $paymentStatus, $newStatus, $paymentPlace, $orderId
        );
        $stmtOrder->execute();
        $stmtOrder->close();

        // --- Step 5: Delete all existing items for this order ---
        $deleteItemsSql = "DELETE FROM order_item WHERE order_id = ?";
        $stmtDelete = $connect->prepare($deleteItemsSql);
        $stmtDelete->bind_param("i", $orderId);
        $stmtDelete->execute();
        $stmtDelete->close();

        // --- Step 6: Insert the new/updated items and deduct their stock ---
        if (!empty($_POST['productName'])) {
            for ($x = 0; $x < count($_POST['productName']); $x++) {
                $productId = $_POST['productName'][$x];
                $quantityInput = $_POST['quantity'][$x];
                $parts = explode('/', $quantityInput);

                // The `quantity` column will store the full string (e.g., "3/10.00")
                $quantity = $parts[0] ? (int)($parts[0]) : 0;
                // The new `size` column will store the numeric part after the slash
                $size= isset($parts[1]) ? trim($parts[1]) : null;

                // ## UPDATED LOGIC: CHECK IF STOCK SHOULD BE MANAGED ##
                // First, find out if this product's inventory is tracked.
                $productCheckSql = "SELECT manage_stock FROM product WHERE product_id = ?";
                $stmtCheck = $connect->prepare($productCheckSql);
                $stmtCheck->bind_param("i", $productId);
                $stmtCheck->execute();
                $manageStockResult = $stmtCheck->get_result()->fetch_assoc();
                $stmtCheck->close();

                $rate = $_POST['rateValue'][$x];
                $total = $_POST['totalValue'][$x];
                $OiRemarks = $_POST['OiRemarks'][$x];
                
                // Insert the new item record
                $itemSql = "INSERT INTO order_item (order_id, product_id, quantity, size, rate, total, remarks, order_item_status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
                $stmtItem = $connect->prepare($itemSql);
                $stmtItem->bind_param("iisssss", $orderId, $productId, $quantity, $size, $rate, $total, $OiRemarks);
                $stmtItem->execute();
                $stmtItem->close();

                // Deduct the new stock quantity
                if ($manageStockResult && $manageStockResult['manage_stock'] == 1) {
                    $deductStockSql = "UPDATE product SET quantity = quantity - ? WHERE product_id = ?";
                    $stmtDeduct = $connect->prepare($deductStockSql);
                    $stmtDeduct->bind_param("ii", $quantity, $productId);
                    $stmtDeduct->execute();
                    $stmtDeduct->close();
                }
            }
        }

        // If all steps were successful, commit the changes
        $connect->commit();
        $valid['success'] = true;
        $valid['messages'] = "Successfully Updated";
    } catch (Exception $e) {
        // If any step failed, roll back all database changes
        $connect->rollback();
        $valid['success'] = false;
        $valid['messages'] = "Error while updating order: " . $e->getMessage();
    }
    
    $connect->close();
    echo json_encode($valid);
}
?>