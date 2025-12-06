<?php 
require_once 'core.php';

// This query now joins with the product table to count products and sum their quantities for each category.
$sql = "SELECT 
            c.categories_id, 
            c.categories_name, 
            c.categories_active, 
            c.categories_status,
            COUNT(p.product_id) AS product_count,
            IFNULL(SUM(p.quantity), 0) AS total_quantity
        FROM 
            categories c
        LEFT JOIN 
            product p ON c.categories_id = p.categories_id
        GROUP BY 
            c.categories_id";

$result = $connect->query($sql);
$output = array('data' => array());

if($result->num_rows > 0) { 
    while($row = $result->fetch_assoc()) {
        $categoriesId = $row['categories_id'];
        
        $status = ($row['categories_active'] == 1) ? "<label class='label label-success'>Available</label>" : "<label class='label label-danger'>Not Available</label>";

        $button = '<div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Action <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><a type="button" data-toggle="modal" data-target="#editCategoriesModal" onclick="editCategories('.$categoriesId.')"> <i class="glyphicon glyphicon-edit"></i> Edit</a></li>
            <li><a type="button" data-toggle="modal" data-target="#removeCategoriesModal" onclick="removeCategories('.$categoriesId.')"> <i class="glyphicon glyphicon-trash"></i> Remove</a></li>       
          </ul>
        </div>';

        // This array now matches the new 5-column table header
        $output['data'][] = array( 
            $row['categories_name'],
            '<div class="text-center">'.$row['product_count'].'</div>',
            '<div class="text-center">'.$row['total_quantity'].'</div>',
            $status,
            $button
        );  
    }
}

$connect->close();
echo json_encode($output);
?>