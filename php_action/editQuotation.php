<?php 
require_once 'core.php';

$valid = array('success' => false, 'messages' => '');

if($_POST) {    
    $connect->begin_transaction();
    try {
        // --- Step 1: Get data from the form ---
        $quotationId     = $_POST['quotationId'];
        $quotationDate   = date('Y-m-d', strtotime($_POST['quotationDate']));
        $partyId         = $_POST['partyId'];
        $subTotal        = $_POST['subTotalValue'];
        $vat             = $_POST['vatValue'];
        $grandTotal      = $_POST['grandTotalValue'];
        $quotationStatus = $_POST['quotationStatus']; // Get status from the edit form
        
        // --- Step 2: Update the main 'quotations' record ---
        $sql = "UPDATE quotations SET
                    quotation_date = ?,
                    party_id = ?,
                    sub_total = ?,
                    vat = ?,
                    grand_total = ?,
                    quotation_status = ?
                WHERE quotation_id = ?";
        
        $stmt = $connect->prepare($sql);
        // Types: s=string, i=int, d=decimal
        $stmt->bind_param("siddiii", $quotationDate, $partyId, $subTotal, $vat, $grandTotal, $quotationStatus, $quotationId);
        $stmt->execute();
        $stmt->close();

        // --- Step 3: Delete all old items for this quotation ---
        $deleteItemsSql = "DELETE FROM quotation_items WHERE quotation_id = ?";
        $stmtDelete = $connect->prepare($deleteItemsSql);
        $stmtDelete->bind_param("i", $quotationId);
        $stmtDelete->execute();
        $stmtDelete->close();

        // --- Step 4: Insert the new/updated list of items ---
        if (!empty($_POST['productName'])) {
            for($x = 0; $x < count($_POST['productName']); $x++) {
                $productId = $_POST['productName'][$x];
                $quantity = $_POST['quantity'][$x];
                $rate = $_POST['rateValue'][$x];
                $total = $_POST['totalValue'][$x];

                $itemSql = "INSERT INTO quotation_items (quotation_id, product_id, quantity, rate, total) VALUES (?, ?, ?, ?, ?)";
                $stmt_item = $connect->prepare($itemSql);
                $stmt_item->bind_param("iisss", $quotationId, $productId, $quantity, $rate, $total);
                $stmt_item->execute();
                $stmt_item->close();
            }
        } else {
            throw new Exception("A quotation must have at least one product.");
        }
        
        // If everything was successful, commit the transaction
        $connect->commit();
        $valid['success'] = true;
        $valid['messages'] = "Quotation Successfully Updated";

    } catch (Exception $e) {
        // If any step failed, roll back all changes
        $connect->rollback();
        $valid['success'] = false;
        $valid['messages'] = "Error while updating quotation: " . $e->getMessage();
    }
    
    $connect->close();
    echo json_encode($valid);
}
?>