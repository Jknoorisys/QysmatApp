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
    <link rel="icon" type="image/png" sizes="16x16" href="assets/uploads/logo/logo.png">
    <title>Qysmat</title>
    <!-- Custom CSS -->
    <link href="dist/css/style.min.css" rel="stylesheet">
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
                        <span class="db"><img src="assets/uploads/logo/logo.png" width="120px" height="100px"  alt="logo" /></span>
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

                            {{-- <form class="form-horizontal m-t-20" id="loginform" action="index.html"> --}}
                            <form action="{{route('login')}}" method="post" class="form-horizontal m-t-20" id="form_login">
                                @csrf
                                <div class="col-md-12">
                                    <label for="email" class="form-label">{{__('msg.Email')}}</label>
                                    <div class="input-group"><input type="email" class="form-control smp-input bg-light" style="font-weight: 300;font-size: 15px;color: #38424C; border-right:none;" name="email" id="email" placeholder="{{ __('msg.Email Address')}}"><a href="javascript:;" style="line-height: 38px;align-items: center; border:1px solid #dfe0e1;border-left:none;" class="input-group-text text-qysmat bg-light"><i class='fas fa-envelope'></i></a>
                                    </div><span class="err_email text-danger">@error('email') {{$message}} @enderror</span>
                                </div><br>
                                <div class="col-md-12">
                                    <label for="password" class="form-label">{{__('msg.Password')}}</label>
                                    <div class="input-group" id="show_hide_password">
                                        <input type="password" class="form-control smp-input bg-light" name="password" style="font-weight: 300;font-size: 15px;color: #38424C; border-right:none;" id="password" placeholder="{{ __('msg.Enter Password')}}"> <a href="javascript:;" style="line-height: 38px;align-items: center; border:1px solid #dfe0e1;border-left:none;" class="input-group-text text-qysmat bg-light"><i class='fas fa-eye-slash'></i></a>
                                    </div>
                                    <span class="err_password text-danger">@error('password') {{$message}} @enderror</span>
                                </div>

                                <div class="pb-4 pt-4 col-md-12">
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-block btn-qysmat" style="font-weight: 300;font-size: 15px;">{{ __('msg.LOGIN')}}&nbsp;&nbsp;<i class="fas fa-long-arrow-alt-right"></i></button>
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

    <!-- ============================================================== -->
    <!-- All Required js -->
    <!-- ============================================================== -->
    <script src="assets/libs/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
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

<script>

    $(function() {
            'use strict';
            /**
             * login-form validation
             * @date: 2021-12-10
             *
             */
            $("#form_login").on('submit', function(e) {
                e.preventDefault();
                let valid = true;
                let form = $(this).get(0);
                let emailPattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i;
                let email = $("#email").val();
                let err_email = "{{__('msg.Enter Valid Email Address')}}";
                let password = $("#password").val();
                let err_password = "{{__('Enter Valid Password')}}";

                if (email.length === 0 || !emailPattern.test(email)) {
                    $(".err_email").text(err_email);
                    $('#email').addClass('is-invalid');
                    valid = false;
                } else {
                    $(".err_email").text('');
                    $('#email').addClass('is-valid');
                    $('#email').removeClass('is-invalid');

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
            // End :: login validation


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
        });
</script>
</body>

</html>
