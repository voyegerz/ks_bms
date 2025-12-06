var manageChallanTable;
var currentChallanId = null; 

$(document).ready(function() {
    var divRequest = $(".div-request").text();

    $("#navChallan").addClass('active');

    if (divRequest == 'add') {
        $('#topNavAddChallan').addClass('active');
        $("#challanDate").datepicker({ dateFormat: 'yy-mm-dd' });

        // Auto-fill party contact when a party is selected
        $("#partyId").on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var contact = selectedOption.data('contact') || '';
            $('.clientContact').val(contact); 
        });

        // Create challan form submission
        $("#createChallanForm").unbind('submit').bind('submit', function() {
            var form = $(this);
            $('.form-group').removeClass('has-error').removeClass('has-success');
            $('.text-danger').remove();

            var formValid = true; // Use a validation flag

            // Validate Challan Date
            if ($("#challanDate").val() == "") {
                $("#challanDate").after('<p class="text-danger">Challan Date is required</p>');
                $('#challanDate').closest('.form-group').addClass('has-error');
                formValid = false;
            } else {
                $('#challanDate').closest('.form-group').addClass('has-success');
            }

            // Validate Party
            if ($("#partyId").val() == "") {
                $("#partyId").after('<p class="text-danger">Party is required</p>');
                $('#partyId').closest('.form-group').addClass('has-error');
                formValid = false;
            } else {
                $('#partyId').closest('.form-group').addClass('has-success');
            }

            if ($("#orderId").val() == "") {
                $("#orderId").after('<p class="text-danger">Reference Order ID is required</p>');
                $('#orderId').closest('.form-group').addClass('has-error');
                formValid = false;
            } else {
                $('#orderId').closest('.form-group').addClass('has-success');
            }
            // Validate product rows
            var productName = document.getElementsByName('productName[]');
            if (productName.length < 1) {
                alert("Please add at least one product to the challan.");
                formValid = false;
            } else {
                $('select[name="productName[]"], input[name="quantity[]"]').each(function() {
                    if ($(this).val() == "" ) {
                        $(this).closest('.form-group').addClass('has-error');
                        formValid = false;
                    } else {
                        $(this).closest('.form-group').addClass('has-success');
                    }
                });
            }

            if (formValid) {
                // Get the selected party's name from the data-attribute
                var partyName = $("#partyId").find('option:selected').data('party-name');
                var formData = form.serialize() + '&partyName=' + encodeURIComponent(partyName);

                $("#createChallanBtn").button('loading');
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        $("#createChallanBtn").button('reset');
                        if (response.success == true) {
                            window.location = 'challans.php?o=manchallan';
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
    } 
    else if (divRequest == 'manchallan') {
        $('#topNavManageChallan').addClass('active');
        manageChallanTable = $("#manageChallanTable").DataTable({
            'ajax': 'php_action/fetchChallan.php',
            'order': []
        });
    
    
    // Clear the challan ID when modal is hidden
    $('#removeChallanModal').on('hidden.bs.modal', function () {
        currentChallanId = null;
    });
    } 
    else if (divRequest == 'editChallan') {
        $('#topNavManageChallan').addClass('active');
        $("#challanDate").datepicker({ dateFormat: 'yy-mm-dd' });
        
        // Auto-fill party contact when a party is selected on the edit page
        $("#partyId").on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var contact = selectedOption.data('contact') || '';
            $('.clientContact').val(contact);
        });

        // --- Full validation for the Edit form ---
        $("#editChallanForm").unbind('submit').bind('submit', function() {
            var form = $(this);
            $('.form-group').removeClass('has-error').removeClass('has-success');
            $('.text-danger').remove();

            var formValid = true;

            if ($("#challanDate").val() == "") {
                $("#challanDate").after('<p class="text-danger">Challan Date is required</p>');
                $('#challanDate').closest('.form-group').addClass('has-error');
                formValid = false;
            } else {
                $('#challanDate').closest('.form-group').addClass('has-success');
            }

            if ($("#partyId").val() == "") {
                $("#partyId").after('<p class="text-danger">Party is required</p>');
                $('#partyId').closest('.form-group').addClass('has-error');
                formValid = false;
            } else {
                $('#partyId').closest('.form-group').addClass('has-success');
            }

            var productName = document.getElementsByName('productName[]');
            if (productName.length < 1) {
                alert("Please add at least one product to the challan.");
                formValid = false;
            } else {
                $('select[name="productName[]"], input[name="quantity[]"]').each(function() {
                    if ($(this).val() == "" ) {
                        $(this).closest('.form-group').addClass('has-error');
                        formValid = false;
                    } else {
                        $(this).closest('.form-group').addClass('has-success');
                    }
                });
            }

            if (formValid) {
                $("#editChallanBtn").button('loading');
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        $("#editChallanBtn").button('reset');
                        if (response.success == true) {
                            $(".success-messages").html('<div class="alert alert-success">'+
                                '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                                '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                                ' <br/><br/><a href="challans.php?o=manchallan" class="btn btn-default"> <i class="glyphicon glyphicon-list-alt"></i> View Challans </a>'+
                               '</div>');
                            $("html, body").animate({scrollTop: '0px'}, 100);
                        } else {
                            alert("Error: " + response.messages);
                        }
                    }
                });
            }
            return false;
        });
    }

    $('#removeChallanBtn').off('click').on('click', function() {
        console.log("Remove button clicked, challanId:", currentChallanId); // Debug
        
        if (!currentChallanId) {
            alert("No challan selected for removal");
            return;
        }
        
        $("#removeChallanBtn").button("loading");
        
        $.ajax({
            url: 'php_action/removeChallan.php',
            type: 'post',
            data: { challan_id: currentChallanId },
            dataType: 'json',
            success: function(response) {
                console.log("AJAX response:", response); // Debug
                
                $("#removeChallanBtn").button("reset");
                
                if (response.success == true) {
                    // Reload the table if it exists
                    if (typeof manageChallanTable !== 'undefined') {
                        manageChallanTable.ajax.reload(null, false);
                    }
                    
                    $("#removeChallanModal").modal('hide');
                    
                    $('#success-messages').html('<div class="alert alert-success">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                    '</div>');
                    
                    // Hide success message after a few seconds
                    $(".alert-success").delay(500).show(10, function() {
                        $(this).delay(3000).hide(10, function() {
                            $(this).remove();
                        });
                    });
                } else {
                    $(".removeChallanMessages").html('<div class="alert alert-danger">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                    '</div>');
                }
                
                // Reset the current challan ID
                currentChallanId = null;
            },
            error: function(xhr, status, error) {
                console.log("AJAX Error:", error); // Debug
                $("#removeChallanBtn").button("reset");
                alert("Error occurred: " + error);
                currentChallanId = null;
            }
        });
    });
});

