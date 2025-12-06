<?php
require_once 'core.php';

if ($_POST) {
    $partyId = $_POST['partyId'];
    $startDate = date('Y-m-d', strtotime($_POST['startDate']));
    $endDate = date('Y-m-d', strtotime($_POST['endDate']));

    // Fetch Company Info for the Report Header
    $companySql = "SELECT company_name, company_addr FROM users WHERE user_id = ?";
    $stmtComp = $connect->prepare($companySql);
    $stmtComp->bind_param("i", $_SESSION['userId']);
    $stmtComp->execute();
    $companyData = $stmtComp->get_result()->fetch_assoc();
    $stmtComp->close();
    
    $reportHtml = '';

    // --- LOGIC FOR SINGLE PARTY DETAILED REPORT ---
    if ($partyId != 'all') {
        $partyId = (int)$partyId;
        
        $partySql = "SELECT name, billing_addr FROM partys WHERE id = ?";
        $stmtParty = $connect->prepare($partySql);
        $stmtParty->bind_param("i", $partyId);
        $stmtParty->execute();
        $partyData = $stmtParty->get_result()->fetch_assoc();
        $stmtParty->close();
        
        // 1. Calculate Opening Balance
        $openingBalanceSql = "SELECT balance FROM party_ledger WHERE party_id = ? AND DATE(transaction_date) < ? ORDER BY id DESC LIMIT 1";
        $stmtOpening = $connect->prepare($openingBalanceSql);
        $stmtOpening->bind_param("is", $partyId, $startDate);
        $stmtOpening->execute();
        $resultOpening = $stmtOpening->get_result();
        $openingBalance = (float)($resultOpening->fetch_assoc()['balance'] ?? 0.00);
        $stmtOpening->close();

        // 2. Fetch Transactions within the date range
        $ledgerSql = "SELECT transaction_date, transaction_type, description, debit, credit FROM party_ledger WHERE party_id = ? AND DATE(transaction_date) BETWEEN ? AND ? ORDER BY id ASC";
        $stmtLedger = $connect->prepare($ledgerSql);
        $stmtLedger->bind_param("iss", $partyId, $startDate, $endDate);
        $stmtLedger->execute();
        $result = $stmtLedger->get_result();

        $reportHtml .= "<h2>Ledger Statement</h2><h3>For: ".htmlspecialchars($partyData['name'])."</h3>";
        $reportHtml .= "<table><thead><tr><th>Date</th><th>Type</th><th>Description</th><th class='text-right'>Debit</th><th class='text-right'>Credit</th><th class='text-right'>Balance</th></tr></thead><tbody>";
        $reportHtml .= "<tr style='font-weight:bold;'><td colspan='5'>Opening Balance</td><td class='text-right'>".number_format($openingBalance, 2)."</td></tr>";
        
        $runningBalance = $openingBalance;
        // ## ADDED: Initialize Period Totals ##
        $periodDebit = 0;
        $periodCredit = 0;

        while($row = $result->fetch_assoc()) {
            $runningBalance += (float)$row['debit'] - (float)$row['credit'];
            // ## ADDED: Sum up the totals for the period ##
            $periodDebit += (float)$row['debit'];
            $periodCredit += (float)$row['credit'];

            $reportHtml .= "<tr>
                <td>".date("d-m-Y", strtotime($row['transaction_date']))."</td>
                <td>".htmlspecialchars($row['transaction_type'])."</td>
                <td>".htmlspecialchars($row['description'])."</td>
                <td class='text-right'>".number_format($row['debit'], 2)."</td>
                <td class='text-right'>".number_format($row['credit'], 2)."</td>
                <td class='text-right'>".number_format($runningBalance, 2)."</td>
            </tr>";
        }
        $reportHtml .= "</tbody>";
        
        // ## ADDED: New Footer with Period Totals ##
        $reportHtml .= "<tfoot>
            <tr style='font-weight:bold;'>
                <td colspan='3' class='text-right'>Period Totals</td>
                <td class='text-right'>".number_format($periodDebit, 2)."</td>
                <td class='text-right'>".number_format($periodCredit, 2)."</td>
                <td colspan='1'></td>
            </tr>
            <tr style='font-weight:bold; background-color:#f8f8f8;'>
                <td colspan='5' class='text-right'>Closing Balance</td>
                <td class='text-right'>".number_format($runningBalance, 2)."</td>
            </tr>
        </tfoot>";

        $reportHtml .= "</table>";
    } 
    // --- LOGIC FOR ALL PARTIES SUMMARY REPORT ---
    else {
        // This single, efficient query calculates everything needed for the summary
        $sql = "SELECT
                    p.name AS party_name,
                    COALESCE((SELECT balance FROM party_ledger WHERE party_id = p.id AND DATE(transaction_date) < ? ORDER BY id DESC LIMIT 1), 0) AS opening_balance,
                    COALESCE((SELECT SUM(debit) FROM party_ledger WHERE party_id = p.id AND DATE(transaction_date) BETWEEN ? AND ?), 0) AS total_debit,
                    COALESCE((SELECT SUM(credit) FROM party_ledger WHERE party_id = p.id AND DATE(transaction_date) BETWEEN ? AND ?), 0) AS total_credit,
                    (SELECT MAX(DATE(transaction_date)) FROM party_ledger WHERE party_id = p.id AND DATE(transaction_date) <= ?) AS last_transaction_date
                FROM partys p
                ORDER BY p.name ASC";
        
        $stmt = $connect->prepare($sql);
        // Bind start and end dates to the multiple placeholders
        $stmt->bind_param("ssssss", $startDate, $startDate, $endDate, $startDate, $endDate, $endDate);
        $stmt->execute();
        $result = $stmt->get_result();

        $reportHtml .= "<h2>All Parties Ledger Summary</h2>";
        $reportHtml .= "<table><thead><tr>
                            <th>Party Name</th>
                            <th class='text-right'>Opening Balance</th>
                            <th class='text-right'>Debit (Sales)</th>
                            <th class='text-right'>Credit (Payments)</th>
                            <th class='text-right'>Closing Balance</th>
                            <th class='text-center'>Last Transaction</th>
                        </tr></thead><tbody>";

        // ## ADDED: Initialize Grand Totals ##
        $grandTotalOpening = 0;
        $grandTotalDebit = 0;
        $grandTotalCredit = 0;
        $grandTotalClosing = 0;

        while($row = $result->fetch_assoc()) {
            $closing_balance = $row['opening_balance'] + $row['total_debit'] - $row['total_credit'];
            $reportHtml .= "<tr>
                                <td>".htmlspecialchars($row['party_name'])."</td>
                                <td class='text-right'>".number_format($row['opening_balance'], 2)."</td>
                                <td class='text-right'>".number_format($row['total_debit'], 2)."</td>
                                <td class='text-right'>".number_format($row['total_credit'], 2)."</td>
                                <td class='text-right'>".number_format($closing_balance, 2)."</td>
                                <td class='text-center'>".($row['last_transaction_date'] ? date("d-m-Y", strtotime($row['last_transaction_date'])) : 'N/A')."</td>
                           </tr>";
            // ## ADDED: Sum up the totals for each party ##
            $grandTotalOpening += $row['opening_balance'];
            $grandTotalDebit += $row['total_debit'];
            $grandTotalCredit += $row['total_credit'];
            $grandTotalClosing += $closing_balance;
        }
        $reportHtml .= '</tbody>';

        // ## ADDED: Grand Total Footer Row ##
        $reportHtml .= '<tfoot><tr style="font-weight:bold; background-color:#f2f2f2;">
                            <td>Grand Totals</td>
                            <td class="text-right">'.number_format($grandTotalOpening, 2).'</td>
                            <td class="text-right">'.number_format($grandTotalDebit, 2).'</td>
                            <td class="text-right">'.number_format($grandTotalCredit, 2).'</td>
                            <td class="text-right">'.number_format($grandTotalClosing, 2).'</td>
                        </tr></tfoot>';
        
        $reportHtml .= '</table>';
    }
    $connect->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ledger Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
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
            <p><strong>Report Date Range:</strong> <?php echo date("d-m-Y", strtotime($_POST['startDate'])); ?> to <?php echo date("d-m-Y", strtotime($_POST['endDate'])); ?></p>
        </div>
        <?php echo $reportHtml; ?>
    </div>
</body>
</html>