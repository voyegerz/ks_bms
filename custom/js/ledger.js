$(document).ready(function() {
    // Make the top navigation bar active
    $('#navLedger').addClass('active');

    // When a party is selected from the dropdown
    $("#partySelect").on('change', function() {
        var partyId = $(this).val();
        var ledgerResultDiv = $("#ledgerResultContainer");
        var printButton = $("#printLedgerBtn");

        if (partyId) {
            ledgerResultDiv.html('<p class="text-center">Loading...</p>');
            printButton.hide(); // Hide button while loading

            $.ajax({
                url: 'php_action/fetchLedger.php',
                type: 'post',
                data: { party_id: partyId },
                dataType: 'html', // Expecting a full HTML table back
                success: function(response) {
                    ledgerResultDiv.html(response);
                    printButton.show(); // Show the print button on success
                }
            });
        } else {
            ledgerResultDiv.html('<p class="text-center text-muted">Please select a party to view their ledger.</p>');
            printButton.hide();
        }
    });

    // When the print button is clicked
    $("#printLedgerBtn").on('click', function() {
        var partyId = $("#partySelect").val();
        if (partyId) {
            printLedger(partyId);
        }
    });
});

// New function to open the print window
function printLedger(partyId) {
    if (partyId) {
        var printURL = 'php_action/printLedger.php?party_id=' + partyId;
        var printWindow = window.open(printURL, 'PrintLedger', 'height=800,width=800');
    }
}