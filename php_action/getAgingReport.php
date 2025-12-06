<?php
require_once 'core.php';

if (empty($_POST['endDate'])) {
    die("Please select an 'As of Date' from the reports page.");
}
$endDate = date('Y-m-d', strtotime($_POST['endDate']));

// Fetch Company Info
$companySql = "SELECT company_name, company_addr FROM users WHERE user_id = ?";
$stmtComp = $connect->prepare($companySql);
$stmtComp->bind_param("i", $_SESSION['userId']);
$stmtComp->execute();
$companyData = $stmtComp->get_result()->fetch_assoc();
$stmtComp->close();

// This query gets all orders with an amount still due and calculates their age based on the selected end date
$sql = "SELECT p.name AS party_name, o.due, DATEDIFF(?, o.order_date) AS age_in_days
        FROM orders o
        JOIN partys p ON o.party_id = p.id
        WHERE o.due > 0.01 AND o.order_status = 1 AND o.order_date <= ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("ss", $endDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();

// Process the results in PHP to group by party and age bucket
$agingData = [];
$totals = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0, 'total' => 0];
while($row = $result->fetch_assoc()) {
    $party = $row['party_name'];
    if (!isset($agingData[$party])) {
        $agingData[$party] = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0, 'total' => 0];
    }
    
    $due = (float)$row['due'];
    $age = (int)$row['age_in_days'];
    
    if ($age <= 30) { $agingData[$party]['0-30'] += $due; }
    else if ($age <= 60) { $agingData[$party]['31-60'] += $due; }
    else if ($age <= 90) { $agingData[$party]['61-90'] += $due; }
    else { $agingData[$party]['90+'] += $due; }
    
    $agingData[$party]['total'] += $due;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Accounts Receivable Aging Report</title>
    <style>
        /* (Same CSS as the Outstanding Balances Report) */
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .report-header { text-align: center; margin-bottom: 20px; }
        h1, h2, p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="report-header">
        <h1><?php echo htmlspecialchars($companyData['company_name']); ?></h1>
        <h2>Accounts Receivable Aging Report</h2>
        <p>As of: <?php echo htmlspecialchars($_POST['endDate']); ?></p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Party Name</th>
                <th class="text-right">0-30 Days</th>
                <th class="text-right">31-60 Days</th>
                <th class="text-right">61-90 Days</th>
                <th class="text-right">90+ Days</th>
                <th class="text-right">Total Due</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($agingData)): ?>
                <tr><td colspan="6" style="text-align:center;">No outstanding invoices found.</td></tr>
            <?php else: foreach($agingData as $party => $data): ?>
            <tr>
                <td><?php echo htmlspecialchars($party); ?></td>
                <td class="text-right"><?php echo number_format($data['0-30'], 2); ?></td>
                <td class="text-right"><?php echo number_format($data['31-60'], 2); ?></td>
                <td class="text-right"><?php echo number_format($data['61-90'], 2); ?></td>
                <td class="text-right"><?php echo number_format($data['90+'], 2); ?></td>
                <td class="text-right"><?php echo number_format($data['total'], 2); ?></td>
            </tr>
            <?php 
                // Sum grand totals
                $totals['0-30'] += $data['0-30']; $totals['31-60'] += $data['31-60'];
                $totals['61-90'] += $data['61-90']; $totals['90+'] += $data['90+'];
                $totals['total'] += $data['total'];
            ?>
            <?php endforeach; endif; ?>
        </tbody>
        <tfoot style="font-weight:bold; background-color:#f2f2f2;">
            <tr>
                <td>Grand Totals</td>
                <td class="text-right"><?php echo number_format($totals['0-30'], 2); ?></td>
                <td class="text-right"><?php echo number_format($totals['31-60'], 2); ?></td>
                <td class="text-right"><?php echo number_format($totals['61-90'], 2); ?></td>
                <td class="text-right"><?php echo number_format($totals['90+'], 2); ?></td>
                <td class="text-right"><?php echo number_format($totals['total'], 2); ?></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>