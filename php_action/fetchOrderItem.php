<?php 
// Use core.php for consistency with your other action files
require_once 'core.php';

// Initialize a standard response structure
$response = array('success' => false, 'items' => array(), 'party_id' => null);

if ($_POST['orderId']) {
    $orderId = $_POST['orderId'];
    
    // --- Get the Party ID from the order first ---
    $orderSql = "SELECT party_id FROM orders WHERE order_id = ?";
    $orderStmt = $connect->prepare($orderSql);
    $orderStmt->bind_param("i", $orderId);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result();
    if($orderResult->num_rows > 0) {
        $orderRow = $orderResult->fetch_assoc();
        $response['party_id'] = $orderRow['party_id'];
    }
    $orderStmt->close();


    // --- Now get the items for that order ---
    $sql = "SELECT product_id, quantity FROM order_item WHERE order_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        $items = array();
        while($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $response['success'] = true;
        $response['items'] = $items;
    } 
    // If the query fails, success will remain false
    
    $stmt->close();
}

$connect->close();

// Echo the structured JSON response
echo json_encode($response);
?>