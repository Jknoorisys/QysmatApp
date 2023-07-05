<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

<style>
    input[type=checkbox]{
    height: 0;
    width: 0;
    visibility: hidden;
    }

    label {
    cursor: pointer;
    text-indent: -9999px;
    width: 32px;
    height: 15px;
    background: rgb(145, 143, 143);
    /* display: block; */
    border-radius: 100px;
    position: relative;
    }

    label:after {
    content: '';
    position: absolute;
    top: 2.2px;
    left: 3px;
    width: 10px;
    height: 10px;
    background: #fff;
    border-radius: 100px;
    transition: 0.2s;
    }

    input:checked + label {
        background: linear-gradient(180deg, #AF9A7F 0%, #A28D69 50%, #8F7C5C 100%);
    }

    input:checked + label:after {
    left: calc(100% - 5px);
    transform: translateX(-100%);
    }

    label:active:after {
    width: 100px;
    }

</style>

<div class="row">
    <!-- column -->
    <div class="col-sm-12 col-lg-6">
        <div class="card bg-light-info no-card-border">
            <div class="card-body">
                <div class="d-flex align-items-center text-center">
                    <div class="m-r-0">
                        <span>{{__('msg.Total Number of Singletons and Parents')}}</span><hr>
                        <div class="row justify-content-center">
                            <div class="col-6">
                                <h5>{{__('msg.Singletons')}}</h5>
                                <h4>{{$singletons}}</h4>
                            </div>
                            <div class="col-6">
                                <h4>{{__('msg.Parents')}}</h4>
                                <h4>{{$parents}}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="ml-auto">
                        <i class="fa-solid fa-users" style="font-size:50px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- column -->
    <div class="col-sm-12 col-lg-6">
        <div class="card bg-light-warning no-card-border">
            <div class="card-body">
                <div class="d-flex align-items-center text-center">
                    <div class="m-r-0">
                        <span>{{__('msg.Total Number of Active, Blocked and Deleted Users')}}</span><hr>
                        <div class="row justify-content-center">
                            <div class="col-4">
                                <h5>{{__('msg.Active')}}</h5>
                                <h4>{{$active}}</h4>
                            </div>
                            <div class="col-4">
                                <h5>{{__('msg.Blocked')}}</h5>
                                <h4>{{$blocked}}</h4>
                            </div>
                            <div class="col-4">
                                <h5>{{__('msg.Deleted')}}</h5>
                                <h4>{{$deleted}}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="ml-auto">
                        <i class="fa-solid fa-user-group" style="font-size:50px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- column -->
    <div class="col-sm-12 col-lg-4">
        <div class="card bg-light-info no-card-border">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="m-r-10">
                        <span>{{__('msg.Number of Conversations')}}</span>
                        <h4>{{$conversations}}</h4>
                    </div>
                    <div class="ml-auto">
                        <i class="fa-solid fa-message" style="font-size:50px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- column -->
    <div class="col-sm-12 col-lg-4">
        <div class="card bg-light-warning no-card-border">
            <div class="card-body">
                <a href="{{route('reported-users')}}" class="d-flex" style="color: #575757;">
                    <div class="m-r-10">
                        <span>{{__('msg.Number of Reported Users')}}</span>
                        <h4>{{$reported}}</h4>
                    </div>
                    <div class="ml-auto">
                        <i class="fa-solid fa-flag" style="font-size:50px;"></i>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <!-- column -->
    <div class="col-sm-12 col-lg-4">
        <div class="card bg-light-success no-card-border">
            <div class="card-body">
                <div class="d-flex" style="color: #575757;">
                    <div class="m-r-10">
                        <span>{{__('msg.Number of Matches')}}</span>
                        <h4>{{$matches}}</h4>
                    </div>
                    <div class="ml-auto">
                        <i class="fa-solid fa-children" style="font-size:50px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- column -->
    <div class="col-sm-12 col-lg-8">
        <div class="card bg-light-info no-card-border">
            <div class="card-body">
                <div class="d-flex align-items-center text-center">
                    <div class="m-r-0">
                        <span>{{__('msg.Survey Results')}}</span><hr>
                        <div class="row justify-content-center">
                            <div class="col-3">
                                <h5>{{__('msg.Didn’t Find the App Useful')}}</h5>
                                <h4>{{$app_not_usefull}}</h4>
                            </div>
                            <div class="col-3">
                                <h5>{{__('msg.Taking a Break')}}</h5>
                                <h4>{{$taking_break}}</h4>
                            </div>
                            <div class="col-3">
                                <h5>{{__('msg.Met Someone/Getting Married')}}</h5>
                                <h4>{{$met_someone}}</h4>
                            </div>
                            <div class="col-3">
                                <h5>{{__('msg.Other')}}</h5>
                                <h4>{{$other}}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="ml-auto">
                        <i class="fa-solid fa-chart-pie" style="font-size:50px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- column -->
    <div class="col-sm-12 col-lg-4">
        <div class="card bg-light-warning no-card-border">
            <div class="card-body">
                <div class="d-flex align-items-center text-center">
                    <div class="m-r-0">
                        <span>{{__('msg.Number of Telephone and Video Calls')}}</span><hr>
                        <div class="row justify-content-center">
                            <div class="col-6">
                                <h5>{{__('msg.Telephone Calls')}}</h5>
                                <h4>{{$audio}}</h4>
                            </div>
                            <div class="col-6">
                                <h5>{{__('msg.Video Calls')}}</h5>
                                <h4>{{$video}}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="ml-auto">
                        <i class="fa-solid fa-phone" style="font-size:50px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-12 col-lg-12">
        <div class="card bg-light-success no-card-border">
            <div class="card-body">
                <a href="{{route('transactions')}}" class="d-flex align-items-center" style="color: #575757;">
                    <div class="m-r-10">
                        <span>{{__('msg.Average Revenue')}}</span>
                        <h4>£{{number_format((float)$revenue, 2, '.', '')}}</h4>
                    </div>
                    <div class="ml-auto">
                        <div class="gaugejs-box">
                            <i class="fa-solid fa-wallet" style="font-size:50px;"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="col-12">
    <div class="card">
       <div class="card-body">
        <h3 class="mb-3 p-2">{{__('msg.Recently Registered Singletons')}}</h3>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">Name</th>
                            <th class="text-center">Email</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Verified</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!empty($records))
                            @foreach ($records as $value)
                                <tr>
                                    <td class="text-center">{{$value->name}}</td>
                                    <td class="text-center">{{$value->email}}</td>
                                    <td class="text-center">{{$value->status}}</td>
                                    <td class="text-center"><span class="label label-{{ $value->is_verified == 'verified' ? 'success': 'danger'}}">{{ $value->is_verified == 'verified' ? 'Verified': 'Not Verified'}}</span></td>
                                    <td class="text-center bt-switch">

                                        <div class="row justify-content-center">
                                            <div class="col-3"></div>
                                            <div class="col-2 mt-1">
                                                <form action="{{route('changeStatus')}}" method="post">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{$value->id}}">
                                                    <input type="hidden" name="status" value="{{$value->status == 'Unblocked' ? 'Blocked' : 'Unblocked' }}">
                                                    <button type="submit" data-status="{{$value->status == 'Unblocked' ? 'Unblocked' : 'Blocked'}}" data-id="{{$value->id}}" data-name="{{$value->name}}" class="btn block_confirm btn-sm"><input type="checkbox" id="switch" {{$value->status == 'Unblocked' ? 'checked' : ''}} /><label for="switch">Toggle</label></button>
                                                </form>
                                            </div>
                                            <div class="col-2">
                                                <form action="{{route('viewSingleton')}}" method="post">
                                                    @csrf
                                                    <input type="hidden" value="{{$value->id}}" id="id" name="id" />
                                                    <button type="submit" class="btn btn-lg text-qysmat" onclick="this.form.submit()" data-toggle="tooltip" title='View'> <i class="fas fa-eye"></i> </button>
                                                </form>
                                            </div>
                                            <div class="col-2">
                                                <form action="{{route('deleteSingleton')}}" method="post">
                                                    @csrf
                                                    <input type="hidden" value="{{$value->id}}" id="id" name="id" />
                                                    <button type="submit" class="btn btn-lg text-qysmat show_confirm" data-name="{{$value->name}}" data-id="{{$value->id}}" data-toggle="tooltip" title='Delete'> <i class="fas fa-trash"></i> </button>
                                                </form>
                                            </div>
                                            <div class="col-3"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="text-center">
                                <td colspan="9"><h4>{{ __('msg.No Data Found')}}</h4></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
       </div>
    </div>
