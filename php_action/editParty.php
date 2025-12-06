<?php 
require_once 'core.php';

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {
    $partyId = $_POST['party_id'];
    $partyName = $_POST['editPartyName'];
    $gstin = $_POST['editGstin'];
    $contactNo = $_POST['editContactNo'];
    $email = $_POST['editEmail'];
    $billingAddr = $_POST['editBillingAddr'];
    $shippingAddr = $_POST['editShippingAddr'];

    $sql = "UPDATE partys SET name = ?, gstin = ?, contact_no = ?, email = ?, billing_addr = ?, shipping_addr = ? WHERE id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("ssssssi", $partyName, $gstin, $contactNo, $email, $billingAddr, $shippingAddr, $partyId);

    if($stmt->execute()) {
        $valid['success'] = true;
        $valid['messages'] = "Successfully Updated";
    } else {
        $valid['success'] = false;
        $valid['messages'] = "Error while updating party info";
    }
    $stmt->close();
    $connect->close();
    echo json_encode($valid);
}