<?php 
require_once 'core.php';

$valid = array('success' => false, 'messages' => array(), 'order_id' => '');

if($_POST) {    
    $connect->begin_transaction();
    try {
        // --- Step 1: Get data and create the main order record ---
        $orderStatus = $_POST['orderStatus'];
        $invoiceNo        = $_POST['invoiceNo'];
        $otherPoNo = $_POST['otherPoNo'] ?? null;
        $otherPoDate = !empty($_POST['otherPoDate']) ? date('Y-m-d', strtotime($_POST['otherPoDate'])) : null;
        $orderDate = date('Y-m-d', strtotime($_POST['orderDate']));
        $partyId = $_POST['partyId'];
        $subTotalValue = $_POST['subTotalValue'];
        $vatValue = $_POST['vatValue'];
        $totalAmountValue = $_POST['totalAmountValue'];
        $discount = $_POST['discount'];
        $grandTotalValue = (float)$_POST['grandTotalValue'];
        $paid = (float)$_POST['paid'];
        $dueValue = $_POST['dueValue'];
        $paymentType = $_POST['paymentType'];
        $paymentStatus = $_POST['paymentStatus'];
        $paymentPlace = $_POST['paymentPlace'];
        $userId = $_SESSION['userId'];
        $refChallanNos = $_POST['refChallanNos'] ?? null;

        $transportName = $_POST['transportName'] ?? null;
        $transportGstNo = $_POST['transportGstNo'] ?? null;

        // --- NEW: Check for duplicate invoice number ---
        $checkSql = "SELECT COUNT(*) as count FROM orders WHERE invoice_no = ?";
        $stmtCheck = $connect->prepare($checkSql);
        $stmtCheck->bind_param("s", $invoiceNo);
        $stmtCheck->execute();
        $checkResult = $stmtCheck->get_result()->fetch_assoc();
        $stmtCheck->close();

        if ($checkResult['count'] > 0) {
            // If count is greater than 0, the invoice number already exists
            throw new Exception("Invoice No '{$invoiceNo}' already exists. Please enter a unique invoice number.");
        }
        
        $sql = "INSERT INTO orders (invoice_no, ref_challan_nos, other_po_no, other_po_date, transport_name, transport_gst_no, order_date, party_id, sub_total, vat, total_amount, discount, grand_total, paid, due, payment_type, payment_status, payment_place, order_status, user_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 3, ?)";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("sssssssisssssssiiis", $invoiceNo, $refChallanNos, $otherPoNo, $otherPoDate, $transportName, $transportGstNo, $orderDate, $partyId, $subTotalValue, $vatValue, $totalAmountValue, $discount, $grandTotalValue, $paid, $dueValue, $paymentType, $paymentStatus, $paymentPlace, $userId);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();
        if(!$order_id) { throw new Exception("Failed to create the main order record."); }
        $valid['order_id'] = $order_id; 

        // --- Step 2: Create order_items and update stock/challan links ---
        if (!empty($_POST['productName'])) {
            for($x = 0; $x < count($_POST['productName']); $x++) {
                $productId = $_POST['productName'][$x];
                $quantityInput = $_POST['quantity'][$x];
                $parts = explode('/', $quantityInput);

                // The `quantity` column will store the string (e.g., "3")
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

                $rateValue = $_POST['rateValue'][$x];
                $totalValue = $_POST['totalValue'][$x];
                $OiRemarks = $_POST['OiRemarks'][$x];
                
                // Only update the stock if manage_stock is 1 (Yes)
                if ($manageStockResult && $manageStockResult['manage_stock'] == 1) {
                    $updateProductStockSql = "UPDATE product SET quantity = quantity - ? WHERE product_id = ?";
                    $stmt_stock = $connect->prepare($updateProductStockSql);
                    $stmt_stock->bind_param("ii", $quantity, $productId);
                    $stmt_stock->execute();
                    $stmt_stock->close();
                }

                // Insert into 'order_item' table and get the new ID
                $orderItemSql = "INSERT INTO order_item (order_id, product_id, quantity, size, rate, total, remarks, order_item_status) VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
                $stmt_item = $connect->prepare($orderItemSql);
                $stmt_item->bind_param("iisssss", $order_id, $productId, $quantity, $size, $rateValue, $totalValue, $OiRemarks);
                $stmt_item->execute();
                $new_order_item_id = $stmt_item->insert_id;
                $stmt_item->close();
                if (!$new_order_item_id) { throw new Exception("Failed to create an order item record."); }

                // If created from challans, create the link in the tracking table
                if (!empty($_POST['challanIds'])) {
                    $quantityToBillForThisItem = $quantity;
                    $challanIds = explode(',', $_POST['challanIds']);
                    $placeholders = implode(',', array_fill(0, count($challanIds), '?'));
                    $types = str_repeat('i', count($challanIds));

                    // Find unbilled challan items for this product
                    $findChallanItemsSql = "SELECT ci.challan_item_id, (ci.quantity - IFNULL(bci.total_billed, 0)) as remaining_quantity
                        FROM challan_items ci
                        LEFT JOIN (SELECT challan_item_id, SUM(billed_quantity) as total_billed FROM billed_challan_items GROUP BY challan_item_id) bci ON ci.challan_item_id = bci.challan_item_id
                        WHERE ci.product_id = ? AND ci.challan_id IN ($placeholders) AND (ci.quantity > bci.total_billed OR bci.total_billed IS NULL)
                        ORDER BY ci.challan_id ASC";
                    
                    $stmtFind = $connect->prepare($findChallanItemsSql);
                    $stmtFind->bind_param('i' . $types, $productId, ...$challanIds);
                    $stmtFind->execute();
                    $unbilledChallanItems = $stmtFind->get_result();

                    while ($unbilledItem = $unbilledChallanItems->fetch_assoc()) {
                        if ($quantityToBillForThisItem <= 0) break;
                        $billableAmount = min($quantityToBillForThisItem, $unbilledItem['remaining_quantity']);
                        
                        $linkSql = "INSERT INTO billed_challan_items (challan_item_id, order_item_id, billed_quantity) VALUES (?, ?, ?)";
                        $stmtLink = $connect->prepare($linkSql);
                        $stmtLink->bind_param("iis", $unbilledItem['challan_item_id'], $new_order_item_id, $billableAmount);
                        $stmtLink->execute();
                        $stmtLink->close();
                        $quantityToBillForThisItem -= $billableAmount;
                    }
                    $stmtFind->close();
                }
            }
        } else {
            throw new Exception("No products were added to the order.");
        }
        
        // ## NEW - Step 3: Check if source challans are now fully billed ##
        if (!empty($_POST['challanIds'])) {
            $challanIdsToCheck = explode(',', $_POST['challanIds']);
            
            foreach ($challanIdsToCheck as $challanId) {
                // Query to compare total quantity vs total billed quantity for a challan
                $checkSql = "SELECT
                                (SELECT SUM(quantity) FROM challan_items WHERE challan_id = ?) AS total_challan_qty,
                                (SELECT SUM(bci.billed_quantity) FROM billed_challan_items bci
                                 INNER JOIN challan_items ci ON bci.challan_item_id = ci.challan_item_id
                                 WHERE ci.challan_id = ?) AS total_billed_qty";
                
                $stmtCheck = $connect->prepare($checkSql);
                $stmtCheck->bind_param("ii", $challanId, $challanId);
                $stmtCheck->execute();
                $checkResult = $stmtCheck->get_result()->fetch_assoc();
                $stmtCheck->close();

                // If total quantity equals total billed, mark the challan as Billed (status 4)
                if ($checkResult && (float)$checkResult['total_challan_qty'] == (float)$checkResult['total_billed_qty']) {
                    $updateStatusSql = "UPDATE challans SET challan_status = 4 WHERE challan_id = ?";
                    $stmtUpdateStatus = $connect->prepare($updateStatusSql);
                    $stmtUpdateStatus->bind_param("i", $challanId);
                    $stmtUpdateStatus->execute();
                    $stmtUpdateStatus->close();
                }
            }
        }

        // // --- Step 4: Create Ledger Entries ---
        // // First, get the last balance for this party
        // $lastBalanceSql = "SELECT balance FROM party_ledger WHERE party_id = ? ORDER BY id DESC LIMIT 1";
        // $stmtBalance = $connect->prepare($lastBalanceSql);
        // $stmtBalance->bind_param("i", $partyId);
        // $stmtBalance->execute();
        // $resultBalance = $stmtBalance->get_result();
        // $lastBalance = 0.00;
        // if ($resultBalance->num_rows > 0) {
        //     $lastBalance = (float)$resultBalance->fetch_assoc()['balance'];
        // }
        // $stmtBalance->close();

        // // 4a: Create the DEBIT entry for the invoice total
        // $newBalanceAfterDebit = $lastBalance + $grandTotalValue;
        // $debitDescription = "Invoice No. : " . $invoiceNo;
        // if (!empty($_POST['challanIds'])) {
        //         $debitDescription .= " (from Challans)";
        // }
        // $ledgerSqlDebit = "INSERT INTO party_ledger (party_id, description, transaction_type, order_id, debit, balance) VALUES (?, ?, 'Invoice', ?, ?, ?)";
        // $stmtLedgerDebit = $connect->prepare($ledgerSqlDebit);
        // $stmtLedgerDebit->bind_param("isidd", $partyId, $debitDescription, $order_id, $grandTotalValue, $newBalanceAfterDebit);
        // $stmtLedgerDebit->execute();
        // $stmtLedgerDebit->close();

        // // 4b: Create the CREDIT entry if a payment was made
        // if ($paid > 0) {
        //     $paymentDescription = "Payment for Order #" . $order_id;
        //     // Check if the order was generated from challans to add detail
        //     if (!empty($_POST['challanIds'])) {
        //         $paymentDescription .= " (from Challans)";
        //     }

        //     $finalBalance = $newBalanceAfterDebit - $paid;
        //     $ledgerSqlCredit = "INSERT INTO party_ledger (party_id, description, transaction_type, order_id, credit, balance) VALUES (?, ?, 'Payment', ?, ?, ?)";
        //     $stmtLedgerCredit = $connect->prepare($ledgerSqlCredit);
        //     $stmtLedgerCredit->bind_param("isidd", $partyId, $paymentDescription, $order_id, $paid, $finalBalance);
        //     $stmtLedgerCredit->execute();
        //     $stmtLedgerCredit->close();
        // }

        // Step 5: Commit the transaction
        $connect->commit();
        $valid['success'] = true;
        $valid['messages'] = "Successfully Added";
        
    } catch (Exception $e) {
        $connect->rollback();
        $valid['success'] = false;
        $valid['messages'] = "Error while adding order: " . $e->getMessage();
    }
    
    $connect->close();
    echo json_encode($valid);
}
?>