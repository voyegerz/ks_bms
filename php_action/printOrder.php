<?php
require_once 'core.php';

// Get the order ID from the request
$orderId = $_POST['orderId'] ?? $_GET['orderId'] ?? 0;
$terms = "";

if (!$orderId) {
    die("No Order ID provided.");
}

// --- Main query to fetch all required data from orders, users, and partys tables ---
$sql = "SELECT
            o.order_id, o.invoice_no, o.ref_challan_nos, o.other_po_no, o.other_po_date, o.order_date, o.sub_total, o.vat, o.total_amount, o.discount, 
            o.grand_total, o.paid, o.due, o.payment_type, o.payment_place, o.transport_name, o.transport_gst_no,
            u.company_name, u.company_addr, u.gstin AS company_gstin, u.state_name, 
            u.state_code, u.company_email, u.contact_no AS company_contact, u.company_bank_details, u.cgst, u.sgst,
            p.name AS party_name, p.billing_addr, p.shipping_addr, p.gstin AS party_gstin
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        LEFT JOIN partys p ON o.party_id = p.id
        WHERE o.order_id = ?";

$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$orderData = $result->fetch_assoc();
$stmt->close();
if (!$orderData) {
    die("Order not found.");
}

// --- Assign Company & Order data to variables ---
// Company (Seller) Details
$companyName        = $orderData['company_name'] ?? 'Your Company Name';
$companyAddr        = $orderData['company_addr'] ?? 'Your Company Address';
$companyGstin       = $orderData['company_gstin'] ?? 'Your Company GSTIN';
$companyState       = $orderData['state_name'] ?? 'State';
$companyStateCode   = $orderData['state_code'] ?? '00';
$companyEmail       = $orderData['company_email'] ?? 'email@example.com';
$companyBankDetails = $orderData['company_bank_details'] ?? 'Bank details not set.';
$cgstRate           = (float)($orderData['cgst'] ?? 9.00);
$sgstRate           = (float)($orderData['sgst'] ?? 9.00);
$igstRate           = $cgstRate + $sgstRate;

// Party (Buyer) Details
$orderDate        = $orderData['order_date'];
$buyerName        = $orderData['party_name'];
$buyerBillingAddr = $orderData['billing_addr'] ?? 'N/A';
$buyerShippingAddr = !empty($orderData['shipping_addr']) ? $orderData['shipping_addr'] : $buyerBillingAddr;
$buyerGstin       = $orderData['party_gstin'];
$otherPoNo        = $orderData['other_po_no'];
$otherPoDate        = $orderData['other_po_date'];

// --- Fetch order items ---
$itemsSql = "SELECT oi.quantity, oi.size, oi.rate, oi.total, oi.remarks AS oi_remarks, p.product_name, p.hsn
             FROM order_item oi
             LEFT JOIN product p ON oi.product_id = p.product_id
             WHERE oi.order_id = ?";
$stmt_items = $connect->prepare($itemsSql);
$stmt_items->bind_param("i", $orderId);
$stmt_items->execute();
$itemsResult = $stmt_items->get_result();

$paymentTypeStr = '';
switch ($orderData['payment_type']) {
    case 1:
        $paymentTypeStr = "Cheque";
        break;
    case 2:
        $paymentTypeStr = "Cash";
        break;
    case 3:
        $paymentTypeStr = "Credit Card";
        break;
    case 4:
        $paymentTypeStr = "UPI";
        break;
    case 5:
        $paymentTypeStr = "Debit Card";
        break;
    case 6:
        $paymentTypeStr = "Netbanking";
        break;
}

$items = [];
$subTotal = 0;
while($row = $itemsResult->fetch_assoc()) {
    $items[] = [$row['product_name'], $row['hsn'], $row['quantity'], $row['rate'], $row['total']];
    $subTotal += (float)$row['total'];
}

