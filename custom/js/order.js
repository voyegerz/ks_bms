var manageOrderTable;

$(document).ready(function() {
    // --- SETUP LISTENERS ---
    $("#paymentPlace").change(function() {
        if ($("#paymentPlace").val() == 2) {
            $(".gst").text("IGST 18%");
        } else {
            $(".gst").text("GST 18%");
        }
    });

    // This listener works for both Add and Edit forms
    $("#createOrderForm, #editOrderForm").on('change', '#partyId', function() {
        var selectedOption = $(this).find('option:selected');
        var contact = selectedOption.data('contact') || '';
        var gstin = selectedOption.data('gstin') || '';
        var form = $(this).closest('form');
        form.find('.clientContact').val(contact);
        form.find('.gstn').val(gstin);
    });

    var divRequest = $(".div-request").text();
    $("#navOrder").addClass('active');

    // --- ADD ORDER & ADD AGAINST CHALLAN PAGE LOGIC (COMBINED) ---
    if (divRequest == 'add' || divRequest == 'addAgainstChallan') { 
        
        $("#orderDate, #otherPoDate").datepicker({ dateFormat: 'yy-mm-dd' });

        // Fetch the next invoice number on page load
        $.ajax({
            url: 'php_action/fetchNextInvoiceNo.php',
            type: 'post',
            dataType: 'json',
            success: function(response) {
                // Pre-fill the invoice number input field
                $("#invoiceNo").val(response.invoice_no);
            }
        });
      

        // --- Logic that is ONLY for the 'addAgainstChallan' page ---
        if (divRequest == 'addAgainstChallan') {
            $('#topNavAddOrderAgainstChallan').addClass('active');
            
            // 1. When "Get Challans" is clicked, open the modal
            $("#getChallansBtn").on('click', function(){
                var partyId = $("#partyIdForChallan").val();
                var modalBody = $("#challanListModalBody");
                
                if (!partyId) { 
                    alert("Please select a Party.");
                    return;
                }

                $('#challanSelectionModal').modal('show');
                modalBody.html('<p class="text-center">Loading...</p>');

                $.ajax({
                    url: 'php_action/fetchOpenChallans.php',
                    type: 'post', 
                    data: { party_id: partyId }, 
                    dataType: 'json',
                    success: function(response){
                        if(response.success && response.challans.length > 0){
                            var html = '<h6>Select challans to include in this order:</h6>';
                            response.challans.forEach(function(challan){
                                html += '<div class="checkbox"><label>' +
                                    '<input type="checkbox" class="challan-checkbox" value="' + challan.challan_id + '"> ' +
                                    'Challan #' + challan.challan_id + ' (Dated: ' + challan.challan_date + ')' + ' Challan No: ' + challan.challan_no +
                                    '</label></div>';
                            });
                            modalBody.html(html);
                        } else {
                            modalBody.html('<p class="text-muted text-center">No unbilled challans found for this party.</p>');
                        }
                    }
                });
            });

            // 2. When the "Apply" button inside the modal is clicked
			// in custom/js/order.js
            $("#applyChallansBtn").on('click', function(){
                var selectedChallans = [];
                var challanRefs = [];

                $('.challan-checkbox:checked').each(function(){
                    selectedChallans.push($(this).val());

                    var labelText = $(this).closest('label').text().trim();
                    var dateMatch = labelText.match(/\(Dated: (.*?)\)/);
                    var noMatch = labelText.match(/Challan No: (.*)/); // Number match

                    var date = dateMatch ? dateMatch[1] : ''; // e.g., "2025-10-27"
                    var challanNo = noMatch ? noMatch[1].trim() : ''; // e.g., "HDBA/761"

                    if (challanNo && date) {
                        challanRefs.push(challanNo + '-' + date);
                    }

                });

                if (selectedChallans.length > 0) {
                    $("#createOrderForm").find("#challanIds").remove(); 
                    $("#createOrderForm").append('<input type="hidden" id="challanIds" name="challanIds" value="' + selectedChallans.join(',') + '">');
                    
                    var challanRefString = challanRefs.join(', '); // e.g., "HDBA/761-2025-10-27, HDBA/762-2025-10-28"
                    $("#createOrderForm").find("#refChallanNos").remove(); 
                    $("#createOrderForm").append('<input type="hidden" id="refChallanNos" name="refChallanNos" value="' + challanRefString + '">');

                    
                    var partyId = $("#partyIdForChallan").val();
                    $("#partyId").val(partyId).trigger('change');

                    // --- CORRECTED LOGIC ---
                    // AJAX Call 1: Get the products and quantities from the selected challans
                    $.ajax({
                        url: 'php_action/fetchChallanProducts.php',
                        type: 'post',
                        data: { challan_ids: selectedChallans },
                        dataType: 'json',
                        success: function(challanProductsResponse){
                            if(challanProductsResponse.success && challanProductsResponse.products.length > 0){
                                
                                // AJAX Call 2: Get the list of ALL available products to build the dropdowns
                                $.ajax({
                                    url: 'php_action/fetchProductData.php',
                                    type: 'post',
                                    dataType: 'json',
                                    success: function(allProductsResponse) {
                                        $("#productTable tbody").empty();

                                        var productsFromChallan = challanProductsResponse.products;
                                        var allProducts = allProductsResponse;
                                        
                                        // Loop through the products from the challan
                                        productsFromChallan.forEach(function(product, index) {
                                            var count = index + 1;
                                            
                                            // Build the HTML for the product dropdown
                                            var optionsHtml = '<option value="">-- SELECT --</option>';
                                            allProducts.forEach(function(p){
                                                var selected = (p.product_id == product.product_id) ? 'selected' : '';
                                                optionsHtml += '<option value="'+p.product_id+'" '+selected+'>'+p.product_name+'</option>';
                                            });

                                            // Build the entire table row HTML string in one go
                                            var tr = '<tr id="row'+count+'" style="vertical-align: middle;">' +
                                                '<td><select class="form-control" name="productName[]" id="productName'+count+'" onchange="getProductData('+count+')">'+optionsHtml+'</select></td>' +
                                                '<td><textarea name="OiRemarks[]" id="OiRemarks'+count+'" class="form-control" style="height:34px;">'+(product.remarks || '')+'</textarea></td>' +
                                                '<td><input type="text" name="rate[]" id="rate'+count+'" class="form-control" /><input type="hidden" name="rateValue[]" id="rateValue'+count+'" /></td>' +
                                                '<td class="text-center"><p id="available_quantity'+count+'" style="margin: 0;"></p></td>' +
                                                '<td><input type="text" name="quantity[]" id="quantity'+count+'" onkeyup="getTotal('+count+')" class="form-control" min="0" value="'+product.remaining_quantity+'/'+product.size+'" /></td>' +
                                                '<td><input type="text" name="total[]" id="total'+count+'" onkeyup="getTotal('+count+')" class="form-control"  /><input type="hidden" name="totalValue[]" id="totalValue'+count+'" onkeyup="getTotal('+count+')" /></td>' +
                                                '<td class="text-center"><button class="btn btn-default removeProductRowBtn" type="button" onclick="removeProductRow('+count+')"><i class="glyphicon glyphicon-trash"></i></button></td>' +
                                            '</tr>';
                                            
                                            // Append the fully built row to the table
                                            $("#productTable tbody").append(tr);
                                            
                                            // Trigger the change event to fetch rate and available qty for this specific row
                                            $("#productName"+count).trigger('change');
                                            $("#total"+count).trigger("keyup");
                                            
                                            // getTotal(count); // Calculate total for this row
                                            
                                        });

                                        // Calculate the final totals after all rows have been added and populated
                                        subAmount();
                                    },
                                    error: function() {
                                        alert("Error: Could not fetch the full product list.");
                                    }
                                });
                            } else {
                                alert("No products found in the selected challans.");
                            }
                        },
                        error: function() {
                            alert("Error: Could not fetch products from challans.");
                        }
                    });
                    
                    $('#challanSelectionModal').modal('hide');
                } else {
                    alert('Please select at least one challan.');
                }
            });

        } else {
            // Logic ONLY for the regular 'add' page
            $('#topNavAddOrder').addClass('active');
        }


        $("#createOrderForm").unbind('submit').bind('submit', function() {
            var form = $(this);
            $('.form-group').removeClass('has-error').removeClass('has-success');
            $('.text-danger').remove();

            var formValid = true; // Use a single flag for robust validation
            
            // ## NEW VALIDATION CHECK ##
            if ($("#invoiceNo").val() == "") {
                $("#invoiceNo").closest('.form-group').addClass('has-error');
                formValid = false;
            } else {
                $("#invoiceNo").closest('.form-group').addClass('has-success');
            }
            
            // Main form field validation
            if ($("#orderDate").val() == "") {
                $("#orderDate").after('<p class="text-danger">Order Date is required</p>');
                $('#orderDate').closest('.form-group').addClass('has-error');
                formValid = false;
            } else {
                $('#orderDate').closest('.form-group').addClass('has-success');
            }

            if ($("#partyId").val() == "") {
                $("#partyId").after('<p class="text-danger">Party is required</p>');
                $('#partyId').closest('.form-group').addClass('has-error');
                formValid = false;
            } else {
                $('#partyId').closest('.form-group').addClass('has-success');
            }

            if ($("#paid").val() === "") {
                $("#paid").after('<p class="text-danger">Paid Amount is required</p>');
                $('#paid').closest('.form-group').addClass('has-error');
                formValid = false;
            } else {
                $('#paid').closest('.form-group').addClass('has-success');
            }

            if ($("#paymentType").val() == "") {
                $("#paymentType").after('<p class="text-danger">Payment Type is required</p>');
                $('#paymentType').closest('.form-group').addClass('has-error');
                formValid = false;
            } else {
                $('#paymentType').closest('.form-group').addClass('has-success');
            }

            if ($("#paymentStatus").val() == "") {
                $("#paymentStatus").after('<p class="text-danger">Payment Status is required</p>');
                $('#paymentStatus').closest('.form-group').addClass('has-error');
                formValid = false;
            } else {
                $('#paymentStatus').closest('.form-group').addClass('has-success');
            }

            // Table item validation
            var productName = document.getElementsByName('productName[]');
            if (productName.length < 1) {
                alert("Please add at least one product to the order.");
                formValid = false;
            } else {
                $('select[name^="productName"], input[name^="quantity"]').each(function() {
                    if ($(this).val() == "" ) {
                        $(this).closest('.form-group').addClass('has-error');
                        formValid = false;
                    } else {
                        $(this).closest('.form-group').addClass('has-success');
                    }
                });
            }

            // Final check before submitting
            if (formValid) {
                if ($("#discount").val() == "") { $("#discount").val(0); }
                $("#createOrderBtn").button('loading');
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        $("#createOrderBtn").button('reset');
                        if (response.success == true) {
                            $(".success-messages").html('<div class="alert alert-success">' +
                                '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                                '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> ' + response.messages +
                                ' <br /> <br /> <a type="button" onclick="printOrder(' + response.order_id + ')" class="btn btn-primary"> <i class="glyphicon glyphicon-print"></i> Print </a>' +
                                '<a href="orders.php?o=add" class="btn btn-default" style="margin-left:10px;"> <i class="glyphicon glyphicon-plus-sign"></i> Add New Order </a>' +
                                '</div>');
                            $("html, body").animate({ scrollTop: '0px' }, 100);
                            $("#createOrderForm")[0].reset();
                            $(".submitButtonFooter").hide();
                            $("#productTable tbody").empty();
                        } else {
                            alert(response.messages);
                        }
                    }
                });
            }
            return false;
        });

        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('from_quote')) {
            // If it exists, run the total calculation immediately
            subAmount();
            // Also auto-fill the contact info
            $('#partyId').trigger('change');
        }
    } else if (divRequest == 'manord') {
        $('#topNavManageOrder').addClass('active');
        manageOrderTable = $("#manageOrderTable").DataTable({
            'ajax': 'php_action/fetchOrder.php',
            'order': []
        });
    } else if (divRequest == 'editOrd') {
        // This now includes the full, corrected validation logic for the edit form.
        $('#topNavManageOrder').addClass('active');
        $("#orderDate, #editOtherPoDate").datepicker();


        $("#editOrderForm").unbind('submit').bind('submit', function() {
            var form = $(this);
            $('.form-group').removeClass('has-error').removeClass('has-success');
            $('.text-danger').remove();

            var formValid = true; // Use a single flag for robust validation

            // Main form field validation
            if ($("#orderDate").val() == "") {
                $("#orderDate").after('<p class="text-danger">Order Date is required</p>');
                $('#orderDate').closest('.form-group').addClass('has-error');
                formValid = false;
            } else {
                $('#orderDate').closest('.form-group').addClass('has-success');
            }

            if ($("#partyId").val() == "") {
                $("#partyId").after('<p class="text-danger">Party is required</p>');
                $('#partyId').closest('.form-group').addClass('has-error');
                formValid = false;
            } else {
                $('#partyId').closest('.form-group').addClass('has-success');
            }

            if ($("#paid").val() === "") {
                $("#paid").after('<p class="text-danger">Paid Amount is required</p>');
                $('#paid').closest('.form-group').addClass('has-error');
                formValid = false;
            } else {
                $('#paid').closest('.form-group').addClass('has-success');
            }

            if ($("#paymentType").val() == "") {
                $("#paymentType").after('<p class="text-danger">Payment Type is required</p>');
                $('#paymentType').closest('.form-group').addClass('has-error');
                formValid = false;
            } else {
                $('#paymentType').closest('.form-group').addClass('has-success');
            }

            if ($("#paymentStatus").val() == "") {
                $("#paymentStatus").after('<p class="text-danger">Payment Status is required</p>');
                $('#paymentStatus').closest('.form-group').addClass('has-error');
                formValid = false;
            } else {
                $('#paymentStatus').closest('.form-group').addClass('has-success');
            }

            // Table item validation
            var productName = document.getElementsByName('productName[]');
            if (productName.length < 1) {
                alert("An order must have at least one product.");
                formValid = false;
            } else {
                 $('input[name^="productName"], input[name^="quantity"]').each(function() {
                    if ($(this).val() == "" ) {
                        $(this).closest('.form-group').addClass('has-error');
                        formValid = false;
                    } else {
                        $(this).closest('.form-group').addClass('has-success');
                    }
                });
            }

            // Final check before submitting
            if (formValid) {
                 if ($("#discount").val() == "") { $("#discount").val(0); }
                $("#editOrderBtn").button('loading');
                $.ajax({
                    url: form.attr('action'),
                    type: form.attr('method'),
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        $("#editOrderBtn").button('reset');
                        if (response.success == true) {
                            $(".success-messages").html('<div class="alert alert-success">' +
                                '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                                '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> ' + response.messages +
                                ' <br /><br /><a href="orders.php?o=manord" class="btn btn-default" style="margin-left:10px;"> <i class="glyphicon glyphicon-list-alt"></i> View Orders </a>' +
                                '</div>');
                            $("html, body").animate({ scrollTop: '0px' }, 100);
                            $(".editButtonFooter").hide();
                        } else {
                            alert(response.messages);
                        }
                    }
                });
            }
            return false;
        });
    }
    
});



