<?php 
require_once 'php_action/core.php'; 
require_once 'includes/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <ol class="breadcrumb">
            <li><a href="dashboard.php">Home</a></li>
            <li class="active">Ledgers</li>
        </ol>

        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="page-heading"> <i class="glyphicon glyphicon-list-alt"></i> Party Ledger</div>
            </div>
            <div class="panel-body">
                <div class="form-horizontal">
                    <div class="form-group">
                        <label for="partySelect" class="col-sm-2 control-label">Select Party</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="partySelect" name="partySelect">
                                <option value="">-- Select a Party --</option>
                                <?php 
                                    $partySql = "SELECT id, name FROM partys ORDER BY name ASC";
                                    $partyResult = $connect->query($partySql);
                                    while($row = $partyResult->fetch_assoc()) {
                                        echo "<option value='".$row['id']."'>".$row['name']."</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <button type="button" class="btn btn-primary" id="printLedgerBtn" style="display:none;">
                                <i class="glyphicon glyphicon-print"></i> Print Ledger
                            </button>
                        </div>
                    </div>
                </div>
                <hr/>
                
                <div id="ledgerResultContainer">
                    <p class="text-muted text-center">Please select a party to view their ledger statement.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="custom/js/ledger.js"></script>

<?php require_once 'includes/footer.php'; ?>