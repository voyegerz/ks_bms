<?php 
require_once 'core.php';
$valid['success'] = false; $valid['messages'] = array();

if ($_POST) {
    $voucherId = $_POST['voucherId'];
    $voucherNo = $_POST['editVoucherNo'];
    $entryDate = date('Y-m-d', strtotime($_POST['editEntryDate']));
    $description = $_POST['editDescription'];
    $newDebitAccountId = (int)$_POST['editDebitAccount'];
    $newCreditAccountId = (int)$_POST['editCreditAccount'];
    $newAmount = (float)$_POST['editAmount'];

    $connect->begin_transaction();
    try {
        // --- Step 1: Check if the new voucher_no is a duplicate ---
        // Check if another voucher (not this one) is already using the new number
        $checkSql = "SELECT voucher_id FROM journal_vouchers WHERE voucher_no = ? AND voucher_id != ?";
        $stmtCheck = $connect->prepare($checkSql);
        $stmtCheck->bind_param("ii", $voucherNo, $voucherId);
        $stmtCheck->execute();
        if ($stmtCheck->get_result()->num_rows > 0) {
            throw new Exception("Voucher No. {$voucherNo} already exists.");
        }
        $stmtCheck->close();

        // --- Step 1: Get OLD entry details BEFORE making changes ---
        $oldDebitAccountId = 0;
        $oldCreditAccountId = 0;
        $oldAmount = 0;
        
        $sqlLines = "SELECT account_id, debit, credit FROM journal_entry_lines WHERE voucher_id = ?";
        $stmtLines = $connect->prepare($sqlLines);
        $stmtLines->bind_param("i", $voucherId);
        $stmtLines->execute();
        $lines = $stmtLines->get_result();
        while ($row = $lines->fetch_assoc()) {
            if ($row['debit'] > 0) {
                $oldDebitAccountId = (int)$row['account_id'];
                $oldAmount = (float)$row['debit'];
            }
            if ($row['credit'] > 0) {
                $oldCreditAccountId = (int)$row['account_id'];
            }
        }
        $stmtLines->close();

        // --- Step 2: Update the main voucher ---
        $sqlVoucher = "UPDATE journal_vouchers SET voucher_no = ?, voucher_date = ?, description = ? WHERE voucher_id = ?";
        $stmtVoucher = $connect->prepare($sqlVoucher);
        $stmtVoucher->bind_param("issi", $voucherNo, $entryDate, $description, $voucherId);
        $stmtVoucher->execute();

        // --- Step 3: Delete old lines ---
        $sqlDelete = "DELETE FROM journal_entry_lines WHERE voucher_id = ?";
        $stmtDelete = $connect->prepare($sqlDelete);
        $stmtDelete->bind_param("i", $voucherId);
        $stmtDelete->execute();
        
        // --- Step 4: Create new journal lines ---
        $sqlDebit = "INSERT INTO journal_entry_lines (voucher_id, account_id, debit) VALUES (?, ?, ?)";
        $stmtDebit = $connect->prepare($sqlDebit);
        $stmtDebit->bind_param("iid", $voucherId, $newDebitAccountId, $newAmount);
        $stmtDebit->execute();
        
        $sqlCredit = "INSERT INTO journal_entry_lines (voucher_id, account_id, credit) VALUES (?, ?, ?)";
        $stmtCredit = $connect->prepare($sqlCredit);
        $stmtCredit->bind_param("iid", $voucherId, $newCreditAccountId, $newAmount);
        $stmtCredit->execute();

        // --- Step 5: Update Party Ledgers ---
        $reversalDesc = "Reversal for Voucher #" . $voucherId . " (Edit)";
        $newDesc = $description;

        // a) Reverse old DEBIT to party (if it was one)
        $oldDebitPartyId = getPartyIdFromAccountId($connect, $oldDebitAccountId);
        if ($oldDebitPartyId) {
            updatePartyLedger($connect, $oldDebitPartyId, $reversalDesc, $voucherId, 0, $oldAmount);
        }
        // b) Reverse old CREDIT to party (if it was one)
        $oldCreditPartyId = getPartyIdFromAccountId($connect, $oldCreditAccountId);
        if ($oldCreditPartyId) {
            updatePartyLedger($connect, $oldCreditPartyId, $reversalDesc, $voucherId, $oldAmount, 0);
        }
        // c) Add new DEBIT to party (if it is one)
        $newDebitPartyId = getPartyIdFromAccountId($connect, $newDebitAccountId);
        if ($newDebitPartyId) {
            updatePartyLedger($connect, $newDebitPartyId, $newDesc, $voucherId, $newAmount, 0);
        }
        // d) Add new CREDIT to party (if it is one)
        $newCreditPartyId = getPartyIdFromAccountId($connect, $newCreditAccountId);
        if ($newCreditPartyId) {
            updatePartyLedger($connect, $newCreditPartyId, $newDesc, $voucherId, 0, $newAmount);
        }
        
        $connect->commit();
        $valid['success'] = true;
        $valid['messages'] = "Successfully Updated";
        
    } catch (Exception $e) {
        $connect->rollback();
        $valid['success'] = false;
        $valid['messages'] = "Error: " . $e->getMessage();
    }
    $connect->close();
    echo json_encode($valid);
}

// --- HELPER FUNCTIONS (Copied from createJournalEntry.php) ---
function getPartyIdFromAccountId($connect, $accountId) {
    if ($accountId == 0) return null;
    $sql = "SELECT party_id FROM chart_of_accounts WHERE account_id = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $accountId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['party_id'] ?? null;
}

function updatePartyLedger($connect, $partyId, $description, $voucher_id, $debit, $credit) {
    $lastBalanceSql = "SELECT balance FROM party_ledger WHERE party_id = ? ORDER BY id DESC LIMIT 1";
    $stmtBalance = $connect->prepare($lastBalanceSql);
    $stmtBalance->bind_param("i", $partyId);
    $stmtBalance->execute();
    $lastBalance = (float)($stmtBalance->get_result()->fetch_assoc()['balance'] ?? 0.00);
    $stmtBalance->close();

    $newBalance = $lastBalance + $debit - $credit;
    
    $transactionType = ($debit > 0) ? 'Debit Note' : 'Credit Note';
    $ledgerSql = "INSERT INTO party_ledger (party_id, description, transaction_type, order_id, debit, credit, balance) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmtLedger = $connect->prepare($ledgerSql);
    $stmtLedger->bind_param("issidds", $partyId, $description, $transactionType, $voucher_id, $debit, $credit, $newBalance);
    $stmtLedger->execute();
    $stmtLedger->close();
}
?>