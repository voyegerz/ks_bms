<?php 
require_once 'core.php';

$valid['success'] = array('success' => false, 'messages' => array());

if($_POST) {    
    $hsn           = $_POST['hsn'];
    $productName   = $_POST['productName'];
    $quantity      = $_POST['quantity'];
    $rate          = $_POST['rate'];
    $brandName     = $_POST['brandName'];
    $categoryName  = $_POST['categoryName'];
    $productStatus = $_POST['productStatus'];
    
    $url = ''; // Initialize the URL variable

    // --- UPDATED LOGIC FOR OPTIONAL IMAGE ---
    // Check if a file was uploaded and there's no error
    if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] == 0) {
        $productImage  = $_FILES['productImage'];
        $type = explode('.', $productImage['name']);
        $type = strtolower(end($type));
        $url = '../assests/images/stock/'.uniqid(rand()).'.'.$type;

        if(in_array($type, array('gif', 'jpg', 'jpeg', 'png'))) {
            if(!move_uploaded_file($productImage['tmp_name'], $url)) {
                // If upload fails, stop and return an error
                $valid['success'] = false;
                $valid['messages'] = "Error while uploading the file.";
                echo json_encode($valid);
                exit();
            }
        } else {
            // If file type is invalid, stop and return an error
            $valid['success'] = false;
            $valid['messages'] = "Invalid file type. Only JPG, PNG, and GIF are accepted.";
            echo json_encode($valid);
            exit();
        }
    } else {
        // If no file is uploaded, use a path to a default placeholder image
        $url = '../assests/images/photo_default.png';
    }

    // --- SECURE DATABASE INSERT ---
    $sql = "INSERT INTO product (hsn, product_name, product_image, brand_id, categories_id, quantity, rate, active, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";

    $stmt = $connect->prepare($sql);
    $stmt->bind_param("sssiissi", $hsn, $productName, $url, $brandName, $categoryName, $quantity, $rate, $productStatus);

    if($stmt->execute()) {
        $valid['success'] = true;
        $valid['messages'] = "Successfully Added";   
    } else {
        $valid['success'] = false;
        $valid['messages'] = "Error while adding the product: " . $connect->error;
    }
    $stmt->close();

    $connect->close();
    echo json_encode($valid);
 
} // /if $_POST