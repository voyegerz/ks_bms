<?php 
require_once 'php_action/db_connect.php'; 
require_once 'includes/header.php'; 

$page = $_GET['q'] ?? 'manage';

if ($page == 'add') { echo "<div class='div-request div-hide'>add</div>"; } 
else if ($page == 'manage') { echo "<div class='div-request div-hide'>manage</div>"; }
else if ($page == 'edit') { echo "<div class='div-request div-hide'>edit</div>"; }
?>

<ol class="breadcrumb">
  <li><a href="dashboard.php">Home</a></li>
  <li>Quotations</li>
  <li class="active">
    <?php 
        if($page == 'add') { echo "Add Quotation"; } 
        else if($page == 'manage') { echo "Manage Quotations"; }
        else if($page == 'edit') { echo "Edit Quotation"; }
    ?>
  </li>
</ol>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4><i class="glyphicon glyphicon-<?php if($page == 'add' || $page == 'edit') echo 'plus-sign'; else echo 'list-alt'; ?>"></i> 
            <?php 
                if($page == 'add') { echo "Add Quotation"; } 
                else if($page == 'manage') { echo "Manage Quotations"; }
                else if($page == 'edit') { echo "Edit Quotation"; }
            ?>
        </h4>
    </div>
    <div class="panel-body">
        <?php if ($page == 'add'): ?>
            <div class="success-messages"></div>
            <form class="form-horizontal" method="POST" action="php_action/createQuotation.php" id="createQuotationForm">
                <div class="form-group">
                    <label for="quotationDate" class="col-sm-2 control-label">Quotation Date</label>
                    <div class="col-sm-10"><input type="text" class="form-control" id="quotationDate" name="quotationDate" autocomplete="off" /></div>
                </div>
                <div class="form-group">
                    <label for="partyId" class="col-sm-2 control-label">Party / Client</label>
                    <div class="col-sm-10">
                        <select class="form-control" id="partyId" name="partyId">
                            <option value="">-- Select Party --</option>
                            <?php 
                                $partySql = "SELECT * FROM partys ORDER BY name ASC";
                                $partyResult = $connect->query($partySql);
                                while($row = $partyResult->fetch_array()) {
                                    echo "<option value='".$row['id']."'>".$row[1]."</option>";
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
                                <input type="text" name="rate[]" id="rate1" onkeyup="getTotal(1)" class="form-control" autocomplete="off" />
                                <input type="hidden" name="rateValue[]" id="rateValue1" />
                            </td>
                            <td>
                                <input type="number" name="quantity[]" id="quantity1" onkeyup="getTotal(1)" class="form-control" min="1" autocomplete="off" />
                            </td>
                            <td>
                                <input type="text" name="total[]" id="total1" class="form-control"  />
                                <input class="totalValue" type="hidden" name="totalValue[]" id="totalValue1" />
                            </td>
                            <td><button class="btn btn-default removeProductRowBtn" type="button" onclick="removeProductRow(1)"><i class="glyphicon glyphicon-trash"></i></button></td>
                        </tr>
                    </tbody>
                </table>

                <div class="col-md-offset-8 col-md-4">
                    <div class="form-group"><label for="subTotal" class="col-sm-4 control-label">Sub Total</label><div class="col-sm-8"><input type="text" class="form-control" id="subTotal" name="subTotal"  /><input type="hidden" id="subTotalValue" name="subTotalValue" /></div></div>
                    <div class="form-group"><label for="vat" class="col-sm-4 control-label gst">VAT/GST (18%)</label><div class="col-sm-8"><input type="text" class="form-control" id="vat" name="vat"  /><input type="hidden" id="vatValue" name="vatValue" /></div></div>
                    <div class="form-group"><label for="grandTotal" class="col-sm-4 control-label">Grand Total</label><div class="col-sm-8"><input type="text" class="form-control" id="grandTotal" name="grandTotal"  /><input type="hidden" id="grandTotalValue" name="grandTotalValue" /></div></div>
                </div>

                <div class="form-group submitButtonFooter">
                    <div class="col-sm-offset-2 col-sm-10">
                        <button type="button" class="btn btn-default" onclick="addRow()" id="addRowBtn"> <i class="glyphicon glyphicon-plus-sign"></i> Add Row </button>
                        <button type="submit" id="createQuotationBtn" data-loading-text="Loading..." class="btn btn-success"><i class="glyphicon glyphicon-ok-sign"></i> Save Quotation</button>
                        <button type="reset" class="btn btn-default"><i class="glyphicon glyphicon-erase"></i> Reset</button>
                    </div>
                </div>
            </form>
            
        <?php elseif ($page == 'edit'): 
        $quotationId = $_GET['id'];
        $sql = "SELECT quotation_id, quotation_date, party_id, sub_total, vat, grand_total, quotation_status FROM quotations WHERE quotation_id = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("i", $quotationId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        ?>
            <div class="success-messages"></div>
            <form class="form-horizontal" method="POST" action="php_action/editQuotation.php" id="editQuotationForm">
                <div class="form-group">
                    <label for="quotationDate" class="col-sm-2 control-label">Quotation Date</label>
                    <div class="col-sm-10"><input type="text" class="form-control" id="quotationDate" name="quotationDate" value="<?php echo $data['quotation_date']; ?>" autocomplete="off" /></div>
                </div>
                <div class="form-group">
                    <label for="partyId" class="col-sm-2 control-label">Party / Client</label>
                    <div class="col-sm-10">
                        <select class="form-control" id="partyId" name="partyId">
                            <option value="">-- Select Party --</option>
                            <?php 
                                $partySql = "SELECT id, name FROM partys ORDER BY name ASC";
                                $partyResult = $connect->query($partySql);
                                while($row = $partyResult->fetch_array()) {
                                    $selected = ($data['party_id'] == $row[0]) ? "selected" : "";
                                    echo "<option value='".$row[0]."' ".$selected.">".$row[1]."</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div> 

                <table class="table" id="productTable">
                    <thead>
                        <tr>
                            <th style="width:40%;">Product</th><th style="width:20%;">Rate</th><th style="width:15%;">Quantity</th><th style="width:20%;">Total</th><th style="width:5%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $quoteItemSql = "SELECT qi.product_id, qi.quantity, qi.rate, qi.total FROM quotation_items qi WHERE qi.quotation_id = ?";
                        $itemStmt = $connect->prepare($quoteItemSql);
                        $itemStmt->bind_param("i", $quotationId);
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
                
                <div class="form-group">
                    <label for="quotationStatus" class="col-sm-2 control-label">Status</label>
                    <div class="col-sm-10">
                        <select class="form-control" name="quotationStatus" id="quotationStatus">
                            <option value="1" <?php if($data['quotation_status'] == 1) echo "selected"; ?>>Pending</option>
                            <option value="2" <?php if($data['quotation_status'] == 2) echo "selected"; ?>>Approved</option>
                            <option value="3" <?php if($data['quotation_status'] == 3) echo "selected"; ?>>Rejected</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-offset-8 col-md-4">
                    <div class="form-group"><label for="subTotal" class="col-sm-4 control-label">Sub Total</label><div class="col-sm-8"><input type="text" class="form-control" id="subTotal" name="subTotal"  value="<?php echo $data['sub_total']; ?>" /><input type="hidden" id="subTotalValue" name="subTotalValue" value="<?php echo $data['sub_total']; ?>" /></div></div>
                    <div class="form-group"><label for="vat" class="col-sm-4 control-label gst">VAT/GST (18%)</label><div class="col-sm-8"><input type="text" class="form-control" id="vat" name="vat"  value="<?php echo $data['vat']; ?>" /><input type="hidden" id="vatValue" name="vatValue" value="<?php echo $data['vat']; ?>" /></div></div>
                    <div class="form-group"><label for="grandTotal" class="col-sm-4 control-label">Grand Total</label><div class="col-sm-8"><input type="text" class="form-control" id="grandTotal" name="grandTotal"  value="<?php echo $data['grand_total']; ?>" /><input type="hidden" id="grandTotalValue" name="grandTotalValue" value="<?php echo $data['grand_total']; ?>" /></div></div>
                </div>

                <div class="form-group submitButtonFooter">
                    <div class="col-sm-offset-2 col-sm-10">
                        <input type="hidden" name="quotationId" value="<?php echo $quotationId; ?>" />
                        <button type="button" class="btn btn-default" onclick="addRow()" id="addRowBtn"> <i class="glyphicon glyphicon-plus-sign"></i> Add Row </button>
                        <button type="submit" id="editQuotationBtn" data-loading-text="Loading..." class="btn btn-success"><i class="glyphicon glyphicon-ok-sign"></i> Save Changes</button>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <div class="div-action pull pull-right" style="padding-bottom:20px;">
                <a href="quotations.php?q=add" class="btn btn-default button1"> <i class="glyphicon glyphicon-plus-sign"></i> Add Quotation </a>
            </div>
            <table class="table" id="manageQuotationTable">
                <thead>
                    <tr>
                        <th style="width:10%;">Quote No.</th>
                        <th>Quotation Date</th>
                        <th>Party Name</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th style="width:10%;">Options</th>
                    </tr>
                </thead>
            </table>
            <?php endif; ?>
    </div>
</div>

<script src="custom/js/quotation.js"></script>
<?php require_once 'includes/footer.php'; ?>