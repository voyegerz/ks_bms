<?php 
require_once 'php_action/db_connect.php'; 
require_once 'includes/header.php'; 

$page = $_GET['o'] ?? 'manord';

// This sets up the page type for JavaScript to read
if ($page == 'add') { echo "<div class='div-request div-hide'>add</div>"; } 
else if ($page == 'manord') { echo "<div class='div-request div-hide'>manord</div>"; } 
else if ($page == 'editOrd') { echo "<div class='div-request div-hide'>editOrd</div>"; }
else if ($page == 'addAgainstChallan') { echo "<div class='div-request div-hide'>addAgainstChallan</div>"; }
?>

<ol class="breadcrumb">
    <li><a href="dashboard.php">Home</a></li>
    <li>Order</li>
    <li class="active">
        <?php 
        if($page == 'add') { echo "Add Order"; } 
        else if($page == 'manord') { echo "Manage Orders"; }
        else if($page == 'editOrd') { echo "Edit Order"; }
        else if($page == 'addAgainstChallan') { echo "Add Order Against Challan"; }
    ?>
    </li>
</ol>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4>
            <i class='glyphicon glyphicon-circle-arrow-right'></i>
            <?php 
                if($page == 'add') { echo "Add Order"; } 
                else if($page == 'manord') { echo "Manage Orders"; }
                else if($page == 'editOrd') { echo "Edit Order"; }
                else if($page == 'addAgainstChallan') { echo "Add Order Against Challan"; }
            ?>
        </h4>
    </div>
    <div class="panel-body">

        <?php if ($page == 'addAgainstChallan') { ?>
        <div class="well">
            <p>Use this section to find and load items  existing challans.</p>
            <div class="form-horizontal">
                
                <div class="form-group">
                    <label for="partyIdForChallan" class="col-sm-2 control-label">Select Party</label>
                    <div class="col-sm-8">
                        <select class="form-control" id="partyIdForChallan">
                            <option value="">-- Select a Party --</option>
                            <?php 
                                    $partySql = "SELECT * FROM partys ORDER BY name ASC";
                                    $partyResult = $connect->query($partySql);
                                    while($row = $partyResult->fetch_array()) {
                                        echo "<option value='".$row['id']."'>".$row['name']."</option>";
                                    }
                                ?>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <button type="button" class="btn btn-primary" id="getChallansBtn">
                            <i class="glyphicon glyphicon-search"></i> Get Challans
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <hr />
        <?php } ?>

        <?php if($page == 'add' || $page == 'addAgainstChallan') { 
			
			// ## THIS IS THE NEW BLOCK FOR "CONVERT TO ORDER" ##
        $fromQuoteData = null;
        $fromQuoteItems = [];
        if (isset($_GET['from_quote'])) {
            $quotationId = (int)$_GET['from_quote'];
            
            // Securely fetch main quote data
            $quoteSql = "SELECT * FROM quotations WHERE quotation_id = ?";
            $stmtQuote = $connect->prepare($quoteSql);
            $stmtQuote->bind_param("i", $quotationId);
            $stmtQuote->execute();
            $fromQuoteData = $stmtQuote->get_result()->fetch_assoc();
            $stmtQuote->close();
            
            // Securely fetch quote items
            $quoteItemSql = "SELECT * FROM quotation_items WHERE quotation_id = ?";
            $stmtItems = $connect->prepare($quoteItemSql);
            $stmtItems->bind_param("i", $quotationId);
            $stmtItems->execute();
            $itemResult = $stmtItems->get_result();
            while($row = $itemResult->fetch_assoc()){
                $fromQuoteItems[] = $row;
            }
            $stmtItems->close();
        }
        // END OF NEW BLOCK
			
			?>

        <div class="success-messages"></div>
        <form class="form-horizontal" method="POST" action="php_action/createOrder.php" id="createOrderForm">
            
			<div class="form-group">
                <label for="invoiceNo" class="col-sm-2 control-label">Invoice No.</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="invoiceNo" name="invoiceNo" placeholder="Enter a unique invoice number" autocomplete="off" required />
                </div>
            </div>

            <div class="form-group">
                <label for="orderDate" class="col-sm-2 control-label">Order Date</label>
                <div class="col-sm-10"><input type="text" class="form-control" id="orderDate" name="orderDate"
                        autocomplete="off" /></div>
            </div>

            <div class="form-group">
                <label for="otherPoNo" class="col-sm-2 control-label">PO No.</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="otherPoNo" name="otherPoNo" placeholder="Optional: PO number" autocomplete="off" />
                </div>
            </div>

            <div class="form-group">
                <label for="otherPoDate" class="col-sm-2 control-label">PO Date</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="otherPoDate" name="otherPoDate" autocomplete="off" />
                </div>
            </div>
			
			
            <div class="form-group">
                <label for="partyId" class="col-sm-2 control-label">Party</label>
                <div class="col-sm-10">
                    <select class="form-control" id="partyId" name="partyId">
                        <option value="">-- Select Party --</option>
                        <?php 
                                $partySql = "SELECT * FROM partys ORDER BY name ASC";
                                $partyResult = $connect->query($partySql);
                                while($row = $partyResult->fetch_array()) {
                                    // Pre-select the party from the quotation
                                $selected = ($fromQuoteData && $fromQuoteData['party_id'] == $row['id']) ? "selected" : "";
                                echo "<option value='".$row['id']."' data-contact='".$row['contact_no']."' data-gstin='".$row['gstin']."' ".$selected.">".$row['name']."</option>";
                                }
                            ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="clientContact" class="col-sm-2 control-label">Party Contact</label>
                <div class="col-sm-10"><input type="text" class="form-control clientContact" id="clientContact"
                        name="clientContact" placeholder="Contact will appear here" /></div>
            </div>
            <div class="form-group">
                <label for="gstn" class="col-sm-2 control-label">Party GSTIN</label>
                <div class="col-sm-10"><input type="text" class="form-control gstn" id="gstn" name="gstn"
                        placeholder="GSTIN will appear here" /></div>
            </div>

            <div class="form-group">
                <label for="transportName" class="col-sm-2 control-label">Transport Name</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="transportName" name="transportName" placeholder="Optional: Enter Transport/Courier Name" autocomplete="off" />
                </div>
            </div>
            <div class="form-group">
                <label for="transportGstNo" class="col-sm-2 control-label">Transport GSTIN</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="transportGstNo" name="transportGstNo" placeholder="Optional: Enter Transport's GSTIN" autocomplete="off" />
                </div>
            </div>

            <table class="table" id="productTable">
                <thead>
                    <tr>
                        <th style="width: 30%;">Product</th>
                        <th style="width: 25%;">Product Description/B.No</th>
                        <th style="width: 10%;">Rate</th>
                        <th style="width: 10%; text-align: center;">Available Qty</th>
                        <th style="width: 10%; text-align: center;">Quantity/No. of size</th>
                        <th style="width: 10%;">Total</th>
                        <th style="width: 5%;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($fromQuoteData): // If loading from a quote, pre-fill all items
                            $x = 1;
                            foreach($fromQuoteItems as $item) { ?>
                        <tr id="row<?php echo $x; ?>" style="vertical-align: middle;">
                        <td>
                            <select class="form-control" name="productName[]" id="productName<?php echo $x; ?>"
                                onchange="getProductData(<?php echo $x; ?>)">
                                <option value="">-- SELECT --</option>
                                <?php
                                                $productSql = "SELECT product_id, product_name FROM product WHERE active = 1 AND status = 1";
                                                $productData = $connect->query($productSql);
                                                while($prow = $productData->fetch_array()) {
                                                    $selected = ($item['product_id'] == $prow[0]) ? "selected" : "";
                                                    echo "<option value='".$prow[0]."' ".$selected.">".$prow[1]."</option>";
                                                }
                                            ?>
                            </select>
                        </td>
                        <td><textarea name="OiRemarks[]" id="OiRemarks<?php echo $x; ?>" class="form-control"
                                style="height:34px;"></textarea></td>
                        <td><input type="text" name="rate[]" id="rate<?php echo $x; ?>" class="form-control"
                                value="<?php echo $item['rate']; ?>" /><input type="hidden" name="rateValue[]"
                                id="rateValue<?php echo $x; ?>" value="<?php echo $item['rate']; ?>" /></td>
                        <td class="text-center">
                            <p id="available_quantity<?php echo $x; ?>" style="margin: 0;"></p>
                        </td>
                        <td><input type="text" name="quantity[]" id="quantity<?php echo $x; ?>"
                                onkeyup="getTotal(<?php echo $x ?>)" class="form-control" min="0"
                                value="<?php echo $item['quantity']; ?>" /></td>
                        <td><input type="text" name="total[]" id="total<?php echo $x; ?>" onkeyup="getTotal(<?php echo $x ?>)" class="form-control"
                                value="<?php echo $item['total']; ?>" /><input onkeyup="getTotal(<?php echo $x ?>)" type="hidden" class="totalValue"
                                name="totalValue[]" id="totalValue<?php echo $x; ?>"
                                value="<?php echo $item['total']; ?>" /></td>
                        <td class="text-center"><button class="btn btn-default removeProductRowBtn" type="button"
                                onclick="removeProductRow(<?php echo $x; ?>)"><i
                                    class="glyphicon glyphicon-trash"></i></button></td>
                    </tr>
                    <?php $x++; } ?>
                    <?php else: // Otherwise, show the default empty row for a new order ?>

                    <?php
						// Only show the first empty row on the regular 'add' page
						if ($page == 'add') {
							$arrayNumber = 0;
							for($x = 1; $x < 2; $x++) { ?>
                            
                        <tr id="row<?php echo $x; ?>" class="<?php echo $arrayNumber; ?>" style="vertical-align: middle;">
                        <td>
                            <select class="form-control" name="productName[]" id="productName<?php echo $x; ?>"
                                onchange="getProductData(<?php echo $x; ?>)">
                                <option value="">-- SELECT --</option>
                                <?php
												$productSql = "SELECT product_id, product_name FROM product WHERE active = 1 AND status = 1 AND quantity > 0";
												$productData = $connect->query($productSql);
												while($row = $productData->fetch_array()) {
													echo "<option value='".$row[0]."'>".$row[1]."</option>";
												}
											?>
                            </select>
                        </td>
                        <td>
                            <textarea style="height:34px;" name="OiRemarks[]" id="OiRemarks<?php echo $x; ?>"
                                class="form-control" placeholder="Optional remarks for this item"></textarea>
                        </td>
                        <td>
                            <input type="text" name="rate[]" id="rate<?php echo $x; ?>" class="form-control" />
                            <input type="hidden" name="rateValue[]" id="rateValue<?php echo $x; ?>" />
                        </td>
                        <td class="text-center">
                            <p id="available_quantity<?php echo $x; ?>" style="margin: 0;"></p>
                        </td>
                        <td>
                            <input type="text" name="quantity[]" id="quantity<?php echo $x; ?>"
                                onkeyup="getTotal(<?php echo $x ?>)" class="form-control" min="0" />
                        </td>
                        <td>
                            <input type="text" name="total[]" id="total<?php echo $x; ?>" onkeyup="getTotal(<?php echo $x ?>)" class="form-control" />
                            <input type="hidden" name="totalValue[]" id="totalValue<?php echo $x; ?>" onkeyup="getTotal(<?php echo $x ?>)" />
                        </td>
                        <td class="text-center">
                            <button class="btn btn-default removeProductRowBtn" type="button"
                                onclick="removeProductRow(<?php echo $x; ?>)"><i
                                    class="glyphicon glyphicon-trash"></i></button>
                        </td>
                    </tr>
                    <?php }
						} 
						?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="col-md-6">
                <div class="form-group"><label for="subTotal" class="col-sm-3 control-label">Sub Amount</label>
                    <div class="col-sm-9"><input type="text" class="form-control" id="subTotal" name="subTotal" /><input
                            type="hidden" id="subTotalValue" name="subTotalValue" /></div>
                </div>
                <div class="form-group"><label for="vat" class="col-sm-3 control-label gst">GST 18%</label>
                    <div class="col-sm-9"><input type="text" class="form-control" id="vat" name="vat" /><input
                            type="hidden" id="vatValue" name="vatValue" /></div>
                </div>
                <div class="form-group"><label for="totalAmount" class="col-sm-3 control-label">Total Amount</label>
                    <div class="col-sm-9"><input type="text" class="form-control" id="totalAmount"
                            name="totalAmount" /><input type="hidden" id="totalAmountValue" name="totalAmountValue" />
                    </div>
                </div>
                <div class="form-group"><label for="discount" class="col-sm-3 control-label">Discount</label>
                    <div class="col-sm-9"><input type="text" class="form-control" id="discount" name="discount"
                            onkeyup="discountFunc()" autocomplete="off" value="0" /></div>
                </div>
                <div class="form-group"><label for="grandTotal" class="col-sm-3 control-label">Grand Total</label>
                    <div class="col-sm-9"><input type="text" class="form-control" id="grandTotal"
                            name="grandTotal" /><input type="hidden" id="grandTotalValue" name="grandTotalValue" />
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group"><label for="paid" class="col-sm-3 control-label">Paid Amount</label>
                    <div class="col-sm-9"><input type="text" class="form-control" id="paid" name="paid"
                            autocomplete="off" onkeyup="paidAmount()" /></div>
                </div>
                <div class="form-group"><label for="due" class="col-sm-3 control-label">Due Amount</label>
                    <div class="col-sm-9"><input type="text" class="form-control" id="due" name="due" /><input
                            type="hidden" id="dueValue" name="dueValue" /></div>
                </div>
                <div class="form-group"><label class="col-sm-3 control-label">Payment Type</label>
                    <div class="col-sm-9"><select class="form-control" name="paymentType" id="paymentType">
                            <option value="">-- SELECT --</option>
                            <option value="1">Cheque</option>
                            <option value="2">Cash</option>
                            <option value="3">Credit Card</option>
                            <option value="4">UPI</option>
                            <option value="5">Debit Card</option>
                            <option value="6">Netbanking</option>
                        </select></div>
                </div>
                <div class="form-group"><label class="col-sm-3 control-label">Payment Status</label>
                    <div class="col-sm-9"><select class="form-control" name="paymentStatus" id="paymentStatus">
                            <option value="">-- SELECT --</option>
                            <option value="1">Full Payment</option>
                            <option value="2">Advance Payment</option>
                            <option value="3">No Payment</option>
                        </select></div>
                </div>
                <div class="form-group"><label class="col-sm-3 control-label">Payment Place</label>
                    <div class="col-sm-9">
                        <select class="form-control" name="paymentPlace" id="paymentPlace">
                            <option value="">-- SELECT --</option>
                            <option value="1" selected>In Gujarat</option>
                            <option value="2">Out Of Gujarat</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="orderStatus" class="col-sm-3 control-label">Order Status</label>
                    <div class="col-sm-9">
                        <select class="form-control" name="orderStatus" id="orderStatus">
                            <option value="3" selected>Draft</option>
                            <option value="1" disabled>Finalized</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group submitButtonFooter">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="button" class="btn btn-default" onclick="addRow()" id="addRowBtn"
                        data-loading-text="Loading..."> <i class="glyphicon glyphicon-plus-sign"></i> Add Row </button>
                    <button type="submit" id="createOrderBtn" data-loading-text="Loading..." class="btn btn-success"><i
                            class="glyphicon glyphicon-ok-sign"></i> Save Changes</button>
                    <button type="reset" class="btn btn-default" onclick="resetOrderForm()"><i
                            class="glyphicon glyphicon-erase"></i> Reset</button>
                </div>
            </div>
        </form>

        <?php } else if($page == 'manord') { 
			// manage order
			?>

        <div id="success-messages"></div>

        <table class="table" id="manageOrderTable">
            <thead>
                <tr>
                    <th>Sr no.</th>
                    <th>Order No.</th>
                    <th>Order Date</th>
                    <th>Party Name</th>
                    <th>Contact</th>
                    <th>Total Order Item</th>
                    <th>Payment Status</th>
                    <th>Order Status</th>
                    <th>Option</th>
                </tr>
            </thead>
        </table>

        <?php 
		// /else manage order
		} else if($page == 'editOrd') {
			// get order
			?>

        <div class="success-messages"></div>
        <!--/success-messages-->

        <form class="form-horizontal" method="POST" action="php_action/editOrder.php" id="editOrderForm">

            <?php $orderId = $_GET['i'];

  			// UPDATED: This query now JOINS the partys table to get the client's details
  			$sql = "SELECT 
                        o.order_id, o.invoice_no, o.other_po_no, o.other_po_date, o.order_date, o.sub_total, o.vat, o.total_amount, o.discount, 
                        o.grand_total, o.paid, o.due, o.payment_type, o.payment_status, o.payment_place, o.order_status, o.transport_name, o.transport_gst_no,
                        p.id AS party_id, p.name AS client_name, p.contact_no AS client_contact, p.gstin
                    FROM orders o
                    LEFT JOIN partys p ON o.party_id = p.id
					WHERE o.order_id = {$orderId}";

				$result = $connect->query($sql);
				$data = $result->fetch_assoc(); // Use fetch_assoc() for easier access by column name
  			?>

			<div class="form-group">
                <label for="invoiceNo" class="col-sm-2 control-label">Invoice No.</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="invoiceNo" name="invoiceNo" placeholder="Enter a unique invoice number" autocomplete="off" required value="<?php echo $data['invoice_no'] ?>" />
                </div>
            </div>

            <div class="form-group">
                <label for="orderDate" class="col-sm-2 control-label">Order Date</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="orderDate" name="orderDate" autocomplete="off"
                        value="<?php echo $data['order_date'] ?>" />
                </div>
            </div>

            <div class="form-group">
                <label for="editOtherPoNo" class="col-sm-2 control-label">Other PO No.</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="editOtherPoNo" name="editOtherPoNo" placeholder="Optional: Enter another PO number" autocomplete="off" value="<?php echo $data['other_po_no'] ?>" />
                </div>
            </div>
            <div class="form-group">
                <label for="editOtherPoDate" class="col-sm-2 control-label">PO Date</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="editOtherPoDate" name="editOtherPoDate" autocomplete="off" value="<?php echo $data['other_po_date']; ?>" />
                </div>
            </div>

            <!--/form-group-->
            <div class="form-group">
                <label for="partyId" class="col-sm-2 control-label">Party / Client</label>
                <div class="col-sm-10">
                    <select class="form-control" id="partyId" name="partyId">
                        <option value="">-- Select a Party --</option>
                        <?php 
                        $partySql = "SELECT * FROM partys ORDER BY name ASC";
                        $partyResult = $connect->query($partySql);
                        while($row = $partyResult->fetch_array()) {
                            // Pre-select the party associated with this order
                            $selected = ($data['party_id'] == $row['id']) ? "selected" : "";
                            echo "<option value='".$row['id']."' data-contact='".$row['contact_no']."' data-gstin='".$row['gstin']."' ".$selected.">".$row['name']."</option>";
                        }
                    ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="clientContact" class="col-sm-2 control-label">Party Contact</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control clientContact" id="clientContact" name="clientContact"
                        placeholder="Contact will appear here" value="<?php echo $data['client_contact']; ?>" />
                </div>
            </div>
            <div class="form-group">
                <label for="gstn" class="col-sm-2 control-label">Party G.S.T.IN</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control gstn" id="gstn" name="gstn"
                        value="<?php echo $data['gstin']; ?>" />
                </div>
            </div>

            <div class="form-group">
                <label for="editTransportName" class="col-sm-2 control-label">Transport Name</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="editTransportName" name="editTransportName" value="<?php echo htmlspecialchars($data['transport_name']); ?>" autocomplete="off" />
                </div>
            </div>
            <div class="form-group">
                <label for="editTransportGstNo" class="col-sm-2 control-label">Transport GSTIN</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="editTransportGstNo" name="editTransportGstNo" value="<?php echo htmlspecialchars($data['transport_gst_no']); ?>" autocomplete="off" />
                </div>
            </div>

            <table class="table" id="productTable">
                <thead>
                    <tr>
                        <th style="width: 30%;">Product</th>
                        <th style="width: 25%;">Product Description/B.No</th>
                        <th style="width: 15%;">Rate</th>
                        <th style="width: 10%; text-align: center;">Available Qty</th>
                        <th style="width: 5%; text-align: center;">Quantity/No. of size</th>
                        <th style="width: 15%;">Total</th>
                        <th style="width: 5%;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
					$orderItemSql = "SELECT 
										oi.order_item_id, oi.product_id, oi.quantity, oi.size, oi.rate, oi.total, oi.remarks AS oi_remarks,
										p.quantity AS available_qty
									FROM order_item oi
									JOIN product p ON oi.product_id = p.product_id
									WHERE oi.order_id = ?";
					
					$orderItemStmt = $connect->prepare($orderItemSql);
					$orderItemStmt->bind_param("i", $orderId);
					$orderItemStmt->execute();
					$orderItemResult = $orderItemStmt->get_result();

					$x = 1;
					while($orderItemData = $orderItemResult->fetch_assoc()) { 
					?>
                    <tr id="row<?php echo $x; ?>" style="vertical-align: middle;">
                        <td>
                            <select class="form-control" name="productName[]" id="productName<?php echo $x; ?>"
                                onchange="getProductData(<?php echo $x; ?>)">
                                <option value="">-- SELECT --</option>
                                <?php
										$productSql = "SELECT product_id, product_name FROM product WHERE active = 1 AND status = 1";
										$productData = $connect->query($productSql);
										while($row = $productData->fetch_array()) {                                     
											$selected = ($row[0] == $orderItemData['product_id']) ? "selected" : "";
											echo "<option value='".$row[0]."' ".$selected." >".$row[1]."</option>";
										}
									?>
                            </select>
                        </td>
                        <td>
                            <textarea style="height:34px;" name="OiRemarks[]" id="OiRemarks<?php echo $x; ?>"
                                class="form-control"
                                placeholder="Optional remarks for this item"><?php echo $orderItemData['oi_remarks']; ?></textarea>
                        </td>
                        <td>
                            <input type="text" name="rate[]" id="rate<?php echo $x; ?>" autocomplete="off"
                                class="form-control" value="<?php echo $orderItemData['rate']; ?>" />
                            <input type="hidden" name="rateValue[]" id="rateValue<?php echo $x; ?>"
                                value="<?php echo $orderItemData['rate']; ?>" />
                        </td>
                        <td class="text-center">
                            <p id="available_quantity<?php echo $x; ?>" style="margin: 0;">
                                <?php echo $orderItemData['available_qty']; ?></p>
                        </td>
                        <td>
                            <input type="text" name="quantity[]" id="quantity<?php echo $x; ?>"
                                onkeyup="getTotal(<?php echo $x ?>)" autocomplete="off" class="form-control" min="0"
                                value="<?php echo $orderItemData['quantity']."/".$orderItemData['size']; ?>" />
                        </td>
                        <td>
                            <input type="text" name="total[]" id="total<?php echo $x; ?>" autocomplete="off" onkeyup="getTotal(<?php echo $x ?>)"
                                class="form-control" value="<?php echo $orderItemData['total']; ?>" />
                            <input type="hidden" name="totalValue[]" id="totalValue<?php echo $x; ?>" onkeyup="getTotal(<?php echo $x ?>)"
                                value="<?php echo $orderItemData['total']; ?>" />
                        </td>
                        <td class="text-center">
                            <button class="btn btn-default removeProductRowBtn" type="button"
                                onclick="removeProductRow(<?php echo $x; ?>)"><i
                                    class="glyphicon glyphicon-trash"></i></button>
                        </td>
                    </tr>
                    <?php
					$x++;
					} // /while
					?>
                </tbody>
            </table>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="subTotal" class="col-sm-3 control-label">Sub Amount</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="subTotal" name="subTotal"
                            value="<?php echo $data['sub_total'] ?>" />
                        <input type="hidden" class="form-control" id="subTotalValue" name="subTotalValue"
                            value="<?php echo $data['sub_total'] ?>" />
                    </div>
                </div>
                <!--/form-group-->

                <div class="form-group">
                    <label for="totalAmount" class="col-sm-3 control-label">Total Amount</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="totalAmount" name="totalAmount"
                            value="<?php echo $data['total_amount'] ?>" />
                        <input type="hidden" class="form-control" id="totalAmountValue" name="totalAmountValue"
                            value="<?php echo $data['total_amount'] ?>" />
                    </div>
                </div>
                <!--/form-group-->
                <div class="form-group">
                    <label for="discount" class="col-sm-3 control-label">Discount</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="discount" name="discount" onkeyup="discountFunc()"
                            autocomplete="off" value="<?php echo $data['discount'] ?>" />
                    </div>
                </div>
                <!--/form-group-->
                <div class="form-group">
                    <label for="grandTotal" class="col-sm-3 control-label">Grand Total</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="grandTotal" name="grandTotal"
                            value="<?php echo $data['grand_total'] ?>" />
                        <input type="hidden" class="form-control" id="grandTotalValue" name="grandTotalValue"
                            value="<?php echo $data['grand_total'] ?>" />
                    </div>
                </div>
                <!--/form-group-->
                <div class="form-group">
                    <label for="vat"
                        class="col-sm-3 control-label gst"><?php if($data['vat'] == 2) {echo "IGST 18%";} else echo "GST 18%"; ?></label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="vat" name="vat"
                            value="<?php echo $data['vat'] ?>" />
                        <input type="hidden" class="form-control" id="vatValue" name="vatValue"
                            value="<?php echo $data['vat'] ?>" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="gstn" class="col-sm-3 control-label gst">G.S.T.IN</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="gstn" name="gstn"
                            value="<?php echo $data['gstin'] ?>" />
                    </div>
                </div>
                <!--/form-group-->
            </div>
            <!--/col-md-6-->

            <div class="col-md-6">
                <div class="form-group">
                    <label for="paid" class="col-sm-3 control-label">Paid Amount</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="paid" name="paid" autocomplete="off"
                            onkeyup="paidAmount()" value="<?php echo $data['paid'] ?>" />
                    </div>
                </div>
                <!--/form-group-->
                <div class="form-group">
                    <label for="due" class="col-sm-3 control-label">Due Amount</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="due" name="due"
                            value="<?php echo $data['due'] ?>" />
                        <input type="hidden" class="form-control" id="dueValue" name="dueValue"
                            value="<?php echo $data['due'] ?>" />
                    </div>
                </div>
                <!--/form-group-->
                <div class="form-group">
                    <label for="clientContact" class="col-sm-3 control-label">Payment Type</label>
                    <div class="col-sm-9">
                        <select class="form-control" name="paymentType" id="paymentType">
                            <option value="">~~SELECT~~</option>
                            <option value="1" <?php if($data['payment_type'] == 1) {
				      		echo "selected";
				      	} ?>>Cheque</option>
                            <option value="2" <?php if($data['payment_type'] == 2) {
				      		echo "selected";
				      	} ?>>Cash</option>
                            <option value="3" <?php if($data['payment_type'] == 3) {
				      		echo "selected";
				      	} ?>>Credit Card</option>
                            <option value="4" <?php if($data['payment_type'] == 4) {
				      		echo "selected";
				      	} ?>>UPI</option>
                            <option value="5" <?php if($data['payment_type'] == 5) {
				      		echo "selected";
				      	} ?>>Debit Card</option>
                            <option value="6" <?php if($data['payment_type'] == 6) {
				      		echo "selected";
				      	} ?>>Netbanking</option>
                        </select>
                    </div>
                </div>
                <!--/form-group-->
                <div class="form-group">
                    <label for="clientContact" class="col-sm-3 control-label">Payment Status</label>
                    <div class="col-sm-9">
                        <select class="form-control" name="paymentStatus" id="paymentStatus">
                            <option value="">~~SELECT~~</option>
                            <option value="1" <?php if($data['payment_status'] == 1) {
				      		echo "selected";
				      	} ?>>Full Payment</option>
                            <option value="2" <?php if($data['payment_status'] == 2) {
				      		echo "selected";
				      	} ?>>Advance Payment</option>
                            <option value="3" <?php if($data['payment_status'] == 3) {
				      		echo "selected";
				      	} ?>>No Payment</option>
                        </select>
                    </div>
                </div>
                <!--/form-group-->
                <div class="form-group">
                    <label for="clientContact" class="col-sm-3 control-label">Payment Place</label>
                    <div class="col-sm-9">
                        <select class="form-control" name="paymentPlace" id="paymentPlace">
                            <option value="">~~SELECT~~</option>
                            <option value="1" <?php if($data['payment_place'] == 1) {
				      		echo "selected";
				      	} ?>>In Gujarat</option>
                            <option value="2" <?php if($data['payment_place'] == 2) {
				      		echo "selected";
				      	} ?>>Out Gujarat</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="orderStatus" class="col-sm-3 control-label">Order Status</label>
                    <div class="col-sm-9">
                        <select class="form-control" name="orderStatus" id="orderStatus">
                            <option value="3" <?php if($data['order_status'] == 3) echo "selected"; ?>>Draft</option>
                            <option value="1" <?php if($data['order_status'] == 1) echo "selected"; ?>>Finalized</option>
                        </select>
                    </div>
                </div>
            </div>
            <!--/col-md-6-->


            <div class="form-group editButtonFooter">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="button" class="btn btn-default" onclick="addRow()" id="addRowBtn"
                        data-loading-text="Loading..."> <i class="glyphicon glyphicon-plus-sign"></i> Add Row </button>

                    <input type="hidden" name="orderId" id="orderId" value="<?php echo $_GET['i']; ?>" />

                    <button type="submit" id="editOrderBtn" data-loading-text="Loading..." class="btn btn-success"><i
                            class="glyphicon glyphicon-ok-sign"></i> Save Changes</button>

                </div>
            </div>
        </form>

        <?php
		} // /get order else  ?>


    </div>
    <!--/panel-->
