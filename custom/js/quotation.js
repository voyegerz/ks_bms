var manageQuotationTable;
var currentQuotationId = null;

$(document).ready(function() {
    $('#navQuotation').addClass('active');
    var divRequest = $(".div-request").text();

    if (divRequest == 'manage') {
        // Initialize the manage quotations data table
        manageQuotationTable = $("#manageQuotationTable").DataTable({
            'ajax': 'php_action/fetchQuotations.php',
            'order': []
        });

        // --- REMOVE QUOTATION MODAL LOGIC ---
        $('#removeQuotationBtn').on('click', function() {
            if (!currentQuotationId){
                alert("Error: No Quotation ID found. Please refresh.");
                return;
            };

            $("#removeQuotationBtn").button("loading");

            $.ajax({
                url: 'php_action/removeQuotation.php',
                type: 'post',
                data: { quotationId: currentQuotationId },
                dataType: 'json',
                success: function(response) {
                    $("#removeQuotationBtn").button("reset");
                    if (response.success == true) {
                        // Reload the data table
                        manageQuotationTable.ajax.reload(null, false);
                        
                        // Close the modal
                        $("#removeQuotationModal").modal('hide');
                        
                        // Show a temporary success message on the main page
                        $(".success-messages").html('<div class="alert alert-success alert-dismissible" role="alert">'+
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
                        '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                        '</div>');
                        
                        // Auto-hide the success message after 3 seconds
                        $(".alert-success").delay(500).show(10, function() {
                            $(this).delay(3000).hide(10, function() {
                                $(this).remove();
                            });
                        });
                    } else {
                        // Show an error message inside the modal
                        $(".removeQuotationMessages").html('<div class="alert alert-danger alert-dismissible" role="alert">'+
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                        '</div>');
                    }
                }
            });
        });
    } else if (divRequest == 'add' || divRequest == 'edit') {
        // Initialize date picker for the add form
        $("#quotationDate").datepicker({ dateFormat: 'yy-mm-dd' });

        // On page load for the EDIT page, calculate initial totals
        if(divRequest == 'edit') {
            subAmount();
            
        }

        // Add Quotation Form submission
        $("#createQuotationForm").unbind('submit').bind('submit', function() {
            var form = $(this);
            $('.form-group').removeClass('has-error').removeClass('has-success');
            $('.text-danger').remove();

            var formValid = true;
            
            // --- Form validation ---
            if ($("#quotationDate").val() == "") {
                $("#quotationDate").closest('.form-group').addClass('has-error');
                formValid = false;
            }
            if ($("#partyId").val() == "") {
                $("#partyId").closest('.form-group').addClass('has-error');
                formValid = false;
            }

            var productCount = $("#productTable tbody tr").length;
            if (productCount < 1) {
                alert("Please add at least one product to the quotation.");
                formValid = false;
            } else {
                $('select[name="productName[]"], input[name="quantity[]"], input[name="rate[]"]').each(function() {
                    if ($(this).val() == "") {
                        $(this).closest('td').find('.form-group, .form-control').addClass('has-error');
                        formValid = false;
                    }
                });
            }

            if (formValid) {
                $("#createQuotationBtn").button('loading');
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        $("#createQuotationBtn").button('reset');
                        if (response.success == true) {
                            window.location = 'quotations.php?q=manage';
                        } else {
                            $(".success-messages").html('<div class="alert alert-danger">'+
                                '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                                '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                              '</div>');
                        }
                    }
                });
            }
            return false;
        });

        // --- EDIT QUOTATION FORM SUBMISSION ---
        $("#editQuotationForm").unbind('submit').bind('submit', function() {
            var form = $(this);
            if (validateQuotationForm(form)) {
                form.find('button[type="submit"]').button('loading');
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        form.find('button[type="submit"]').button('reset');
                        if (response.success == true) {
                            $(".success-messages").html('<div class="alert alert-success alert-dismissible" role="alert">'+
                              '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
                              '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                              ' <br/><br/><a href="quotations.php?q=manage" class="btn btn-default"><i class="glyphicon glyphicon-list-alt"></i> Manage Quotations</a>'+
                            '</div>');
                            
                            $(".alert-success").delay(500).show(10, function() {
                                $(this).delay(5000).hide(10, function() {
                                    $(this).remove();
                                    window.location = 'quotations.php?q=manage';
                                });
                            });
                        } else {
                             $(".success-messages").html('<div class="alert alert-danger alert-dismissible" role="alert">'+
                              '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
                              '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                            '</div>');
                        }
                    }
                });
            }
            return false;
        });
    }
});

// --- GLOBAL HELPER FUNCTIONS ---

function validateQuotationForm(form) {
    // Basic validation logic
    var isValid = true;
    if(form.find("#quotationDate").val() == "") { isValid = false; }
    if(form.find("#partyId").val() == "") { isValid = false; }
    if(form.find("#productTable tbody tr").length < 1) { isValid = false; alert("Please add at least one product."); }
    return isValid;
}

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
                '<td><input type="text" name="total[]" id="total'+count+'" class="form-control" disabled /><input type="hidden" class="totalValue" name="totalValue[]" id="totalValue'+count+'" /></td>'+
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
    $('.totalValue').each(function() {
        if($(this).val()) {
            totalSubAmount += Number($(this).val());
        }
    });
    totalSubAmount = totalSubAmount.toFixed(2);
    $("#subTotal").val(totalSubAmount);
    $("#subTotalValue").val(totalSubAmount);

    var vat = (Number(totalSubAmount) / 100) * 18; // Assumes 18% tax
    vat = vat.toFixed(2);
    $("#vat").val(vat);
    $("#vatValue").val(vat);
    
    var grandTotal = (Number(totalSubAmount) + Number(vat)).toFixed(2);
    $("#grandTotal").val(grandTotal);
    $("#grandTotalValue").val(grandTotal);
}

// This function is called by the 'onclick' to set the ID
function removeQuotation(quotationId = null) {
    if (quotationId) {
        currentQuotationId = quotationId;
    }
}

// Print Quotation function
function printQuotation(quotationId = null) {
    if(quotationId) {
        // Direct link to the print page for simplicity, or use AJAX if you need to manipulate response
        var printUrl = 'php_action/printQuotation.php?i=' + quotationId;
        var mywindow = window.open(printUrl, 'Quotation', 'height=600,width=800');
        mywindow.focus();
        mywindow.onload = function() { // Ensure content is loaded before printing
            setTimeout(function() {
                mywindow.print();
                mywindow.close();
            }, 1000); // Give it a bit more time to render
        };
    }
}