
var manageJournalTable;

$(document).ready(function() {
    $('#navJournal').addClass('active');
    
    // Initialize the datepicker
    $("#entryDate").datepicker({ dateFormat: 'yy-mm-dd' });

    manageJournalTable = $('#manageJournalTable').DataTable({
        'ajax': 'php_action/fetchJournalVouchers.php',
        'order': [[ 0, 'desc' ]] // Order by Voucher ID descending
    });

    $("#addEntryModalBtn").on('click', function() {
        // Clear previous messages
        $("#add-entry-messages").html('');
        
        // Fetch the next voucher number
        $.ajax({
            url: 'php_action/fetchNextVoucherNo.php',
            type: 'post',
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    $("#voucherNo").val(response.next_voucher_no);
                }
            }
        });
    });

    // Add Journal Entry form submission
    $("#submitEntryForm").unbind('submit').bind('submit', function() {
        var form = $(this);
        var url = form.attr('action');
        var data = form.serialize();

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.success == true) {
                    $("#addEntryModal").modal('hide');
                    form[0].reset();
                    manageJournalTable.ajax.reload(null, false);
                    
                    $(".remove-messages").html('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="glyphicon glyphicon-ok-sign"></i></strong> ' + response.messages + '</div>');
                    $(".alert-success").delay(500).show(10, function() { $(this).delay(3000).hide(10, function() { $(this).remove(); }); });
                } else {
                    $("#add-entry-messages").html('<div class="alert alert-warning alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> ' + response.messages + '</div>');
                }
            }
        });
        return false;
    });

    // --- NEW: Edit Journal Entry form submission ---
    $("#editEntryForm").unbind('submit').bind('submit', function() {
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success == true) {
                    $("#editEntryModal").modal('hide');
                    manageJournalTable.ajax.reload(null, false);
                    $(".remove-messages").html('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="glyphicon glyphicon-ok-sign"></i></strong> ' + response.messages + '</div>');
                    $(".alert-success").delay(500).show(10, function() { $(this).delay(3000).hide(10, function() { $(this).remove(); }); });
                } else {
                    $("#edit-entry-messages").html('<div class="alert alert-warning alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> ' + response.messages + '</div>');
                }
            }
        });
        return false;
    });

    // --- NEW: Remove Journal Entry button click in modal ---
    $("#removeEntryBtn").on('click', function() {
        if (!currentVoucherId) return;
        $("#removeEntryBtn").button('loading');
        $.ajax({
            url: 'php_action/removeJournalEntry.php',
            type: 'post',
            data: { voucherId: currentVoucherId },
            dataType: 'json',
            success: function(response) {
                $("#removeEntryBtn").button('reset');
                if (response.success == true) {
                    $("#removeEntryModal").modal('hide');
                    manageJournalTable.ajax.reload(null, false);
                    $(".remove-messages").html('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="glyphicon glyphicon-ok-sign"></i></strong> ' + response.messages + '</div>');
                    $(".alert-success").delay(500).show(10, function() { $(this).delay(3000).hide(10, function() { $(this).remove(); }); });
                } else {
                    $(".removeEntryMessages").html('<div class="alert alert-warning alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> ' + response.messages + '</div>');
                }
            }
        });
    });
});

// --- NEW: Function to open the edit modal and fetch data ---
function editEntry(voucherId) {
    if (voucherId) {
        $.ajax({
            url: 'php_action/fetchSelectedJournalEntry.php',
            type: 'post',
            data: { voucherId: voucherId },
            dataType: 'json',
            success: function(response) {
                $("#editVoucherNo").val(response.voucher_no);
                $("#editEntryDate").val(response.voucher_date);
                $("#editDescription").val(response.description);
                $("#editDebitAccount").val(response.debit_account_id);
                $("#editCreditAccount").val(response.credit_account_id);
                $("#editAmount").val(response.amount);
                $("#voucherId").val(response.voucher_id);
            }
        });
    }
}

// --- NEW: Function to open the remove modal ---
function removeEntry(voucherId) {
    if (voucherId) {
        currentVoucherId = voucherId;
        $(".removeEntryMessages").html(''); // Clear previous messages
    }
}