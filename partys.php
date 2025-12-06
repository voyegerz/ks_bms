<?php require_once 'php_action/db_connect.php'; ?>
<?php require_once 'includes/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <ol class="breadcrumb">
            <li><a href="dashboard.php">Home</a></li>
            <li class="active">Party</li>
        </ol>

        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="page-heading"> <i class="glyphicon glyphicon-user"></i> Manage Parties</div>
            </div> <div class="panel-body">

                <div class="remove-messages"></div>

                <div class="div-action pull pull-right" style="padding-bottom:20px;">
                    <button class="btn btn-default button1" data-toggle="modal" id="addPartyModalBtn" data-target="#addPartyModal">
                        <i class="glyphicon glyphicon-plus-sign"></i> Add Party
                    </button>
                </div> <table class="table" id="managePartyTable">
                    <thead>
                        <tr>
                            <th>Party Name</th>
                            <th>GSTIN</th>
                            <th>Contact No.</th>
                            <th style="width:15%;">Options</th>
                        </tr>
                    </thead>
                </table>
                </div> </div> </div> </div> <div class="modal fade" id="addPartyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="form-horizontal" id="submitPartyForm" action="php_action/createParty.php" method="POST">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-plus"></i> Add Party</h4>
                </div>
                <div class="modal-body">
                    <div id="add-party-messages"></div>
                    <div class="form-group">
                        <label for="partyName" class="col-sm-4 control-label">Party Name:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="partyName" name="partyName" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="gstin" class="col-sm-4 control-label">GSTIN:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="gstin" name="gstin" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="contactNo" class="col-sm-4 control-label">Contact No.:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="contactNo" name="contactNo" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-sm-4 control-label">Email:</label>
                        <div class="col-sm-8">
                            <input type="email" class="form-control" id="email" name="email" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="billingAddr" class="col-sm-4 control-label">Billing Address:</label>
                        <div class="col-sm-8">
                            <textarea class="form-control" id="billingAddr" name="billingAddr" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="shippingAddr" class="col-sm-4 control-label">Shipping Address:</label>
                        <div class="col-sm-8">
                            <textarea class="form-control" id="shippingAddr" name="shippingAddr" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="createPartyBtn" data-loading-text="Loading...">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editPartyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="form-horizontal" id="editPartyForm" action="php_action/editParty.php" method="POST">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-edit"></i> Edit Party</h4>
                </div>
                <div class="modal-body">
                    <div id="edit-party-messages"></div>
                    <div class="form-group">
                        <label for="editPartyName" class="col-sm-4 control-label">Party Name:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="editPartyName" name="editPartyName" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="editGstin" class="col-sm-4 control-label">GSTIN:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="editGstin" name="editGstin" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="editContactNo" class="col-sm-4 control-label">Contact No.:</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="editContactNo" name="editContactNo" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="editEmail" class="col-sm-4 control-label">Email:</label>
                        <div class="col-sm-8">
                            <input type="email" class="form-control" id="editEmail" name="editEmail" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="editBillingAddr" class="col-sm-4 control-label">Billing Address:</label>
                        <div class="col-sm-8">
                            <textarea class="form-control" id="editBillingAddr" name="editBillingAddr" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="editShippingAddr" class="col-sm-4 control-label">Shipping Address:</label>
                        <div class="col-sm-8">
                            <textarea class="form-control" id="editShippingAddr" name="editShippingAddr" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="party_id" id="party_id" />
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="editPartyBtn" data-loading-text="Loading...">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="removePartyModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="glyphicon glyphicon-trash"></i> Remove Party</h4>
            </div>
            <div class="modal-body">
                <p>Do you really want to remove this party?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="removePartyBtn">Save changes</button>
            </div>
        </div>
    </div>
</div>

<script src="custom/js/party.js"></script>
<?php require_once 'includes/footer.php'; ?>