// print order function
function printOrder(orderId = null) {
	if(orderId) {		
			
		$.ajax({
			url: 'php_action/printOrder.php',
			type: 'post',
			data: {orderId: orderId},
			dataType: 'text',
			success:function(response) {
				
				var mywindow = window.open('', 'Stock Management System', 'height=400,width=600');
                mywindow.document.write('<html><head><title>Order Invoice</title>');        
                mywindow.document.write('</head><body>');
                mywindow.document.write(response);
                mywindow.document.write('</body></html>');

                mywindow.document.close(); // necessary for IE >= 10
                mywindow.focus(); // necessary for IE >= 10
                mywindow.resizeTo(screen.width, screen.height);
                setTimeout(function() {
                    mywindow.print();
                    mywindow.close();
                }, 1250);
				
			}
		}); 
	} // /if orderId
} // /print order function

function addRow() {
	$("#addRowBtn").button("loading");

	var tableLength = $("#productTable tbody tr").length;
	var count = tableLength > 0 ? Number($("#productTable tbody tr:last").attr('id').substring(3)) + 1 : 1;

	$.ajax({
		url: 'php_action/fetchProductData.php',
		type: 'post',
		dataType: 'json',
		success:function(response) {
			$("#addRowBtn").button("reset");			

			var tr = '<tr id="row'+count+'" style="vertical-align: middle;">'+
				'<td>'+
					// CORRECTED: Removed the extra form-group div
					'<select class="form-control" name="productName[]" id="productName'+count+'" onchange="getProductData('+count+')" >'+
						'<option value="">-- SELECT --</option>';
						$.each(response, function(index, value) {
							tr += '<option value="'+value.product_id+'">'+value.product_name+'</option>';							
						});
					tr += '</select>'+
				'</td>'+
                '<td><textarea name="OiRemarks[]" id="OiRemarks'+count+'" class="form-control" style="height:34px;"></textarea></td>'+
				'<td>'+
					'<input type="text" name="rate[]" id="rate'+count+'" autocomplete="off" class="form-control" />'+
					'<input type="hidden" name="rateValue[]" id="rateValue'+count+'" autocomplete="off" class="form-control" />'+
				'</td>'+
				'<td class="text-center">'+
					'<p id="available_quantity'+count+'" style="margin: 0;"></p>'+
				'</td>'+
				'<td>'+
					'<input type="text" name="quantity[]" id="quantity'+count+'" onkeyup="getTotal('+count+')" autocomplete="off" class="form-control" min="0" />'+
				'</td>'+
				'<td>'+
					'<input type="text" name="total[]" id="total'+count+'" autocomplete="off" class="form-control" onkeyup="getTotal('+count+')" />'+
					'<input type="hidden" name="totalValue[]" id="totalValue'+count+'" autocomplete="off" class="form-control" onkeyup="getTotal('+count+')" />'+
				'</td>'+
				'<td class="text-center">'+
					'<button class="btn btn-default removeProductRowBtn" type="button" onclick="removeProductRow('+count+')"><i class="glyphicon glyphicon-trash"></i></button>'+
				'</td>'+
			'</tr>';

			if(tableLength > 0) {							
				$("#productTable tbody tr:last").after(tr);
			} else {				
				$("#productTable tbody").append(tr);
			}		

		} // /success
	});	// get the product data

} // /add row