// --- Calculate Totals ---
$shipping = 0.00; // Can be made dynamic if needed
$taxableBase = $subTotal + $shipping;
$cgst = round($taxableBase * ($cgstRate / 100), 2);
$sgst = round($taxableBase * ($sgstRate / 100), 2);
$igst = round($sgst+$cgst, 2);
$totalWithGST = $subTotal + $shipping + $cgst + $sgst;


// --- Corrected Number to Words Function ---
function convertNumberToWords($number) {
    $number = (string)$number;
    $number_parts = explode('.', $number);
    $rupees = (int)$number_parts[0];
    $paise = isset($number_parts[1]) ? (int)substr($number_parts[1], 0, 2) : 0;
    $rupees_words = '';
    if ($rupees > 0) { $rupees_words = toWords($rupees) . ' Rupees'; }
    $paise_words = '';
    if ($paise > 0) { $paise_words = ' and ' . toWords($paise) . ' Paise'; }
    if ($rupees === 0 && $paise === 0) { return 'Zero Rupees Only'; }
    return ucwords($rupees_words . $paise_words . ' Only');
}
function toWords($num) {
    $ones = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');
    $tens = array('', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety');
    if ($num == 0) return '';
    $words = '';
    if (floor($num / 10000000) > 0) { $words .= toWords(floor($num / 10000000)) . ' Crore '; $num %= 10000000; }
    if (floor($num / 100000) > 0) { $words .= toWords(floor($num / 100000)) . ' Lakh '; $num %= 100000; }
    if (floor($num / 1000) > 0) { $words .= toWords(floor($num / 1000)) . ' Thousand '; $num %= 1000; }
    if (floor($num / 100) > 0) { $words .= toWords(floor($num / 100)) . ' Hundred '; $num %= 100; }
    if ($num > 0) { if ($words != '') $words .= ''; if ($num < 20) { $words .= $ones[$num]; } else { $words .= $tens[floor($num / 10)]; if ($num % 10 > 0) { $words .= ' ' . $ones[$num % 10]; } } }
    return trim($words);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice</title>
    <style>
        @page { size: A4; margin: 10mm; }
        @media print { body { margin: 0; } .no-print { display: none; } }
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 15px; }
        .invoice-container { background-color: white; max-width: 800px; margin: 0 auto; }
        .invoice-header { text-align: center; font-weight: bold; font-size: 16px; padding: 8px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        .header-section td { padding: 4px; border: 1px solid black; vertical-align: top; }
        .items-table th, .items-table td { border: 1px solid black; padding: 5px; text-align: left; font-size: 11px; }
        .items-table th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
        .items-table .center { text-align: center; }
        .items-table .right { text-align: right; }
        .items-table .description {
            text-align: left;
            white-space: pre-wrap; /* This preserves newlines and wraps text */
            word-break: break-word;   /* This breaks long words nicely */
        }
        .tax-breakdown-table th, .tax-breakdown-table td { border: 1px solid black; padding: 4px; text-align: center; font-size: 10px; }
        .tax-breakdown-table th { background-color: #f0f0f0; }
        .amount-words, .bank-details, .declaration { padding: 5px; border-left: 1px solid black; border-right: 1px solid black; }
        
        .computer-generated { text-align: center; font-size: 9px; margin-top: 15px; font-style: italic; }

        /* ## NEW CSS FOR THE HEADER ## */
        .header-flex-container {
            display: flex;
            align-items: flex-start; /* Aligns items to the top */
            border-bottom: 1px solid black;
            padding-bottom: 5px;
            margin-bottom: 3px;
        }
        .logo-container {
            flex: 0 0 100px; /* Do not grow, do not shrink, base width of 150px */
            margin-right: -10px;
            margin-left:30px;
            display: none;
        }
        .logo-container img {
            max-width: 55%;
            height: auto;

        }
        .company-details-container {
            flex: 1; /* Grow to fill remaining space */
        }
        .company-details-container h2 { 
            margin: 0; 
            font-size: 24px;
            color:#3700B3; 
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
        }
        .company-details-container p {
            margin: 2px 0; 
            color:#B82B00;
            
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">Tax Invoice</div>

        <div class="header-flex-container">
            <div class="logo-container">
                <img src="logoe.jpg" alt="Company Logo">
            </div>
            <div class="company-details-container">
                <h2><?php echo htmlspecialchars(strtoupper($orderData['company_name'])); ?></h2>
                <p><?php echo nl2br(htmlspecialchars($orderData['company_addr'])); ?></p>
                <p>(M) <?php echo htmlspecialchars($orderData['company_contact']); ?> Email: <?php echo htmlspecialchars($orderData['company_email']); ?></p>
            </div>
        </div>

        <table class="header-section">
            <tr>
                <td style="width: 50%;" rowspan="2">
                    <strong><?php echo htmlspecialchars($companyName); ?></strong><br>
                    <?php echo nl2br(htmlspecialchars($companyAddr)); ?><br>
                    <strong>E-Mail:</strong> <?php echo htmlspecialchars($companyEmail); ?><br>
                    <strong>GSTIN/UIN:</strong> <?php echo htmlspecialchars($companyGstin); ?><br>
                    <strong>State:</strong> <?php echo htmlspecialchars($companyState); ?> (Code: <?php echo htmlspecialchars($companyStateCode); ?>)
                </td>
                <td style="width: 25%;">Invoice No.<br><strong><?php echo htmlspecialchars($orderData['invoice_no']); ?></strong></td>
                <td style="width: 25%;">Dated<br><strong><?php echo $orderDate?></strong></td>
            </tr>
            <tr>
                <td>
                    Transport:<strong><?php echo htmlspecialchars($orderData['transport_name']); ?></strong> <br>
                    Transport GSTIN:<strong><?php echo htmlspecialchars($orderData['transport_gst_no']); ?></strong> 
                </td>
                <td>Mode/Terms of Payment<br><strong><?php echo $paymentTypeStr ?></strong></td>
            </tr>
            <tr>
                <td style="width: 50%;" rowspan="2">
                    <strong>Buyer (Bill to)</strong><br>
                    <strong><?php echo htmlspecialchars($buyerName); ?></strong><br>
                    <?php echo nl2br(htmlspecialchars($buyerBillingAddr)); ?><br>
                    <strong>GSTIN/UIN:</strong> <?php echo htmlspecialchars($buyerGstin); ?>
                </td>
                <td  style="width: 25%;">Challan No. & Date<br><strong><?php echo htmlspecialchars($orderData['ref_challan_nos']); ?></strong></td>
                <td  style="width: 25%;">Other References<br><strong></strong></td>
            </tr>
            <tr>
                <td>PO No.<br><strong><?php echo $otherPoNo ?></strong></td>
                <td>Dated<br><?php echo $otherPoDate?></td>
            </tr>
            <tr>
                <td style="border-bottom: none;">
                    <strong>Consignee (Ship to)</strong><br>
                    <strong><?php echo htmlspecialchars($buyerName); ?></strong><br>
                    <?php echo nl2br(htmlspecialchars($buyerShippingAddr)); ?>
                </td>
                
                <td colspan="2" style="border-bottom: none;">Terms & Conditions<br><?php echo $terms?></td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:5%;">Sl No.</th>
                    <th colspan="2" style="width:55%;">Description of Goods</th>
                    <!-- <th style="width:35%;">Product Description/B.No</th> -->
                    <th>HSN/SAC</th>
                    <th>Quantity/No. of size</th>
                    <th>Rate</th>
                    <th>per</th>
                    <th style="width:15%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $itemsResult->data_seek(0);
                $x = 1;
                while($item = $itemsResult->fetch_assoc()): 
                ?>
                <tr>
                    <td class='center'><?php echo $x++; ?></td>
                    <td colspan="2"><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <!-- <td class="description"><?php echo htmlspecialchars($item['oi_remarks']); ?></td> -->
                    <td class='center'><?php echo htmlspecialchars($item['hsn']); ?></td>
                    <td class='center'><?php echo htmlspecialchars(!empty($item['size']) ? $item['quantity']."/".$item['size'] : $item['quantity']); ?></td>
                    <td class='right'><?php echo number_format($item['rate'], 2); ?></td>
                    <td class='center'>KGS</td>
                    <td class='right'><?php echo number_format($item['total'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
                <?php
                    // ## NEW: This loop adds empty rows to fill up the table ##
                    // Adjust '12' to a number that fits your A4 page well
                    for ($i = $x; $i <= 5; $i++) {
                        echo '<tr class="empty-row">
                                <td>&nbsp;</td> <td colspan="2"></td> <td></td> <td></td> <td></td> <td></td> <td></td>
                            </tr>';
                    }
                    ?>
                <tr>
                    <td colspan="7" class="right">Sub Total</td>
                    <td class="right"><strong><?php echo number_format($subTotal, 2); ?></strong></td>
                </tr>
                <tr>
                    <td colspan="7" class="right"><em>Shipping</em></td>
                    <td class="right"><?php echo number_format($shipping, 2); ?></td>
                </tr>
                <?php if($orderData['payment_place'] == 2): // 2 = Out of Gujarat (IGST) ?>
                    <tr>
                        <td colspan="7" class="right">IGST @ <?php echo $cgstRate+$sgstRate; ?>%</td>
                        <td class="right"><?php echo number_format($igst, 2); ?></td>
                    </tr>
                <?php else: // 1 = In Gujarat (CGST/SGST) ?>
                    <tr>
                        <td colspan="7" class="right">CGST @ <?php echo $cgstRate; ?>%</td>
                        <td class="right"><?php echo number_format($cgst, 2); ?></td>
                    </tr>
                    <tr>
                        <td colspan="7" class="right">SGST @ <?php echo $sgstRate; ?>%</td>
                        <td class="right"><?php echo number_format($sgst, 2); ?></td>
                    </tr>
                <?php endif; ?>
                
                <tr style="font-weight: bold; background-color: #f0f0f0;">
                    <td colspan="7" class="right">TOTAL</td>
                    <td class="right">₹ <?php echo number_format(round($totalWithGST), 2); ?></td>
                </tr>
                
            </tbody>
        </table>

        <div class="amount-words">
            <strong>Amount Chargeable (in words):</strong><br>
            INR <?php echo convertNumberToWords(round($totalWithGST)); ?>
        </div>

        <table class="tax-breakdown-table">
            <thead>
                <tr>
                    <th rowspan="2">HSN/SAC</th>
                    <th rowspan="2">Taxable Value</th>
                    <?php if($orderData['payment_place'] == 2): // IGST ?>
                        <th colspan="4">Integrated Tax (IGST)</th>
                    <?php else: // CGST/SGST ?>
                        <th colspan="2">Central Tax (CGST)</th>
                        <th colspan="2">State Tax (SGST)</th>
                    <?php endif; ?>
                    <th rowspan="2">Total Tax Amount</th>
                </tr>
                <tr>
                    <?php if($orderData['payment_place'] == 2): // IGST ?>
                        <th colspan="2">Rate</th>
                        <th colspan="2">Amount</th>
                    <?php else: // CGST/SGST ?>
                        <th>Rate</th>
                        <th>Amount</th>
                        <th>Rate</th>
                        <th>Amount</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $hsnGroups = [];
                foreach($items as $it) {
                    $hsn = $it[1];
                    $amount = (float)$it[4];
                    if(!isset($hsnGroups[$hsn])) { $hsnGroups[$hsn] = 0; }
                    $hsnGroups[$hsn] += $amount;
                }
                
                $totalTaxableValue = 0; $totalCGST = 0; $totalSGST = 0; $totalIGST = 0;
                
                foreach($hsnGroups as $hsn => $taxableValue):
                    $cgstAmount = round($taxableValue * ($cgstRate / 100), 2);
                    $sgstAmount = round($taxableValue * ($sgstRate / 100), 2);
                    $igstAmount = $cgstAmount + $sgstAmount;
                    $totalTax = $cgstAmount + $sgstAmount;
                    $totalTaxableValue += $taxableValue; $totalCGST += $cgstAmount; $totalSGST += $sgstAmount;
                ?>
                <tr>
                    <td><?php echo $hsn; ?></td>
                    <td><?php echo number_format($taxableValue, 2); ?></td>
                    <?php if($orderData['payment_place'] == 2): // IGST ?>
                        <td colspan="2"><?php echo $igstRate; ?>%</td>
                        <td colspan="2"><?php echo number_format($igstAmount, 2); ?></td>
                        <td><?php echo number_format($totalTax, 2); ?></td>
                    <?php else: // CGST/SGST ?>
                        <td><?php echo $cgstRate; ?>%</td>
                        <td><?php echo number_format($cgstAmount, 2); ?></td>
                        <td><?php echo $sgstRate; ?>%</td>
                        <td><?php echo number_format($sgstAmount, 2); ?></td>
                        <td><?php echo number_format($totalTax, 2); ?></td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                
                <?php
                    $shippingCGST = round($shipping * ($cgstRate / 100), 2);
                    $shippingSGST = round($shipping * ($sgstRate / 100), 2);
                ?>
                 
                <tr style="font-weight: bold; background-color: #f0f0f0;">
                    <td>Total</td>
                    <td><?php echo number_format($totalTaxableValue + $shipping, 2); ?></td>
                    <?php if($orderData['payment_place'] == 2): // IGST ?>
                        <td colspan="2"></td>
                        <td colspan="2"><?php echo number_format($igst + $shippingCGST + $shippingSGST, 2); ?></td>
                    <?php else: // CGST/SGST ?>
                        <td></td>
                        <td><?php echo number_format($totalCGST + $shippingCGST, 2); ?></td>
                        <td></td>
                        <td><?php echo number_format($totalSGST + $shippingSGST, 2); ?></td>
                    <?php endif; ?>
                    <td><?php echo number_format($cgst + $sgst, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="amount-words">
            <strong>Tax Amount (in words) :</strong> INR <?php echo convertNumberToWords($cgst + $sgst); ?>
        </div>
        
        <table class="footer-section">
             <tr>
                <td style="width: 50%; text-align: left; border: 1px solid black; padding: 5px;">
                    <strong>Company's Bank Details</strong><br>
                    <strong><?php echo nl2br(htmlspecialchars($companyBankDetails)); ?></strong>
                </td>
                <td style="width: 50%; text-align: right; border: 1px solid black; padding: 5px; height: 100px; vertical-align: bottom;">
                    <strong>for <?php echo htmlspecialchars($companyName); ?></strong>
                    <br><br><br>
                    <strong>Authorised Signatory</strong>
                </td>
            </tr>
            <tr>
                 <td colspan="2" style="text-align: left; border: 1px solid black; padding: 5px;">
                    <strong>Declaration:</strong>
                    We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.
                    <p style="margin: 0;">1. Goods once sold, will not be taken back. </p>
                    <p style="margin: 0;">2. Our Responsibilities ceases after delivery of goods.</p>
                    <p style="margin: 0;">3. If payment not made within 15 days interest as per HSN.</p>
                    <p style="margin: 0;">4. Subject to HALOL Jurisdiction.</p>
                 </td>
            </tr>
        </table>
        <div class="computer-generated">This is a Computer Generated Invoice</div>
    </div>

    <div class="no-print" style="text-align: center; margin: 20px;">
        <button onclick="window.print()">Print Invoice</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>
</html>