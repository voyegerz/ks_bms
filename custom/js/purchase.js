var managePurchaseTable;
var currentPurchaseId = null;

$(document).ready(function() {
    $('#navPurchase').addClass('active');
    var divRequest = $(".div-request").text();

    if (divRequest == 'manage') {
        managePurchaseTable = $("#managePurchaseTable").DataTable({
            'ajax': 'php_action/fetchPurchases.php',
            'order': []
        });

        // --- REMOVE PURCHASE MODAL LOGIC ---
        // This handler is for the "Save changes" button inside the remove modal
        $('#removePurchaseBtn').on('click', function() {
            if (!currentPurchaseId) {
                alert("Error: No Purchase ID found. Please refresh.");
                return;
            }
            
            $("#removePurchaseBtn").button("loading");
            
            $.ajax({
                url: 'php_action/removePurchase.php',
                type: 'post',
                data: { purchaseId: currentPurchaseId },
                dataType: 'json',
                success: function(response) {
                    $("#removePurchaseBtn").button("reset");
                    
                    if (response.success == true) {
                        managePurchaseTable.ajax.reload(null, false);
                        $("#removePurchaseModal").modal('hide');
                        
                        // Show a temporary success message on the main page
                        $(".success-messages").html('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+response.messages+'</div>');
                        $(".alert-success").delay(500).show(10, function() { $(this).delay(3000).hide(10, function() { $(this).remove(); }); });

                    } else {
                        // Show error message inside the modal
                        $(".removePurchaseMessages").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+response.messages+'</div>');
                    }
                }
            });
        });
    } else if (divRequest == 'add' || divRequest == 'edit') {
        // This logic applies to both Add and Edit pages
        $("#purchaseDate, #dueDate").datepicker({ dateFormat: 'yy-mm-dd' });

        if(divRequest == 'edit') {
            subAmount();
        }

        // --- ADD SUPPLIER MODAL FORM SUBMISSION ---
        $("#submitSupplierForm").unbind('submit').bind('submit', function() {
            var form = $(this);
            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success == true) {
                        $("#addSupplierModal").modal('hide');
                        $("#submitSupplierForm")[0].reset();
                        // Show success message
                        $(".success-messages").html('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="glyphicon glyphicon-ok-sign"></i></strong> ' + response.messages + '</div>');
                        $(".alert-success").delay(500).show(10, function() { $(this).delay(3000).hide(10, function() { $(this).remove(); }); });
                        
                        // Refresh the supplier dropdown list with the new entry
                        $('#supplierId').load(location.href + ' #supplierId>*', '');
                    } else {
                        // Show error message inside the modal
                        $("#add-supplier-messages").html('<div class="alert alert-warning alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> ' + response.messages + '</div>');
                    }
                }
            });
            return false;
        });

        // --- ADD PURCHASE FORM SUBMISSION ---
        $("#createPurchaseForm").unbind('submit').bind('submit', function() {
            var form = $(this);
            if (validateForm(form)) {
                form.find('button[type="submit"]').button('loading');
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        form.find('button[type="submit"]').button('reset');
                        if (response.success == true) {
                            window.location = 'purchases.php?p=manage';
                        } else {
                            $(".success-messages").html('<div class="alert alert-danger">...</div>');
                        }
                    }
                });
            }
            return false;
        });

        // --- EDIT PURCHASE FORM SUBMISSION ---
        $("#editPurchaseForm").unbind('submit').bind('submit', function() {
            var form = $(this);
            if (validateForm(form)) {
                form.find('button[type="submit"]').button('loading');
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        form.find('button[type="submit"]').button('reset');
                        if (response.success == true) {
                            $(".success-messages").html('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+response.messages+' <br/><br/><a href="purchases.php?p=manage" class="btn btn-default"><i class="glyphicon glyphicon-list-alt"></i> Manage Purchases</a></div>');
                        } else {
                             $(".success-messages").html('<div class="alert alert-danger">...</div>');
                        }
                    }
                });
            }
            return false;
        });
    }
});

