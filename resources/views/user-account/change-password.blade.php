@extends('crudbooster::admin_template')
@push('head')
    <style type="text/css">
        .modal-content {
			-webkit-border-radius: 10px !important;
			-moz-border-radius: 10px !important;
			border-radius: 10px !important; 
		}
		.modal-header{
			-webkit-border-radius: 10px 10px 0px 0px !important;
			-moz-border-radius: 10px 10px 0px 0px !important;
			border-radius: 10px 10px 0px 0px !important; 
		}
		#passwordStrengthBar {
			display: flex;
			justify-content: space-between;
			width: 100%;
		}

		.progress-bar {
			width: 32%;
			height: 8px;
			background-color: lightgray;
			transition: background-color 0.3s;
			border-radius: 5px;
		}

		#bar1.active {
			background-color: #dd4b39 ; /* Weak */
		}

		#bar2.active {
			background-color: #f39c12 ; /* Strong */
		}

		#bar3.active {
			background-color: #00a65a; /* Excellent */
		}
		#textUppercase.active {
			color: #00a65a; /* Excellent */
		}
		#textLength.active {
			color: #00a65a; /* Excellent */
		}
		#textNumber.active {
			color: #00a65a; /* Excellent */
		}
		#textChar.active {
			color: #00a65a; /* Excellent */
		}

        .error-text{
            color: red;
            font-size: 14px;
            display: inline-block;
            padding: 10px 0 5px;
            margin-bottom: -12px;
        }

        @media (min-width:729px){
           .panel-danger{
                width:40% !important; 
                margin:auto !important;
           }

        }

        .eye-icon {
            cursor: pointer; 
            position: absolute; 
            right: 15px; 
            top: 10px; 
            z-index: 99
        }

        
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">

@endpush
@section('content')
<div class="panel panel-danger">
    <div class="panel-heading">
        <h4 class="panel-title">
            <b><i class="fa fa-lock"></i> 
            <span class="label label-danger" style="margin-left:5px">Change Password</span>
            </b>
        </h4>
    </div>

    <div class="panel-body">
        <form method="POST" action="" id="changePasswordForm">
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />

            <div class="form-group">
                <label for="password">Current Password</label>
                <div class="input-group">
                    <div class="input-group-addon">
                        <span class="glyphicon glyphicon-lock"></span>
                    </div>
                    <input type="password" class="form-control inputs" id="password" name="password" placeholder="Current password" required>
                    <i class="fa fa-eye eye-icon" id="toggleCurrentPassword"></i>
                </div>
                <p id="password_feedback" class="error-text" style="display: none"></p>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <div class="input-group">
                    <div class="input-group-addon">
                        <span class="glyphicon glyphicon-lock"></span>
                    </div>
                    <input type="password" class="form-control inputs match_pass" id="new_password" name="new_password" placeholder="New password" required>
                    <i class="fa fa-eye eye-icon" id="toggleNewPassword"></i>
                </div>

                <div class="password-requirement" style="display: none">
                
                    <div id="passwordStrengthBar" style="margin-top: 10px;">
                        <div class="progress-bar" id="bar1"></div>
                        <div class="progress-bar" id="bar2"></div>
                        <div class="progress-bar" id="bar3"></div>
                    </div>

                    <p id="form_feedback" class="error-text" style="display: none"></p>

                    <div style="margin-top: 10px;">
                        <div class="progress-text" id="textUppercase"> <i id="iconUppercase" class="fa fa-info-circle" style="margin-right: 4px"></i> Must include at least one uppercase letter.</div>
                        <div class="progress-text" id="textLength"> <i id="iconLength" class="fa fa-info-circle" style="margin-right: 4px"></i> Minimum length of 8 characters.</div>
                        <div class="progress-text" id="textNumber"> <i id="iconNumber" class="fa fa-info-circle" style="margin-right: 4px"></i> Must contain at least one number.</div>
                        <div class="progress-text" id="textChar"> <i id="iconChar" class="fa fa-info-circle" style="margin-right: 4px"></i> Must include at least one special character.</div>
                    </div>

                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-group" >
                    <div class="input-group-addon">
                        <span class="glyphicon glyphicon-lock"></span>
                    </div>
                    <input type="password" class="form-control inputs match_pass" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>

                    <i class="fa fa-eye eye-icon" id="toggleConfirmPassword"></i>

                </div>
                <p id="pass_not_match" class="error-text" style="display: none">Please ensure both passwords match!</p>
            </div>
        </form>
    </div>
    <div class="panel-footer">
        <button type="button" class="btn btn-danger" id="btnSubmit" disabled><i class="fa fa-key"></i> Change password</button>
    </div>
</div>
@endsection