// Add Row function for product table
function addRow() {
    $("#addRowBtn").button("loading");

    var tableLength = $("#productTable tbody tr").length;
    var tableRow;
    var count;

    if(tableLength > 0) {
        tableRow = $("#productTable tbody tr:last").attr('id');
        count = tableRow.substring(3);
        count = Number(count) + 1;
    } else {
        count = 1;
    }

    $.ajax({
        url: 'php_action/fetchProductData.php', // This will fetch all active products for the dropdown
        type: 'post',
        dataType: 'json',
        success:function(response) {
            $("#addRowBtn").button("reset");

            var tr = '<tr id="row'+count+'">'+
                '<td style="margin-left:20px;">'+
                    '<select class="form-control" name="productName[]" id="productName'+count+'" onchange="getProductData('+count+')">'+
                        '<option value="">~~SELECT~~</option>';
                        $.each(response, function(index, value) {
                            tr += '<option value="'+value.product_id+'">'+value.product_name+'</option>';
                        });
                    tr += '</select>'+
                '</td>'+
                '<td>'+
                        '<textarea style="height:34px;" class="form-control" id="itemRemarks'+count+'" name="itemRemarks[]" placeholder="Any special instructions or notes"></textarea>'+
                '</td>'+
                '<td>'+
                        '<p  style="text-align: center; vertical-align: middle;" id="available_quantity'+count+'"></p>'+
                '</td>'+
                '<td ">'+
                        '<input type="text" name="quantity[]" id="quantity'+count+'" autocomplete="off" class="form-control" min="0" />'+
                '</td>'+
                '<td>'+
                    '<button class="btn btn-default removeProductRowBtn" type="button" onclick="removeProductRow('+count+')"><i class="glyphicon glyphicon-trash"></i></button>'+
                '</td>'+
            '</tr>';
            if(tableLength > 0) {
                $("#productTable tbody tr:last").after(tr);
            } else {
                $("#productTable tbody").append(tr);
            }
        } // /success
    }); // get the product data

    return false;
}


// Remove Row function
function removeProductRow(rowId) {
    $("#row"+rowId).remove();
}