// Reusable validation function for both Add and Edit forms
function validateForm(form) {
    $('.form-group').removeClass('has-error');
    $('.text-danger').remove();
    var formValid = true;

    if (form.find("#purchaseDate").val() == "") { formValid = false; form.find("#purchaseDate").closest('.form-group').addClass('has-error'); }
    if (form.find("#supplierId").val() == "") { formValid = false; form.find("#supplierId").closest('.form-group').addClass('has-error'); }
    if (form.find("#paid").val() == "") { formValid = false; form.find("#paid").closest('.form-group').addClass('has-error'); }
    if (form.find("#paymentStatus").val() == "") { formValid = false; form.find("#paymentStatus").closest('.form-group').addClass('has-error'); }
    if (form.find("#dueDate").val() == "") { formValid = false; form.find("#dueDate").closest('.form-group').addClass('has-error'); }

    if (form.find("#productTable tbody tr").length < 1) {
        alert("Please add at least one product.");
        formValid = false;
    }
    return formValid;
}

// --- GLOBAL HELPER FUNCTIONS ---

function addRow() {
    $("#addRowBtn").button("loading");
    var tableLength = $("#productTable tbody tr").length;
    var count = tableLength + 1;

    $.ajax({
        url: 'php_action/fetchProductData.php',
        type: 'post',
        dataType: 'json',
        success:function(response) {
            $("#addRowBtn").button("reset");
            var optionsHtml = '<option value="">-- SELECT --</option>';
            $.each(response, function(index, value) {
                optionsHtml += '<option value="'+value.product_id+'">'+value.product_name+'</option>';                         
            });

            var tr = '<tr id="row'+count+'">'+
                '<td><select class="form-control" name="productName[]" id="productName'+count+'" onchange="getProductData('+count+')">'+optionsHtml+'</select></td>'+
                '<td><input type="text" name="rate[]" id="rate'+count+'" onkeyup="getTotal('+count+')" class="form-control" /><input type="hidden" name="rateValue[]" id="rateValue'+count+'" /></td>'+
                '<td><input type="number" name="quantity[]" id="quantity'+count+'" onkeyup="getTotal('+count+')" class="form-control" min="1" /></td>'+
                '<td>'+
                    '<input type="text" name="total[]" id="total'+count+'" class="form-control" disabled />'+
                    // ## THIS IS THE FIX: Added class="totalValue" ##
                    '<input type="hidden" name="totalValue[]" id="totalValue'+count+'" class="totalValue" />'+
                '</td>'+
                '<td><button class="btn btn-default removeProductRowBtn" type="button" onclick="removeProductRow('+count+')"><i class="glyphicon glyphicon-trash"></i></button></td>'+
            '</tr>';
            $("#productTable tbody").append(tr);
        }
    });
}

function removeProductRow(rowId) {
    $("#row" + rowId).remove();
    subAmount();
}

function getProductData(rowId) {
    var productId = $("#productName" + rowId).val();
    if (productId) {
        $.ajax({
            url: 'php_action/fetchSelectedProduct.php',
            type: 'post',
            data: { productId: productId },
            dataType: 'json',
            success: function(response) {
                $("#rate" + rowId).val(response.rate);
                $("#rateValue" + rowId).val(response.rate);
                $("#quantity" + rowId).val(1);
                getTotal(rowId);
            }
        });
    }
}

function getTotal(rowId) {
    var rate = Number($("#rate" + rowId).val());
    var qty = Number($("#quantity" + rowId).val());
    var total = (rate * qty).toFixed(2);
    $("#total" + rowId).val(total);
    $("#totalValue" + rowId).val(total);
    subAmount();
}

function subAmount() {
    var totalSubAmount = 0;
    // The class selector '.totalValue' will now work correctly
    $('.totalValue').each(function() {
        if($(this).val()) {
            totalSubAmount += Number($(this).val());
        }
    });
    totalSubAmount = totalSubAmount.toFixed(2);
    $("#subTotal").val(totalSubAmount);
    $("#subTotalValue").val(totalSubAmount);

    // ## IMPROVEMENT: Auto-calculate 18% VAT/GST ##
    var vat = (Number(totalSubAmount) / 100) * 18;
    vat = vat.toFixed(2);
    $("#vat").val(vat);
    $("#vatValue").val(vat);
    
    var grandTotal = (Number(totalSubAmount) + Number(vat)).toFixed(2);
    $("#grandTotal").val(grandTotal);
    $("#grandTotalValue").val(grandTotal);
    
    paidAmount();
}

function paidAmount() {
    var grandTotal = Number($("#grandTotalValue").val());
    var paid = Number($("#paid").val());
    var due = (grandTotal - paid).toFixed(2);
    $("#due").val(due);
    $("#dueValue").val(due);
}
function removePurchase(purchaseId = null) {
    if (purchaseId) {
        // Set the global variable to the ID of the purchase to be removed
        currentPurchaseId = purchaseId;
    }
}