function removeProductRow(row = null) {
	if(row) {
		$("#row"+row).remove();


		subAmount();
	} else {
		alert('error! Refresh the page again');
	}
}

// select on product data
function getProductData(row = null) {

	if(row) {
		var productId = $("#productName"+row).val();		
		
		if(productId == "") {
			$("#rate"+row).val("");

			$("#quantity"+row).val("");						
			$("#total"+row).val("");

			// remove check if product name is selected
			// var tableProductLength = $("#productTable tbody tr").length;			
			// for(x = 0; x < tableProductLength; x++) {
			// 	var tr = $("#productTable tbody tr")[x];
			// 	var count = $(tr).attr('id');
			// 	count = count.substring(3);

			// 	var productValue = $("#productName"+row).val()

			// 	if($("#productName"+count).val() == "") {					
			// 		$("#productName"+count).find("#changeProduct"+productId).removeClass('div-hide');	
			// 		console.log("#changeProduct"+count);
			// 	}											
			// } // /for

		} else {
			$.ajax({
				url: 'php_action/fetchSelectedProduct.php',
				type: 'post',
				data: {productId : productId},
				dataType: 'json',
				success:function(response) {
					// setting the rate value into the rate input field
					
					$("#rate"+row).val(response.rate);
					$("#rateValue"+row).val(response.rate);

					if ($("#quantity"+row).val() == "" || Number($("#quantity"+row).val()) == 0) {
                        $("#quantity"+row).val(0);
                    }
					$("#available_quantity"+row).text(response.quantity);

					var total = Number(response.rate) * parseQuantity($("#quantity"+row).val()).quantity;
					total = total.toFixed(2);
					$("#total"+row).val(total);
					$("#totalValue"+row).val(total);

					
					// check if product name is selected
					// var tableProductLength = $("#productTable tbody tr").length;					
					// for(x = 0; x < tableProductLength; x++) {
					// 	var tr = $("#productTable tbody tr")[x];
					// 	var count = $(tr).attr('id');
					// 	count = count.substring(3);

					// 	var productValue = $("#productName"+row).val()

					// 	if($("#productName"+count).val() != productValue) {
					// 		// $("#productName"+count+" #changeProduct"+count).addClass('div-hide');	
					// 		$("#productName"+count).find("#changeProduct"+productId).addClass('div-hide');								
					// 		console.log("#changeProduct"+count);
					// 	}											
					// } // /for
			
					subAmount();
				} // /success
			}); // /ajax function to fetch the product data	
		}
				
	} else {
		alert('no row! please refresh the page');
	}
} // /select on product data

