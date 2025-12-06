<?php 
require_once 'core.php';

$valid = array('success' => false, 'messages' => '');

if($_POST) {    
    $connect->begin_transaction();
    try {
        // --- Main purchase data from the form ---
        $purchaseDate   = date('Y-m-d', strtotime($_POST['purchaseDate']));
        $supplierId     = $_POST['supplierId'];
        $subTotal       = $_POST['subTotalValue'];
        $vat            = $_POST['vatValue'];
        $grandTotal     = $_POST['grandTotalValue'];
        $paid           = $_POST['paid'];
        $due            = $_POST['dueValue'];
        $paymentStatus  = $_POST['paymentStatus'];
        $dueDate        = !empty($_POST['dueDate']) ? date('Y-m-d', strtotime($_POST['dueDate'])) : null;
        $userId         = $_SESSION['userId'];
        $poNumber       = $_POST['poNumber']?? null;
        

        // --- Step 1: Insert into the main 'purchases' table ---
        $sql = "INSERT INTO purchases (supplier_id, po_number, purchase_date, due_date, sub_total, vat, grand_total, paid, due, payment_status, user_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $connect->prepare($sql);
        // Types: i=int, s=string, d=decimal
        $stmt->bind_param("isssddddisi", $supplierId, $poNumber, $purchaseDate, $dueDate, $subTotal, $vat, $grandTotal, $paid, $due, $paymentStatus, $userId);
        $stmt->execute();
        $purchase_id = $stmt->insert_id;
        $stmt->close();

        if (!$purchase_id) {
            throw new Exception("Failed to create the main purchase record.");
        }

        // --- Step 2: Loop through and insert all the items from the form ---
        if (!empty($_POST['productName'])) {
            for($x = 0; $x < count($_POST['productName']); $x++) {
                $productId = $_POST['productName'][$x];
                $quantity = $_POST['quantity'][$x];
                $rate = $_POST['rateValue'][$x];
                $total = $_POST['totalValue'][$x];

                $itemSql = "INSERT INTO purchase_items (purchase_id, product_id, quantity, rate, total) VALUES (?, ?, ?, ?, ?)";
                $stmt_item = $connect->prepare($itemSql);
                // Types: i=int, i=int, s=string, s=string, s=string
                $stmt_item->bind_param("iisss", $purchase_id, $productId, $quantity, $rate, $total);
                $stmt_item->execute();
                $stmt_item->close();
            }
        } else {
            throw new Exception("No products were added to the purchase bill.");
        }

        // If everything was successful, commit the transaction
        $connect->commit();
        $valid['success'] = true;
        $valid['messages'] = "Purchase Bill Successfully Added";

    } catch (Exception $e) {
        // If any step failed, roll back all changes
        $connect->rollback();
        $valid['success'] = false;
        $valid['messages'] = "Error while adding purchase bill: " . $e->getMessage();
    }
    
    $connect->close();
    echo json_encode($valid);
}
?>