// Get Product Data (e.g., available quantity)
function getProductData(rowId) {
    var productId = $("#productName"+rowId).val();
    if(productId == "") {
        $("#available_quantity"+rowId).text("");
        $("#quantity"+rowId).val("");
    } else {
        $.ajax({
            url: 'php_action/fetchSelectedProduct.php', // This will fetch data for a single product
            type: 'post',
            dataType: 'json',
            data: {productId : productId},
            success:function(response) {
                // Set the available quantity
                
                $("#available_quantity"+rowId).text(response.quantity);
                $("#quantity"+rowId).attr('max', response.quantity); // Set max attribute for input number
                $("#quantity"+rowId).val(0); // Default to 1, or previous value if editing
            } // /success
        }); // /ajax function to fetch the product data
    }
}

// Reset Challan Form
function resetChallanForm(isSuccess = false) {
    $("#createChallanForm")[0].reset();
    $('#productTable tbody tr').not(':first').remove(); // Remove all but the first row
    $('#productName1').val('');
    $('#available_quantity1').text('');
    $('#quantity1').val('');
    $("#orderId").val(''); // Clear order ID field
    $("#remarks").val(''); // Clear remarks field
    // Only clear success messages if it's a manual reset, not after successful submission
    if (!isSuccess) {
        $(".success-messages").html('');
    }

    // Re-enable buttons if they were disabled (for a fresh form)
    if (!isSuccess) {
        $(".submitButtonFooter").removeClass('div-hide');
        $(".removeProductRowBtn").removeClass('div-hide');
    } else {
        // If it's a success reset, we want the buttons to stay hidden initially
        // The "Add New Challan" button will link back to challans.php?o=add, which will re-initialize
        // the form and make them visible.
        $(".submitButtonFooter").addClass('div-hide');
        $(".removeProductRowBtn").addClass('div-hide');
    }
}


// Remove Challan - Fixed version
var currentChallanId = null;

function removeChallan(challanId = null) {
    console.log("removeChallan called with ID:", challanId);
    
    if (challanId) {
        currentChallanId = challanId;
        // Modal will show automatically due to data-toggle="modal" in the HTML
    }
}


// Print Challan function
function printChallan(challanId = null) {
    if(challanId) {
        // Direct link to the print page for simplicity, or use AJAX if you need to manipulate response
        var printUrl = 'php_action/printChallan.php?i=' + challanId;
        var mywindow = window.open(printUrl, 'Sales Challan', 'height=600,width=800');
        mywindow.focus();
        mywindow.onload = function() { // Ensure content is loaded before printing
            setTimeout(function() {
                mywindow.print();
                mywindow.close();
            }, 1000); // Give it a bit more time to render
        };
    }
}


// Update Challan Status - not used directly in challans.php but if you add a button to trigger this from manage challans table
function updateChallanStatus(challanId = null) {
    if(challanId) {
        // Fetch current status
        $.ajax({
            url: 'php_action/fetchSingleChallan.php', // You'll need this file too
            type: 'post',
            dataType: 'json',
            data: {challan_id : challanId},
            success:function(response) {
                $("#editChallanStatus").val(response.challan_status);
                $('#updateChallanStatusBtn').unbind('click').bind('click', function() {
                    $("#updateChallanStatusBtn").button("loading");
                    $.ajax({
                        url: 'php_action/updateChallanStatus.php', // You'll need this file
                        type: 'post',
                        data: {challanId : challanId, challanStatus : $("#editChallanStatus").val()},
                        dataType: 'json',
                        success:function(response) {
                            $("#updateChallanStatusBtn").button("reset");
                            if(response.success == true) {
                                manageChallanTable.ajax.reload(null, false);
                                $("#updateChallanStatusModal").modal('hide');
                                $('#success-messages').html('<div class="alert alert-success">'+
                                    '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                                    '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                                '</div>');
                                // Hide success message after a few seconds
                                $(".alert-success").delay(500).show(10, function() {
                                    $(this).delay(3000).hide(10, function() {
                                        $(this).remove();
                                    });
                                });
                            } else {
                                $(".updateChallanStatusMessages").html('<div class="alert alert-warning">'+
                                    '<button type="button" class="close" data-dismiss="alert">&times;'+
                                    '</button>'+
                                    '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                                '</div>');
                            }
                        }
                    });
                });
            }
        });
    }
}


function parseQuantity(inputValue) {
    if (typeof inputValue !== 'string' || inputValue.indexOf('/') === -1) {
        return { quantity: Number(inputValue) || 0, size: null };
    }
    const parts = inputValue.split('/');
    const quantity = Number(parts[0]) || 0;
    const size = parts[1] ? parts[1].trim() : null; // Now treated as a string
    return { quantity: quantity, size: size };
}