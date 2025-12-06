<?php 
require_once 'core.php';

// UPDATED: Query now selects the new 'chart_of_account_id'
$sql = "SELECT account_id, bank_name, account_holder_name, account_number, ifsc_code, is_default, chart_of_account_id
        FROM bank_accounts";
$result = $connect->query($sql);
$output = array('data' => array());
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $accountId = $row['account_id']; // This is the bank's primary key (e.g., 1, 2, 3)
        $chartOfAccountId = $row['chart_of_account_id']; // This is the key for the ledger (e.g., 15, 16, 17)
        
        $isDefault = ($row['is_default'] == 1) ? "<label class='label label-success'>Yes</label>" : "<label class='label label-default'>No</label>";
        
        // UPDATED: "View Statement" link now uses the correct chart_of_account_id
        // "Edit" and "Remove" buttons still use the bank's main account_id
        $button = '<div class="btn-group"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action <span class="caret"></span></button><ul class="dropdown-menu">
            <li><a href="bank_statement.php?id='.$chartOfAccountId.'" "><i class="glyphicon glyphicon-list-alt"></i> View Statement</a></li>
            <li><a type="button" data-toggle="modal" data-target="#editAccountModal" onclick="editAccount('.$accountId.')"><i class="glyphicon glyphicon-edit"></i> Edit</a></li>
            <li><a type="button" data-toggle="modal" data-target="#removeAccountModal" onclick="removeAccount('.$accountId.')"><i class="glyphicon glyphicon-trash"></i> Remove</a></li>
            </ul></div>';
            
        $output['data'][] = array($row['bank_name'], $row['account_holder_name'], $row['account_number'], $row['ifsc_code'], $isDefault, $button);
    }
}
$connect->close();
echo json_encode($output);
?>