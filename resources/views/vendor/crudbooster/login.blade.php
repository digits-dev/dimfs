<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{trans("crudbooster.page_title_login")}} : {{Session::get('appname')}}</title>
    <meta name='generator' content='CRUDBooster'/>
    <meta name='robots' content='noindex,nofollow'/>
    <link rel="shortcut icon"
          href="{{ CRUDBooster::getSetting('favicon')?asset(CRUDBooster::getSetting('favicon')):asset('vendor/crudbooster/assets/logo_crudbooster.png') }}">

    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- Bootstrap 3.3.2 -->
    <link href="{{asset('vendor/crudbooster/assets/adminlte/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css"/>
    <!-- Font Awesome Icons -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <!-- Theme style -->
    <link href="{{asset('vendor/crudbooster/assets/adminlte/dist/css/AdminLTE.min.css')}}" rel="stylesheet" type="text/css"/>

    <!-- support rtl-->
    @if (in_array(App::getLocale(), ['ar', 'fa']))
        <link rel="stylesheet" href="//cdn.rawgit.com/morteza/bootstrap-rtl/v3.3.4/dist/css/bootstrap-rtl.min.css">
        <link href="{{ asset("vendor/crudbooster/assets/rtl.css")}}" rel="stylesheet" type="text/css"/>
@endif

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

    <link rel='stylesheet' href='{{asset("vendor/crudbooster/assets/css/main.css")}}'/>
    <style type="text/css">
        .login-page, .register-page {
            background: {{ CRUDBooster::getSetting("login_background_color")?:'#dddddd'}} url('{{ CRUDBooster::getSetting("login_background_image")?asset(CRUDBooster::getSetting("login_background_image")):asset('vendor/crudbooster/assets/bg_blur3.jpg') }}');
            color: {{ CRUDBooster::getSetting("login_font_color")?:'#ffffff' }}  !important;
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
        }

        .login-box, .register-box {
            margin: 2% auto;
        }

        .login-box-body {
            box-shadow: 0px 0px 50px rgba(0, 0, 0, 0.8);
            background: rgba(255, 255, 255, 0.9);
            color: {{ CRUDBooster::getSetting("login_font_color")?:'#666666' }}  !important;
        }

        html, body {
            overflow: hidden;
        }

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

        .modal{
            color:black;
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
            padding: 10px 0 0;
            margin-bottom: -12px;
        }

    </style>
</head>

<body class="login-page">

<div class="login-box">
    <div class="login-logo">
        <a href="{{url('/')}}">
            <img title='{!!(Session::get('appname') == 'CRUDBooster')?"<b>CRUD</b>Booster":CRUDBooster::getSetting('appname')!!}'
                 src='{{ CRUDBooster::getSetting("logo")?asset(CRUDBooster::getSetting('logo')):asset('vendor/crudbooster/assets/logo_crudbooster.png') }}'
                 style='max-width: 100%;max-height:170px'/>
        </a>
    </div><!-- /.login-logo -->
    <div class="login-box-body">

        @if ( Session::get('message') != '' )
            <div id="crudAlert" class='alert alert-warning'>
                {{ Session::get('message') }}
            </div>
        @endif
        {{-- custom  --}}
        <div id="alert" class='alert alert-warning' style="display: none">
            <span id="alert-message"></span>
        </div>

        <p class='login-box-msg'>{{trans("crudbooster.login_message")}}</p>
        <form id="crudForm" autocomplete='off' action="{{ route('postLogin') }}" method="post">
            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
            <div class="form-group has-feedback">
                <input autocomplete='off' type="text" class="form-control" id="email" name='email' required placeholder="Email"/>
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input autocomplete='off' type="password" class="form-control" id="password" name='password' required placeholder="Password"/>
                <span id="crudEye" class=" fa fa-eye" style="cursor: pointer; position: absolute; right: 0px; top: 0px; z-index: 10; padding:10px;"></span>

            </div>
            <div style="margin-bottom:10px" class='row'>
                <div class='col-xs-12'>
                    <button type="button" id="crudSubmit" class="btn btn-primary btn-block btn-flat"><i class='fa fa-lock'></i> {{trans("crudbooster.button_sign_in")}}</button>
                </div>
            </div>

            <div class='row'>
                <div class='col-xs-12' align="center"><p style="padding:10px 0px 10px 0px">{{trans("crudbooster.text_forgot_password")}} <a
                                href='{{route("getForgot")}}'>{{trans("crudbooster.click_here")}}</a></p></div>
            </div>
        </form>


        <br/>
        <!--a href="#">I forgot my password</a-->

   

    </div><!-- /.login-box-body -->


    
