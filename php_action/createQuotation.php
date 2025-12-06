<?php 
require_once 'core.php';

$valid = array('success' => false, 'messages' => '');

if($_POST) {    
    $connect->begin_transaction();
    try {
        // --- Main quotation data from the form ---
        $quotationDate   = date('Y-m-d', strtotime($_POST['quotationDate']));
        $partyId         = $_POST['partyId'];
        $subTotal        = $_POST['subTotalValue'];
        $vat             = $_POST['vatValue'];
        $grandTotal      = $_POST['grandTotalValue'];
        $userId          = $_SESSION['userId'];

        // --- Step 1: Insert into the main 'quotations' table ---
        // Default status is 1 (Pending)
        $sql = "INSERT INTO quotations (quotation_date, party_id, sub_total, vat, grand_total, quotation_status, user_id)
                VALUES (?, ?, ?, ?, ?, 1, ?)";
        
        $stmt = $connect->prepare($sql);
        // Types: s=string, i=int, d=decimal
        $stmt->bind_param("siddii", $quotationDate, $partyId, $subTotal, $vat, $grandTotal, $userId);
        $stmt->execute();
        $quotation_id = $stmt->insert_id;
        $stmt->close();

        if (!$quotation_id) {
            throw new Exception("Failed to create the main quotation record.");
        }

        // --- Step 2: Loop through and insert all the items from the form ---
        if (!empty($_POST['productName'])) {
            for($x = 0; $x < count($_POST['productName']); $x++) {
                $productId = $_POST['productName'][$x];
                $quantity = $_POST['quantity'][$x];
                $rate = $_POST['rateValue'][$x];
                $total = $_POST['totalValue'][$x];

                $itemSql = "INSERT INTO quotation_items (quotation_id, product_id, quantity, rate, total) VALUES (?, ?, ?, ?, ?)";
                $stmt_item = $connect->prepare($itemSql);
                // Types: i=int, i=int, s=string, s=string, s=string
                $stmt_item->bind_param("iisss", $quotation_id, $productId, $quantity, $rate, $total);
                $stmt_item->execute();
                $stmt_item->close();
            }
        } else {
            throw new Exception("No products were added to the quotation.");
        }
        
        // If everything was successful, commit the transaction
        $connect->commit();
        $valid['success'] = true;
        $valid['messages'] = "Quotation Successfully Created";

    } catch (Exception $e) {
        // If any step failed, roll back all changes
        $connect->rollback();
        $valid['success'] = false;
        $valid['messages'] = "Error while creating quotation: " . $e->getMessage();
    }
    
    $connect->close();
    echo json_encode($valid);
}
?>