</div>

<div class="col-12">
    <div class="card">
       <div class="card-body">
        <h3 class="mb-3 p-2">{{__('msg.Recently Registered Parents')}}</h3>
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">Name</th>
                            <th class="text-center">Email</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Verified</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!empty($parents_records))
                            @foreach ($parents_records as $value)
                                <tr>
                                    <td class="text-center">{{$value->name}}</td>
                                    <td class="text-center">{{$value->email}}</td>
                                    <td class="text-center">{{$value->status}}</td>
                                    <td class="text-center"><span class="label label-{{ $value->is_verified == 'verified' ? 'success': 'danger'}}">{{ $value->is_verified == 'verified' ? 'Verified': 'Not Verified'}}</span></td>
                                    <td class="text-center bt-switch">

                                        <div class="row justify-content-center">
                                            <div class="col-3"></div>
                                            <div class="col-2 mt-1">
                                                <form action="{{route('changeParentStatus')}}" method="post">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{$value->id}}">
                                                    <input type="hidden" name="status" value="{{$value->status == 'Unblocked' ? 'Blocked' : 'Unblocked' }}">
                                                    <button type="submit" data-status="{{$value->status == 'Unblocked' ? 'Unblocked' : 'Blocked'}}" data-id="{{$value->id}}" data-name="{{$value->name}}" class="btn block_confirm btn-sm"><input type="checkbox" id="switch" {{$value->status == 'Unblocked' ? 'checked' : ''}} /><label for="switch">Toggle</label></button>
                                                </form>
                                            </div>
                                            <div class="col-2">
                                                <form action="{{route('viewParent')}}" method="post">
                                                    @csrf
                                                    <input type="hidden" value="{{$value->id}}" id="id" name="id" />
                                                    <button type="submit" class="btn btn-lg text-qysmat" onclick="this.form.submit()" data-toggle="tooltip" title='View'> <i class="fas fa-eye"></i> </button>
                                                </form>
                                            </div>
                                            <div class="col-2">
                                                <form action="{{route('deleteParent')}}" method="post">
                                                    @csrf
                                                    <input type="hidden" value="{{$value->id}}" id="id" name="id" />
                                                    <button type="submit" class="btn btn-lg text-qysmat show_confirm" data-name="{{$value->name}}" data-id="{{$value->id}}" data-toggle="tooltip" title='Delete'> <i class="fas fa-trash"></i> </button>
                                                </form>
                                            </div>
                                            <div class="col-3"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="text-center">
                                <td colspan="9"><h4>{{ __('msg.No Data Found')}}</h4></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
       </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script type="text/javascript">

     $('.show_confirm').click(function(event) {
        var form =  $(this).closest("form");
        var name = $(this).data("name");
        let id = $(this).data('id');
        event.preventDefault();
        swal({
            title: "{{__('msg.Are You Sure')}}",
            text: "{{__('msg.You want to Delete ')}}"+name+" ?",
            icon: "warning",
            buttons: ["{{__('msg.Cancel')}}", "{{__('msg.Yes')}}"],
            dangerMode: true,
        })
        .then((willDelete) => {
        if (willDelete) {
            form.submit();
        }
        });
    });

    $('.block_confirm').click(function(event) {
        var form =  $(this).closest("form");
        var name = $(this).data("name");
        let status = $(this).data('status');
        let id = $(this).data('id');
        event.preventDefault();
        swal({
            title: "{{__('msg.Are You Sure')}}",
            text: (status == 'Unblocked') ? "{{__('msg.You want to Block ')}}"+name+" ?" : "{{__('msg.You want to Unblock ')}}"+name+" ?",
            icon: "warning",
            buttons: ["{{__('msg.Cancel')}}", "{{__('msg.Yes')}}"],
            dangerMode: true,
        })
        .then((willDelete) => {
        if (willDelete) {
            form.submit();
        }
        });
    });

</script>