@push('bottom')
<script>

    $(document).ready(function() {

        // Handle button click in Change Pass Form
        $('#btnSubmit').click(function(event){
            event.preventDefault();
            handleChangePassForm();
        });

        // Handle Enter key press in Change Pass Form
        $('#changePasswordForm').on('keypress', function(event) {
            if (event.which === 13) { // 13 is the Enter key
                event.preventDefault(); 
                handleChangePassForm(); 
            }
        });

        // Toggle Password Visibility
        $('#crudEye').click(function() {
            let input = $('#password');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                $(this).removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                $(this).removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // Toggle Password Visibility
        $('#toggleNewPassword').click(function() {
            handleToggleEye();
        });

        // Toggle Password Visibility
        $('#toggleConfirmPassword').click(function() {
            handleToggleEye();
        });

        $('#toggleCurrentPassword').click(function() {
				let currentPassword = $('#password');
				let type = currentPassword.attr('type') === 'password' ? 'text' : 'password';
				currentPassword.attr('type', type);
				$(this).toggleClass('fa-eye fa-eye-slash');
		});

        $('#password').on('input', function() {
            $('#password_feedback').hide();
        });
       
        // Password Strength Validation
        $('#new_password').on('input', function() {
            $('.password-requirement').show();
            $('#form_feedback').hide();
            checkValidation();
        });

        // Confirm Password Matching
        $('.match_pass').on('input', function() {
            const newPassword = $('#new_password').val();
            const confirmPassword = $('#confirm_password').val();

            if (newPassword === confirmPassword && newPassword !== '') {
                $('#pass_not_match').hide();
                checkValidation();
            } else {
                $('#pass_not_match').show();
                $('#btnSubmit').prop('disabled', true);
            }
        });

        //Function to handle toggle eye
        function handleToggleEye() {
            const inputs = [
                { input: $('#new_password'), icon: $('#toggleNewPassword') },
                { input: $('#confirm_password'), icon: $('#toggleConfirmPassword') }
            ];

            inputs.forEach(({ input, icon }) => {
                const isPassword = input.attr('type') === 'password';
                input.attr('type', isPassword ? 'text' : 'password');
                icon.toggleClass('fa-eye', isPassword);
                icon.toggleClass('fa-eye-slash', !isPassword);
            });
        }

        // Function to check all validation rules
        function checkValidation() {
            let password = $('#new_password').val();
            let strength = 0;

            // Regex to check password rules
            const uppercase = /[A-Z]/;
            const number = /\d/;
            const specialChar = /[\W_]/;
            const minLength = 8;

            // Check Uppercase
            if (uppercase.test(password)) {
                $('#textUppercase').removeClass('text-danger').addClass('text-success');
                $('#iconUppercase').removeClass('fa-info-circle').addClass('fa-check-circle');
                strength += 25;

            } else {
                $('#textUppercase').removeClass('text-success').addClass('text-danger');
                $('#iconUppercase').removeClass('fa-check-circle').addClass('fa-info-circle');

            }

            // Check Length
            if (password.length >= minLength) {
                $('#textLength').removeClass('text-danger').addClass('text-success');
                $('#iconLength').removeClass('fa-info-circle').addClass('fa-check-circle');
                strength += 25;

            } else {
                $('#textLength').removeClass('text-success').addClass('text-danger');
                $('#iconLength').removeClass('fa-check-circle').addClass('fa-info-circle');

            }

            // Check Number
            if (number.test(password)) {
                $('#textNumber').removeClass('text-danger').addClass('text-success');
                $('#iconNumber').removeClass('fa-info-circle').addClass('fa-check-circle');
                strength += 25;

            } else {
                $('#textNumber').removeClass('text-success').addClass('text-danger');
                $('#iconNumber').removeClass('fa-check-circle').addClass('fa-info-circle');

            }

            // Check Special Character
            if (specialChar.test(password)) {
                $('#textChar').removeClass('text-danger').addClass('text-success');
                $('#iconChar').removeClass('fa-info-circle').addClass('fa-check-circle');

                strength += 25;
            } else {
                $('#textChar').removeClass('text-success').addClass('text-danger');
                $('#iconChar').removeClass('fa-check-circle').addClass('fa-info-circle');

            }

            // Update Progress Bar
            $('#bar1, #bar2, #bar3').css('background-color', '');
            if (strength >= 25) $('#bar1').css('background-color', '#ff0000');
            if (strength >= 50) $('#bar2').css('background-color', '#ffc107');
            if (strength === 100) $('#bar3').css('background-color', '#28a745');

            // Enable/disable the submit button based on the validation
            if (strength === 100) {
                $('#btnSubmit').prop('disabled', false);  // Enable the button
            } else {
                $('#btnSubmit').prop('disabled', true);  // Disable the button
            }
        }   

        //Function to handle change password
        function handleChangePassForm(){
            const email = $('#email').val();
            const password = $('#password').val();
            const confirmPassword = $('#confirm_password').val();
            const newPassword = $('#new_password').val();

            fetch('/change-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Include CSRF token for security
                },
                body: JSON.stringify({ email, password, new_password: newPassword, confirm_password: confirmPassword, change_pass_inside: true })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if(data?.status == 'success'){
                    swal({
                        title: "",
                        text: data?.message,
                        type: "info",
                        confirmButtonColor: "#008bff",
                        confirmButtonText: "OK",
                        closeOnConfirm: true
                    },
                    function(){
                        const admin_path = "{{CRUDBooster::adminPath()}}"

                        location.assign(admin_path+'/logout');
                    });


                } else {
                    if(data?.name == 'new_password'){
                            $('#form_feedback').text(data?.message);
                            $('#form_feedback').show();
                    } else if(data?.name == 'password'){
                        $('#password_feedback').text(data?.message);
                        $('#password_feedback').show();
                    }
               
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });


</script>
@endpush