<?php 
require_once 'core.php';

$sql = "SELECT q.quotation_id, q.quotation_date, q.grand_total, q.quotation_status, p.name AS party_name 
        FROM quotations q
        LEFT JOIN partys p ON q.party_id = p.id
        ORDER BY q.quotation_id DESC";

$result = $connect->query($sql);
$output = array('data' => array());

if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $quotationId = $row['quotation_id'];
        
        $status = '';
        if($row['quotation_status'] == 1) { 
            $status = "<label class='label label-info'>Pending</label>"; 
        } else if($row['quotation_status'] == 2) { 
            $status = "<label class='label label-success'>Approved</label>"; 
        } else { 
            $status = "<label class='label label-danger'>Rejected</label>"; 
        }

        $button = '<div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Action <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><a href="orders.php?o=add&from_quote='.$quotationId.'"> <i class="glyphicon glyphicon-new-window"></i> Convert to Order</a></li>
            <li><a href="quotations.php?q=edit&id='.$quotationId.'"> <i class="glyphicon glyphicon-edit"></i> Edit</a></li>
            <li><a href="php_action/printQuotation.php?i='.$quotationId.'" target="_blank"> <i class="glyphicon glyphicon-print"></i> Print</a></li>
            <li><a type="button" data-toggle="modal" data-target="#removeQuotationModal" onclick="removeQuotation('.$quotationId.')"> <i class="glyphicon glyphicon-trash"></i> Remove</a></li>       
          </ul>
        </div>';
        
        $output['data'][] = array(
            $quotationId, 
            date("d-m-Y", strtotime($row['quotation_date'])), 
            $row['party_name'], 
            $row['grand_total'], 
            $status, 
            $button
        );
    }
}

$connect->close();
echo json_encode($output);
?>