function getTotal(row = null) {
	if(row) {
		var rate = Number($("#rate" + row).val()) || 0;
        var qtyInput = $("#quantity" + row).val();
        var parsed = parseQuantity(qtyInput);
        var qty = parsed.quantity;

        // --- NEW LOGIC ---
        // Only calculate total automatically if quantity is greater than 0.
        if (qty > 0) {
            var total = rate * qty;
            $("#total" + row).val(total.toFixed(2));
            $("#totalValue" + row).val(total.toFixed(2));
        } else {
            // If quantity is 0, respect the manual input in the 'total' field.
            // Ensure the hidden 'totalValue' is updated with the manual value.
            var manualTotal = Number($("#total" + row).val()) || 0;
            $("#totalValue" + row).val(manualTotal.toFixed(2));
        }
        // --- END NEW LOGIC ---

		// Always recalculate the grand totals.
		subAmount();

	} else {
		alert('no row !! please refresh the page');
	}
}

function subAmount() {
	var tableProductLength = $("#productTable tbody tr").length;
	var totalSubAmount = 0;
	for(x = 0; x < tableProductLength; x++) {
		var tr = $("#productTable tbody tr")[x];
		var count = $(tr).attr('id');
		count = count.substring(3);

		totalSubAmount = Number(totalSubAmount) + Number($("#total"+count).val());
	} // /for

	totalSubAmount = totalSubAmount.toFixed(2);

	// sub total
	$("#subTotal").val(totalSubAmount);
	$("#subTotalValue").val(totalSubAmount);

	// vat
	var vat = (Number($("#subTotal").val())/100) * 18;
	vat = vat.toFixed(2);
	$("#vat").val(vat);
	$("#vatValue").val(vat);

	// total amount
	var totalAmount = (Number($("#subTotal").val()) + Number($("#vat").val()));
	totalAmount = totalAmount.toFixed(2);
	$("#totalAmount").val(totalAmount);
	$("#totalAmountValue").val(totalAmount);

	var discount = $("#discount").val();
	if(discount) {
		var grandTotal = Number($("#totalAmount").val()) - Number(discount);
		grandTotal = grandTotal.toFixed(2);
		$("#grandTotal").val(grandTotal);
		$("#grandTotalValue").val(grandTotal);
	} else {
		$("#grandTotal").val(totalAmount);
		$("#grandTotalValue").val(totalAmount);
	} // /else discount	

	var paidAmount = $("#paid").val();
	if(paidAmount) {
		paidAmount =  Number($("#grandTotal").val()) - Number(paidAmount);
		paidAmount = paidAmount.toFixed(2);
		$("#due").val(paidAmount);
		$("#dueValue").val(paidAmount);
	} else {	
		$("#due").val($("#grandTotal").val());
		$("#dueValue").val($("#grandTotal").val());
	} // else

} // /sub total amount

