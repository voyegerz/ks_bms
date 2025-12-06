var managePartyTable;

$(document).ready(function() {
    // Top nav bar active
    $('#navParty').addClass('active');

    // Manage party data table
    managePartyTable = $('#managePartyTable').DataTable({
        'ajax': 'php_action/fetchPartys.php',
        'order': []
    });

    // On clicking the add party modal button
    $("#addPartyModalBtn").on('click', function() {
        // Reset the form
        $("#submitPartyForm")[0].reset();
        // Remove text-danger
        $(".text-danger").remove();
        // Remove form-group error
        $(".form-group").removeClass('has-error').removeClass('has-success');
        // Clear messages
        $("#add-party-messages").html('');

        // Submit form
        $("#submitPartyForm").unbind('submit').bind('submit', function() {
            var form = $(this);
            $("#createPartyBtn").button('loading');

            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    $("#createPartyBtn").button('reset');
                    if (response.success == true) {
                        // Reload the table
                        managePartyTable.ajax.reload(null, false);
                        // Show success message
                        $("#add-party-messages").html('<div class="alert alert-success alert-dismissible" role="alert">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                            '<strong> <span class="glyphicon glyphicon-ok-sign"></span> </strong>' + response.messages +
                            '</div>');
                        // Hide the modal
                        $("#addPartyModal").modal('hide');
                        // Reset the form
                        $("#submitPartyForm")[0].reset();
                    } else {
                        // Show error message
                        $("#add-party-messages").html('<div class="alert alert-warning alert-dismissible" role="alert">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                            '<strong> <span class="glyphicon glyphicon-exclamation-sign"></span> </strong>' + response.messages +
                            '</div>');
                    }
                }
            });
            return false;
        });
    });
});

// Function to open the edit modal
function editParty(partyId) {
    if (partyId) {
        // Reset form and messages
        $("#editPartyForm")[0].reset();
        $('.form-group').removeClass('has-error').removeClass('has-success');
        $('.text-danger').remove();
        $('#edit-party-messages').html('');
        
        // Fetch selected party information
        $.ajax({
            url: 'php_action/fetchSelectedParty.php',
            type: 'post',
            data: { party_id: partyId },
            dataType: 'json',
            success: function(response) {
                $("#editPartyName").val(response.name);
                $("#editGstin").val(response.gstin);
                $("#editContactNo").val(response.contact_no);
                $("#editEmail").val(response.email);
                $("#editBillingAddr").val(response.billing_addr);
                $("#editShippingAddr").val(response.shipping_addr);
                $("#party_id").val(response.id);

                // Submit the edit form
                $("#editPartyForm").unbind('submit').bind('submit', function() {
                    var form = $(this);
                    $('#editPartyBtn').button('loading');

                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method'),
                        data: form.serialize(),
                        dataType: 'json',
                        success: function(response) {
                            $('#editPartyBtn').button('reset');
                            if (response.success == true) {
                                managePartyTable.ajax.reload(null, false);
                                $("#edit-party-messages").html('<div class="alert alert-success alert-dismissible" role="alert">' +
                                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                                    '<strong> <span class="glyphicon glyphicon-ok-sign"></span> </strong>' + response.messages +
                                    '</div>');
                                $("#editPartyModal").modal('hide');
                            } else {
                                $("#edit-party-messages").html('<div class="alert alert-warning alert-dismissible" role="alert">' +
                                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                                    '<strong> <span class="glyphicon glyphicon-exclamation-sign"></span> </strong>' + response.messages +
                                    '</div>');
                            }
                        }
                    });
                    return false;
                });
            }
        });
    }
}

// Function to open the remove confirmation modal
function removeParty(partyId) {
    if (partyId) {
        $("#removePartyBtn").unbind('click').bind('click', function() {
            $.ajax({
                url: 'php_action/removeParty.php',
                type: 'post',
                data: { party_id: partyId },
                dataType: 'json',
                success: function(response) {
                    if (response.success == true) {
                        $("#removePartyModal").modal('hide');
                        managePartyTable.ajax.reload(null, false);
                        $('.remove-messages').html('<div class="alert alert-success alert-dismissible" role="alert">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                            '<strong> <span class="glyphicon glyphicon-ok-sign"></span> </strong>' + response.messages +
                            '</div>');
                    } else {
                        $('.remove-messages').html('<div class="alert alert-warning alert-dismissible" role="alert">' +
                            '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                            '<strong> <span class="glyphicon glyphicon-exclamation-sign"></span> </strong>' + response.messages +
                            '</div>');
                    }
                }
            });
        });
    }
}