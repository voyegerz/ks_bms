<?php 
require_once 'core.php';

// UPDATED: Added product.hsn to the query
$sql = "SELECT 
            product.product_id, 
            product.hsn, 
            product.product_name, 
            product.product_image, 
            product.brand_id,
            product.categories_id, 
            product.quantity, 
            product.rate, 
            product.active, 
            product.status, 
            product.manage_stock,
            brands.brand_name, 
            categories.categories_name 
        FROM product 
        INNER JOIN brands ON product.brand_id = brands.brand_id 
        INNER JOIN categories ON product.categories_id = categories.categories_id  
        WHERE product.status = 1
        ORDER BY product.product_id DESC";

$result = $connect->query($sql);

$output = array('data' => array());

if($result->num_rows > 0) { 
    $active = ""; 
    // UPDATED: Using fetch_assoc() for better readability
    while($row = $result->fetch_assoc()) {
        $productId = $row['product_id'];
        
        // Active status
        if($row['active'] == 1) {
            $active = "<label class='label label-success'>Available</label>";
        } else {
            $active = "<label class='label label-danger'>Not Available</label>";
        }

        if($row['manage_stock'] == 1) {
            $manageStock = "<div class='text-center'><label class='label label-success'>Yes</label></div>";

        } else {
            $manageStock = "<div class='text-center'><label class='label label-danger'>No</label></div>";
        }

        $button = '<div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Action <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><a type="button" data-toggle="modal" id="editProductModalBtn" data-target="#editProductModal" onclick="editProduct('.$productId.')"> <i class="glyphicon glyphicon-edit"></i> Edit</a></li>
            <li><a type="button" data-toggle="modal" data-target="#removeProductModal" id="removeProductModalBtn" onclick="removeProduct('.$productId.')"> <i class="glyphicon glyphicon-trash"></i> Remove</a></li>       
          </ul>
        </div>';

		$hsn = $row['hsn'];
        $brand = $row['brand_name'];
        $category = $row['categories_name'];
        $imageUrl = substr($row['product_image'], 3);
        $productImage = "<img class='img-round' src='".$imageUrl."' style='height:30px; width:50px;'/>";
        
        // UPDATED: Using column names instead of numeric indexes
        $output['data'][] = array(       
            $productImage,
			$hsn,
            $row['product_name'], 
            $row['rate'],
            $row['quantity'],          
            $brand,      
            $category,
            $active,
            $manageStock,
            $button         
        );  
    } // /while 
} // if num_rows

$connect->close();

echo json_encode($output);