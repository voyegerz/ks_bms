<?php 
require_once 'core.php'; // Use core.php for consistency

$valid = array('success' => false, 'messages' => '');

$challanId = $_POST['challan_id'] ?? null;

if($challanId) { 
    // Begin a transaction for data integrity
    $connect->begin_transaction();

    try {
        // --- Step 1: Get all items from the challan to reverse stock ---
        // We only need to do this if the challan was actually dispatched or delivered.
        $fetchSql = "SELECT ci.product_id, ci.quantity, c.challan_status
                     FROM challan_items ci
                     JOIN challans c ON ci.challan_id = c.challan_id
                     WHERE ci.challan_id = ?";
        
        $stmtFetch = $connect->prepare($fetchSql);
        $stmtFetch->bind_param("i", $challanId);
        $stmtFetch->execute();
        $result = $stmtFetch->get_result();
        
        $itemsToRevert = [];
        $challanStatus = null;
        while ($row = $result->fetch_assoc()) {
            $challanStatus = $row['challan_status']; // Status is the same for all items
            $itemsToRevert[] = $row;
        }
        $stmtFetch->close();

        // --- Step 2: If challan was dispatched/delivered, add stock back ---
        if ($challanStatus == 1 || $challanStatus == 2) {
            $updateStockSql = "UPDATE product SET quantity = quantity + ? WHERE product_id = ?";
            $stmtUpdate = $connect->prepare($updateStockSql);
            
            foreach ($itemsToRevert as $item) {
                $stmtUpdate->bind_param("ii", $item['quantity'], $item['product_id']);
                $stmtUpdate->execute();
            }
            $stmtUpdate->close();
        }

        // --- Step 3: Delete the main challan record ---
        // The database's ON DELETE CASCADE will now automatically delete all associated challan_items.
        $deleteChallanSql = "DELETE FROM challans WHERE challan_id = ?";
        $stmtDelete = $connect->prepare($deleteChallanSql);
        $stmtDelete->bind_param("i", $challanId);
        $stmtDelete->execute();
        $stmtDelete->close();

        // If all operations were successful, commit the transaction
        $connect->commit();

        $valid['success'] = true;
        $valid['messages'] = "Successfully Removed Challan";

    } catch (Exception $e) {
        // If any operation failed, roll back the transaction
        $connect->rollback();
        $valid['success'] = false;
        $valid['messages'] = "Error while removing challan: " . $e->getMessage();
    }
} else {
    $valid['messages'] = "Challan ID is required.";
}

$connect->close();
echo json_encode($valid);
?>