</div>
<!--/panel-->

<!-- challanSelectionModal -->
<div class="modal fade" id="challanSelectionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="glyphicon glyphicon-list"></i> Select Challans to Bill</h4>
            </div>
            <div class="modal-body" id="challanListModalBody" style="max-height: 400px; overflow-y: auto;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="applyChallansBtn">Apply</button>
            </div>
        </div>
    </div>
</div>

<!-- edit order -->
<div class="modal fade" tabindex="-1" role="dialog" id="paymentOrderModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="glyphicon glyphicon-edit"></i> Edit Payment</h4>
            </div>

            <div class="modal-body form-horizontal" style="max-height:500px; overflow:auto;">

                <div class="paymentOrderMessages"></div>


                <div class="form-group">
                    <label for="due" class="col-sm-3 control-label">Due Amount</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="due" name="due" />
                    </div>
                </div>
                <!--/form-group-->
                <div class="form-group">
                    <label for="payAmount" class="col-sm-3 control-label">Pay Amount</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="payAmount" name="payAmount" />
                    </div>
                </div>
                <!--/form-group-->
                <div class="form-group">
                    <label for="clientContact" class="col-sm-3 control-label">Payment Type</label>
                    <div class="col-sm-9">
                        <select class="form-control" name="paymentType" id="paymentType">
                            <option value="">~~SELECT~~</option>
                            <option value="1">Cheque</option>
                            <option value="2">Cash</option>
                            <option value="3">Credit Card</option>
                            <option value="4">UPI</option>
                            <option value="5">Debit Card</option>
                            <option value="6">Netbanking</option>
                        </select>
                    </div>
                </div>
                <!--/form-group-->
                <div class="form-group">
                    <label for="clientContact" class="col-sm-3 control-label">Payment Status</label>
                    <div class="col-sm-9">
                        <select class="form-control" name="paymentStatus" id="paymentStatus">
                            <option value="">~~SELECT~~</option>
                            <option value="1">Full Payment</option>
                            <option value="2">Advance Payment</option>
                            <option value="3">No Payment</option>
                        </select>
                    </div>
                </div>
                <!--/form-group-->

            </div>
            <!--/modal-body-->
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"> <i
                        class="glyphicon glyphicon-remove-sign"></i> Close</button>
                <button type="button" class="btn btn-primary" id="updatePaymentOrderBtn" data-loading-text="Loading...">
                    <i class="glyphicon glyphicon-ok-sign"></i> Save changes</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- /edit order-->

<!-- remove order -->
<div class="modal fade" tabindex="-1" role="dialog" id="removeOrderModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="glyphicon glyphicon-trash"></i> Remove Order</h4>
            </div>
            <div class="modal-body">

                <div class="removeOrderMessages"></div>

                <p>Do you really want to remove ?</p>
            </div>
            <div class="modal-footer removeProductFooter">
                <button type="button" class="btn btn-default" data-dismiss="modal"> <i
                        class="glyphicon glyphicon-remove-sign"></i> Close</button>
                <button type="button" class="btn btn-primary" id="removeOrderBtn" data-loading-text="Loading..."> <i
                        class="glyphicon glyphicon-ok-sign"></i> Save changes</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<!-- /remove order-->


<script src="custom/js/order.js"></script>

<?php require_once 'includes/footer.php'; ?>