<?php 
require_once 'core.php';

$sql = "SELECT 
            c.challan_id, 
            c.challan_no,
            c.challan_date, 
            c.challan_status, 
            p.name AS party_name,
            o.invoice_no
        FROM challans c
        LEFT JOIN partys p ON c.party_id = p.id
        LEFT JOIN orders o ON c.order_id = o.order_id
        ORDER BY c.challan_id DESC";

$result = $connect->query($sql);
$output = array('data' => array());

if($result->num_rows > 0) {
    $x = 1;
    while($row = $result->fetch_assoc()) {
        $challanId = $row['challan_id'];

        // Challan Status logic
        $challanStatus = '';
        if($row['challan_status'] == 0) {
            $challanStatus = "<label class='label label-info'>Pending</label>";
        } else if($row['challan_status'] == 1) {
            $challanStatus = "<label class='label label-primary'>Dispatched</label>";
        } else if($row['challan_status'] == 2) {
            $challanStatus = "<label class='label label-success'>Delivered</label>";
        } else if($row['challan_status'] == 3) {
            $challanStatus = "<label class='label label-danger'>Cancelled</label>";
        } else if($row['challan_status'] == 4) {
            $challanStatus = "<label class='label label-default'>Billed</label>";
        }

        // Action button dropdown
        $button = '<div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Action <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><a href="challans.php?o=editChallan&i='.$challanId.'"> <i class="glyphicon glyphicon-edit"></i> Edit</a></li>
            <li><a href="#" onclick="printChallan('.$challanId.')"> <i class="glyphicon glyphicon-print"></i> Print</a></li>
            <li><a type="button" data-toggle="modal" data-target="#removeChallanModal" onclick="removeChallan('.$challanId.')"> <i class="glyphicon glyphicon-trash"></i> Remove</a></li>       
          </ul>
        </div>';

        // CORRECTED: This array now has 5 items to match your 5 table columns
        $output['data'][] = array( 
            $row['challan_id'],
            // $row['invoice_no'],
            $row['challan_no'],
            date("d-m-Y", strtotime($row['challan_date'])),
            $row['party_name'],
            $challanStatus,
            $button
        ); 	
        $x++;
    } // /while
} // if num_rows

$connect->close();
echo json_encode($output);
?>