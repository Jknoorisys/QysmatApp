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

                            <h1>{{__('msg.Unable to Reset Password! Please Try Again...')}}</h1>
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
