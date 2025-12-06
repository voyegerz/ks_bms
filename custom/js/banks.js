var manageBankTable;

$(document).ready(function() {
    $('#topNavBankAccounts').addClass('active');
    
    manageBankTable = $('#manageBankTable').DataTable({
        'ajax': 'php_action/fetchBankAccounts.php',
        'order': []
    });

    // Add Account form submission
    $("#submitAccountForm").unbind('submit').bind('submit', function() {
        var form = $(this);
        $.ajax({
            url: form.attr('action'), type: 'POST', data: form.serialize(), dataType: 'json',
            success: function(response) {
                if (response.success == true) {
                    $("#addAccountModal").modal('hide');
                    form[0].reset();
                    manageBankTable.ajax.reload(null, false);
                    $(".remove-messages").html('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="glyphicon glyphicon-ok-sign"></i></strong> ' + response.messages + '</div>');
                    $(".alert-success").delay(500).show(10, function() { $(this).delay(3000).hide(10, function() { $(this).remove(); }); });
                } else {
                    $("#add-account-messages").html('<div class="alert alert-warning alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> ' + response.messages + '</div>');
                }
            }
        });
        return false;
    });

    // Edit Account form submission
    $("#editAccountForm").unbind('submit').bind('submit', function() {
        var form = $(this);
        $.ajax({
            url: form.attr('action'), type: 'POST', data: form.serialize(), dataType: 'json',
            success: function(response) {
                if (response.success == true) {
                    $("#editAccountModal").modal('hide');
                    manageBankTable.ajax.reload(null, false);
                    $(".remove-messages").html('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="glyphicon glyphicon-ok-sign"></i></strong> ' + response.messages + '</div>');
                    $(".alert-success").delay(500).show(10, function() { $(this).delay(3000).hide(10, function() { $(this).remove(); }); });
                } else {
                    $("#edit-account-messages").html('<div class="alert alert-warning alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> ' + response.messages + '</div>');
                }
            }
        });
        return false;
    });

    // Remove Account button click in modal
    $("#removeAccountBtn").on('click', function() {
        var accountId = $("#removeAccountModal").data('id');
        $("#removeAccountBtn").button('loading');
        $.ajax({
            url: 'php_action/removeBankAccount.php', type: 'post', data: { accountId: accountId }, dataType: 'json',
            success: function(response) {
                $("#removeAccountBtn").button('reset');
                if (response.success == true) {
                    $("#removeAccountModal").modal('hide');
                    manageBankTable.ajax.reload(null, false);
                    $(".remove-messages").html('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="glyphicon glyphicon-ok-sign"></i></strong> ' + response.messages + '</div>');
                    $(".alert-success").delay(500).show(10, function() { $(this).delay(3000).hide(10, function() { $(this).remove(); }); });
                } else {
                    $(".removeAccountMessages").html('<div class="alert alert-warning alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> ' + response.messages + '</div>');
                }
            }
        });
    });
});

function editAccount(accountId) {
    if (accountId) {
        $.ajax({
            url: 'php_action/fetchSelectedBankAccount.php', type: 'post', data: { accountId: accountId }, dataType: 'json',
            success: function(response) {
                $("#editBankName").val(response.bank_name);
                $("#editAccountHolderName").val(response.account_holder_name);
                $("#editAccountNumber").val(response.account_number);
                $("#editIfscCode").val(response.ifsc_code);
                $("#editBranchAddress").val(response.branch_address);
                $("#editIsDefault").val(response.is_default);
                $("#accountId").val(response.account_id);
            }
        });
    }
}

function removeAccount(accountId) {
    if (accountId) {
        $("#removeAccountModal").data('id', accountId); // Set the ID to the modal
        $(".removeAccountMessages").html(''); // Clear previous messages
    }
}