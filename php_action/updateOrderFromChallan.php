<?php 
require_once 'core.php';

$response = array('success' => false, 'challans' => array());
$partyId = $_POST['party_id'] ?? 0;

if ($partyId) {
    // This query now finds challans that have items where the dispatched quantity
    // is greater than the sum of quantities already billed for that item.
    $sql = "SELECT DISTINCT c.challan_id, c.challan_date FROM challans c
            INNER JOIN challan_items ci ON c.challan_id = ci.challan_id
            LEFT JOIN (
                SELECT challan_item_id, SUM(billed_quantity) as total_billed
                FROM billed_challan_items
                GROUP BY challan_item_id
            ) bci ON ci.challan_item_id = bci.challan_item_id
            WHERE c.party_id = ? AND c.challan_status = 2 AND (ci.quantity > bci.total_billed OR bci.total_billed IS NULL)";
    
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $partyId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $challans = array();
    while ($row = $result->fetch_assoc()) {
        $challans[] = $row;
    }
    
    $response['success'] = true;
    $response['challans'] = $challans;
    $stmt->close();
}

$connect->close();
echo json_encode($response);
?>