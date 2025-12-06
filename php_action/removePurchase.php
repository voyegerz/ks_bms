<?php 
require_once 'core.php';

$valid['success'] = false;
$valid['messages'] = null;

if ($_POST) {
    $purchaseId = $_POST['purchaseId'] ?? null;

    if ($purchaseId) {
        $connect->begin_transaction();
        try {
            // Main query to delete the purchase record
            $sql = "DELETE FROM purchases WHERE purchase_id = ?";
            $stmt = $connect->prepare($sql);
            $stmt->bind_param("i", $purchaseId);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                // If deletion was successful, commit the transaction
                $connect->commit();
                $valid['success'] = true;
                $valid['messages'] = "Purchase Bill Successfully Removed";
            } else {
                // Throw an exception if the record was not found or not deleted
                throw new Exception("Failed to remove the purchase bill. It may have already been deleted.");
            }
            $stmt->close();
        } catch (Exception $e) {
            // If any error occurs, roll back the transaction
            $connect->rollback();
            $valid['success'] = false;
            $valid['messages'] = "Error while removing the purchase bill: " . $e->getMessage();
        }
    } else {
        $valid['messages'] = "Purchase ID was not provided.";
    }

    $connect->close();
    echo json_encode($valid);
}
?>