<?php 
require_once 'core.php';

$valid = array('success' => false, 'messages' => '');

if($_POST) {
    $orderId = $_POST['orderId'] ?? null;

    if ($orderId) {
        $connect->begin_transaction();
        try {
            // --- Step 1: Get all necessary data from the order BEFORE deleting ---
            $sql = "SELECT invoice_no, order_status, grand_total, paid, party_id FROM orders WHERE order_id = ?";
            $stmt = $connect->prepare($sql);
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $orderData = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$orderData) {
                throw new Exception("Order not found.");
            }

            // --- Step 2: If the order was finalized, reverse its stock and ledger impact ---
            if ($orderData['order_status'] == 1 || $orderData['order_status'] == 3) { // Handles both 'Finalized' statuses
                // a) Reverse Stock
                $itemsSql = "SELECT product_id, quantity FROM order_item WHERE order_id = ?";
                $itemStmt = $connect->prepare($itemsSql);
                $itemStmt->bind_param("i", $orderId);
                $itemStmt->execute();
                $itemResult = $itemStmt->get_result();
                while($item = $itemResult->fetch_assoc()){
                    // Check if stock is managed for this product
                    $productCheckSql = "SELECT manage_stock FROM product WHERE product_id = ?";
                    $stmtCheck = $connect->prepare($productCheckSql);
                    $stmtCheck->bind_param("i", $item['product_id']);
                    $stmtCheck->execute();
                    $manageStockResult = $stmtCheck->get_result()->fetch_assoc();
                    $stmtCheck->close();
                    if ($manageStockResult && $manageStockResult['manage_stock'] == 1) {
                        // Add the quantity back to the product table
                        $updateStockSql = "UPDATE product SET quantity = quantity + ? WHERE product_id = ?";
                        $stockStmt = $connect->prepare($updateStockSql);
                        $stockStmt->bind_param("ii", $item['quantity'], $item['product_id']);
                        $stockStmt->execute();
                        $stockStmt->close();
                    }
                }
                $itemStmt->close();
                
                // b) Reverse Ledger Entries
                $partyId = $orderData['party_id'];
                $lastBalanceSql = "SELECT balance FROM party_ledger WHERE party_id = ? ORDER BY id DESC LIMIT 1";
                $stmtBalance = $connect->prepare($lastBalanceSql);
                $stmtBalance->bind_param("i", $partyId);
                $stmtBalance->execute();
                $lastBalance = (float)($stmtBalance->get_result()->fetch_assoc()['balance'] ?? 0.00);
                $stmtBalance->close();

                // Create a reversing CREDIT entry to cancel the invoice debit
                $newBalance = $lastBalance - (float)$orderData['grand_total'];
                $reversalDesc = "Reversal for cancelled Invoice #" . $orderData['invoice_no'];
                $ledgerSql = "INSERT INTO party_ledger (party_id, description, transaction_type, order_id, credit, balance) VALUES (?, ?, 'Credit Note', ?, ?, ?)";
                $stmtLedger = $connect->prepare($ledgerSql);
                $stmtLedger->bind_param("isidd", $partyId, $reversalDesc, $orderId, $orderData['grand_total'], $newBalance);
                $stmtLedger->execute();
                $stmtLedger->close();
            }
            
            // --- Step 3: Modify the invoice_no and "soft delete" the order ---
            $deletedInvoiceNo = $orderData['invoice_no'] . '-DELETED-' . $orderId;
            $updateOrderSql = "UPDATE orders SET order_status = 2, invoice_no = ? WHERE order_id = ?";
            $updateStmt = $connect->prepare($updateOrderSql);
            $updateStmt->bind_param("si", $deletedInvoiceNo, $orderId);
            $updateStmt->execute();
            $updateStmt->close();

            // --- Step 4: Update order_item status as well ---
            $updateItemSql = "UPDATE order_item SET order_item_status = 2 WHERE order_id = ?";
            $updateItemStmt = $connect->prepare($updateItemSql);
            $updateItemStmt->bind_param("i", $orderId);
            $updateItemStmt->execute();
            $updateItemStmt->close();

            // --- Step 5: Commit ---
            $connect->commit();
            $valid['success'] = true;
            $valid['messages'] = "Order Successfully Removed";

        } catch (Exception $e) {
            $connect->rollback();
            $valid['success'] = false;
            $valid['messages'] = "Error: " . $e->getMessage();
        }

        $connect->close();
        echo json_encode($valid);
    } else {
        $valid['messages'] = "Order ID was not provided.";
        echo json_encode($valid);
    }
}
?>