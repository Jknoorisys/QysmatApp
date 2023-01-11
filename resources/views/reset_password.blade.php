<!DOCTYPE html>
<html dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('assets/uploads/logo/logo.png')}}">
    <title>Qysmat</title>
    <!-- Custom CSS -->
    <link href="{{asset('dist/css/style.min.css')}}" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        .auth-wrapper{
            justify-content: left !important;
            padding-left: 5%;
        }

        .auth-wrapper .auth-box {
            background: transparent;
            color: white;
            border: 0.5px solid white;
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <!-- ============================================================== -->
        <!-- Preloader - style you can find in spinners.css -->
        <!-- ============================================================== -->
        <div class="preloader">
            <div class="lds-ripple">
                <div class="lds-pos"></div>
                <div class="lds-pos"></div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- Preloader - style you can find in spinners.css -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Login box.scss -->
        <!-- ============================================================== -->
        <div class="auth-wrapper d-flex no-block justify-content-center align-items-center" style="background:linear-gradient(0deg, rgba(1, 23, 81, 0.7), rgba(1, 23, 81, 0.7)), url({{asset("assets/images/big/bg.jpg")}}) no-repeat center center;">
            <div class="auth-box">
                <div id="loginform">
                    <div class="logo">
                        <span class="db"><img src="{{asset('assets/uploads/logo/logo.png')}}" width="120px" height="100px"  alt="logo" /></span>
                        <h3 class="font-medium m-b-0" style="font-family: 'Times New Roman'; color:#8F7C5C">Qysmat</h3>
                    </div>
                    <!-- Form -->
                    <div class="row">
                        <div class="col-12 mt-4">
                            @if (Session::has('success'))
                                <div class="alert alert-success">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                                    <h6 class="text-success"><i class="fa fa-check-circle"></i> Success</h6> {{Session::get('success')}}
                                </div>
                            @elseif (Session::has('fail'))
                                <div class="alert alert-danger">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                                    <h6 class="text-danger"><i class="fa fa-times-circle"></i> Failed</h6> {{Session::get('fail')}}
                                </div>
                            @elseif (Session::has('info'))
                                <div class="alert alert-info">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                                    <h6 class="text-info"><i class="fa fa-exclamation-circle"></i> Information</h6> {{Session::get('info')}}
                                </div>
                            @elseif (Session::has('warning'))
                                <div class="alert alert-warning">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                                    <h6 class="text-warning"><i class="fa fa-exclamation-triangle"></i> Warning</h6> {{Session::get('warning')}}
                                </div>
                            @endif

                            <form action="{{route('reset-password')}}" method="post" class="form-horizontal m-t-20" id="login_form">
                                @csrf
                                <input type="hidden" name="id" value="{{$user->id}}">
                                <input type="hidden" name="user_type" value="{{$user->user_type}}">
                                <input type="hidden" name="email" value="{{$user->email}}">

                                <div class="col-md-12">
                                    <label for="password" class="form-label">{{__('msg.Password')}}</label>
                                    <div class="input-group" id="show_hide_password">
                                        <input type="password" class="form-control smp-input bg-light" required name="password" style="font-weight: 300;font-size: 15px;color: #38424C; border-right:none;" id="password" placeholder="{{ __('msg.Enter Password')}}"> <a href="javascript:;" style="line-height: 38px;align-items: center; border:1px solid #dfe0e1;border-left:none;" class="input-group-text text-qysmat bg-light"><i class='fas fa-eye-slash'></i></a>
                                    </div>
                                    </div><span class="err_email text-danger"></span>
                                </div>

                                <div class="col-md-12" style="margin-top: 15px;">
                                    <label for="cnfm_password" class="form-label">{{__('msg.Confirm Password')}}</label>
                                    <div class="input-group" id="show_hide_cnfm_password">
                                        <input type="password" required class="form-control smp-input bg-light" name="cnfm_password" style="font-weight: 300;font-size: 15px;color: #38424C; border-right:none;" id="cnfm_password" placeholder="{{ __('msg.Confirm Password')}}"> <a href="javascript:;" style="line-height: 38px;align-items: center; border:1px solid #dfe0e1;border-left:none;" class="input-group-text text-qysmat bg-light"><i class='fas fa-eye-slash'></i></a>
                                    </div>
                                    <span class="err_password text-danger"></span>
                                </div>

                                <div class="pb-4 pt-4 col-md-12">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-block btn-qysmat" style="font-weight: 300;font-size: 15px;">{{ __('msg.SUBMIT')}}&nbsp;&nbsp;<i class="fas fa-long-arrow-alt-right"></i></button>
                                    </div>
                                </div>
                            </form>
                           
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- Login box.scss -->
        <!-- ============================================================== -->
    </div>

    <script src="{{asset('assets/libs/jquery/dist/jquery.min.js')}}"></script>

    <script>
        $(function() {

            $("#login_form").on('submit', function(e) {
                e.preventDefault();
                let valid = true;
                let form = $(this).get(0);

                let password = $("#password").val();
                let err_password = "{{__('Enter Valid Password')}}";

                let cnfm_password = $("#cnfm_password").val();
                let err_cnfm_password = "{{__('Enter Valid Password')}}";

                if (cnfm_password.length === 0) {
                    $(".err_cnfm_password").text(err_cnfm_password);
                    $('#cnfm_password').addClass('is-invalid');
                    valid = false;
                } else {
                    $(".err_cnfm_password").text('');
                    $('#cnfm_password').addClass('is-valid');
                    $('#cnfm_password').removeClass('is-invalid');

                }
                if (password.length === 0) {
                    $(".err_password").text(err_password);
                    $('#password').addClass('is-invalid');
                    valid = false;
                } else {
                    $(".err_password").text('');
                    $('#password').addClass('is-valid');
                    $('#password').removeClass('is-invalid');
                }
                if (valid) {
                    form.submit();
                }
            });

            var password = document.getElementById("password")
            , cnfm_password = document.getElementById("cnfm_password");

            function validatePassword(){
                if(password.value != cnfm_password.value) {
                    password.setCustomValidity("Passwords Don't Match");
                } else {
                    password.setCustomValidity('');
                }
            }

            password.onkeyup = validatePassword;
            cnfm_password.onkeyup = validatePassword;

            $("#show_hide_password a").on('click', function(event) {
                event.preventDefault();
                if ($('#show_hide_password input').attr("type") == "text") {
                    $('#show_hide_password input').attr('type', 'password');
                    $('#show_hide_password i').addClass("fa-eye-slash");
                    $('#show_hide_password i').removeClass("fa-eye");
                } else if ($('#show_hide_password input').attr("type") == "password") {
                    $('#show_hide_password input').attr('type', 'text');
                    $('#show_hide_password i').removeClass("fa-eye-slash");
                    $('#show_hide_password i').addClass("fa-eye");
                }
            });

            $("#show_hide_cnfm_password a").on('click', function(event) {
                event.preventDefault();
                if ($('#show_hide_cnfm_password input').attr("type") == "text") {
                    $('#show_hide_cnfm_password input').attr('type', 'password');
                    $('#show_hide_cnfm_password i').addClass("fa-eye-slash");
                    $('#show_hide_cnfm_password i').removeClass("fa-eye");
                } else if ($('#show_hide_cnfm_password input').attr("type") == "password") {
                    $('#show_hide_cnfm_password input').attr('type', 'text');
                    $('#show_hide_cnfm_password i').removeClass("fa-eye-slash");
                    $('#show_hide_cnfm_password i').addClass("fa-eye");
                }
            });
            });
    </script>

    <!-- ============================================================== -->
    <!-- All Required js -->
    <!-- ============================================================== -->
    <script src="{{asset('assets/libs/jquery/dist/jquery.min.js')}}"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="{{asset('assets/libs/popper.js/dist/umd/popper.min.js')}}"></script>
    <script src="{{asset('assets/libs/bootstrap/dist/js/bootstrap.min.js')}}"></script>
    <!-- ============================================================== -->
    <!-- This page plugin js -->
    <!-- ============================================================== -->
    <script>
    $('[data-toggle="tooltip"]').tooltip();
    $(".preloader").fadeOut();
    // ==============================================================
    // Login and Recover Password
    // ==============================================================
    $('#to-recover').on("click", function() {
        $("#loginform").slideUp();
        $("#recoverform").fadeIn();
    });
    </script>


</body>

</html>
