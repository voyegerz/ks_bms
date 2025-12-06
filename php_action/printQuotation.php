<?php 
require_once 'core.php';

$quotationId = $_GET['i'] ?? 0;
if (!$quotationId) {
    die("Quotation ID not specified.");
}

// Fetch main quotation, company, and party data
$sql = "SELECT
            q.quotation_id, q.quotation_date, q.sub_total, q.vat, q.grand_total,
            u.company_name, u.company_addr, u.contact_no AS company_contact, u.company_email,
            p.name AS party_name, p.billing_addr
        FROM quotations q
        LEFT JOIN users u ON q.user_id = u.user_id
        LEFT JOIN partys p ON q.party_id = p.id
        WHERE q.quotation_id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $quotationId);
$stmt->execute();
$quoteData = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$quoteData) {
    die("Quotation not found.");
}

// Fetch quotation items
$itemsSql = "SELECT qi.quantity, qi.rate, qi.total, p.product_name, p.hsn
             FROM quotation_items qi
             LEFT JOIN product p ON qi.product_id = p.product_id
             WHERE qi.quotation_id = ?";
$stmt_items = $connect->prepare($itemsSql);
$stmt_items->bind_param("i", $quotationId);
$stmt_items->execute();
$itemsResult = $stmt_items->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quotation #<?php echo $quotationId; ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .container { width: 800px; margin: auto; }
        .header { text-align: center; margin-bottom: 20px; }
        .details-table, .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table td { padding: 8px; border: 1px solid #ccc; vertical-align: top; }
        .items-table th, .items-table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .items-table th { background-color: #f2f2f2; text-align: center; }
        .text-right { text-align: right; }
        .document-title { text-align: center; font-size: 18px; font-weight: bold; text-decoration: underline; margin-top: 10px; margin-bottom: 5px; }

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
        }
        .logo-container img {
            max-width: 55%;
            height: auto;
        }
        .company-details-container {
            flex: 1; /* Grow to fill remaining space */
        }
        .company-details-container h2 { margin: 0; font-size: 24px; }
        .company-details-container p { margin: 2px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="document-title">Quotation</div>

        <div class="header-flex-container">
            <div class="logo-container">
                <img src="../logoe.jpg" alt="Company Logo">
            </div>
            <div class="company-details-container">
                <h2><?php echo htmlspecialchars(strtoupper($quoteData['company_name'])); ?></h2>
                <p><?php echo nl2br(htmlspecialchars($quoteData['company_addr'])); ?></p>
                <p>
                (M) <?php echo htmlspecialchars($quoteData['company_contact']); ?> 
                Email: <?php echo htmlspecialchars($quoteData['company_email']); ?>
            </p>
            </div>
        </div>

        <table class="details-table">
            <tr>
                <td style="width:50%;"><strong>To:</strong><br><?php echo htmlspecialchars($quoteData['party_name']); ?><br><?php echo nl2br(htmlspecialchars($quoteData['billing_addr'])); ?></td>
                <td style="width:50%;"><strong>Quotation No.:</strong> QUO-<?php echo $quotationId; ?><br><strong>Date:</strong> <?php echo date("d-m-Y", strtotime($quoteData['quotation_date'])); ?></td>
            </tr>
        </table>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:5%;">#</th>
                    <th>Description</th>
                    <th style="width:10%;">HSN</th>
                    <th style="width:10%;">Qty</th>
                    <th style="width:15%;" class="text-right">Rate</th>
                    <th style="width:15%;" class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php $x = 1; while($item = $itemsResult->fetch_assoc()): ?>
                <tr>
                    <td style="text-align:center;"><?php echo $x++; ?></td>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td style="text-align:center;"><?php echo htmlspecialchars($item['hsn']); ?></td>
                    <td style="text-align:center;"><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td class="text-right"><?php echo number_format($item['rate'], 2); ?></td>
                    <td class="text-right"><?php echo number_format($item['total'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr><td colspan="5" class="text-right"><strong>Sub Total</strong></td><td class="text-right"><?php echo number_format($quoteData['sub_total'], 2); ?></td></tr>
                <tr><td colspan="5" class="text-right"><strong>VAT/GST (18%)</strong></td><td class="text-right"><?php echo number_format($quoteData['vat'], 2); ?></td></tr>
                <tr style="font-weight:bold; background-color:#f2f2f2;"><td colspan="5" class="text-right"><strong>Grand Total</strong></td><td class="text-right"><?php echo number_format($quoteData['grand_total'], 2); ?></td></tr>
            </tfoot>
        </table>
        <p><strong>Terms & Conditions:</strong><br>...</p>
    </div>

    <!-- <div class="no-print" style="text-align: center; margin: 20px;">
        <button onclick="window.print()">Print Quotation</button>
        <button onclick="window.close()">Close</button>
    </div> -->
    <script type="text/javascript">
        window.onload = function() {
            // Automatically print when the page loads
            window.print();
            // Close the window after printing (or if user cancels print)
            window.onafterprint = function() { window.close(); };
        }
    </script>
</body>
</html>