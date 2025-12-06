var manageSupplierTable;

$(document).ready(function() {
    $('#navSuppliers').addClass('active'); // Assumes you'll add this ID to your header link
    
    manageSupplierTable = $('#manageSupplierTable').DataTable({
        'ajax': 'php_action/fetchSuppliers.php',
        'order': []
    });

    // Add Supplier form submission
    $("#submitSupplierForm").unbind('submit').bind('submit', function() {
        var form = $(this);
        $.ajax({
            url: form.attr('action'), type: form.attr('method'), data: form.serialize(), dataType: 'json',
            success: function(response) {
                if (response.success == true) {
                    $("#addSupplierModal").modal('hide');
                    form[0].reset();
                    manageSupplierTable.ajax.reload(null, false);
                    // Show success message
                } else {
                    // Show error message in modal
                }
            }
        });
        return false;
    });

    // Edit Supplier form submission
    $("#editSupplierForm").unbind('submit').bind('submit', function() {
        var form = $(this);
        $.ajax({
            url: form.attr('action'), type: form.attr('method'), data: form.serialize(), dataType: 'json',
            success: function(response) {
                if (response.success == true) {
                    $("#editSupplierModal").modal('hide');
                    manageSupplierTable.ajax.reload(null, false);
                    // Show success message
                } else {
                    // Show error message in modal
                }
            }
        });
        return false;
    });

    // Remove Supplier button click in modal
    $("#removeSupplierBtn").on('click', function() {
        var supplierId = $("#removeSupplierModal").data('id');
        $.ajax({
            url: 'php_action/removeSupplier.php', type: 'post', data: { supplierId: supplierId }, dataType: 'json',
            success: function(response) {
                if (response.success == true) {
                    $("#removeSupplierModal").modal('hide');
                    manageSupplierTable.ajax.reload(null, false);
                    // Show success message
                } else {
                    // Show error message in modal
                }
            }
        });
    });
});

function editSupplier(supplierId) {
    if (supplierId) {
        $.ajax({
            url: 'php_action/fetchSelectedSupplier.php', type: 'post', data: { supplierId: supplierId }, dataType: 'json',
            success: function(response) {
                $("#editSupplierName").val(response.name);
                $("#editSupplierContact").val(response.contact_no);
                $("#editSupplierGstin").val(response.gstin);
                $("#editSupplierAddress").val(response.address);
                $("#supplierId").val(response.id);
            }
        });
    }
}

function removeSupplier(supplierId) {
    if (supplierId) {
        // Set the ID to the modal's data attribute
        $("#removeSupplierModal").data('id', supplierId);
    }
}