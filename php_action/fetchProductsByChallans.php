<?php 
require_once 'core.php';

$response = array('success' => false, 'products' => array());
$challanIds = $_POST['challan_ids'] ?? [];

if (!empty($challanIds)) {
    $placeholders = implode(',', array_fill(0, count($challanIds), '?'));
    $types = str_repeat('i', count($challanIds));

    // This query SUMS up quantities if a product appears in multiple selected challans
    $sql = "SELECT product_id, SUM(quantity) as total_quantity 
            FROM challan_items 
            WHERE challan_id IN ($placeholders)
            GROUP BY product_id";
    
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