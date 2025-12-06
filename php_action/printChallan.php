<?php
require_once 'core.php';

// Check for challan ID
$challanId = $_GET['i'] ?? 0;
if (!$challanId) {
    die("Challan ID not specified.");
}

// --- Fetch main challan, company, and party data ---
// Added u.contact_no to the SELECT statement
$sql = "SELECT
            c.challan_id, c.challan_no, c.challan_date, c.order_id, c.remarks,
            u.company_name, u.company_addr, u.gstin AS company_gstin, u.state_name, u.state_code, u.company_email, u.contact_no,
            p.name AS party_name, p.shipping_addr, p.billing_addr, p.gstin AS party_gstin,
            o.invoice_no, o.order_date AS invoice_order_date
        FROM challans c
        LEFT JOIN users u ON c.user_id = u.user_id
        LEFT JOIN partys p ON c.party_id = p.id
        LEFT JOIN orders o ON c.order_id = o.order_id
        WHERE c.challan_id = ?";

$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $challanId);
$stmt->execute();
$result = $stmt->get_result();
$challanData = $result->fetch_assoc();
$stmt->close();

if (!$challanData) {
    die("Challan not found.");
}

// --- Fetch challan items, including HSN and Rate from the product table ---
$itemsSql = "SELECT ci.quantity, ci.size, ci.remarks AS item_remarks , p.product_name, p.hsn, p.rate
             FROM challan_items ci
             LEFT JOIN product p ON ci.product_id = p.product_id
             WHERE ci.challan_id = ?";
$stmt_items = $connect->prepare($itemsSql);
$stmt_items->bind_param("i", $challanId);
$stmt_items->execute();
$itemsResult = $stmt_items->get_result();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Delivery Challan #<?php echo htmlspecialchars($challanData['challan_id']); ?></title>
    <style type="text/css">
        @page { size: A4; margin: 10mm; }
        @media print { body { -webkit-print-color-adjust: exact; margin: 0; } .no-print { display: none; } }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
        .invoice-container { max-width: 800px; margin: auto; }
        table { width: 100%; border-collapse: collapse; }
        .main-table, .main-table td { border: 1px solid black; }
        .main-table td { padding: 5px; vertical-align: top; }
        .items-table { margin-top: -1px; }
        .items-table th, .items-table td { border: 1px solid black; padding: 5px; text-align: center; }
        .items-table .description {
            text-align: left;
            white-space: pre-wrap; /* This preserves newlines and wraps text */
            word-break: break-word;   /* This breaks long words nicely */
        }
        .right-align { text-align: right; }
        strong { font-weight: bold; }

        /* Styles for the new header */
        .company-header { text-align: center; margin-bottom: 10px; }
        .company-header h2 { margin: 0; font-size: 22px; text-decoration: underline; }
        .company-header h3 { margin: 5px 0; font-size: 16px; font-weight: normal; }
        .company-header p { margin: 2px 0; font-size: 12px; }
        .company-header span { display: block; }
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
            display: none;
        }
        .logo-container img {
            max-width: 55%;
            height: auto;
        }
        .company-details-container {
            flex: 1; /* Grow to fill remaining space */
        }
        .company-details-container h2 { margin: 0; font-size: 24px; color:#3700B3; 
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
        }
        .company-details-container p { margin: 2px 0; color:#B82B00;}
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="document-title">DELIVERY CHALLAN</div>

        <div class="header-flex-container">
            <div class="logo-container">
                <img src="../logoe.jpg" alt="Company Logo">
            </div>
            <div class="company-details-container">
                <h2><?php echo htmlspecialchars(strtoupper($challanData['company_name'])); ?></h2>
                <p><?php echo nl2br(htmlspecialchars($challanData['company_addr'])); ?></p>
                <p>
                (M) <?php echo htmlspecialchars($challanData['contact_no']); ?>
                Email: <?php echo htmlspecialchars($challanData['company_email']); ?>
            </p>
            </div>
        </div>
    
        <table class="main-table">
            <tr>
                <td rowspan="3" style="width: 50%;">
                    <strong>Party's Name:</strong> <?php echo htmlspecialchars($challanData['party_name']); ?><br>
                    <strong>Address:</strong> <?php echo nl2br(htmlspecialchars($challanData['billing_addr'])); ?><br>
                    <strong>GSTIN:</strong> <?php echo htmlspecialchars($challanData['party_gstin']); ?>
                </td>
                <td style="width: 25%;">
                    <strong>Challan No:</strong><br>
                    <?php echo htmlspecialchars($challanData['challan_no']); ?>
                </td>
                <td style="width: 25%;">
                    <strong>Date:</strong><br>
                    <?php echo date("d/m/Y", strtotime($challanData['challan_date'])); ?>
                </td>
            </tr>
            
            <tr>
                <td colspan="2">
                    <strong>Ship To:</strong><br>
                    <?php echo nl2br(htmlspecialchars($challanData['shipping_addr'])); ?>
                </td>
                
             
            </tr>

        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:5%;">No.</th>
                    <th style="width:20%;">Description of Goods</th>
                    <th style="width:40%;">Product Description/B.No</th>
                    <th>HSN</th>
                    <th>Qty./No. of Size</th>
                    <th>Po.Sr.No.</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $x = 1;
                $grandTotal = 0;
                while($item = $itemsResult->fetch_assoc()): 
                    $amount = (float)$item['quantity'] * (float)$item['rate'];
                    $grandTotal += $amount;
                ?>
                <tr>
                    <td><?php echo $x++; ?></td>
                    <td class="description"><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td class="description"><?php echo htmlspecialchars($item['item_remarks']); ?></td>
                    <td><?php echo htmlspecialchars($item['hsn']); ?></td>
                    <td class='center'><?php echo htmlspecialchars(!empty($item['size']) ? $item['quantity']."/".$item['size'] : $item['quantity']); ?></td>
                    <td><?php echo $x - 1; /* Placeholder for PO Sr. No. */ ?></td>
                </tr>
                <?php endwhile; ?>
                
                <?php 
                // Fill up to 10 rows if there are fewer items
                for ($i = $x; $i <= 10; $i++) { ?>
                <tr>
                    <td>&nbsp;</td> <td></td> <td></td> <td></td> <td></td> <td></td>
                </tr>
                <?php } ?>

                <!-- <tr style="font-weight: bold;">
                    <td colspan="6" class="right-align">Total</td>
                    <td class="right-align"><?php echo number_format($grandTotal, 2); ?></td>
                </tr> -->
            </tbody>
        </table>
        
        <div style="margin-top: 15px; border: 1px solid black; padding: 5px; min-height: 40px;">
            <strong>Remarks:</strong>
            <p style="margin: 0;"><?php echo nl2br(htmlspecialchars($challanData['remarks'])); ?></p>
            
        </div>

        <table style="margin-top: 20px;">
            <tr>
                <td style="border: none; width: 60%; vertical-align: bottom; text-align: left;">
                    <br><br><br>
                    Receiver's Signature
                </td>
                <td style="border: none; width: 40%; text-align: center;">
                    <strong>For <?php echo htmlspecialchars($challanData['company_name']); ?></strong><br><br><br><br>
                    Authorised Signatory
                </td>
            </tr>
        </table>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Print Challan</button>
        <button onclick="window.close()">Close</button>
    </div>
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