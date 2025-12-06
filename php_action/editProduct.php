<?php 
require_once 'core.php';

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {
    $productId     = $_POST['productId'];
    $hsn           = $_POST['editHsn'];
    $productName   = $_POST['editProductName']; 
    $quantity      = $_POST['editQuantity'];
    $rate          = $_POST['editRate'];
    $brandName     = $_POST['editBrandName'];
    $categoryName  = $_POST['editCategoryName'];
    $productStatus = $_POST['editProductStatus'];
    // ## ADDED: Get the new manage_stock value ##
    $manageStock   = $_POST['editManageStock'];

    // --- SECURE DATABASE UPDATE ---
    // UPDATED: Query now includes the 'manage_stock' column
    $sql = "UPDATE product SET 
                hsn = ?, 
                product_name = ?, 
                brand_id = ?, 
                categories_id = ?, 
                quantity = ?, 
                rate = ?, 
                active = ?,
                manage_stock = ?
            WHERE product_id = ?";

    $stmt = $connect->prepare($sql);
    // UPDATED: Added 'i' for the new integer and bound the variable
    $stmt->bind_param("ssiissiii", $hsn, $productName, $brandName, $categoryName, $quantity, $rate, $productStatus, $manageStock, $productId);

    if($stmt->execute()) {
        $valid['success'] = true;
        $valid['messages'] = "Successfully Updated"; 
    } else {
        $valid['success'] = false;
        $valid['messages'] = "Error while updating product info: " . $connect->error;
    }
    $stmt->close();
    
} // /$_POST
    
$connect->close();

echo json_encode($valid);