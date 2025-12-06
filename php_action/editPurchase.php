<?php 
require_once 'core.php';

$valid = array('success' => false, 'messages' => '');

if($_POST) {    
    $connect->begin_transaction();
    try {
        // --- Step 1: Get data from the form ---
        $purchaseId     = $_POST['purchaseId'];
        $poNumber       = $_POST['poNumber']?? null;
        $purchaseDate   = date('Y-m-d', strtotime($_POST['purchaseDate']));
        $supplierId     = $_POST['supplierId'];
        $subTotal       = $_POST['subTotalValue'];
        $vat            = $_POST['vatValue'];
        $grandTotal     = $_POST['grandTotalValue'];
        $paid           = $_POST['paid'];
        $due            = $_POST['dueValue'];
        $paymentStatus  = $_POST['paymentStatus'];
        $dueDate        = !empty($_POST['dueDate']) ? date('Y-m-d', strtotime($_POST['dueDate'])) : null;
        
        // --- Step 2: Update the main 'purchases' record ---
        $sql = "UPDATE purchases SET
                    supplier_id = ?, 
                    po_number = ?,
                    purchase_date = ?, 
                    due_date = ?, 
                    sub_total = ?, 
                    vat = ?,
                    grand_total = ?, 
                    paid = ?, 
                    due = ?, 
                    payment_status = ?
                WHERE purchase_id = ?";
        
        $stmt = $connect->prepare($sql);
        // Types: i=int, s=string, d=decimal
        $stmt->bind_param("issdddddisi", $supplierId, $poNumber, $purchaseDate, $dueDate, $subTotal, $vat, $grandTotal, $paid, $due, $paymentStatus, $purchaseId);
        $stmt->execute();
        $stmt->close();

        // --- Step 3: Delete all old items for this purchase ---
        // This is the simplest and safest way to handle item edits.
        $deleteItemsSql = "DELETE FROM purchase_items WHERE purchase_id = ?";
        $stmtDelete = $connect->prepare($deleteItemsSql);
        $stmtDelete->bind_param("i", $purchaseId);
        $stmtDelete->execute();
        $stmtDelete->close();

        // --- Step 4: Insert the new/updated list of items ---
        if (!empty($_POST['productName'])) {
            for($x = 0; $x < count($_POST['productName']); $x++) {
                $productId = $_POST['productName'][$x];
                $quantity = $_POST['quantity'][$x];
                $rate = $_POST['rateValue'][$x];
                $total = $_POST['totalValue'][$x];

                $itemSql = "INSERT INTO purchase_items (purchase_id, product_id, quantity, rate, total) VALUES (?, ?, ?, ?, ?)";
                $stmt_item = $connect->prepare($itemSql);
                $stmt_item->bind_param("iisss", $purchaseId, $productId, $quantity, $rate, $total);
                $stmt_item->execute();
                $stmt_item->close();
            }
        } else {
            throw new Exception("A purchase must have at least one product.");
        }
        
        // If everything was successful, commit the transaction
        $connect->commit();
        $valid['success'] = true;
        $valid['messages'] = "Purchase Bill Successfully Updated";

    } catch (Exception $e) {
        // If any step failed, roll back all changes
        $connect->rollback();
        $valid['success'] = false;
        $valid['messages'] = "Error while updating purchase bill: " . $e->getMessage();
    }
    
    $connect->close();
    echo json_encode($valid);
}
?>