<?php
require_once 'php_action/db_connect.php';
require_once 'includes/header.php';

$page = $_GET['o'] ?? 'manchallan';

if ($page == 'add') {
    echo "<div class='div-request div-hide'>add</div>";
} else if ($page == 'manchallan') {
    echo "<div class='div-request div-hide'>manchallan</div>";
} else if ($page == 'editChallan') {
    echo "<div class='div-request div-hide'>editChallan</div>";
}
?>

<ol class="breadcrumb">
    <li><a href="dashboard.php">Home</a></li>
    <li>Challan</li>
    <li class="active">
        <?php 
            if ($page == 'add') { echo "Add Challan"; } 
            else if ($page == 'manchallan') { echo "Manage Challans"; } 
            else if ($page == 'editChallan') { echo "Edit Challan"; }
        ?>
    </li>
</ol>

<h4>
    <i class='glyphicon glyphicon-circle-arrow-right'></i>
    <?php 
        if ($page == 'add') { echo "Add Challan"; } 
        else if ($page == 'manchallan') { echo "Manage Challans"; } 
        else if ($page == 'editChallan') { echo "Edit Challan"; } 
    ?>
</h4>

<div class="panel panel-default">
    <div class="panel-heading">
        <?php 
            if ($page == 'add') { echo "<i class='glyphicon glyphicon-plus-sign'></i> Add Challan"; } 
            else if ($page == 'manchallan') { echo "<i class='glyphicon glyphicon-edit'></i> Manage Challans"; } 
            else if ($page == 'editChallan') { echo "<i class='glyphicon glyphicon-edit'></i> Edit Challan"; }
        ?>
    </div>
    <div class="panel-body">

        <?php if ($page == 'add') { ?>
            <div class="success-messages"></div>
            <form class="form-horizontal" method="POST" action="php_action/createChallan.php" id="createChallanForm">
            
                <div class="form-group">
                    <label for="challanDate" class="col-sm-2 control-label">Challan Date</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="challanDate" name="challanDate" autocomplete="off" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="partyId" class="col-sm-2 control-label">Party / Client</label>
                    <div class="col-sm-10">
                        <select class="form-control" id="partyId" name="partyId">
                            <option value="">-- Select a Party --</option>
                            <?php
                                $partySql = "SELECT * FROM partys ORDER BY name ASC";
                                $partyResult = $connect->query($partySql);
                                while($row = $partyResult->fetch_array()) {
                                    echo "<option value='".$row['id']."' data-contact='".$row['contact_no']."'>".$row['name']."</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="challanNo" class="col-sm-2 control-label">Challan No.</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="challanNo" name="challanNo" placeholder="Enter a unique Challan Number" required autocomplete="off" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="clientContact" class="col-sm-2 control-label">Client Contact</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control clientContact" id="clientContact" name="clientContact" placeholder="Contact will appear here" />
                    </div>
                </div>
                <!-- <div class="form-group">
                    <label for="orderId" class="col-sm-2 control-label">Invoice No.</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control orderId" id="orderId" name="orderId" placeholder="Enter invoice Number"/>
                    </div>
                </div> -->
                <div class="form-group">
                    <label for="remarks" class="col-sm-2 control-label">Remarks</label>
                    <div class="col-sm-10">
                        <textarea style="height:34px;" class="form-control" id="remarks" name="remarks" placeholder="Any special instructions or notes"></textarea>
                    </div>
                </div>

                <table class="table" id="productTable">
                    <thead>
                        <tr>
                            <th style="width:35%;">Product</th>
                            <th style="width:30%;">Product Description/B.No</th>
                            <th style="width:15%;">Available Quantity</th>
                            <th style="width:15%;">Quantity/No. of Size</th>
                            <th style="width:5%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for($x = 1; $x < 2; $x++) { ?>
                        <tr id="row<?php echo $x; ?>">
                            <td>
                                
                                    <select class="form-control" name="productName[]" id="productName<?php echo $x; ?>" onchange="getProductData(<?php echo $x; ?>)">
                                        <option value="">-- SELECT --</option>
                                        <?php
                                            $productSql = "SELECT * FROM product WHERE active = 1 AND status = 1 AND quantity > 0";
                                            $productData = $connect->query($productSql);
                                            while($row = $productData->fetch_array()) {
                                                echo "<option value='".$row['product_id']."'>".$row['product_name']."</option>";
                                            }
                                        ?>
                                    </select>
                                
                            </td>

                            <td>
                                <textarea style="height:34px;" name="itemRemarks[]" id="itemRemarks<?php echo $x; ?>" class="form-control" placeholder="Optional remarks for this item" ></textarea>
                            </td>
                            <td><p style="text-align: center; vertical-align: middle;" id="available_quantity<?php echo $x; ?>"></p></td>
                            <td>
                                
                                    <input type="text" name="quantity[]" id="quantity<?php echo $x; ?>" class="form-control" min="0" />
                                
                            </td>
                            <td>
                                <button class="btn btn-default removeProductRowBtn" type="button" onclick="removeProductRow(<?php echo $x; ?>)"><i class="glyphicon glyphicon-trash"></i></button>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <div class="form-group submitButtonFooter">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="button" class="btn btn-default" onclick="addRow()" id="addRowBtn" data-loading-text="Loading..."> <i class="glyphicon glyphicon-plus-sign"></i> Add Row </button>
                        <button type="submit" id="createChallanBtn" data-loading-text="Loading..." class="btn btn-success"><i class="glyphicon glyphicon-ok-sign"></i> Create Challan</button>
                        <button type="reset" class="btn btn-default"><i class="glyphicon glyphicon-erase"></i> Reset</button>
                    </div>
                </div>
            </form>
            
        <?php } else if ($page == 'manchallan') { ?>
            <div id="success-messages"></div>
            <table class="table" id="manageChallanTable">
                <thead>
                    <tr>
                        <th style="width:1%;">ID#</th>
                        <!-- <th>Billed Invoice.</th> -->
                        <th>Challan No.</th>
                        <th>Challan Date</th>
                        <th>Party Name</th>
                        <th>Status</th>
                        <th>Options</th>
                    </tr>
                </thead>
            </table>

        <?php } else if ($page == 'editChallan') { ?>
            <div class="success-messages"></div>
            <form class="form-horizontal" method="POST" action="php_action/editChallan.php" id="editChallanForm">
                <?php 
                $challanId = $_GET['i'];
                $sql = "SELECT c.challan_id, c.challan_no, c.challan_date, c.party_id, c.remarks, c.challan_status, p.contact_no
                        FROM challans c
                        LEFT JOIN partys p ON c.party_id = p.id
                        WHERE c.challan_id = ?";
                $stmt = $connect->prepare($sql);
                $stmt->bind_param("i", $challanId);
                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result->fetch_assoc();
                ?>

                <div class="form-group">
                    <label for="challanDate" class="col-sm-2 control-label">Challan Date</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="challanDate" name="challanDate" value="<?php echo $data['challan_date']; ?>" autocomplete="off" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="partyId" class="col-sm-2 control-label">Party / Client</label>
                    <div class="col-sm-10">
                        <select class="form-control" id="partyId" name="partyId">
                            <option value="">-- Select a Party --</option>
                            <?php
                                $partySql = "SELECT * FROM partys ORDER BY name ASC";
                                $partyResult = $connect->query($partySql);
                                while($row = $partyResult->fetch_array()) {
                                    $selected = ($data['party_id'] == $row['id']) ? "selected" : "";
                                    echo "<option value='".$row['id']."' data-contact='".$row['contact_no']."' ".$selected.">".$row['name']."</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="challanNo" class="col-sm-2 control-label">Challan No.</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="challanNo" name="challanNo" placeholder="Enter a unique Challan Number" value="<?php echo $data['challan_no']; ?>" required autocomplete="off" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="clientContact" class="col-sm-2 control-label">Client Contact</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control clientContact" id="clientContact" name="clientContact" value="<?php echo $data['contact_no']; ?>" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="remarks" class="col-sm-2 control-label">Remarks</label>
                    <div class="col-sm-10">
                        <textarea style="height:34px;" class="form-control" id="remarks" name="remarks"><?php echo $data['remarks']; ?></textarea>
                    </div>
                </div>

                <table class="table" id="productTable" >
                    <thead>
                        <tr>
                            <th style="width:35%;">Product</th>
                            <th style="width:30%;">Product Description/B.No</th>
                            <th style="width:15%;">Available Quantity</th>
                            <th style="width:15%;">Quantity/No. of Size</th>
                            <th style="width:5%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $challanItemSql = "SELECT ci.product_id, ci.quantity, ci.size, ci.remarks AS item_remarks, p.quantity AS available_qty FROM challan_items ci JOIN product p ON ci.product_id = p.product_id WHERE ci.challan_id = ?";
                        $itemStmt = $connect->prepare($challanItemSql);
                        $itemStmt->bind_param("i", $challanId);
                        $itemStmt->execute();
                        $itemResult = $itemStmt->get_result();
                        $x = 1;
                        while($itemRow = $itemResult->fetch_assoc()) {
                        ?>
                        <tr id="row<?php echo $x; ?>">
                            <td>
                                <select class="form-control" name="productName[]" id="productName<?php echo $x; ?>" onchange="getProductData(<?php echo $x; ?>)">
                                    <option value="">-- SELECT --</option>
                                    <?php
                                        $productSql = "SELECT * FROM product WHERE active = 1 AND status = 1";
                                        $productData = $connect->query($productSql);
                                        while($prow = $productData->fetch_array()) {
                                            $pselected = ($itemRow['product_id'] == $prow['product_id']) ? "selected" : "";
                                            echo "<option value='".$prow['product_id']."' ".$pselected.">".$prow['product_name']."</option>";
                                        }
                                    ?>
                                </select>
                             
                            </td>
                            <td>
                                <textarea style="height:34px;" name="itemRemarks[]" id="itemRemarks<?php echo $x; ?>" class="form-control" placeholder="Optional remarks for this item" ><?php echo $itemRow['item_remarks']; ?></textarea>
                            </td>
                            <td><p style="text-align: center; vertical-align: middle;"  id="available_quantity<?php echo $x; ?>"><?php echo $itemRow['available_qty']; ?></p></td>
                            <td>
                                
                                    <input type="text" name="quantity[]" id="quantity<?php echo $x; ?>" class="form-control" value="<?php echo $itemRow['quantity']."/".$itemRow['size']; ?>" min="0" />
                                
                            </td>
                            <td><button class="btn btn-default removeProductRowBtn" type="button" onclick="removeProductRow(<?php echo $x; ?>)"><i class="glyphicon glyphicon-trash"></i></button></td>
                        </tr>
                        <?php $x++; } ?>
                    </tbody>
                </table>
                
                <div class="form-group">
                    <label for="challanStatus" class="col-sm-2 control-label">Challan Status</label>
                    <div class="col-sm-10">
                        <select class="form-control" name="challanStatus" id="challanStatus">
                            <option value="0" <?php if($data['challan_status'] == 0) echo "selected"; ?>>Pending</option>
                            <option value="1" <?php if($data['challan_status'] == 1) echo "selected"; ?>>Dispatched</option>
                            <option value="2" <?php if($data['challan_status'] == 2) echo "selected"; ?>>Delivered</option>
                            <option value="3" <?php if($data['challan_status'] == 3) echo "selected"; ?>>Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="form-group submitButtonFooter">
                    <div class="col-sm-offset-2 col-sm-10">
                        <input type="hidden" name="challanId" value="<?php echo $challanId; ?>" />
                        <button type="button" class="btn btn-default" onclick="addRow()" id="addRowBtn"> <i class="glyphicon glyphicon-plus-sign"></i> Add Row </button>
                        <button type="submit" id="editChallanBtn" data-loading-text="Loading..." class="btn btn-success"><i class="glyphicon glyphicon-ok-sign"></i> Save Changes</button>
                    </div>
                </div>
            </form>
        <?php } ?>
    </div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" id="removeChallanModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="glyphicon glyphicon-trash"></i> Remove Challan</h4>
            </div>
            <div class="modal-body">
                <div class="removeChallanMessages"></div>
                <p>Do you really want to remove this challan?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"> <i class="glyphicon glyphicon-remove-sign"></i> Close</button>
                <button type="button" class="btn btn-primary" id="removeChallanBtn" data-loading-text="Loading..."> <i class="glyphicon glyphicon-ok-sign"></i> Save changes</button>
            </div>
        </div>
    </div>
</div>
<script src="custom/js/challan.js"></script>
<?php require_once 'includes/footer.php'; ?>