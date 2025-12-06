<?php 
require_once 'core.php';

$response = array('success' => false, 'products' => array());
$challanIds = $_POST['challan_ids'] ?? [];

if (!empty($challanIds)) {
    $placeholders = implode(',', array_fill(0, count($challanIds), '?'));
    $types = str_repeat('i', count($challanIds));

    // UPDATED: Query now uses GROUP_CONCAT to gather all item remarks
    $sql = "SELECT 
                ci.product_id, 
                SUM(ci.quantity - IFNULL(bci.total_billed, 0)) as remaining_quantity,ci.size,
                GROUP_CONCAT(ci.remarks SEPARATOR ', ') as remarks
            FROM challan_items ci
            LEFT JOIN (
                SELECT challan_item_id, SUM(billed_quantity) as total_billed
                FROM billed_challan_items
                GROUP BY challan_item_id
            ) bci ON ci.challan_item_id = bci.challan_item_id
            WHERE ci.challan_id IN ($placeholders)
            GROUP BY ci.product_id
            HAVING remaining_quantity > 0";
    
    $stmt = $connect->prepare($sql);
    $stmt->bind_param($types, ...$challanIds);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = array();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    $response['success'] = true;
    $response['products'] = $products;
    $stmt->close();
}

$connect->close();
echo json_encode($response);
?>