function discountFunc() {
	var discount = $("#discount").val();
 	var totalAmount = Number($("#totalAmount").val());
 	totalAmount = totalAmount.toFixed(2);

 	var grandTotal;
 	if(totalAmount) { 	
 		grandTotal = Number($("#totalAmount").val()) - Number($("#discount").val());
 		grandTotal = grandTotal.toFixed(2);

 		$("#grandTotal").val(grandTotal);
 		$("#grandTotalValue").val(grandTotal);
 	} else {
 	}

 	var paid = $("#paid").val();

 	var dueAmount; 	
 	if(paid) {
 		dueAmount = Number($("#grandTotal").val()) - Number($("#paid").val());
 		dueAmount = dueAmount.toFixed(2);

 		$("#due").val(dueAmount);
 		$("#dueValue").val(dueAmount);
 	} else {
 		$("#due").val($("#grandTotal").val());
 		$("#dueValue").val($("#grandTotal").val());
 	}

} // /discount function

function paidAmount() {
	var grandTotal = $("#grandTotal").val();

	if(grandTotal) {
		var dueAmount = Number($("#grandTotal").val()) - Number($("#paid").val());
		dueAmount = dueAmount.toFixed(2);
		$("#due").val(dueAmount);
		$("#dueValue").val(dueAmount);
	} // /if
} // /paid amoutn function


