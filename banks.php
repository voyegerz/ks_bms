<?php require_once 'php_action/db_connect.php'; ?>
<?php require_once 'includes/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <ol class="breadcrumb">
          <li><a href="dashboard.php">Home</a></li>          
          <li class="active">Bank Accounts</li>
        </ol>
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="page-heading"> <i class="glyphicon glyphicon-piggy-bank"></i> Manage Bank Accounts</div>
            </div>
            <div class="panel-body">
                <div class="remove-messages"></div>
                <div class="div-action pull pull-right" style="padding-bottom:20px;">
                    <button class="btn btn-default button1" data-toggle="modal" data-target="#addAccountModal"> <i class="glyphicon glyphicon-plus-sign"></i> Add Bank Account </button>
                </div>                 
                <table class="table" id="manageBankTable">
                    <thead>
                        <tr>
                            <th>Bank Name</th>
                            <th>Account Holder Name</th>
                            <th>Account Number</th>
                            <th>IFSC Code</th>
                            <th>Default</th>
                            <th style="width:15%;">Options</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
        <form class="form-horizontal" id="submitAccountForm" action="php_action/createBankAccount.php" method="POST">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fa fa-plus"></i> Add Bank Account</h4>
          </div>
          <div class="modal-body">
            <div id="add-account-messages"></div>
            <div class="form-group"><label class="col-sm-4 control-label">Bank Name</label><div class="col-sm-8"><input type="text" class="form-control" name="bankName" required></div></div>
            <div class="form-group"><label class="col-sm-4 control-label">Account Holder</label><div class="col-sm-8"><input type="text" class="form-control" name="accountHolderName" required></div></div>
            <div class="form-group"><label class="col-sm-4 control-label">Account Number</label><div class="col-sm-8"><input type="text" class="form-control" name="accountNumber" required></div></div>
            <div class="form-group"><label class="col-sm-4 control-label">IFSC Code</label><div class="col-sm-8"><input type="text" class="form-control" name="ifscCode"></div></div>
            <div class="form-group"><label class="col-sm-4 control-label">Branch Address</label><div class="col-sm-8"><textarea class="form-control" name="branchAddress"></textarea></div></div>
            <div class="form-group"><label class="col-sm-4 control-label">Set as Default</label><div class="col-sm-8"><select class="form-control" name="isDefault"><option value="0">No</option><option value="1">Yes</option></select></div></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" id="createAccountBtn" data-loading-text="Loading...">Save Changes</button>
          </div>
        </form>
    </div>
  </div>
</div>

<!-- Edit Account Modal -->
<div class="modal fade" id="editAccountModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
        <form class="form-horizontal" id="editAccountForm" action="php_action/editBankAccount.php" method="POST">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fa fa-edit"></i> Edit Bank Account</h4>
          </div>
          <div class="modal-body">
            <div id="edit-account-messages"></div>
            <div class="form-group"><label class="col-sm-4 control-label">Bank Name</label><div class="col-sm-8"><input type="text" class="form-control" id="editBankName" name="editBankName" required></div></div>
            <div class="form-group"><label class="col-sm-4 control-label">Account Holder</label><div class="col-sm-8"><input type="text" class="form-control" id="editAccountHolderName" name="editAccountHolderName" required></div></div>
            <div class="form-group"><label class="col-sm-4 control-label">Account Number</label><div class="col-sm-8"><input type="text" class="form-control" id="editAccountNumber" name="editAccountNumber" required></div></div>
            <div class="form-group"><label class="col-sm-4 control-label">IFSC Code</label><div class="col-sm-8"><input type="text" class="form-control" id="editIfscCode" name="editIfscCode"></div></div>
            <div class="form-group"><label class="col-sm-4 control-label">Branch Address</label><div class="col-sm-8"><textarea class="form-control" id="editBranchAddress" name="editBranchAddress"></textarea></div></div>
            <div class="form-group"><label class="col-sm-4 control-label">Set as Default</label><div class="col-sm-8"><select class="form-control" id="editIsDefault" name="editIsDefault"><option value="0">No</option><option value="1">Yes</option></select></div></div>
          </div>
          <div class="modal-footer">
            <input type="hidden" name="accountId" id="accountId" />
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" id="editAccountBtn" data-loading-text="Loading...">Save Changes</button>
          </div>
        </form>
    </div>
  </div>
</div>

<!-- Remove Account Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="removeAccountModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><i class="glyphicon glyphicon-trash"></i> Remove Bank Account</h4></div>
            <div class="modal-body"><div class="removeAccountMessages"></div><p>Do you really want to remove this account?</p></div>
            <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button><button type="button" class="btn btn-primary" id="removeAccountBtn">Save changes</button></div>
        </div>
    </div>
</div>

<script src="custom/js/banks.js"></script>
<?php require_once 'includes/footer.php'; ?>