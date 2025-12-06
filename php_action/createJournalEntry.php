<?php 
require_once 'core.php';
$valid['success'] = false; $valid['messages'] = array();

// --- HELPER FUNCTIONS (Moved to top for use) ---
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
// --- END HELPER FUNCTIONS ---


if ($_POST) {
    $voucherNo = $_POST['voucherNo'];
    $entryDate = date('Y-m-d', strtotime($_POST['entryDate']));
    $description = $_POST['description'];
    $debitAccountId = $_POST['debitAccount'];
    $creditAccountId = $_POST['creditAccount'];
    $amount = (float)$_POST['amount'];
    $userId = $_SESSION['userId'];

    // --- NEW VALIDATION LOGIC ---
    if (empty($amount) || $amount <= 0) {
        $valid['messages'] = "Amount must be greater than 0.";
    } elseif (empty($debitAccountId) && empty($creditAccountId)) {
        $valid['messages'] = "You must select at least one account (either Debit or Credit).";
    } elseif ($debitAccountId == $creditAccountId) {
        $valid['messages'] = "Debit and Credit accounts cannot be the same.";
    } else {
        // --- ALL VALIDATION PASSED, START TRANSACTION ---
        $connect->begin_transaction();
        try {
            // Get the ID for "Cash on Hand" to use as the default
            $cashAccountSql = "SELECT account_id FROM chart_of_accounts WHERE account_name = 'Cash on Hand' LIMIT 1";
            $cashResult = $connect->query($cashAccountSql);
            if ($cashResult->num_rows == 0) {
                throw new Exception("Critical Error: 'Cash on Hand' account not found in Chart of Accounts.");
            }
            $cashAccountId = $cashResult->fetch_assoc()['account_id'];

            // --- Determine the final Debit and Credit IDs ---
            if (!empty($debitAccountId) && !empty($creditAccountId)) {
                // SCENARIO 1: Both provided (Transfer)
                $finalDebitId = $debitAccountId;
                $finalCreditId = $creditAccountId;
            } elseif (!empty($debitAccountId) && empty($creditAccountId)) {
                // SCENARIO 2: Only Debit provided (e.g., Payment)
                $finalDebitId = $debitAccountId;
                $finalCreditId = $cashAccountId;
            } elseif (empty($debitAccountId) && !empty($creditAccountId)) {
                // SCENARIO 3: Only Credit provided (e.g., Receipt)
                $finalDebitId = $cashAccountId;
                $finalCreditId = $creditAccountId;
            }

            // 1. Create the main voucher
            $sqlVoucher = "INSERT INTO journal_vouchers (voucher_no, voucher_date, description, user_id) VALUES (?, ?, ?, ?)";
            $stmtVoucher = $connect->prepare($sqlVoucher);
            $stmtVoucher->bind_param("issi", $voucherNo, $entryDate, $description, $userId);
            $stmtVoucher->execute();
            $voucher_id = $stmtVoucher->insert_id;
            
            // 2. Create the Debit entry
            $sqlDebit = "INSERT INTO journal_entry_lines (voucher_id, account_id, debit) VALUES (?, ?, ?)";
            $stmtDebit = $connect->prepare($sqlDebit);
            $stmtDebit->bind_param("iid", $voucher_id, $finalDebitId, $amount);
            $stmtDebit->execute();
            
            // 3. Create the Credit entry
            $sqlCredit = "INSERT INTO journal_entry_lines (voucher_id, account_id, credit) VALUES (?, ?, ?)";
            $stmtCredit = $connect->prepare($sqlCredit);
            $stmtCredit->bind_param("iid", $voucher_id, $finalCreditId, $amount);
            $stmtCredit->execute();
            
            // 4. Update Party Ledgers if necessary
            $debitPartyId = getPartyIdFromAccountId($connect, $finalDebitId);
            if ($debitPartyId) {
                updatePartyLedger($connect, $debitPartyId, $description, $voucher_id, $amount, 0);
            }
            $creditPartyId = getPartyIdFromAccountId($connect, $finalCreditId);
            if ($creditPartyId) {
                updatePartyLedger($connect, $creditPartyId, $description, $voucher_id, 0, $amount);
            }
            
            $connect->commit();
            $valid['success'] = true;
            $valid['messages'] = "Journal Entry Successfully Added";
            
        } catch (Exception $e) {
            $connect->rollback();
            $valid['success'] = false;
            $valid['messages'] = "Error: " . $e->getMessage();
        }
    }
    $connect->close();
    echo json_encode($valid);
}
?>