<?php 
require_once 'php_action/core.php'; 
require_once 'includes/header.php'; 

$accountId = $_GET['id'] ?? 0;
if (!$accountId) {
    echo "Account ID not specified.";
    exit();
}

// Fetch Account & Company Details
$sql = "SELECT ca.account_name, u.company_name, u.company_addr 
        FROM chart_of_accounts ca
        LEFT JOIN users u ON u.user_id = 1
        WHERE ca.account_id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $accountId);
$stmt->execute();
$headerData = $stmt->get_result()->fetch_assoc();

// Fetch Transactions
$sql = "SELECT v.voucher_date, v.description, l.debit, l.credit
        FROM journal_entry_lines l
        JOIN journal_vouchers v ON l.voucher_id = v.voucher_id
        WHERE l.account_id = ?
        ORDER BY v.voucher_date ASC, l.line_id ASC";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $accountId);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="glyphicon glyphicon-list-alt"></i> Account Statement
                <button class="btn btn-default pull-right" onclick="window.print();" style="margin-top:-7px;">
                    <i class="glyphicon glyphicon-print"></i> Print
                </button>
            </div>
            <div class="panel-body">
                <div class="report-header" style="text-align:center; margin-bottom: 20px;">
                    <h1><?php echo htmlspecialchars($headerData['company_name']); ?></h1>
                    <p><?php echo htmlspecialchars($headerData['company_addr']); ?></p>
                    <h2>Account Statement for: <?php echo htmlspecialchars($headerData['account_name']); ?></h2>
                </div>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th class="text-right">Debit</th>
                            <th class="text-right">Credit</th>
                            <th class="text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $balance = 0;
                        if($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $debit = (float)$row['debit'];
                                $credit = (float)$row['credit'];
                                $balance = $balance + $debit - $credit;
                                ?>
                                <tr>
                                    <td><?php echo date("d-m-Y", strtotime($row['voucher_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                                    <td class="text-right"><?php echo number_format($debit, 2); ?></td>
                                    <td class="text-right"><?php echo number_format($credit, 2); ?></td>
                                    <td class="text-right"><?php echo number_format($balance, 2); ?></td>
                                </tr>
                            <?php }
                        } else {
                            echo '<tr><td colspan="5" class="text-center">No transactions found for this account.</td></tr>';
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight:bold; background-color:#f8f8f8;">
                            <td colspan="4" class="text-right">Closing Balance</td>
                            <td class="text-right"><?php echo number_format($balance, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>