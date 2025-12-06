<?php 
require_once 'core.php';

$sql = "SELECT id, name, gstin, contact_no FROM partys ORDER BY name ASC";
$result = $connect->query($sql);
$output = array('data' => array());

if($result->num_rows > 0) { 
    while($row = $result->fetch_assoc()) {
        $partyId = $row['id'];
        $button = '<div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Action <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><a type="button" data-toggle="modal" data-target="#editPartyModal" onclick="editParty('.$partyId.')"> <i class="glyphicon glyphicon-edit"></i> Edit</a></li>
            <li><a type="button" data-toggle="modal" data-target="#removePartyModal" onclick="removeParty('.$partyId.')"> <i class="glyphicon glyphicon-trash"></i> Remove</a></li>       
          </ul>
        </div>';

        $output['data'][] = array( 
            $row['name'],
            $row['gstin'],
            $row['contact_no'],
            $button
        ); 	
    }
}
$connect->close();
echo json_encode($output);