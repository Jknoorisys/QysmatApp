<style>
    .notification .badge {
  position: absolute;
  top: -10px;
  right: -10px;
  padding: 5px 10px;
  border-radius: 50%;
  background-color: rgb(233, 233, 7);
  color: white;
}
</style>
<header class="topbar">
    <nav class="navbar top-navbar navbar-expand-md navbar-dark">
        <div class="navbar-header" style="border: none">
            <!-- This is for the sidebar toggle which is visible on mobile only -->
            <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)">
                <i class="ti-menu ti-close"></i>
            </a>
            <!-- ============================================================== -->
            <!-- Logo -->
            <!-- ============================================================== -->
            <a class="navbar-brand mt-3 m-b-3" href="{{route('dashboard')}}">
                <!-- Logo icon -->
                <b class="logo-icon">
                    <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                    <!-- Dark Logo icon -->
                    <img src="assets/uploads/logo/logo.png" width="50px" height="40px" alt="homepage" class="dark-logo" />
                    <!-- Light Logo icon -->
                    <img src="assets/uploads/logo/logo.png" alt="homepage" class="light-logo" />
                </b>
                <!--End Logo icon -->
                <!-- Logo text -->
                <span class="logo-text">
                    <!-- dark Logo text -->
                    {{-- <img src="assets/images/logo-text.png" alt="homepage" class="dark-logo" /> --}}
                    <!-- Light Logo text -->
                    {{-- <img src="assets/images/logo-light-text.png" class="light-logo" alt="homepage" /> --}}
                    <h2 class="font-medium m-b-0" style="font-family: 'Times New Roman'; color:#8F7C5C">Qysmat</h2>
                </span>
            </a>
            <!-- ============================================================== -->
            <!-- End Logo -->
            <!-- ============================================================== -->
            <!-- ============================================================== -->
            <!-- Toggle which is visible on mobile only -->
            <!-- ============================================================== -->
            <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <i class="ti-more"></i>
            </a>
        </div>
        <!-- ============================================================== -->
        <!-- End Logo -->
        <!-- ============================================================== -->
        <div class="navbar-collapse collapse bg-light" id="navbarSupportedContent">
            <!-- ============================================================== -->
            <!-- toggle and nav items -->
            <!-- ============================================================== -->
            <ul class="navbar-nav float-left mr-auto">
                <li class="nav-item d-none d-md-block">
                    <a class="nav-link sidebartoggler waves-effect waves-light" href="javascript:void(0)" data-sidebartype="mini-sidebar">
                        <i class="sl-icon-menu font-20"></i>
                    </a>
                </li>
                <!-- ============================================================== -->
                <!-- mega menu -->
                <!-- ============================================================== -->
                <li class="nav-item dropdown mega-dropdown d-none">
                    <a class="nav-link dropdown-toggle waves-effect waves-dark" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ti-gift font-20"></i>
                    </a>
                    <div class="dropdown-menu animated bounceInDown">
                        <div class="mega-dropdown-menu row">
                            <div class="col-lg-3 col-xlg-2 m-b-30">
                                <h5 class="m-b-20">Carousel</h5>
                                <!-- CAROUSEL -->
                                <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
                                    <div class="carousel-inner" role="listbox">
                                        <div class="carousel-item active">
                                            <div class="container p-0">
                                                <img class="d-block img-fluid" src="../../assets/images/big/img1.jpg" alt="First slide">
                                            </div>
                                        </div>
                                        <div class="carousel-item">
                                            <div class="container p-0">
                                                <img class="d-block img-fluid" src="../../assets/images/big/img2.jpg" alt="Second slide">
                                            </div>
                                        </div>
                                        <div class="carousel-item">
                                            <div class="container p-0">
                                                <img class="d-block img-fluid" src="../../assets/images/big/img3.jpg" alt="Third slide">
                                            </div>
                                        </div>
                                    </div>
                                    <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Previous</span>
                                    </a>
                                    <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="sr-only">Next</span>
                                    </a>
                                </div>
                                <!-- End CAROUSEL -->
                            </div>
                            <div class="col-lg-3 m-b-30">
                                <h5 class="m-b-20">Accordion</h5>
                                <!-- Accordian -->
                                <div id="accordion">
                                    <div class="card m-b-5">
                                        <div class="card-header" id="headingOne">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                    Collapsible Group Item #1
                                                </button>
                                            </h5>
                                        </div>
                                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                                            <div class="card-body">
                                                Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card m-b-5">
                                        <div class="card-header" id="headingTwo">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                                    Collapsible Group Item #2
                                                </button>
                                            </h5>
                                        </div>
                                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                                            <div class="card-body">
                                                Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card m-b-5">
                                        <div class="card-header" id="headingThree">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                                    Collapsible Group Item #3
                                                </button>
                                            </h5>
                                        </div>
                                        <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                                            <div class="card-body">
                                                Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3  m-b-30">
                                <h5 class="m-b-20">Contact Us</h5>
                                <!-- Contact -->
                                <form>
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="exampleInputname1" placeholder="Enter Name"> </div>
                                    <div class="form-group">
                                        <input type="email" class="form-control" placeholder="Enter email"> </div>
                                    <div class="form-group">
                                        <textarea class="form-control" id="exampleTextarea" rows="3" placeholder="Message"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-info">Submit</button>
                                </form>
                            </div>
                            <div class="col-lg-3 col-xlg-4 m-b-30">
                                <h5 class="m-b-20">List style</h5>
                                <!-- List style -->
                                <ul class="list-style-none">
                                    <li>
                                        <a href="javascript:void(0)">
                                            <i class="fa fa-check text-success"></i> You can give link</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)">
                                            <i class="fa fa-check text-success"></i> Give link</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)">
                                            <i class="fa fa-check text-success"></i> Another Give link</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)">
                                            <i class="fa fa-check text-success"></i> Forth link</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)">
                                            <i class="fa fa-check text-success"></i> Another fifth link</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </li>
                <!-- ============================================================== -->
                <!-- End mega menu -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Comment -->
                <!-- ============================================================== -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle waves-effect waves-dark " href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <a href="{{ route('markAllread') }}"><i class="ti-bell font-20"><span class="badge badge-pill text-white text-bold" style="font-size:12px; position:absolute; top:8px; right:0px; background-color:#8F7C5C">{{ $notifications->count() }}</span></i></a>
                    </a>
                    <div class="dropdown-menu mailbox animated bounceInDown">
                        <span class="with-arrow">
                            <span class="bg-qysmat"></span>
                        </span>
                        <ul class="list-style-none">
                            <li>
                                <div class="drop-title bg-qysmat text-white">
                                    <h4 class="m-b-0 m-t-5">{{$notifications->count().' '. __('msg.New')}}</h4>
                                    <span class="font-light">{{__('msg.Notifications')}}</span>
                                    {{-- <a href="{{route('readNotifications')}}" class="text-light">{{__('msg.Read all notifications')}}</a> --}}
                                </div>
                            </li>
                            <li>
                                <div class="message-center notifications">
                                    @if (!$notifications->isEmpty())
                                        @foreach ($notifications as $notification)
                                            <!-- Message -->
                                            <a href="{{route('readNotifications', $notification->id)}}" class="message-item">
                                                <span class="btn btn-qysmat btn-circle">
                                                    <i class="fa fa-bell" style="font-size: 18px;"></i>
                                                </span>
                                                <div class="mail-contnet">
                                                    <h5 class="message-title">{{$notification->data['title']}}</h5>
                                                    <span class="mail-desc">{{$notification->data['msg']}}</span>
                                                    {{-- <span class="time">{{ date('D h:m A', strtotime($notification->created_at)) }}</span> --}}
                                                </div>
                                            </a>
                                        @endforeach
                                    @else
                                        <h3 class="text-center wrap text-qysmat" style="margin-top: 150px;">{{__('msg.No Notification Found')}}</h3>
                                    @endif
                                </div>
                            </li>
                            <li>
                                <a class="nav-link text-center m-b-5" href="{{route('notifications')}}">
                                    <strong>{{__('msg.View all notifications')}}</strong>
                                    <i class="fa fa-angle-right"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <!-- ============================================================== -->
                <!-- End Comment -->
                <!-- ============================================================== -->
                <!-- ============================================================== -->
                <!-- Messages -->
                <!-- ============================================================== -->
                <li class="nav-item dropdown d-none">
                    <a class="nav-link dropdown-toggle waves-effect waves-dark" href="" id="2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="font-20 ti-email"></i>

                    </a>
                    <div class="dropdown-menu mailbox animated bounceInDown" aria-labelledby="2">
                        <span class="with-arrow">
                            <span class="bg-danger"></span>
                        </span>
                        <ul class="list-style-none">
                            <li>
                                <div class="drop-title bg-danger text-white">
                                    <h4 class="m-b-0 m-t-5">5 New</h4>
                                    <span class="font-light">Messages</span>
                                </div>
                            </li>
                            <li>
                                <div class="message-center message-body">
                                    <!-- Message -->
                                    <a href="javascript:void(0)" class="message-item">
                                        <span class="user-img">
                                            <img src="../../assets/images/users/1.jpg" alt="user" class="rounded-circle">
                                            <span class="profile-status online pull-right"></span>
                                        </span>
                                        <div class="mail-contnet">
                                            <h5 class="message-title">Pavan kumar</h5>
                                            <span class="mail-desc">Just see the my admin!</span>
                                            <span class="time">9:30 AM</span>
                                        </div>
                                    </a>
                                    <!-- Message -->
                                    <a href="javascript:void(0)" class="message-item">
                                        <span class="user-img">
                                            <img src="../../assets/images/users/2.jpg" alt="user" class="rounded-circle">
                                            <span class="profile-status busy pull-right"></span>
                                        </span>
                                        <div class="mail-contnet">
                                            <h5 class="message-title">Sonu Nigam</h5>
                                            <span class="mail-desc">I've sung a song! See you at</span>
                                            <span class="time">9:10 AM</span>
                                        </div>
                                    </a>
                                    <!-- Message -->
                                    <a href="javascript:void(0)" class="message-item">
                                        <span class="user-img">
                                            <img src="../../assets/images/users/3.jpg" alt="user" class="rounded-circle">
                                            <span class="profile-status away pull-right"></span>
                                        </span>
                                        <div class="mail-contnet">
                                            <h5 class="message-title">Arijit Sinh</h5>
                                            <span class="mail-desc">I am a singer!</span>
                                            <span class="time">9:08 AM</span>
                                        </div>
                                    </a>
                                    <!-- Message -->
                                    <a href="javascript:void(0)" class="message-item">
                                        <span class="user-img">
                                            <img src="../../assets/images/users/4.jpg" alt="user" class="rounded-circle">
                                            <span class="profile-status offline pull-right"></span>
                                        </span>
                                        <div class="mail-contnet">
                                            <h5 class="message-title">Pavan kumar</h5>
                                            <span class="mail-desc">Just see the my admin!</span>
                                            <span class="time">9:02 AM</span>
                                        </div>
                                    </a>
                                </div>
                            </li>
                            <li>
                                <a class="nav-link text-center link" href="javascript:void(0);">
                                    <b>See all e-Mails</b>
                                    <i class="fa fa-angle-right"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <!-- ============================================================== -->
                <!-- End Messages -->
                <!-- ============================================================== -->


            </ul>
            <!-- ============================================================== -->
            <!-- Right side toggle and nav items -->
            <!-- ============================================================== -->
            <ul class="navbar-nav float-right">
                <!-- ============================================================== -->
                <!-- Search -->
                <!-- ============================================================== -->
                <li class="nav-item search-box d-none">
                    <a class="nav-link waves-effect waves-dark" href="javascript:void(0)">
                        <i class="ti-search font-16"></i>
                    </a>
                    <form class="app-search position-absolute">
                        <input type="text" class="form-control" placeholder="Search &amp; enter">
                        <a class="srh-btn">
                            <i class="ti-close"></i>
                        </a>
                    </form>
                </li>
                <!-- ============================================================== -->
                <!-- create new -->
                <!-- ============================================================== -->
                <li class="nav-item dropdown d-none">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown2" role="button" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <i class="flag-icon flag-icon-{{Config::get('languages')[App::getlocale()]['flag-icon']}} font-18"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right  animated bounceInDown" aria-labelledby="navbarDropdown2">
                        <a class="dropdown-item" href="#">
                            <span class="flag-icon flag-icon-{{Config::get('languages')[App::getlocale()]['flag-icon']}}"></span>
                            {{ Config::get('languages')[App::getlocale()]['language'] }}
                        </a>
                        @foreach (Config::get('languages') as $lang=>$language)
                            @if ($lang != App::getlocale())
                                <a class="dropdown-item" href="{{route('lang.switch', $lang)}}">
                                    <span class="flag-icon flag-icon-{{$language['flag-icon']}}"></span> {{ $language['language'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </li>
                <!-- ============================================================== -->
                <!-- User profile and search -->
                <!-- ============================================================== -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <img src="{{ $admin->profile ? asset('uploads/admin-profile/'.$admin->profile) : 'assets/images/users/5.jpg'}}" alt="user" class="rounded-circle" width="31">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right user-dd animated flipInY">
                        <span class="with-arrow">
                            <span class="bg-qysmat"></span>
                        </span>
                        <div class="d-flex no-block align-items-center p-15 bg-qysmat text-white m-b-10">
                            <div class="">
                                <img src="{{ $admin->profile ? asset('uploads/admin-profile'.$admin->profile) : 'assets/images/users/5.jpg'}}" alt="user" class="img-circle" width="60">
                            </div>

                            <div class="m-l-10">
                                <h4 class="m-b-0">{{$admin->name}}</h4>
                                <p class=" m-b-0">{{$admin->email}}</p>
                            </div>
                        </div>
                        <a class="dropdown-item d-none" href="javascript:void(0)">
                            <i class="ti-user m-r-5 m-l-5"></i> My Profile
                        </a>
                        <a class="dropdown-item d-none" href="javascript:void(0)">
                            <i class="ti-wallet m-r-5 m-l-5"></i> My Balance
                        </a>
                        <a class="dropdown-item d-none" href="javascript:void(0)">
                            <i class="ti-email m-r-5 m-l-5"></i> Inbox
                        </a>
                        <div class="dropdown-divider d-none"></div>
                        <a class="dropdown-item" href="{{route('changePassword')}}">
                            <i class="ti-settings m-r-5 m-l-5"></i> Change Password
                        </a>
                        <div class="dropdown-divider d-none"></div>
                        <a class="dropdown-item" href="{{route('logout')}}">
                            <i class="fa fa-power-off m-r-5 m-l-5"></i> Logout
                        </a>
                        <div class="dropdown-divider d-none"></div>
                        <div class="p-l-30 p-10 d-none">
                            <a href="javascript:void(0)" class="btn btn-sm btn-success btn-rounded">View Profile</a>
                        </div>
                    </div>
                </li>
                <!-- ============================================================== -->
                <!-- User profile and search -->
                <!-- ============================================================== -->
            </ul>
        </div>
    </nav>
</header>
