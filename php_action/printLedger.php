<?php
require_once 'core.php';

$partyId = $_GET['party_id'] ?? 0;
if (!$partyId) {
    die("Party ID not specified.");
}

// Fetch company, party, and ledger data
$companySql = "SELECT company_name, company_addr FROM users WHERE user_id = ?";
$stmtComp = $connect->prepare($companySql);
$stmtComp->bind_param("i", $_SESSION['userId']);
$stmtComp->execute();
$companyData = $stmtComp->get_result()->fetch_assoc();
$stmtComp->close();

$partySql = "SELECT name, billing_addr FROM partys WHERE id = ?";
$stmtParty = $connect->prepare($partySql);
$stmtParty->bind_param("i", $partyId);
$stmtParty->execute();
$partyData = $stmtParty->get_result()->fetch_assoc();
$stmtParty->close();

$ledgerSql = "SELECT transaction_date, transaction_type, description, order_id, debit, credit, balance FROM party_ledger WHERE party_id = ? ORDER BY id ASC";
$stmtLedger = $connect->prepare($ledgerSql);
$stmtLedger->bind_param("i", $partyId);
$stmtLedger->execute();
$ledgerResult = $stmtLedger->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ledger Statement - <?php echo htmlspecialchars($partyData['name']); ?></title>
    <style>
        @page { size: A4; margin: 15mm; }
        @media print { body { -webkit-print-color-adjust: exact; } .no-print { display: none; } }
        body { font-family: Arial, sans-serif; font-size: 13px; }
        .report-container { width: 100%; }
        .report-header { text-align: center; margin-bottom: 20px; }
        h1, h2, h3, p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="report-header">
            <h1><?php echo htmlspecialchars($companyData['company_name']); ?></h1>
            <p><?php echo nl2br(htmlspecialchars($companyData['company_addr'])); ?></p>
            <h2>Ledger Statement</h2>
            <h3>For: <?php echo htmlspecialchars($partyData['name']); ?></h3>
            <p>As of: <?php echo date("d-m-Y"); ?></p>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                    <th class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $finalBalance = 0;
                if ($ledgerResult->num_rows > 0):
                    while($row = $ledgerResult->fetch_assoc()): 
                        $finalBalance = $row['balance'];
                ?>
                <tr>
                    <td><?php echo date("d-m-Y", strtotime($row['transaction_date'])); ?></td>
                    <td><?php echo htmlspecialchars($row['transaction_type']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td class="text-right"><?php echo number_format((float)$row['debit'], 2); ?></td>
                    <td class="text-right"><?php echo number_format((float)$row['credit'], 2); ?></td>
                    <td class="text-right"><?php echo number_format((float)$row['balance'], 2); ?></td>
                </tr>
                <?php 
                    endwhile;
                else: ?>
                    <tr><td colspan="6" style="text-align:center;">No transactions found.</td></tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background-color: #f2f2f2;">
                    <td colspan="5" class="text-right">Closing Balance</td>
                    <td class="text-right"><?php echo number_format((float)$finalBalance, 2); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="no-print" style="text-align:center; margin-top:20px;">
        <button onclick="window.print()">Print</button>
    </div>
</body>
</html>