function resetOrderForm() {
	// reset the input field
	$("#createOrderForm")[0].reset();
	// remove remove text danger
	$(".text-danger").remove();
	// remove form group error 
	$(".form-group").removeClass('has-success').removeClass('has-error');
} // /reset order form


// remove order from server
function removeOrder(orderId = null) {
	if(orderId) {
		$("#removeOrderBtn").unbind('click').bind('click', function() {
			$("#removeOrderBtn").button('loading');

			$.ajax({
				url: 'php_action/removeOrder.php',
				type: 'post',
				data: {orderId : orderId},
				dataType: 'json',
				success:function(response) {
					$("#removeOrderBtn").button('reset');

					if(response.success == true) {

						manageOrderTable.ajax.reload(null, false);
						// hide modal
						$("#removeOrderModal").modal('hide');
						// success messages
						$("#success-messages").html('<div class="alert alert-success">'+
	            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
	            '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
	          '</div>');

						// remove the mesages
	          $(".alert-success").delay(500).show(10, function() {
							$(this).delay(3000).hide(10, function() {
								$(this).remove();
							});
						}); // /.alert	          

					} else {
						// error messages
						$(".removeOrderMessages").html('<div class="alert alert-warning">'+
	            '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
	            '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
	          '</div>');

						// remove the mesages
	          $(".alert-success").delay(500).show(10, function() {
							$(this).delay(3000).hide(10, function() {
								$(this).remove();
							});
						}); // /.alert	          
					} // /else

				} // /success
			});  // /ajax function to remove the order

		}); // /remove order button clicked
		

	} else {
		alert('error! refresh the page again');
	}
}
// /remove order from server

