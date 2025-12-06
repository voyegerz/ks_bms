<?php 
require_once 'php_action/db_connect.php'; 
require_once 'includes/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <ol class="breadcrumb">
          <li><a href="dashboard.php">Home</a></li>          
          <li class="active">Journal</li>
        </ol>
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="page-heading"> <i class="glyphicon glyphicon-book"></i> Manage Journal Entries</div>
            </div>
            <div class="panel-body">
                <div class="remove-messages"></div>
                <div class="div-action pull pull-right" style="padding-bottom:20px;">
                    <button class="btn btn-default button1" data-toggle="modal" id="addEntryModalBtn" data-target="#addEntryModal"> <i class="glyphicon glyphicon-plus-sign"></i> Add Journal Entry </button>
                </div>                 
                <table class="table" id="manageJournalTable">
                    <thead>
                        <tr>
                            <th>Voucher No.</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th class="text-right">Amount</th>
                            <th style="width:15%;">Options</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addEntryModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <form class="form-horizontal" id="submitEntryForm" action="php_action/createJournalEntry.php" method="POST">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fa fa-plus"></i> Add Simple Journal Entry</h4>
          </div>
          <div class="modal-body">
            <div id="add-entry-messages"></div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Voucher No.</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="voucherNo" name="voucherNo" />
                </div>
                <label class="col-sm-2 control-label">Date</label>
                <div class="col-sm-4"><input type="text" class="form-control" id="entryDate" name="entryDate" autocomplete="off" /></div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Description</label>
                <div class="col-sm-10"><textarea class="form-control" name="description" rows="3" placeholder="Narration (e.g., Bank fees for September)"></textarea></div>
            </div>
            <hr>
            <div class="form-group">
                <label class="col-sm-6 control-label">Account to Debit (Increase)</label>
                <label class="col-sm-6 control-label">Account to Credit (Decrease)</label>
            </div>
            <div class="form-group">
                <div class="col-sm-6">
                    <select class="form-control" name="debitAccount">
                        <option value="">-- Select Account --</option>
                        <?php $sql = "SELECT account_id, account_name FROM chart_of_accounts WHERE is_active = 1 ORDER BY account_name";
                              $result = $connect->query($sql);
                              while($row = $result->fetch_assoc()) { echo "<option value='".$row['account_id']."'>".$row['account_name']."</option>"; } ?>
                    </select>
                </div>
                <div class="col-sm-6">
                    <select class="form-control" name="creditAccount">
                        <option value="">-- Select Account --</option>
                        <?php $result->data_seek(0); // Reset result pointer
                              while($row = $result->fetch_assoc()) { echo "<option value='".$row['account_id']."'>".$row['account_name']."</option>"; } ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Amount</label>
                <div class="col-sm-10"><input type="number" step="0.01" class="form-control" name="amount" placeholder="0.00" autocomplete="off" /></div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" id="createEntryBtn" data-loading-text="Loading...">Save Changes</button>
          </div>
        </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editEntryModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <form class="form-horizontal" id="editEntryForm" action="php_action/editJournalEntry.php" method="POST">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fa fa-edit"></i> Edit Journal Entry</h4>
          </div>
          <div class="modal-body">
            <div id="edit-entry-messages"></div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Voucher No.</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" id="editVoucherNo" name="editVoucherNo"  />
                </div>
                <label class="col-sm-2 control-label">Date</label>
                <div class="col-sm-4"><input type="text" class="form-control" id="editEntryDate" name="editEntryDate" autocomplete="off" /></div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Description</label>
                <div class="col-sm-10"><textarea class="form-control" id="editDescription" name="editDescription" rows="3"></textarea></div>
            </div>
            <hr>
            <div class="form-group">
                <label class="col-sm-6 control-label">Account to Debit (Increase)</label>
                <label class="col-sm-6 control-label">Account to Credit (Decrease)</label>
            </div>
            <div class="form-group">
                <div class="col-sm-6">
                    <select class="form-control" id="editDebitAccount" name="editDebitAccount">
                        <option value="">-- Select Account --</option>
                        <?php $sql = "SELECT account_id, account_name FROM chart_of_accounts WHERE is_active = 1 ORDER BY account_name";
                              $result = $connect->query($sql);
                              while($row = $result->fetch_assoc()) { echo "<option value='".$row['account_id']."'>".$row['account_name']."</option>"; } ?>
                    </select>
                </div>
                <div class="col-sm-6">
                    <select class="form-control" id="editCreditAccount" name="editCreditAccount">
                        <option value="">-- Select Account --</option>
                        <?php $result->data_seek(0); // Reset result pointer
                              while($row = $result->fetch_assoc()) { echo "<option value='".$row['account_id']."'>".$row['account_name']."</option>"; } ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Amount</label>
                <div class="col-sm-10"><input type="number" step="0.01" class="form-control" id="editAmount" name="editAmount" autocomplete="off" /></div>
            </div>
          </div>
          <div class="modal-footer">
            <input type="hidden" name="voucherId" id="voucherId" />
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" id="editEntryBtn" data-loading-text="Loading...">Save Changes</button>
          </div>
        </form>
    </div>
  </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="removeEntryModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><i class="glyphicon glyphicon-trash"></i> Remove Journal Entry</h4></div>
            <div class="modal-body"><div class="removeEntryMessages"></div><p>Do you really want to remove this journal entry?</p></div>
            <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button><button type="button" class="btn btn-primary" id="removeEntryBtn">Save changes</button></div>
        </div>
    </div>
</div>

<script src="custom/js/journal.js"></script>
<?php require_once 'includes/footer.php'; ?>