<?php 
require_once 'core.php';

$sql = "SELECT p.purchase_id, p.po_number, p.purchase_date, p.grand_total, p.due, p.due_date, p.payment_status, s.name AS supplier_name 
        FROM purchases p
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        ORDER BY p.purchase_id DESC";

$result = $connect->query($sql);
$output = array('data' => array());

if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $purchaseId = $row['purchase_id'];
        
        // --- ADDED: Payment Status Logic ---
        $paymentStatus = "";
        if($row['payment_status'] == 1) {
            $paymentStatus = "<label class='label label-success'>Full Payment</label>";
        } else if($row['payment_status'] == 2) {
            $paymentStatus = "<label class='label label-info'>Advance Payment</label>";
        } else {
            $paymentStatus = "<label class='label label-warning'>No Payment</label>";
        }
        
        // --- ADDED: Action Button Logic ---
        $button = '<div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Action <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><a href="purchases.php?p=edit&id='.$purchaseId.'"> <i class="glyphicon glyphicon-edit"></i> Edit</a></li>
            <li><a type="button" data-toggle="modal" data-target="#removePurchaseModal" onclick="removePurchase('.$purchaseId.')"> <i class="glyphicon glyphicon-trash"></i> Remove</a></li>       
          </ul>
        </div>';
        
        $output['data'][] = array(
            $row['purchase_id'],
            $row['po_number'],
            date("d-m-Y", strtotime($row['purchase_date'])),
            $row['supplier_name'],
            $row['grand_total'],
            $row['due'],
            $row['due_date'] ? date("d-m-Y", strtotime($row['due_date'])) : 'N/A',
            $paymentStatus,
            $button
        );
    }
}
$connect->close();
echo json_encode($output);
?>