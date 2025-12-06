<?php 
require_once 'core.php';

$valid['success'] = false;
$valid['messages'] = null;

if ($_POST) {
    $quotationId = $_POST['quotationId'] ?? null;

    if ($quotationId) {
        $connect->begin_transaction();
        try {
            // The main DELETE query
            $sql = "DELETE FROM quotations WHERE quotation_id = ?";
            $stmt = $connect->prepare($sql);
            $stmt->bind_param("i", $quotationId);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                // Because of ON DELETE CASCADE in your database,
                // all items in 'quotation_items' are automatically deleted.
                
                $connect->commit();
                $valid['success'] = true;
                $valid['messages'] = "Quotation Successfully Removed";
            } else {
                throw new Exception("Failed to remove the quotation.");
            }
            $stmt->close();
        } catch (Exception $e) {
            $connect->rollback();
            $valid['success'] = false;
            $valid['messages'] = "Error while removing the quotation: " . $e->getMessage();
        }
    } else {
        $valid['messages'] = "Quotation ID was not provided.";
    }

    $connect->close();
    echo json_encode($valid);
}
?>