<?php 
require_once 'core.php';

$valid['success'] = array('success' => false, 'messages' => array());
$partyId = $_POST['party_id'];

if($partyId) { 
    $sql = "DELETE FROM partys WHERE id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $partyId);
    
    if($stmt->execute()) {
        $valid['success'] = true;
        $valid['messages'] = "Successfully Removed";		
    } else {
        $valid['success'] = false;
        $valid['messages'] = "Error while removing the party";
    }
    $stmt->close();
    $connect->close();
    echo json_encode($valid);
}