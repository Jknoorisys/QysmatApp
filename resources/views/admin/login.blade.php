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

        .qysmat-icon{
            border-top-left-radius: 25px;
            border-bottom-left-radius: 25px;
        }

        #email, #password{
            border-top-right-radius: 25px;
            border-bottom-right-radius: 25px;
            border-left: none;
        }

        @keyframes slide-in {
            0% {
            left: -100%;
            }
            100% {
            left: 0;
            }
        }
        
        @keyframes bounce {
            0% {
            transform: translateY(-100%) rotateX(0deg);
            }
            40% {
            transform: translateY(0) rotateX(360deg);
            }
            60% {
            transform: translateY(0) rotateX(280deg);
            }
            80% {
            transform: translateY(0) rotateX(320deg);
            }
            100% {
            transform: translateY(0) rotateX(0deg);
            }
        }

        .light-logo {
            animation-name: bounce;
            animation-duration: 1s;
            animation-delay: 0.5s;
            animation-fill-mode: forwards;
            transform-origin: center;
        }

        .auth-wrapper .auth-box {
                max-width: 330px;
                width: 90%;
                background: #00000057;
                color: white;
                border: 1px solid #3a3a3a;
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
                        <span class="db"><img src="assets/uploads/logo/logo.png" width="120px" class="light-logo" height="100px"  alt="logo" /></span>
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

                            <form action="{{route('login')}}" method="post" class="form-horizontal m-t-20" id="form_login">
                                @csrf
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text qysmat-icon" id="basic-addon1"><i class="mdi mdi-email"></i></span>
                                    </div>
                                    <input type="email" class="form-control form-control-lg" required name="email" placeholder="Email" id="email" placeholder="{{ __('msg.Email Address')}}" aria-label="Email" aria-describedby="basic-addon1">
                                    <div class="err_email text-danger">@error('email') {{$message}} @enderror</div>
                                </div>
                                
                                <div class="input-group mb-3" id="show_hide_password">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text qysmat-icon" id="basic-addon1"><i class="mdi mdi-eye-off"></i></span>
                                    </div>
                                    <input type="password" class="form-control form-control-lg" required name="password" id="password" placeholder="{{ __('msg.Enter Password')}}" aria-label="Password" aria-describedby="basic-addon1">
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
                let email = $("#email").val();
                let err_email = "{{__('msg.Enter Valid Email Address')}}";
                let password = $("#password").val();
                let err_password = "{{__('Enter Valid Password')}}";

                if (email.length === 0) {
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

            $(document).ready(function () {
                $("#show_hide_password span").on('click', function (event) {
                    event.preventDefault();
                    if ($('#show_hide_password input').attr("type") == "text") {
                        $('#show_hide_password input').attr('type', 'password');
                        $('#show_hide_password i').addClass("mdi-eye-off");
                        $('#show_hide_password i').removeClass("mdi-eye");
                    } else if ($('#show_hide_password input').attr("type") == "password") {
                        $('#show_hide_password input').attr('type', 'text');
                        $('#show_hide_password i').removeClass("mdi-eye-off");
                        $('#show_hide_password i').addClass("mdi-eye");
                    }
                });
            });

        });
</script>
</body>

</html>
