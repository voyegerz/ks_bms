<?php require_once 'includes/header.php'; ?>

<?php 
// IMPORTANT SECURITY NOTE: Your original query is vulnerable to SQL injection.
// It's better to use prepared statements to keep your application secure.
// I've updated the query below as an example.

$states = [
    35 => "Andaman and Nicobar Islands", 37 => "Andhra Pradesh", 12 => "Arunachal Pradesh",
    18 => "Assam", 10 => "Bihar", 4 => "Chandigarh", 22 => "Chhattisgarh",
    26 => "Dadra and Nagar Haveli and Daman and Diu", 7 => "Delhi", 30 => "Goa",
    24 => "Gujarat", 6 => "Haryana", 2 => "Himachal Pradesh", 1 => "Jammu and Kashmir",
    20 => "Jharkhand", 29 => "Karnataka", 32 => "Kerala", 31 => "Lakshadweep",
    23 => "Madhya Pradesh", 27 => "Maharashtra", 14 => "Manipur", 17 => "Meghalaya",
    15 => "Mizoram", 13 => "Nagaland", 21 => "Odisha", 34 => "Puducherry",
    3 => "Punjab", 8 => "Rajasthan", 11 => "Sikkim", 33 => "Tamil Nadu",
    36 => "Telangana", 16 => "Tripura", 9 => "Uttar Pradesh", 5 => "Uttarakhand",
    19 => "West Bengal", 38 => "Ladakh"
];
// Sort states alphabetically by name for a better user experience
asort($states);



