<?php 

require_once 'core.php';

// Get the orderId from the POST request
$orderId = $_POST['orderId'];

// Setup the response array
$valid = array('order' => array(), 'order_item' => array());

// UPDATED: The SQL query now JOINS the partys table to get the correct client info.
// SECURE: It uses a prepared statement to prevent SQL injection.
$sql = "SELECT 
            o.order_id, o.order_date, o.sub_total, o.vat, o.total_amount, o.discount, 
            o.grand_total, o.paid, o.due, o.payment_type, o.payment_status,
            p.name AS client_name, p.contact_no AS client_contact, p.gstin
        FROM orders o
        LEFT JOIN partys p ON o.party_id = p.id
        WHERE o.order_id = ?";

$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();

// Use fetch_assoc() to get an associative array (key-value pairs), which is easier to work with.
$data = $result->fetch_assoc();
$valid['order'] = $data;

$stmt->close();
$connect->close();

// Return the data as a JSON object
echo json_encode($valid);