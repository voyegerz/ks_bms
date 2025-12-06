$(document).ready(function() {
	// Initialize all datepickers on the page
	$("#startDate, #endDate, #ledgerStartDate, #ledgerEndDate, #outstandingEndDate, #agingEndDate").datepicker();

    // A reusable function to handle the AJAX submission and printing
    function generatePrintableReport(form) {
        // Show a loading state on the button
        var submitButton = form.find('button[type="submit"]');
        submitButton.button('loading');

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            dataType: 'html', // We expect the full HTML of the report back
            success: function(response) {
                var printWindow = window.open('', 'PrintReport', 'height=800,width=1100');
                printWindow.document.write(response);
                printWindow.document.close();
                
                // Wait for the content to fully load before printing
                printWindow.onload = function() {
                    printWindow.focus(); // Focus the new window
                    printWindow.print(); // Open the print dialog
                    printWindow.close();
                    // This event fires after the print dialog is closed (by printing or cancelling)
                    printWindow.onafterprint = function() {
                        printWindow.close();
                    };
                };
            },
            error: function() {
                alert("Error: Could not generate the report. Please check the browser console for more details.");
            },
            complete: function() {
                // Always reset the button state
                submitButton.button('reset');
            }
        });
    }

	// Order Report Form submission
	$("#getOrderReportForm").unbind('submit').bind('submit', function(e) {
		e.preventDefault(); // Stop the default form submission
		$('.form-group').removeClass('has-error');
		var startDate = $("#startDate").val();
		var endDate = $("#endDate").val();

		if (startDate == "" || endDate == "") {
			if (startDate == "") { $("#startDate").closest('.form-group').addClass('has-error'); }
			if (endDate == "") { $("#endDate").closest('.form-group').addClass('has-error'); }
		} else {
			generatePrintableReport($(this)); // Call our new function
		}
	});

    // Ledger Report Form submission
    $("#getLedgerReportForm").unbind('submit').bind('submit', function(e) {
		e.preventDefault();
        $('.form-group').removeClass('has-error');
		var startDate = $("#ledgerStartDate").val();
		var endDate = $("#ledgerEndDate").val();

		if (startDate == "" || endDate == "") {
			if (startDate == "") { $("#ledgerStartDate").closest('.form-group').addClass('has-error'); }
			if (endDate == "") { $("#ledgerEndDate").closest('.form-group').addClass('has-error'); }
		} else {
			generatePrintableReport($(this));
		}
	});

    // Outstanding Balances Report Form submission
    $("#getOutstandingReportForm").unbind('submit').bind('submit', function(e) {
        e.preventDefault();
        $('.form-group').removeClass('has-error');
		var endDate = $("#outstandingEndDate").val();
		if (endDate == "") {
			$("#outstandingEndDate").closest('.form-group').addClass('has-error');
		} else {
			generatePrintableReport($(this));
		}
	});

    // Aging Report Form submission
    $("#getAgingReportForm").unbind('submit').bind('submit', function(e) {
		e.preventDefault();
        $('.form-group').removeClass('has-error');
		var endDate = $("#agingEndDate").val();
		if (endDate == "") {
			$("#agingEndDate").closest('.form-group').addClass('has-error');
		} else {
			generatePrintableReport($(this));
		}
	});
});