$user_id = $_SESSION['userId'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

$connect->close();
?>

<div class="row">
    <div class="col-md-12">
        <ol class="breadcrumb">
            <li><a href="dashboard.php">Home</a></li>          
            <li class="active">Setting</li>
        </ol>

        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="page-heading"> <i class="glyphicon glyphicon-wrench"></i> Setting</div>
            </div> <div class="panel-body">
                
                <form action="php_action/updateCompanyInfo.php" method="post" class="form-horizontal" id="companySettingsForm">
                    <fieldset>
                        <legend>Company Settings</legend>

                        <div class="companySettingsMessages"></div>

                        <div class="form-group">
                            <label for="company_name" class="col-sm-2 control-label">Company Name</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Company Name" value="<?php echo $result['company_name']; ?>"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="gstin" class="col-sm-2 control-label">GSTIN</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="gstin" name="gstin" placeholder="GSTIN" value="<?php echo $result['gstin']; ?>"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="company_addr" class="col-sm-2 control-label">Address</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="company_addr" name="company_addr" placeholder="Company Address" value="<?php echo $result['company_addr']; ?>"/>
                            </div>
                        </div>
                         <div class="form-group">
                            <label for="company_email" class="col-sm-2 control-label">Email</label>
                            <div class="col-sm-10">
                              <input type="email" class="form-control" id="company_email" name="company_email" placeholder="Company Email" value="<?php echo $result['company_email']; ?>"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="contact_no" class="col-sm-2 control-label">Contact No</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="contact_no" name="contact_no" placeholder="Contact No" value="<?php echo $result['contact_no']; ?>"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="cgst" class="col-sm-2 control-label">CGST Rate (%)</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="cgst" name="cgst" placeholder="Central GST Rate" value="<?php echo $result['cgst']; ?>"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="sgst" class="col-sm-2 control-label">SGST Rate (%)</label>
                            <div class="col-sm-10">
                              <input type="text" class="form-control" id="sgst" name="sgst" placeholder="State GST Rate" value="<?php echo $result['sgst']; ?>"/>
                            </div>
                        </div>
                        <div class="form-group">
                          <label for="state_name" class="col-sm-2 control-label">State Name</label>
                          <div class="col-sm-10">
                            <select class="form-control" id="state_name" name="state_name" required>
                              <option value="">-- Select Your State --</option>
                              <?php
                              foreach ($states as $code => $name) {
                                // Ensure the state code is always two digits with a leading zero if needed
                                $formatted_code = sprintf('%02d', $code);
                                // Check if this state is the one currently saved for the user
                                $selected = ($result['state_name'] == $name) ? 'selected' : '';
                                // Create the option tag with a custom data-code attribute
                                echo "<option value='{$name}' data-code='{$formatted_code}' {$selected}>{$name}</option>";
                              }
                              ?>
                            </select>
                          </div>
                        </div>

                        <div class="form-group">
                          <label for="state_code" class="col-sm-2 control-label">State Code</label>
                          <div class="col-sm-10">
                          <input type="text" class="form-control" id="state_code" name="state_code" placeholder="State Code" value="<?php echo $result['state_code']; ?>" readonly/>
                          </div>
                        </div>

                        <div class="form-group">
                            <label for="company_bank_details" class="col-sm-2 control-label">Bank Details</label>
                            <div class="col-sm-10">
                                <textarea class="form-control" id="company_bank_details" name="company_bank_details" rows="5" placeholder="A/c Holder's Name:&#10;Bank Name:&#10;A/c No.:&#10;Branch & IFS Code:"><?php echo htmlspecialchars($result['company_bank_details']); ?></textarea>
                                
                                <div class="alert alert-info" style="margin-top: 10px; padding: 10px; font-size: 12px;">
                                    <strong>Example Format:</strong><br>
                                    A/c Holder's Name: Your Company Name<br>
                                    Bank Name: State Bank of India<br>
                                    A/c No.: 123456789012<br>
                                    Branch & IFS Code: Nawanagar & SBIN0001234
                                </div>
                            </div>
                        </div>
						
                        
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <input type="hidden" name="user_id" id="user_id" value="<?php echo $result['user_id'] ?>" /> 
                              <button type="submit" class="btn btn-info" data-loading-text="Loading..." id="saveCompanySettingsBtn"> <i class="glyphicon glyphicon-ok-sign"></i> Save Changes </button>
                            </div>
                        </div>
                    </fieldset>
                </form>

                <form action="php_action/changeUsername.php" method="post" class="form-horizontal" id="changeUsernameForm">
                    <fieldset>
                        <legend>Change Username</legend>
                        <div class="changeUsenrameMessages"></div>            
                        <div class="form-group">
                        <label for="username" class="col-sm-2 control-label">Username</label>
                        <div class="col-sm-10">
                          <input type="text" class="form-control" id="username" name="username" placeholder="Usename" value="<?php echo $result['username']; ?>"/>
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <input type="hidden" name="user_id" id="user_id" value="<?php echo $result['user_id'] ?>" /> 
                          <button type="submit" class="btn btn-success" data-loading-text="Loading..." id="changeUsernameBtn"> <i class="glyphicon glyphicon-ok-sign"></i> Save Changes </button>
                        </div>
                      </div>
                    </fieldset>
                </form>

                <form action="php_action/changePassword.php" method="post" class="form-horizontal" id="changePasswordForm">
                    <fieldset>
                        <legend>Change Password</legend>
                        <div class="changePasswordMessages"></div>
                        <div class="form-group">
                        <label for="password" class="col-sm-2 control-label">Current Password</label>
                        <div class="col-sm-10">
                          <input type="password" class="form-control" id="password" name="password" placeholder="Current Password">
                        </div>
                      </div>
                      <div class="form-group">
                        <label for="npassword" class="col-sm-2 control-label">New password</label>
                        <div class="col-sm-10">
                          <input type="password" class="form-control" id="npassword" name="npassword" placeholder="New Password">
                        </div>
                      </div>
                      <div class="form-group">
                        <label for="cpassword" class="col-sm-2 control-label">Confirm Password</label>
                        <div class="col-sm-10">
                          <input type="password" class="form-control" id="cpassword" name="cpassword" placeholder="Confirm Password">
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <input type="hidden" name="user_id" id="user_id" value="<?php echo $result['user_id'] ?>" /> 
                          <button type="submit" class="btn btn-primary"> <i class="glyphicon glyphicon-ok-sign"></i> Save Changes </button>
                        </div>
                      </div>
                    </fieldset>
                </form>

            </div> </div> </div> </div> <script src="custom/js/setting.js"></script>
<?php require_once 'includes/footer.php'; ?>