</div><!-- /.login-box -->


    <div class="modal fade" id="tos-modal" role="dialog" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header btn-danger" style="text-center">
                    <h4 class="modal-title" id="pass_qwerty"><b> <i class="fa fa-lock"></i> Account Security: Update Your Password.</b></h4>
                </div>

                <div style="padding: 15px 15px 0">
                    <p><b>Note:</b> <span id="note_message"></span></p>
                </div>
            

                <form method="POST" action="" id="changePasswordForm">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

                    <div class="modal-body">

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <span class="glyphicon glyphicon-lock"></span>
                                </div>
                                <input type="password" class="form-control inputs match_pass" id="new_password" name="new_password" placeholder="New password" required>
                                <i class="fa fa-eye" id="toggleNewPassword" style="cursor: pointer; position: absolute; right: 15px; top: 10px; z-index: 99"></i>

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

                                <i class="fa fa-eye" id="toggleConfirmPassword" style="cursor: pointer; position: absolute; right: 15px; top: 10px; z-index: 10000"></i>

                            </div>
                            <p id="pass_not_match" class="error-text" style="display: none">Please ensure both passwords match!</p>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" id="btnSubmit" disabled><i class="fa fa-key"></i> Change password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>



<!-- jQuery 2.1.3 -->
<script src="{{asset('vendor/crudbooster/assets/adminlte/plugins/jQuery/jQuery-2.1.4.min.js')}}"></script>
<!-- Bootstrap 3.3.2 JS -->
<script src="{{asset('vendor/crudbooster/assets/adminlte/bootstrap/js/bootstrap.min.js')}}" type="text/javascript"></script>

<script>

    $(document).ready(function() {

        // $('#tos-modal').modal('show');

        // Handle button click in CRUD Form
        $('#crudSubmit').click(function(event) {
            event.preventDefault();
            handleCrudForm();
        });

        // Handle Enter key press in CRUD Form
        $('#crudForm').on('keypress', function(event) {
            if (event.which === 13) { // 13 is the Enter key
                event.preventDefault(); 
                handleCrudForm(); 
            }
        });

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

        //Function to handle Password Validation
        function handleCrudForm(){
            const email = $('#email').val();
            const password = $('#password').val();

            if (!email || !password) {
                return;
            }

            fetch('/check-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                },
                body: JSON.stringify({ email, password })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if(data.status == 'success'){
                    if (data.changePass) {
                        $('#note_message').text(data.note_message);
                      
                        if(data.waive){
                            if ($('#btnWaive').length === 0) {
                                const buttonHtml = '<button type="button" class="btn btn-danger" id="btnWaive"><i class="fa fa-refresh"></i> Waive</button>';
                                $('.modal-footer').prepend(buttonHtml); 

                                $('#btnWaive').on('click', function() {
                                    const email = $('#email').val();
                                    const password = $('#password').val();
                                    const newPassword = $('#new_password').val();

                                    fetch('/change-password', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                                        },
                                        body: JSON.stringify({ 
                                            email, 
                                            password, 
                                            new_password: newPassword,
                                            waive: true
                                        })
                                    })
                                    .then(response => {

                                        if (!response.ok) {
                                            throw new Error('Network response was not ok');
                                        }
                                        return response.json();
                                    })
                                    .then(data => {
                                
                                        if(data?.status == 'success'){
                                            $('#tos-modal').modal('hide');

                                            $('#crudAlert').hide();
                                            $('#alert-message').text(data?.message);
                                            $('#alert').show();
                                            
                                            setTimeout(() => {
                                                $('#crudForm').submit();
                                            }, 3000);
                                        } else {
                                            $('#tos-modal').modal('hide');
                                            $('#alert-message').text(data?.message);
                                            $('#alert').show();
                                            $('#crudAlert').hide();
                                        }
                                    
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        alert('An error occurred. Please try again.');
                                    });
                                });
                            }
                        }

                        $('#tos-modal').modal('show');

                    } else {
                        $('#crudForm').submit();
                    }
                } else {
                    $('#alert-message').text(data?.message);
                    $('#alert').show();
                    $('#crudAlert').hide();
                }
              
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        //Function to handle change password
        function handleChangePassForm(){
            const email = $('#email').val();
            const password = $('#password').val();
            const newPassword = $('#new_password').val();

            fetch('/change-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // Include CSRF token for security
                },
                body: JSON.stringify({ email, password, new_password: newPassword })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if(data?.status == 'success'){
                    $('#password').val(newPassword);
                    $('#tos-modal').modal('hide');

                    $('#crudAlert').hide();
                    $('#alert-message').text(data?.message);
                    $('#alert').show();

                    setTimeout(() => {
                        $('#crudForm').submit();
                    }, 3000);

                } else {
                    if(data?.change_pass_form){
                        $('#form_feedback').text(data?.message);
                        $('#form_feedback').show();

                    } else {
                        $('#tos-modal').modal('hide');
                        $('#alert-message').text(data?.message);
                        $('#alert').show();
                        $('#crudAlert').hide();
                    }
               
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });


</script>
</body>
</html>