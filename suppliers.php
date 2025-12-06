<?php require_once 'php_action/db_connect.php'; ?>
<?php require_once 'includes/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <ol class="breadcrumb">
          <li><a href="dashboard.php">Home</a></li>          
          <li class="active">Suppliers</li>
        </ol>
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="page-heading"> <i class="glyphicon glyphicon-user"></i> Manage Suppliers</div>
            </div>
            <div class="panel-body">
                <div class="remove-messages"></div>
                <div class="div-action pull pull-right" style="padding-bottom:20px;">
                    <button class="btn btn-default button1" data-toggle="modal" data-target="#addSupplierModal"> <i class="glyphicon glyphicon-plus-sign"></i> Add Supplier </button>
                </div>                 
                <table class="table" id="manageSupplierTable">
                    <thead>
                        <tr>
                            <th>Supplier Name</th>
                            <th>Contact No</th>
                            <th>GSTIN</th>
                            <th style="width:15%;">Options</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
        <form class="form-horizontal" id="submitSupplierForm" action="php_action/createSupplier.php" method="POST">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fa fa-plus"></i> Add Supplier</h4>
          </div>
          <div class="modal-body">
            <div id="add-supplier-messages"></div>
            <div class="form-group"><label class="col-sm-4 control-label">Supplier Name</label><div class="col-sm-8"><input type="text" class="form-control" name="supplierName" required></div></div>
            <div class="form-group"><label class="col-sm-4 control-label">Contact No</label><div class="col-sm-8"><input type="text" class="form-control" name="supplierContact"></div></div>
            <div class="form-group"><label class="col-sm-4 control-label">GSTIN</label><div class="col-sm-8"><input type="text" class="form-control" name="supplierGstin"></div></div>
            <div class="form-group"><label class="col-sm-4 control-label">Address</label><div class="col-sm-8"><textarea class="form-control" name="supplierAddress"></textarea></div></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" id="createSupplierBtn" data-loading-text="Loading...">Save Changes</button>
          </div>
        </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editSupplierModal" tabindex="-1" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
        <form class="form-horizontal" id="editSupplierForm" action="php_action/editSupplier.php" method="POST">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fa fa-edit"></i> Edit Supplier</h4>
          </div>
          <div class="modal-body">
            <div id="edit-supplier-messages"></div>
            <div class="form-group"><label class="col-sm-4 control-label">Supplier Name</label><div class="col-sm-8"><input type="text" class="form-control" id="editSupplierName" name="editSupplierName" required></div></div>
            <div class="form-group"><label class="col-sm-4 control-label">Contact No</label><div class="col-sm-8"><input type="text" class="form-control" id="editSupplierContact" name="editSupplierContact"></div></div>
            <div class="form-group"><label class="col-sm-4 control-label">GSTIN</label><div class="col-sm-8"><input type="text" class="form-control" id="editSupplierGstin" name="editSupplierGstin"></div></div>
            <div class="form-group"><label class="col-sm-4 control-label">Address</label><div class="col-sm-8"><textarea class="form-control" id="editSupplierAddress" name="editSupplierAddress"></textarea></div></div>
          </div>
          <div class="modal-footer">
            <input type="hidden" name="supplierId" id="supplierId" />
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" id="editSupplierBtn" data-loading-text="Loading...">Save Changes</button>
          </div>
        </form>
    </div>
  </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="removeSupplierModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><i class="glyphicon glyphicon-trash"></i> Remove Supplier</h4></div>
            <div class="modal-body"><div class="removeSupplierMessages"></div><p>Do you really want to remove this supplier?</p></div>
            <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button><button type="button" class="btn btn-primary" id="removeSupplierBtn">Save changes</button></div>
        </div>
    </div>
</div>

<script src="custom/js/suppliers.js"></script>
<?php require_once 'includes/footer.php'; ?>