// Payment ORDER
function paymentOrder(orderId = null) {
    if(orderId) {
        $.ajax({
            url: 'php_action/fetchOrderData.php', // This script should be the updated one
            type: 'post',
            data: {orderId: orderId},
            dataType: 'json',
            success:function(response) {
                // Use associative keys (names) instead of numeric indices
                $("#due").val(response.order.due);
                $("#payAmount").val(response.order.due);

                var paidAmount = response.order.paid;
                var dueAmount = response.order.due;
                var grandTotal = response.order.grand_total;

                // update payment form submission
                $("#updatePaymentOrderBtn").unbind('click').bind('click', function() {
                    var payAmount = $("#payAmount").val();
                    var paymentType = $("#paymentType").val();
                    var paymentStatus = $("#paymentStatus").val();

                    if(payAmount && paymentType && paymentStatus) {
                        $("#updatePaymentOrderBtn").button('loading');
                        $.ajax({
                            url: 'php_action/editPayment.php',
                            type: 'post',
                            data: {
                                orderId: orderId,
                                payAmount: payAmount,
                                paymentType: paymentType,
                                paymentStatus: paymentStatus,
                                paidAmount: paidAmount,
                                grandTotal: grandTotal
                            },
                            dataType: 'json',
                            success:function(response) {
                                $("#updatePaymentOrderBtn").button('reset');
                                $("#paymentOrderModal").modal('hide');
                                manageOrderTable.ajax.reload(null, false);
                                
                                $("#success-messages").html('<div class="alert alert-success">'+
                                    '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                                    '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                                  '</div>');
                                  
                                $(".alert-success").delay(500).show(10, function() {
                                    $(this).delay(3000).hide(10, function() {
                                        $(this).remove();
                                    });
                                });
                            }
                        });
                    }
                    return false;
                });
            }
        });
    } else {
        alert('Error! Refresh the page again');
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