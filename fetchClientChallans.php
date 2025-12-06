<?php

require_once 'db_connect.php';

$valid = array('success' => false, 'messages' => '', 'data' => array());

if(isset($_POST['clientName']) && !empty($_POST['clientName'])) {
    $clientName = $_POST['clientName'];
    
    // Get all challans for this client that are delivered (status = 2)
    $challanSql = "SELECT c.challan_id, c.challan_date, c.client_contact 
                   FROM challans c 
                   WHERE c.client_name = ? AND c.challan_status = 2 
                   ORDER BY c.challan_date DESC";
    
    $stmt = $connect->prepare($challanSql);
    if (!$stmt) {
        $valid['messages'] = "Error preparing challan query: " . $connect->error;
        echo json_encode($valid);
        exit();
    }
    
    $stmt->bind_param("s", $clientName);
    $stmt->execute();
    $challanResult = $stmt->get_result();
    
    if($challanResult->num_rows > 0) {
        $challans = array();
        $clientContact = '';
        
        while($challanRow = $challanResult->fetch_assoc()) {
            $challans[] = $challanRow['challan_id'];
            if(empty($clientContact)) {
                $clientContact = $challanRow['client_contact'];
            }
        }
        
        if(!empty($challans)) {
            // Get all products from these challans, aggregated by product
            $challanIds = implode(',', array_map('intval', $challans));
            
            $productSql = "SELECT 
                            p.product_id,
                            p.product_name,
                            p.rate,
                            SUM(ci.quantity) as total_quantity,
                            GROUP_CONCAT(CONCAT(c.challan_id, ':', ci.quantity) SEPARATOR ',') as challan_details
                           FROM challan_items ci
                           JOIN challans c ON ci.challan_id = c.challan_id
                           JOIN product p ON ci.product_id = p.product_id
                           WHERE ci.challan_id IN ($challanIds)
                           GROUP BY p.product_id, p.product_name, p.rate
                           ORDER BY p.product_name";
            
            $productResult = $connect->query($productSql);
            
            if($productResult && $productResult->num_rows > 0) {
                $products = array();
                while($productRow = $productResult->fetch_assoc()) {
                    $products[] = array(
                        'product_id' => $productRow['product_id'],
                        'product_name' => $productRow['product_name'],
                        'rate' => $productRow['rate'],
                        'total_quantity' => $productRow['total_quantity'],
                        'challan_details' => $productRow['challan_details']
                    );
                }
                
                $valid['success'] = true;
                $valid['data'] = array(
                    'client_contact' => $clientContact,
                    'products' => $products,
                    'challan_count' => count($challans)
                );
                $valid['messages'] = 'Found ' . count($products) . ' products from ' . count($challans) . ' delivered challans';
            } else {
                $valid['messages'] = 'No products found in delivered challans for this client';
            }
        }
    } else {
        $valid['messages'] = 'No delivered challans found for this client';
    }
    
    $stmt->close();
} else {
    $valid['messages'] = 'Client name is required';
}

$connect->close();
echo json_encode($valid);

?>