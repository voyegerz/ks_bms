<?php 
require_once 'core.php';

if($_POST) {

    // Get and format the start and end dates
    $startDate = $_POST['startDate'];
    $date = DateTime::createFromFormat('m/d/Y', $startDate);
    $start_date = $date->format("Y-m-d");

    $endDate = $_POST['endDate'];
    $format = DateTime::createFromFormat('m/d/Y', $endDate);
    $end_date = $format->format("Y-m-d");

    // --- Fetch Company Info for the Report Header ---
    $userId = $_SESSION['userId'];
    $userSql = "SELECT company_name, company_addr FROM users WHERE user_id = ?";
    $userStmt = $connect->prepare($userSql);
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $userData = $userStmt->get_result()->fetch_assoc();
    $companyName = $userData['company_name'];
    $companyAddress = $userData['company_addr'];

    // --- Fetch Order Data (Securely with JOIN) ---
    $sql = "SELECT 
                o.order_id,
                o.order_date,
                o.grand_total,
                o.sub_total,
                o.vat,
                o.discount,
                o.paid,
                o.due,
                p.name AS party_name
            FROM orders o
            LEFT JOIN partys p ON o.party_id = p.id
            WHERE o.order_date >= ? AND o.order_date <= ? AND o.order_status = 1
            ORDER BY o.order_date ASC";
    
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $query = $stmt->get_result();

    // --- Start Building the HTML for the Report ---
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Report</title>
    <style type="text/css">
        @page { size: A4 landscape; margin: 10mm; }
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .report-container { width: 100%; margin: 0 auto; }
        .report-header { text-align: center; margin-bottom: 20px; }
        .report-header h1 { margin: 0; font-size: 24px; }
        .report-header p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        tfoot td { font-weight: bold; background-color: #f8f8f8; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="report-header">
            <h1><?php echo htmlspecialchars($companyName); ?></h1>
            <p><?php echo nl2br(htmlspecialchars($companyAddress)); ?></p>
            <h2>Order Report</h2>
            <p><strong>Date Range:</strong> <?php echo htmlspecialchars($startDate); ?> to <?php echo htmlspecialchars($endDate); ?></p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Order #</th>
                    <th class="text-center">Order Date</th>
                    <th>Party Name</th>
                    <th class="text-right">Sub Total</th>
                    <th class="text-right">VAT/GST</th>
                    <th class="text-right">Discount</th>
                    <th class="text-right">Grand Total</th>
                    <th class="text-right">Paid</th>
                    <th class="text-right">Due</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Initialize totals
                $totalSubTotal = 0;
                $totalVat = 0;
                $totalDiscount = 0;
                $totalGrandTotal = 0;
                $totalPaid = 0;
                $totalDue = 0;

                if ($query->num_rows > 0) {
                    while ($result = $query->fetch_assoc()) {
                        echo "<tr>
                            <td>" . htmlspecialchars($result['order_id']) . "</td>
                            <td class='text-center'>" . date("d-m-Y", strtotime($result['order_date'])) . "</td>
                            <td>" . htmlspecialchars($result['party_name']) . "</td>
                            <td class='text-right'>" . number_format($result['sub_total'], 2) . "</td>
                            <td class='text-right'>" . number_format($result['vat'], 2) . "</td>
                            <td class='text-right'>" . number_format($result['discount'], 2) . "</td>
                            <td class='text-right'>" . number_format($result['grand_total'], 2) . "</td>
                            <td class='text-right'>" . number_format($result['paid'], 2) . "</td>
                            <td class='text-right'>" . number_format($result['due'], 2) . "</td>
                        </tr>";
                        
                        // Sum up the totals
                        $totalSubTotal += (float)$result['sub_total'];
                        $totalVat += (float)$result['vat'];
                        $totalDiscount += (float)$result['discount'];
                        $totalGrandTotal += (float)$result['grand_total'];
                        $totalPaid += (float)$result['paid'];
                        $totalDue += (float)$result['due'];
                    }
                } else {
                    echo "<tr><td colspan='9' class='text-center'>No orders found for the selected date range.</td></tr>";
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right"><strong>Totals:</strong></td>
                    <td class="text-right"><?php echo number_format($totalSubTotal, 2); ?></td>
                    <td class="text-right"><?php echo number_format($totalVat, 2); ?></td>
                    <td class="text-right"><?php echo number_format($totalDiscount, 2); ?></td>
                    <td class="text-right"><?php echo number_format($totalGrandTotal, 2); ?></td>
                    <td class="text-right"><?php echo number_format($totalPaid, 2); ?></td>
                    <td class="text-right"><?php echo number_format($totalDue, 2); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>
<?php
}
?>