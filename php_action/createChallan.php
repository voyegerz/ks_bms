<?php 
require_once 'core.php';

$valid = array('success' => false, 'messages' => array());

if($_POST) {    
    $connect->begin_transaction();
    try {
        // --- Get form data ---
        $challanNo   = $_POST['challanNo'];
        $challanDate = date('Y-m-d', strtotime($_POST['challanDate']));
        $partyId     = $_POST['partyId'];
        $remarks     = $_POST['remarks'] ?? null;
        $userId      = $_SESSION['userId'];

        if (empty($partyId) || empty($challanNo)) {
            throw new Exception("Party and Challan Number are required.");
        }

        // --- Fetch Party Name to create the formatted challan number ---
        $partySql = "SELECT name FROM partys WHERE id = ?";
        $stmtParty = $connect->prepare($partySql);
        $stmtParty->bind_param("i", $partyId);
        $stmtParty->execute();
        $partyName = $stmtParty->get_result()->fetch_assoc()['name'];
        $stmtParty->close();

        // Create the formatted, party-wise challan number
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $partyName), 0, 4));
        $formattedChallanNo = $prefix . '/' . $challanNo;

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


        // --- Insert the new challan ---
        // Note: The order_id column is now omitted from the main insert
        $sql = "INSERT INTO challans (challan_date, challan_no, party_id, remarks, user_id, challan_status) 
                VALUES (?, ?, ?, ?, ?, 0)";
        
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("ssisi", $challanDate, $formattedChallanNo, $partyId, $remarks, $userId);
        $stmt->execute();
        $challan_id = $stmt->insert_id;
        $stmt->close();
        
        if(!$challan_id) { throw new Exception("Failed to create the challan record."); }
        
        // --- Insert Challan Items ---
        if (!empty($_POST['productName'])) {
            for($x = 0; $x < count($_POST['productName']); $x++) {
                $quantityInput = $_POST['quantity'][$x];
                $parts = explode('/', $quantityInput);

                // The `quantity` column will store the full string (e.g., "3/10.00")
                $quantity = $parts[0] ? (int)($parts[0]) : 0;
                // The new `size` column will store the numeric part after the slash
                $size= isset($parts[1]) ? trim($parts[1]) : null;

                $itemSql = "INSERT INTO challan_items (challan_id, product_id, quantity, size, remarks) VALUES (?, ?, ?, ?, ?)";
                $stmt_item = $connect->prepare($itemSql);
                $stmt_item->bind_param("iisss", $challan_id, $_POST['productName'][$x], $quantity, $size,  $_POST['itemRemarks'][$x]);
                $stmt_item->execute();
                $stmt_item->close();
            }
        } else {
             throw new Exception("No products were added to the challan.");
        }

        $connect->commit();
        $valid['success'] = true;
        $valid['messages'] = "Successfully Created Challan.";
        
    } catch (Exception $e) {
        $connect->rollback();
        $valid['success'] = false;
        $valid['messages'] = "Error: " . $e->getMessage();
    }
    
    $connect->close();
    echo json_encode($valid);
}
?>