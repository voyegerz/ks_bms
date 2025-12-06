<?php
require_once 'core.php';

if ($_POST) {
    $partyId = $_POST['party_id'];

    $sql = "SELECT transaction_date, transaction_type, description, order_id, debit, credit, balance 
            FROM party_ledger WHERE party_id = ? ORDER BY id ASC";
    
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $partyId);
    $stmt->execute();
    $result = $stmt->get_result();

    $table = '<table class="table table-bordered table-striped">';
    $table .= '<thead><tr>
                <th>Date</th>
                <th>Trans. Type</th>
                <th>Description</th>
                <th class="text-right">Debit</th>
                <th class="text-right">Credit</th>
                <th class="text-right">Balance</th>
              </tr></thead>';
    $table .= '<tbody>';

    $finalBalance = 0;
    if($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $table .= '<tr>';
            $table .= '<td>'.date("d-m-Y", strtotime($row['transaction_date'])).'</td>';
            $table .= '<td>'.htmlspecialchars($row['transaction_type']).'</td>';
            $table .= '<td>'.htmlspecialchars($row['description']).'</td>';
            $table .= '<td class="text-right">'.number_format($row['debit'], 2).'</td>';
            $table .= '<td class="text-right">'.number_format($row['credit'], 2).'</td>';
            $table .= '<td class="text-right">'.number_format($row['balance'], 2).'</td>';
            $table .= '</tr>';
            $finalBalance = $row['balance'];
        }
    } else {
        $table .= '<tr><td colspan="5" class="text-center">No transactions found for this party.</td></tr>';
    }

    $table .= '</tbody>';
    $table .= '<tfoot><tr style="font-weight:bold; background-color:#f8f8f8;"><td colspan="4" class="text-right">Closing Balance</td><td class="text-right">'.number_format($finalBalance, 2).'</td></tr></tfoot>';
    $table .= '</table>';

    $stmt->close();
    $connect->close();
    echo $table;
}
?>