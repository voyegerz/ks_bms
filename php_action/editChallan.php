<?php
require_once 'core.php';

$valid = array('success' => false, 'messages' => array());

if ($_POST) {
    // Begin a database transaction for safety
    $connect->begin_transaction();
    try {
        $challanNo = $_POST['challanNo'];
        $challanId = $_POST['challanId'];
        $challanDate = date('Y-m-d', strtotime($_POST['challanDate']));
        $partyId = $_POST['partyId'];
        $remarks = $_POST['remarks'] ?? null;
        $challanStatus = $_POST['challanStatus'];

        if (empty($partyId) || empty($challanNo)) {
            throw new Exception("Party, Challan Number are required.");
        }
        
        // --- Fetch Party Name to create the formatted challan number ---
        $partySql = "SELECT name FROM partys WHERE id = ?";
        $stmtParty = $connect->prepare($partySql);
        $stmtParty->bind_param("i", $partyId);
        $stmtParty->execute();
        $partyName = $stmtParty->get_result()->fetch_assoc()['name'];
        $stmtParty->close();

        // --- Check for duplicates FOR THIS PARTY ---
        $checkSql = "SELECT COUNT(*) as count FROM challans WHERE challan_no = ? AND party_id = ?";
        $stmtCheck = $connect->prepare($checkSql);
        $stmtCheck->bind_param("si", $formattedChallanNo, $partyId);
        $stmtCheck->execute();
        $checkResult = $stmtCheck->get_result()->fetch_assoc();
        $stmtCheck->close();
        if ($checkResult['count'] > 0) {
            throw new Exception("Challan No '{$formattedChallanNo}' already exists for this party.");
        }

        // --- Step 1: Fetch all old items from the challan BEFORE making changes ---
        $oldItems = [];
        $getOldItemsSql = "SELECT product_id, quantity FROM challan_items WHERE challan_id = ?";
        $stmtOld = $connect->prepare($getOldItemsSql);
        $stmtOld->bind_param("i", $challanId);
        $stmtOld->execute();
        $resultOld = $stmtOld->get_result();
        while ($row = $resultOld->fetch_assoc()) {
            $oldItems[$row['product_id']] = $row['quantity'];
        }
        $stmtOld->close();

        // --- Step 2: Add the old quantities back to the product stock (reversal) ---
        foreach ($oldItems as $productId => $quantity) {
            $updateStockSql = "UPDATE product SET quantity = quantity + ? WHERE product_id = ?";
            $stmtStock = $connect->prepare($updateStockSql);
            $stmtStock->bind_param("ii", $quantity, $productId);
            $stmtStock->execute();
            $stmtStock->close();
        }

        // --- Step 3: Update the main challan record ---
        $sql = "UPDATE challans SET challan_date = ?, challan_no = ?, party_id = ?, remarks = ?, challan_status = ? WHERE challan_id = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("ssisii", $challanDate, $challanNo, $partyId, $remarks, $challanStatus, $challanId);
        $stmt->execute();
        $stmt->close();

        // --- Step 4: Delete all existing items for this challan ---
        $deleteItemSql = "DELETE FROM challan_items WHERE challan_id = ?";
        // CORRECTED: This line now correctly prepares the SQL string ($deleteItemSql)
        $stmtDelete = $connect->prepare($deleteItemSql);
        $stmtDelete->bind_param("i", $challanId);
        $stmtDelete->execute();
        $stmtDelete->close();

        // --- Step 5: Insert the new/updated items and deduct stock if dispatched ---
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

                // ## ADDED: Get the new remarks value for this item ##
                $itemRemarks = $_POST['itemRemarks'][$x];


                // ## UPDATED: Query now includes the 'remarks' column ##
                $itemSql = "INSERT INTO challan_items (challan_id, product_id, quantity, size, remarks) VALUES (?, ?, ?, ?, ?)";
                $stmtItem = $connect->prepare($itemSql);
                // ## UPDATED: Bind the new remarks variable (iiss for integer, integer, string, string) ##
                $stmtItem->bind_param("iisss", $challanId, $productId, $quantity, $size, $itemRemarks);
                $stmtItem->execute();
                $stmtItem->close();

                // If the challan is marked as "Dispatched", deduct the new stock quantity
                if ($challanStatus == 1) { // 1 = Dispatched
                    if ($manageStockResult && $manageStockResult['manage_stock'] == 1) {
                        $deductStockSql = "UPDATE product SET quantity = quantity - ? WHERE product_id = ?";
                        $stmtDeduct = $connect->prepare($deductStockSql);
                        $stmtDeduct->bind_param("ii", $quantity, $productId);
                        $stmtDeduct->execute();
                        $stmtDeduct->close();
                    }
                }
            }
        }

        // If all steps were successful, commit the changes
        $connect->commit();
        $valid['success'] = true;
        $valid['messages'] = "Successfully Updated Challan";

    } catch (Exception $e) {
        // If any step failed, roll back all database changes
        $connect->rollback();
        $valid['success'] = false;
        $valid['messages'] = "Error while updating challan: " . $e->getMessage();
    }

    $connect->close();
    echo json_encode($valid);
}
?>