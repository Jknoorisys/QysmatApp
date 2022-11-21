<aside class="left-sidebar" style="border: none">
    <!-- Sidebar scroll-->
    <div class="scroll-sidebar">
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav mt-4">
            <ul id="sidebarnav">
                <!-- User Profile-->
                <li class="d-none">
                    <!-- User Profile-->
                    <div class="user-profile dropdown m-t-20">
                        <div class="user-pic">
                            <img src="../../assets/images/users/1.jpg" alt="users" class="rounded-circle img-fluid" />
                        </div>
                        <div class="user-content hide-menu m-t-10">
                            <h5 class="m-b-10 user-name font-medium">Steave Jobs</h5>
                            <a href="javascript:void(0)" class="btn btn-circle btn-sm m-r-5" id="Userdd" role="button" data-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <i class="ti-settings"></i>
                            </a>
                            <a href="javascript:void(0)" title="Logout" class="btn btn-circle btn-sm">
                                <i class="ti-power-off"></i>
                            </a>
                            <div class="dropdown-menu animated flipInY" aria-labelledby="Userdd">
                                <a class="dropdown-item" href="javascript:void(0)">
                                    <i class="ti-user m-r-5 m-l-5"></i> My Profile</a>
                                <a class="dropdown-item" href="javascript:void(0)">
                                    <i class="ti-wallet m-r-5 m-l-5"></i> My Balance</a>
                                <a class="dropdown-item" href="javascript:void(0)">
                                    <i class="ti-email m-r-5 m-l-5"></i> Inbox</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="javascript:void(0)">
                                    <i class="ti-settings m-r-5 m-l-5"></i> Account Setting</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="javascript:void(0)">
                                    <i class="fa fa-power-off m-r-5 m-l-5"></i> Logout</a>
                            </div>
                        </div>
                    </div>
                    <!-- End User Profile-->
                </li>
                <!-- End User Profile-->

                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark" href="{{route('dashboard')}}" aria-expanded="false">
                        <i class="fas fa-home"></i>
                        <span class="hide-menu sidebar-title"> {{__('msg.Dashboard')}} </span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark" href="{{route('sigletons')}}" aria-expanded="false">
                        <i class="fas fa-users"></i>
                        <span class="hide-menu sidebar-title"> {{__('msg.Manage Singletons')}} </span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark" href="{{route('parents')}}" aria-expanded="false">
                        <i class="fa-solid fa-user-group"></i>
                        <span class="hide-menu sidebar-title"> {{__('msg.Manage Parents')}} </span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark" href="{{route('subscriptions')}}" aria-expanded="false">
                        <i class="fa-solid fa-money-check"></i>
                        <span class="hide-menu sidebar-title"> {{__('msg.Manage Subscription Plan and Price')}} </span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark" href="{{route('quotes')}}" aria-expanded="false">
                        <i class="fa-solid fa-quote-left"></i>
                        <span class="hide-menu sidebar-title"> {{__('msg.Manage Islamic Quotes')}} </span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark" href="{{route('reported-users')}}" aria-expanded="false">
                        <i class="fa-solid fa-flag"></i>
                        <span class="hide-menu sidebar-title"> {{__('msg.Manage Reported Users')}} </span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark" href="{{route('deleted-users')}}" aria-expanded="false">
                        <i class="fa-solid fa-user-xmark"></i>
                        <span class="hide-menu sidebar-title"> {{__('msg.Manage Deleted Users')}} </span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark" href="{{route('transactions')}}" aria-expanded="false">
                        <i class="fa-solid fa-wallet"></i>
                        <span class="hide-menu sidebar-title"> {{__('msg.Manage Transactions')}} </span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark" href="{{route('contact_details')}}" aria-expanded="false">
                        <i class="fa-solid fa-address-card"></i>
                        <span class="hide-menu sidebar-title"> {{__('msg.Manage Contact Details')}} </span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark" href="{{route('static_pages')}}" aria-expanded="false">
                        <i class="fas fa-newspaper"></i>
                        <span class="hide-menu sidebar-title"> {{__('msg.Manage Static Pages')}} </span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark" href="{{route('web_pages')}}" aria-expanded="false">
                        <i class="fa-solid fa-file-lines"></i>
                        <span class="hide-menu sidebar-title"> {{__('msg.Manage Web Pages')}} </span>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>
