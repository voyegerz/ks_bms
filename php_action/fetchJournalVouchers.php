<?php 
require_once 'core.php';
$sql = "SELECT jv.voucher_id, jv.voucher_no, jv.voucher_date, jv.description, 
               MAX(jel.debit) as amount 
        FROM journal_vouchers jv
        JOIN journal_entry_lines jel ON jv.voucher_id = jel.voucher_id
        GROUP BY jv.voucher_id
        ORDER BY jv.voucher_id DESC";
$result = $connect->query($sql);
$output = array('data' => array());
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $voucherId = $row['voucher_id'];
        $button = '<div class="btn-group"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action <span class="caret"></span></button><ul class="dropdown-menu">
            <li><a type="button" data-toggle="modal" data-target="#editEntryModal" onclick="editEntry('.$voucherId.')"><i class="glyphicon glyphicon-edit"></i> Edit</a></li>
            <li><a type="button" data-toggle="modal" data-target="#removeEntryModal" onclick="removeEntry('.$voucherId.')"><i class="glyphicon glyphicon-trash"></i> Remove</a></li></ul></div>';
        $output['data'][] = array(
            $row['voucher_no'],
            date("d-m-Y", strtotime($row['voucher_date'])),
            $row['description'],
            number_format($row['amount'], 2),
            $button
        );
    }
}
$connect->close();
echo json_encode($output);
?>