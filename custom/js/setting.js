$(document).ready(function() {

	// NEW CODE: Handle state selection change
    $('#state_name').on('change', function() {
        // Find the currently selected option
        var selectedOption = $(this).find('option:selected');
        
        // Get the state code from its 'data-code' attribute
        var stateCode = selectedOption.data('code') || ''; // Use empty string if nothing selected
        
        // Set the value of the state code input field
        $('#state_code').val(stateCode);
    });
    // main menu
    $("#navSetting").addClass('active');
    // sub manin
    $("#topNavSetting").addClass('active');

    // company settings form
    $("#companySettingsForm").unbind('submit').bind('submit', function() {
        var form = $(this);
        var btn = $("#saveCompanySettingsBtn");

        // remove the error text
        $(".text-danger").remove();
        // remove the form error
        $('.form-group').removeClass('has-error');
        
        btn.button('loading');

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            dataType: 'json',
            success:function(response) {
                btn.button('reset');
                
                if(response.success == true) {                                  
                    // shows a successful message after operation
                    $('.companySettingsMessages').html('<div class="alert alert-success">'+
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
                    // shows an error message after operation
                    $('.companySettingsMessages').html('<div class="alert alert-warning">'+
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                    '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                  '</div>');

                    // remove the mesages
                  $(".alert-warning").delay(500).show(10, function() {
                        $(this).delay(3000).hide(10, function() {
                            $(this).remove();
                        });
                    }); // /.alert                  
                }
            } // /success 
        }); // /ajax
        
        return false;
    });

    // change username
    $("#changeUsernameForm").unbind('submit').bind('submit', function() {
        var form = $(this);
        var username = $("#username").val();

        if(username == "") {
            $("#username").after('<p class="text-danger">Username field is required</p>');
            $("#username").closest('.form-group').addClass('has-error');
        } else {
            $(".text-danger").remove();
            $('.form-group').removeClass('has-error');
            $("#changeUsernameBtn").button('loading');

            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                dataType: 'json',
                success:function(response) {
                    $("#changeUsernameBtn").button('reset');
                    $(".text-danger").remove();
                    $(".form-group").removeClass('has-error').removeClass('has-success');

                    if(response.success == true)  {                                  
                        $('.changeUsenrameMessages').html('<div class="alert alert-success">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                      '</div>');

                      $(".alert-success").delay(500).show(10, function() {
                            $(this).delay(3000).hide(10, function() {
                                $(this).remove();
                            });
                        }); // /.alert                          
                    } else {
                        $('.changeUsenrameMessages').html('<div class="alert alert-warning">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                      '</div>');

                      $(".alert-warning").delay(500).show(10, function() {
                            $(this).delay(3000).hide(10, function() {
                                $(this).remove();
                            });
                        }); // /.alert                  
                    }
                } // /success 
            }); // /ajax
        }
            
        return false;
    });

    // change password
    $("#changePasswordForm").unbind('submit').bind('submit', function() {
        var form = $(this);
        $(".text-danger").remove();
        var currentPassword = $("#password").val();
        var newPassword = $("#npassword").val();
        var conformPassword = $("#cpassword").val();

        if(currentPassword == "" || newPassword == "" || conformPassword == "") {
            if(currentPassword == "") {
                $("#password").after('<p class="text-danger">The Current Password field is required</p>');
                $("#password").closest('.form-group').addClass('has-error');
            } else {
                $("#password").closest('.form-group').removeClass('has-error');
                $(".text-danger").remove();
            }

            if(newPassword == "") {
                $("#npassword").after('<p class="text-danger">The New Password field is required</p>');
                $("#npassword").closest('.form-group').addClass('has-error');
            } else {
                $("#npassword").closest('.form-group').removeClass('has-error');
                $(".text-danger").remove();
            }

            if(conformPassword == "") {
                $("#cpassword").after('<p class="text-danger">The Conform Password field is required</p>');
                $("#cpassword").closest('.form-group').addClass('has-error');
            } else {
                $("#cpassword").closest('.form-group').removeClass('has-error');
                $(".text-danger").remove();
            }
        } else {
            $(".form-group").removeClass('has-error');
            $(".text-danger").remove();

            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                dataType: 'json',
                success:function(response) {
                    console.log(response);
                    if(response.success == true) {
                        $('.changePasswordMessages').html('<div class="alert alert-success">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-ok-sign"></i></strong> '+ response.messages +
                      '</div>');

                      $(".alert-success").delay(500).show(10, function() {
                            $(this).delay(3000).hide(10, function() {
                                $(this).remove();
                            });
                        }); // /.alert   
                    } else {
                        $('.changePasswordMessages').html('<div class="alert alert-warning">'+
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>'+
                        '<strong><i class="glyphicon glyphicon-exclamation-sign"></i></strong> '+ response.messages +
                      '</div>');

                      $(".alert-warning").delay(500).show(10, function() {
                            $(this).delay(3000).hide(10, function() {
                                $(this).remove();
                            });
                        }); // /.alert                  
                    }
                } // /success function
            }); // /ajax function
        } // /else
        return false;
    });
}); // /document