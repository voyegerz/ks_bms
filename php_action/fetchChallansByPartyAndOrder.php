<?php 
require_once 'core.php';

$response = array('success' => false, 'challans' => array());
$partyId = $_POST['party_id'] ?? 0;
$orderId = $_POST['order_id'] ?? 0;

if ($partyId && $orderId) {
    // Fetches challans for the specified party and order that are 'Delivered' (status=2)
    $sql = "SELECT challan_id, challan_date FROM challans 
            WHERE party_id = ? AND order_id = ? AND challan_status = 2
            ORDER BY challan_id ASC";
    
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("ii", $partyId, $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $challans = array();
    while ($row = $result->fetch_assoc()) {
        $challans[] = $row;
    }
    
    $response['success'] = true;
    $response['challans'] = $challans;
    $stmt->close();
} else {
    $response['messages'] = "Party ID and Order ID are required.";
}

$connect->close();
echo json_encode($response);
?>