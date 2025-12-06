<?php 
require_once 'php_action/db_connect.php'; 
require_once 'includes/header.php'; 

$page = $_GET['p'] ?? 'manage';

if ($page == 'add') { echo "<div class='div-request div-hide'>add</div>"; } 
else if ($page == 'manage') { echo "<div class='div-request div-hide'>manage</div>"; }
else if ($page == 'edit') { echo "<div class='div-request div-hide'>edit</div>"; } 
?>

<ol class="breadcrumb">
  <li><a href="dashboard.php">Home</a></li>
  <li>Purchases</li>
  <li class="active">
    <?php if($page == 'add') { echo "Add Purchase"; } else { echo "Manage Purchases"; } ?>
  </li>
</ol>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4><i class="glyphicon glyphicon-<?php echo ($page == 'add') ? 'plus-sign' : 'usd'; ?>"></i> 
            <?php if($page == 'add') { echo "Add Purchase Bill"; } 
                  else if($page == 'edit') { echo "Edit Purchase Bill"; }
                  else { echo "Manage Purchases"; } ?> 
        </h4>
    </div>
    <div class="panel-body">
        <?php if ($page == 'add'): ?>
            <div class="success-messages"></div>
            <form class="form-horizontal" method="POST" action="php_action/createPurchase.php" id="createPurchaseForm">
                <div class="form-group">
                    <label for="poNumber" class="col-sm-2 control-label">PO Number</label>
                    <div class="col-sm-10"><input type="text" class="form-control" id="poNumber" name="poNumber" placeholder="Optional Purchase Order Number" autocomplete="off" /></div>
                </div>

                <div class="form-group">
                    <label for="purchaseDate" class="col-sm-2 control-label">Purchase Date</label>
                    <div class="col-sm-10"><input type="text" class="form-control" id="purchaseDate" name="purchaseDate" autocomplete="off" /></div>
                </div>
                <div class="form-group">
                    <label for="supplierId" class="col-sm-2 control-label">Supplier</label>
                    <div class="col-sm-8">
                        <select class="form-control" id="supplierId" name="supplierId">
                            <option value="">-- Select Supplier --</option>
                            <?php 
                                $supplierSql = "SELECT id, name FROM suppliers ORDER BY name ASC";
                                $supplierResult = $connect->query($supplierSql);
                                while($row = $supplierResult->fetch_array()) {
                                    echo "<option value='".$row[0]."'>".$row[1]."</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#addSupplierModal">
                            <i class="glyphicon glyphicon-plus-sign"></i> Add Supplier
                    </button>
                </div> 

                <table class="table" id="productTable">
                    <thead>
                        <tr>
                            <th style="width:40%;">Product</th>
                            <th style="width:20%;">Rate</th>
                            <th style="width:15%;">Quantity</th>
                            <th style="width:20%;">Total</th>
                            <th style="width:5%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="row1">
                            <td>
                                <select class="form-control" name="productName[]" id="productName1" onchange="getProductData(1)">
                                    <option value="">-- SELECT --</option>
                                    <?php
                                        $productSql = "SELECT product_id, product_name FROM product WHERE active = 1 AND status = 1";
                                        $productData = $connect->query($productSql);
                                        while($row = $productData->fetch_array()) {
                                            echo "<option value='".$row[0]."'>".$row[1]."</option>";
                                        }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="rate[]" id="rate1" onkeyup="getTotal(1)" class="form-control" />
                                <input type="hidden" name="rateValue[]" id="rateValue1" />
                            </td>
                            <td>
                                <input type="number" name="quantity[]" id="quantity1" onkeyup="getTotal(1)" class="form-control" min="1" />
                            </td>
                            <td>
                                <input type="text" name="total[]" id="total1" class="form-control"  />
                                <input class="totalValue" type="hidden" name="totalValue[]" id="totalValue1" />
                            </td>
                            <td><button class="btn btn-default removeProductRowBtn" type="button" onclick="removeProductRow(1)"><i class="glyphicon glyphicon-trash"></i></button></td>
                        </tr>
                    </tbody>
                </table>

                <div class="col-md-6">
                    <div class="form-group"><label for="subTotal" class="col-sm-3 control-label">Sub Amount</label><div class="col-sm-9"><input type="text" class="form-control" id="subTotal" name="subTotal"  /><input type="hidden" id="subTotalValue" name="subTotalValue" /></div></div>
                    <div class="form-group"><label for="vat" class="col-sm-3 control-label gst">VAT/GST</label><div class="col-sm-9"><input type="text" class="form-control" id="vat" name="vat" /><input type="hidden" id="vatValue" name="vatValue" /></div></div>
                    <div class="form-group"><label for="grandTotal" class="col-sm-3 control-label">Grand Total</label><div class="col-sm-9"><input type="text" class="form-control" id="grandTotal" name="grandTotal"  /><input type="hidden" id="grandTotalValue" name="grandTotalValue" /></div></div>
                </div>

                <div class="col-md-6">
                    <div class="form-group"><label for="paid" class="col-sm-3 control-label">Paid Amount</label><div class="col-sm-9"><input type="text" class="form-control" id="paid" name="paid" autocomplete="off" onkeyup="paidAmount()" /></div></div>
                    <div class="form-group"><label for="due" class="col-sm-3 control-label">Due Amount</label><div class="col-sm-9"><input type="text" class="form-control" id="due" name="due"  /><input type="hidden" id="dueValue" name="dueValue" /></div></div>
                    <div class="form-group"><label class="col-sm-3 control-label">Payment Status</label><div class="col-sm-9"><select class="form-control" name="paymentStatus" id="paymentStatus"><option value="">-- SELECT --</option><option value="1">Full Payment</option><option value="2">Advance Payment</option><option value="3">No Payment</option></select></div></div>
                    <div class="form-group"><label for="dueDate" class="col-sm-3 control-label">Due Date</label><div class="col-sm-9"><input type="text" class="form-control" id="dueDate" name="dueDate" autocomplete="off" /></div></div>
                </div>

                <div class="form-group submitButtonFooter">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="button" class="btn btn-default" onclick="addRow()" id="addRowBtn"> <i class="glyphicon glyphicon-plus-sign"></i> Add Row </button>
                        <button type="submit" id="createPurchaseBtn" data-loading-text="Loading..." class="btn btn-success"><i class="glyphicon glyphicon-ok-sign"></i> Save Changes</button>
                        <button type="reset" class="btn btn-default"><i class="glyphicon glyphicon-erase"></i> Reset</button>
                    </div>
                </div>
            </form>
        <?php elseif ($page == 'edit'): 
            $purchaseId = $_GET['id'];
            // Fetch purchase data
            $sql = "SELECT p.purchase_id, p.po_number, p.purchase_date, p.due_date, p.supplier_id, p.sub_total, p.vat, p.grand_total, p.paid, p.due, p.payment_status 
                    FROM purchases p WHERE p.purchase_id = ?";
            $stmt = $connect->prepare($sql);
            $stmt->bind_param("i", $purchaseId);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
        ?>
            <div class="success-messages"></div>
            <form class="form-horizontal" method="POST" action="php_action/editPurchase.php" id="editPurchaseForm">
                <div class="form-group">
                    <label for="poNumber" class="col-sm-2 control-label">PO Number</label>
                    <div class="col-sm-10"><input type="text" class="form-control" id="poNumber" name="poNumber" placeholder="Purchase Order Number" value="<?php echo $data['po_number']; ?>" autocomplete="off" /></div>
                </div>

                <div class="form-group">
                    <label for="purchaseDate" class="col-sm-2 control-label">Purchase Date</label>
                    <div class="col-sm-10"><input type="text" class="form-control" id="purchaseDate" name="purchaseDate" value="<?php echo $data['purchase_date']; ?>" autocomplete="off" /></div>
                </div>
                <div class="form-group">
                    <label for="supplierId" class="col-sm-2 control-label">Supplier</label>
                    <div class="col-sm-10">
                        <select class="form-control" id="supplierId" name="supplierId">
                            <option value="">-- Select Supplier --</option>
                            <?php 
                                $supplierSql = "SELECT id, name FROM suppliers ORDER BY name ASC";
                                $supplierResult = $connect->query($supplierSql);
                                while($row = $supplierResult->fetch_array()) {
                                    $selected = ($data['supplier_id'] == $row[0]) ? "selected" : "";
                                    echo "<option value='".$row[0]."' ".$selected.">".$row[1]."</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div> 

                <table class="table" id="productTable">
                    <thead>
                        <tr>
                            <th style="width:40%;">Product</th>
                            <th style="width:20%;">Rate</th>
                            <th style="width:15%;">Quantity</th>
                            <th style="width:20%;">Total</th>
                            <th style="width:5%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $purchaseItemSql = "SELECT pi.product_id, pi.quantity, pi.rate, pi.total FROM purchase_items pi WHERE pi.purchase_id = ?";
                        $itemStmt = $connect->prepare($purchaseItemSql);
                        $itemStmt->bind_param("i", $purchaseId);
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
                                        $productSql = "SELECT product_id, product_name FROM product WHERE active = 1 AND status = 1";
                                        $productData = $connect->query($productSql);
                                        while($prow = $productData->fetch_array()) {
                                            $pselected = ($itemRow['product_id'] == $prow[0]) ? "selected" : "";
                                            echo "<option value='".$prow[0]."' ".$pselected.">".$prow[1]."</option>";
                                        }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="rate[]" id="rate<?php echo $x; ?>" onkeyup="getTotal(<?php echo $x; ?>)" class="form-control" value="<?php echo $itemRow['rate']; ?>" />
                                <input type="hidden" name="rateValue[]" id="rateValue<?php echo $x; ?>" value="<?php echo $itemRow['rate']; ?>" />
                            </td>
                            <td>
                                <input type="number" name="quantity[]" id="quantity<?php echo $x; ?>" onkeyup="getTotal(<?php echo $x; ?>)" class="form-control" min="1" value="<?php echo $itemRow['quantity']; ?>" />
                            </td>
                            <td>
                                <input type="text" name="total[]" id="total<?php echo $x; ?>" class="form-control"  value="<?php echo $itemRow['total']; ?>" />
                                <input class="totalValue" type="hidden" name="totalValue[]" id="totalValue<?php echo $x; ?>" value="<?php echo $itemRow['total']; ?>" />
                            </td>
                            <td><button class="btn btn-default removeProductRowBtn" type="button" onclick="removeProductRow(<?php echo $x; ?>)"><i class="glyphicon glyphicon-trash"></i></button></td>
                        </tr>
                        <?php $x++; } ?>
                    </tbody>
                </table>
                <div class="col-md-6">
                    <div class="form-group"><label for="subTotal" class="col-sm-3 control-label">Sub Amount</label><div class="col-sm-9"><input type="text" class="form-control" id="subTotal" name="subTotal"  value="<?php echo $data['sub_total']; ?>" /><input type="hidden" id="subTotalValue" name="subTotalValue" value="<?php echo $data['sub_total']; ?>" /></div></div>
                    <div class="form-group"><label for="vat" class="col-sm-3 control-label gst">VAT/GST</label><div class="col-sm-9"><input type="text" class="form-control" id="vat" name="vat" onkeyup="subAmount()" value="<?php echo $data['vat']; ?>" /><input type="hidden" id="vatValue" name="vatValue" value="<?php echo $data['vat']; ?>" /></div></div>
                    <div class="form-group"><label for="grandTotal" class="col-sm-3 control-label">Grand Total</label><div class="col-sm-9"><input type="text" class="form-control" id="grandTotal" name="grandTotal"  value="<?php echo $data['grand_total']; ?>" /><input type="hidden" id="grandTotalValue" name="grandTotalValue" value="<?php echo $data['grand_total']; ?>" /></div></div>
                </div>

                <div class="col-md-6">
                    <div class="form-group"><label for="paid" class="col-sm-3 control-label">Paid Amount</label><div class="col-sm-9"><input type="text" class="form-control" id="paid" name="paid" autocomplete="off" onkeyup="paidAmount()" value="<?php echo $data['paid']; ?>" /></div></div>
                    <div class="form-group"><label for="due" class="col-sm-3 control-label">Due Amount</label><div class="col-sm-9"><input type="text" class="form-control" id="due" name="due"  value="<?php echo $data['due']; ?>" /><input type="hidden" id="dueValue" name="dueValue" value="<?php echo $data['due']; ?>" /></div></div>
                    <div class="form-group"><label class="col-sm-3 control-label">Payment Status</label><div class="col-sm-9"><select class="form-control" name="paymentStatus" id="paymentStatus"><option value="">-- SELECT --</option><option value="1" <?php if($data['payment_status'] == 1) echo "selected"; ?>>Full Payment</option><option value="2" <?php if($data['payment_status'] == 2) echo "selected"; ?>>Advance Payment</option><option value="3" <?php if($data['payment_status'] == 3) echo "selected"; ?>>No Payment</option></select></div></div>
                    <div class="form-group"><label for="dueDate" class="col-sm-3 control-label">Due Date</label><div class="col-sm-9"><input type="text" class="form-control" id="dueDate" name="dueDate" autocomplete="off" value="<?php echo $data['due_date']; ?>" /></div></div>
                </div>

                <div class="form-group submitButtonFooter">
                    <div class="col-sm-offset-2 col-sm-10">
                        <input type="hidden" name="purchaseId" value="<?php echo $purchaseId; ?>" />
                        <button type="button" class="btn btn-default" onclick="addRow()" id="addRowBtn"> <i class="glyphicon glyphicon-plus-sign"></i> Add Row </button>
                        <button type="submit" id="editPurchaseBtn" data-loading-text="Loading..." class="btn btn-success"><i class="glyphicon glyphicon-ok-sign"></i> Save Changes</button>
                    </div>
                </div>
                
            </form>
        <?php else: ?>
            <div class="div-action pull pull-right" style="padding-bottom:20px;">
                <a href="purchases.php?p=add" class="btn btn-default button1"> <i class="glyphicon glyphicon-plus-sign"></i> Add Purchase Bill </a>
            </div>
            <table class="table" id="managePurchaseTable">
                <thead>
                    <tr>
                        <th>ID#</th>
                        <th>PO Number</th>
                        <th>Purchase Date</th>
                        <th>Supplier Name</th>
                        <th>Total Amount</th>
                        <th>Amount Due</th>
                        <th>Due Date</th>
                        <th>Payment Status</th>
                        <th>Options</th>
                    </tr>
                </thead>
            </table>
        <?php endif; ?>
    </div>
</div>
<div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
        <form class="form-horizontal" id="submitSupplierForm" action="php_action/createSupplier.php" method="POST">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fa fa-plus"></i> Add Supplier</h4>
          </div>
          <div class="modal-body">
            <div id="add-supplier-messages"></div>
            <div class="form-group">
                <label for="supplierName" class="col-sm-4 control-label">Supplier Name</label>
                <div class="col-sm-8"><input type="text" class="form-control" name="supplierName" required></div>
            </div>
            <div class="form-group">
                <label for="supplierContact" class="col-sm-4 control-label">Contact No</label>
                <div class="col-sm-8"><input type="text" class="form-control" name="supplierContact"></div>
            </div>
             <div class="form-group">
                <label for="supplierGstin" class="col-sm-4 control-label">GSTIN</label>
                <div class="col-sm-8"><input type="text" class="form-control" name="supplierGstin"></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
    </div>
  </div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" id="removePurchaseModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="glyphicon glyphicon-trash"></i> Remove Purchase Bill</h4>
            </div>
            <div class="modal-body">
                <div class="removePurchaseMessages"></div>
                <p>Do you really want to remove this purchase bill?</p>
                <p>This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"> <i class="glyphicon glyphicon-remove-sign"></i> Close</button>
                <button type="button" class="btn btn-primary" id="removePurchaseBtn" data-loading-text="Loading..."> <i class="glyphicon glyphicon-ok-sign"></i> Save changes</button>
            </div>
        </div>
    </div>
</div>

<script src="custom/js/purchase.js"></script>
<?php require_once 'includes/footer.php'; ?>