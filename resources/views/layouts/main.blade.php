<!DOCTYPE html>
<html dir="ltr" lang="en">

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
    <!-- <link href="assets/libs/chartist/dist/chartist.min.css" rel="stylesheet"> -->
    <!-- <link href="assets/extra-libs/c3/c3.min.css" rel="stylesheet"> -->
    <!-- <link href="assets/libs/morris.js/morris.css" rel="stylesheet"> -->
    <link href="assets/extra-libs/toastr/dist/build/toastr.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/extra-libs/prism/prism.css">
    <link rel="stylesheet" type="text/css" href="assets/libs/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css">
    <link href="assets/libs/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/libs/magnific-popup/dist/magnific-popup.css" rel="stylesheet">
    {{-- Font --}}
    <link href='https://fonts.googleapis.com/css?family=Source+Sans+Pro' rel='stylesheet' type='text/css'>
    {{-- Date Picker --}}
    <link rel="stylesheet" type="text/css" href="assets/libs/pickadate/lib/themes/default.css">
    <link rel="stylesheet" type="text/css" href="assets/libs/pickadate/lib/themes/default.date.css">
    <link rel="stylesheet" type="text/css" href="assets/libs/pickadate/lib/themes/default.time.css">

    {{-- data table --}}
    <link href="assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="dist/css/style.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/libs/ckeditor/samples/toolbarconfigurator/lib/codemirror/neo.css">
    <link rel="stylesheet" type="text/css" href="assets/libs/ckeditor/samples/css/samples.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        table, td, tr, th {
            border: 5px solid white;
            /* background-color: #f8f9fa; */
            font-size: 14px;
        }

        th{
            /* background-color:#d7cfc1; */
            background: linear-gradient(180deg, #AF9A7F 0%, #A28D69 50%, #8F7C5C 100%);
            color: #ffffff;
            border-radius: 12px;
            font-size: 15px;
            font-weight: bolder;
        }

        .card{
            border: none;
        }

        body{
            font-family: 'Source Sans Pro', sans-serif;
        }
    </style>
</head>

<body>
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
    <!-- Main wrapper - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <div id="main-wrapper">
        <!-- ============================================================== -->
        <!-- Topbar header - style you can find in pages.scss -->
        <!-- ============================================================== -->
        @include('layouts.header')
        <!-- ============================================================== -->
        <!-- End Topbar header -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        @include('layouts.sidebar')
        <!-- ============================================================== -->
        <!-- End Left Sidebar - style you can find in sidebar.scss  -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Page wrapper  -->
        <!-- ============================================================== -->
        <div class="page-wrapper bg-light">
            <!-- ============================================================== -->
            <!-- Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <?php if (isset($title) && $title != 'no_breadcrumb') : ?>
                <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 p-2">
                                <li class="breadcrumb-item" style="font-size: 14px;"><a href="{{ (isset($url) && !empty($url)) ? $url : route('/'); }}">{{ (isset($previous_title) && !empty($previous_title)) ? $previous_title : ""; }}</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page" style="font-size: 14px;">{{ (isset($title) && !empty($title)) ? $title : ""; }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- Page Heading  -->
                <!-- ============================================================== -->
                <h6 class="mb-0 p-2 ml-3 text-uppercase" style="font-size: 16px;">{{ (isset($title) && !empty($title)) ? $title : ""; }}</h6>
                <hr class="mb-0 p-2 ml-4 mr-4" />
                <!-- ============================================================== -->
                <!-- End Page Heading  -->
                <!-- ============================================================== -->
            <?php endif; ?>
            <!-- ============================================================== -->
            <!-- End Bread crumb and right sidebar toggle -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Container fluid  -->
            <!-- ============================================================== -->
            <div class="container-fluid">
                <!-- ============================================================== -->
                <!-- Notifications  -->
                <!-- ============================================================== -->

                @if (Session::has('success'))
                    <div class="alert alert-success">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                        <h3 class="text-success"><i class="fa fa-check-circle"></i> Success</h3> {{Session::get('success')}}
                    </div>
                @elseif (Session::has('fail'))
                    <div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                        <h3 class="text-danger"><i class="fa fa-times-circle"></i> Failed</h3> {{Session::get('fail')}}
                    </div>
                @elseif (Session::has('info'))
                    <div class="alert alert-info">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                        <h3 class="text-info"><i class="fa fa-exclamation-circle"></i> Information</h3> {{Session::get('info')}}
                    </div>
                @elseif (Session::has('warning'))
                    <div class="alert alert-warning">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button>
                        <h3 class="text-warning"><i class="fa fa-exclamation-triangle"></i> Warning</h3> {{Session::get('warning')}}
                    </div>
                @endif

                <!-- ============================================================== -->
                <!-- End Notifications  -->
                <!-- ============================================================== -->
                <!-- Main Content  -->
                <!-- ============================================================== -->
                <?php echo (isset($content) && !empty($content)) ? $content : "" ?>
                <!-- ============================================================== -->
                <!-- End Main Content  -->
                <!-- ============================================================== -->
            </div>
            <!-- ============================================================== -->
            <!-- End Container fluid  -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- footer -->
            <!-- ============================================================== -->
            @include('layouts.footer')
            <!-- ============================================================== -->
            <!-- End footer -->
            <!-- ============================================================== -->
        </div>
        <!-- ============================================================== -->
        <!-- End Page wrapper  -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Wrapper -->
    <!-- ============================================================== -->


    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="assets/libs/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="assets/libs/popper.js/dist/umd/popper.min.js"></script>
    <script src="assets/libs/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- apps -->
    <script src="dist/js/app.min.js"></script>
    <script src="dist/js/app.init.light-sidebar.js"></script>
    <script src="dist/js/app-style-switcher.js"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
    <script src="assets/extra-libs/sparkline/sparkline.js"></script>
    <!--Wave Effects -->
    <script src="dist/js/waves.js"></script>
    <!--Menu sidebar -->
    <script src="dist/js/sidebarmenu.js"></script>
    <!--Custom JavaScript -->
    <script src="dist/js/custom.min.js"></script>
    <!--This page JavaScript -->
    <script src="assets/libs/bootstrap-switch/dist/js/bootstrap-switch.min.js"></script>
    <!--chartis chart-->
    <!-- <script src="assets/libs/chartist/dist/chartist.min.js"></script> -->
    <!-- <script src="assets/libs/chartist-plugin-tooltips/dist/chartist-plugin-tooltip.min.js"></script> -->
    <!--c3 charts -->
    <!-- <script src="assets/extra-libs/c3/d3.min.js"></script> -->
    <!-- <script src="assets/extra-libs/c3/c3.min.js"></script> -->
    <!--chartjs -->
    <!-- <script src="assets/libs/raphael/raphael.min.js"></script> -->
    <!-- <script src="assets/libs/morris.js/morris.min.js"></script> -->

    <!-- <script src="dist/js/pages/dashboards/dashboard1.js"></script> -->

    {{-- toasterjs --}}
    <script src="dist/js/custom.min.js"></script>
    <script src="assets/extra-libs/toastr/dist/build/toastr.min.js"></script>
    <script src="assets/extra-libs/toastr/toastr-init.js"></script>

    <script src="assets/libs/magnific-popup/dist/jquery.magnific-popup.min.js"></script>
    <script src="assets/libs/magnific-popup/meg.init.js"></script>

    {{-- date picker --}}
    <script src="assets/libs/pickadate/lib/compressed/picker.js"></script>
    <script src="assets/libs/pickadate/lib/compressed/picker.date.js"></script>
    <script src="assets/libs/pickadate/lib/compressed/picker.time.js"></script>
    <script src="assets/libs/pickadate/lib/compressed/legacy.js"></script>
    <script src="assets/libs/moment/moment.js"></script>
    <script src="assets/libs/daterangepicker/daterangepicker.js"></script>
    <script src="dist/js/pages/forms/datetimepicker/datetimepicker.init.js"></script>

    {{-- data table --}}
    <script src="assets/extra-libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="dist/js/pages/datatable/datatable-basic.init.js"></script>
</body>

</html>
