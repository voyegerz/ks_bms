<?php 

require_once 'core.php';

// UPDATED: This single query is more efficient and secure.
// 1. It JOINS the `partys` table to get the correct client name and contact.
// 2. It uses a subquery to count order items, which is much faster than running a query inside the loop.
$sql = "SELECT 
            o.order_id, 
            o.invoice_no,
            o.order_date, 
            p.name AS client_name, 
            p.contact_no AS client_contact, 
            o.payment_status,
            o.order_status,
            (SELECT count(*) FROM order_item oi WHERE oi.order_id = o.order_id) as item_count
        FROM orders o
        LEFT JOIN partys p ON o.party_id = p.id
        WHERE o.order_status != 2
        ORDER BY o.order_id DESC";

$result = $connect->query($sql);

$output = array('data' => array());

if($result->num_rows > 0) { 
 
    $paymentStatus = ""; 
    $x = 1;

    // Use fetch_assoc() for better readability
    while($row = $result->fetch_assoc()) {
        $orderId = $row['order_id'];
        $itemCount = $row['item_count'];

        // Payment status logic
        if($row['payment_status'] == 1) {       
            $paymentStatus = "<label class='label label-success'>Full Payment</label>";
        } else if($row['payment_status'] == 2) {     
            $paymentStatus = "<label class='label label-info'>Advance Payment</label>";
        } else {        
            $paymentStatus = "<label class='label label-warning'>No Payment</label>";
        } // /else

        $orderStatus = "";
        if($row['order_status'] == 3) {
            $orderStatus = "<label class='label label-info'>Draft</label>";
        } else if ($row['order_status'] == 1) {
            $orderStatus = "<label class='label label-success'>Finalized</label>";
        }

        // Action buttons
        $button = '<div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Action <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><a href="orders.php?o=editOrd&i='.$orderId.'" id="editOrderModalBtn"> <i class="glyphicon glyphicon-edit"></i> Edit</a></li>
            
            <li><a type="button" data-toggle="modal" id="paymentOrderModalBtn" data-target="#paymentOrderModal" onclick="paymentOrder('.$orderId.')"> <i class="glyphicon glyphicon-save"></i> Payment</a></li>

            <li><a type="button" onclick="printOrder('.$orderId.')"> <i class="glyphicon glyphicon-print"></i> Print </a></li>
            
            <li><a type="button" data-toggle="modal" data-target="#removeOrderModal" id="removeOrderModalBtn" onclick="removeOrder('.$orderId.')"> <i class="glyphicon glyphicon-trash"></i> Remove</a></li>       
          </ul>
        </div>';

        $output['data'][] = array(       
            $x,
            // order invoice no
            $row['invoice_no'],
            // order date
            $row['order_date'],
            // client name
            $row['client_name'], 
            // client contact
            $row['client_contact'],            
            $itemCount,           
            $paymentStatus,
            $orderStatus,
            // button
            $button         
        );  
        $x++;
    } // /while 

} // if num_rows

$connect->close();

echo json_encode($output);