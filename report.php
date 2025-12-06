<?php 
require_once 'includes/header.php'; 
require_once 'php_action/db_connect.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="glyphicon glyphicon-check"></i> Reports
            </div>
            <div class="panel-body">
                
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#orderReport" aria-controls="orderReport" role="tab" data-toggle="tab">Order Report</a></li>
                    <li role="presentation"><a href="#ledgerReport" aria-controls="ledgerReport" role="tab" data-toggle="tab">Party Ledger</a></li>
                    <li role="presentation"><a href="#outstandingReport" aria-controls="outstandingReport" role="tab" data-toggle="tab">Outstanding Balances</a></li>
                    <li role="presentation"><a href="#agingReport" aria-controls="agingReport" role="tab" data-toggle="tab">Aging Report</a></li>
                </ul>

                <div class="tab-content">

                    <div role="tabpanel" class="tab-pane active" id="orderReport" style="padding-top: 20px;">
                        <p>Generate a summary of all orders placed within a specific date range.</p>
                        <form class="form-horizontal" action="php_action/getOrderReport.php" method="post" id="getOrderReportForm">
                            <div class="form-group">
                                <label for="startDate" class="col-sm-2 control-label">Start Date</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="startDate" name="startDate" placeholder="Start Date" autocomplete="off" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="endDate" class="col-sm-2 control-label">End Date</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="endDate" name="endDate" placeholder="End Date" autocomplete="off" />
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <button type="submit" class="btn btn-success" id="generateOrderReportBtn"> <i class="glyphicon glyphicon-list-alt"></i> Generate Report</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div role="tabpanel" class="tab-pane" id="ledgerReport" style="padding-top: 20px;">
                        <p>Generate a detailed transaction history for a single party between two dates.</p>
                        <form class="form-horizontal" action="php_action/getLedgerReport.php" method="post" id="getLedgerReportForm">
                            <div class="form-group">
                                <label for="partySelect" class="col-sm-2 control-label">Select Party</label>
                                <div class="col-sm-10">
                                    <select class="form-control" id="partySelect" name="partyId">
                                        <option value="all">-- All Parties (Summary) --</option>
                                        <?php 
                                            $partySql = "SELECT id, name FROM partys ORDER BY name ASC";
                                            $partyResult = $connect->query($partySql);
                                            while($row = $partyResult->fetch_assoc()) {
                                                echo "<option value='".$row['id']."'>".$row['name']."</option>";
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ledgerStartDate" class="col-sm-2 control-label">Start Date</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="ledgerStartDate" name="startDate" placeholder="Start Date" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ledgerEndDate" class="col-sm-2 control-label">End Date</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="ledgerEndDate" name="endDate" placeholder="End Date" autocomplete="off" />
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <button type="submit" class="btn btn-success" id="generateLedgerReportBtn"> <i class="glyphicon glyphicon-list-alt"></i> Generate Report</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div role="tabpanel" class="tab-pane" id="outstandingReport" style="padding-top: 20px;">
                        <p>This report shows the amount owed by each party as of a specific date.</p>
                        <form class="form-horizontal" action="php_action/getOutstandingReport.php" method="post" id="getOutstandingReportForm">
                             <div class="form-group">
                                <label for="outstandingParty" class="col-sm-2 control-label">Select Party</label>
                                <div class="col-sm-10">
                                    <select class="form-control" id="outstandingParty" name="partyId">
                                        <option value="all">-- All Parties (Summary) --</option>
                                        <?php 
                                            $partySql = "SELECT id, name FROM partys ORDER BY name ASC";
                                            $partyResult = $connect->query($partySql);
                                            while($row = $partyResult->fetch_assoc()) {
                                                echo "<option value='".$row['id']."'>".$row['name']."</option>";
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="outstandingEndDate" class="col-sm-2 control-label">As of Date</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="outstandingEndDate" name="endDate" placeholder="Select Date" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <button type="submit" class="btn btn-success" id="generateOutstandingBtn"> <i class="glyphicon glyphicon-list-alt"></i> Generate Report</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div role="tabpanel" class="tab-pane" id="agingReport" style="padding-top: 20px;">
                        <p>This report shows unpaid invoice amounts grouped by how long they have been outstanding as of a specific date.</p>
                        <form class="form-horizontal" action="php_action/getAgingReport.php" method="post" id="getAgingReportForm">
                            <div class="form-group">
                                <label for="agingEndDate" class="col-sm-2 control-label">As of Date</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" id="agingEndDate" name="endDate" placeholder="Select Date" autocomplete="off" />
                                </div>
                            </div>
                             <div class="form-group">
                                <div class="col-sm-offset-2 col-sm-10">
                                    <button type="submit" class="btn btn-success" id="generateAgingBtn"> <i class="glyphicon glyphicon-list-alt"></i> Generate Report</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            </div>
    </div>
    </div>
<script src="custom/js/report.js"></script>

<?php require_once 'includes/footer.php'; ?>