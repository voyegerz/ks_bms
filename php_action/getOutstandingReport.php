<?php
require_once 'core.php';

// Check if a date was submitted
if (empty($_POST['endDate'])) {
    die("Please select an 'As of Date' from the reports page.");
}
$partyId = $_POST['partyId'];
$endDate = date('Y-m-d', strtotime($_POST['endDate']));

// Fetch Company Info for the Report Header
$companySql = "SELECT company_name, company_addr FROM users WHERE user_id = ?";
$stmtComp = $connect->prepare($companySql);
$stmtComp->bind_param("i", $_SESSION['userId']);
$stmtComp->execute();
$companyData = $stmtComp->get_result()->fetch_assoc();
$stmtComp->close();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Outstanding Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .report-container { width: 100%; margin: 0 auto; }
        .report-header { text-align: center; margin-bottom: 20px; }
        h1, h2, p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="report-header">
            <h1><?php echo htmlspecialchars($companyData['company_name']); ?></h1>
            <p><?php echo nl2br(htmlspecialchars($companyData['company_addr'])); ?></p>
        </div>

        <?php if ($partyId != 'all'): 
            // --- DETAILED REPORT FOR A SINGLE PARTY ---
            $partyId = (int)$partyId;
            $partySql = "SELECT name FROM partys WHERE id = ?";
            $stmtParty = $connect->prepare($partySql);
            $stmtParty->bind_param("i", $partyId);
            $stmtParty->execute();
            $partyName = $stmtParty->get_result()->fetch_assoc()['name'];
        ?>
            <h2>Outstanding Invoices for: <?php echo htmlspecialchars($partyName); ?></h2>
            <p><strong>As of Date:</strong> <?php echo htmlspecialchars($_POST['endDate']); ?></p>
            <table>
                <thead><tr>
                    <th class="text-center">Invoice #</th>
                    <th class="text-center">Invoice Date</th>
                    <th class="text-right">Invoice Amt</th>
                    <th class="text-right">Paid Amt</th>
                    <th class="text-right">Outstanding</th>
                    <th class="text-center">Age (Days)</th>
                </tr></thead>
                <tbody>
                <?php
                $sql = "SELECT 
                        order_id, 
                        order_date, 
                        COALESCE(NULLIF(grand_total, ''), 0) AS grand_total, 
                        COALESCE(NULLIF(paid, ''), 0) AS paid, 
                        COALESCE(NULLIF(due, ''), 0) AS due, 
                        DATEDIFF(?, order_date) AS age_in_days
                    FROM orders 
                    WHERE party_id = ? AND due > 0.01 AND order_date <= ?";
                $stmt = $connect->prepare($sql);
                // CORRECTED: bind_param type for partyId is 'i' (integer)
                $stmt->bind_param("sis", $endDate, $partyId, $endDate);
                $stmt->execute();
                $result = $stmt->get_result();
                $totalDue = 0;
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td class='text-center'>{$row['order_id']}</td>
                        <td class='text-center'>".date("d-m-Y", strtotime($row['order_date']))."</td>
                        <td class='text-right'>".number_format($row['grand_total'], 2)."</td>
                        <td class='text-right'>".number_format($row['paid'], 2)."</td>
                        <td class='text-right'>".number_format($row['due'], 2)."</td>
                        <td class='text-center'>{$row['age_in_days']}</td>
                    </tr>";
                    $totalDue += (float)$row['due'];
                }
                ?>
                </tbody>
                </table>

        <?php else: 
            // --- SUMMARY REPORT FOR ALL PARTIES ---
            $sql = "SELECT p.name, COUNT(o.order_id) as total_invoices, SUM(o.grand_total) as total_invoiced, SUM(o.paid) as total_paid, SUM(o.due) as outstanding
                    FROM orders o JOIN partys p ON o.party_id = p.id
                    WHERE o.due > 0.01 AND o.order_date <= ?
                    GROUP BY o.party_id ORDER BY outstanding DESC";
            $stmt = $connect->prepare($sql);
            $stmt->bind_param("s", $endDate);
            $stmt->execute();
            $result = $stmt->get_result();
        ?>
            <h2>All Parties Outstanding Summary</h2>
            <p><strong>As of Date:</strong> <?php echo htmlspecialchars($_POST['endDate']); ?></p>
            <table>
                <thead><tr>
                    <th>Party Name</th>
                    <th class="text-center">Total Invoices</th>
                    <th class="text-right">Total Invoiced</th>
                    <th class="text-right">Total Paid</th>
                    <th class="text-right">Outstanding</th>
                </tr></thead>
                <tbody>
                <?php 
                $grandTotalOutstanding = 0;
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>".htmlspecialchars($row['name'])."</td>
                        <td class='text-center'>{$row['total_invoices']}</td>
                        <td class='text-right'>".number_format($row['total_invoiced'], 2)."</td>
                        <td class='text-right'>".number_format($row['total_paid'], 2)."</td>
                        <td class='text-right'>".number_format($row['outstanding'], 2)."</td>
                    </tr>";
                    $grandTotalOutstanding += (float)$row['outstanding'];
                }
                ?>
                </tbody>
                <tfoot style="font-weight:bold;"><td colspan="4" class="text-right">Grand Total Outstanding</td><td class="text-right"><?php echo number_format($grandTotalOutstanding, 2); ?></td></tfoot>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>