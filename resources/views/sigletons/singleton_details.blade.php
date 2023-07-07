<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<style>
    .card .card-header {
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }

    .card-price {
        font-size: 2.7rem;
    }

    .image-size {
        width: 200px;
        height: 200px;
    }

    .nav-pills.custom-pills .nav-link.active {
        color: #8f7c5c;
        opacity: 1;
        background-color: transparent;
        font-weight: bold;
        border-bottom: 2px solid #8f7c5c;
    }
</style>
<div class="row">
    <!-- Column -->
    <div class="col-lg-4 col-xlg-3 col-md-5">
        <div class="card">
            <div class="card-body">
                <center class="m-t-30"> <img src="{{ (!empty($reverify) && $reverify->photo1) ? $reverify->photo1 : ($details->photo1 ? asset($details->photo1) : 'assets/images/users/no-image.png')}}" class="rounded-circle" width="150" height="150" />
                    {{-- <h4 class="card-title m-t-10">{{(!empty($reverify) && $reverify->name) ? $reverify->name : $details->name}}</h4> --}}
                    <h4 class="card-title m-t-10">{{(!empty($reverify) && $reverify->name) ? $reverify->name.' '.$reverify->lname : $details->name.' '.$details->lname}}</h4>
                    <h6 class="card-subtitle">{{(!empty($reverify) && $reverify->profession) ? $reverify->profession : $details->profession}}</h6>
                    {{-- <div class="row text-center justify-content-md-center">
                        <div class="col-2"></div>
                        <div class="col-3"><i class="icon-people"></i> <font class="font-medium">{{ __('msg.Gender')}}<br></font>{{$details->gender}}</div>
                        <div class="col-2"><i class="icon-picture"></i> <font class="font-medium">{{__('msg.Age')}}<br></font>{{$details->age}}</div>
                        <div class="col-3"><i class="icon-picture"></i> <font class="font-medium">{{__('msg.Height')}}<br></font>{{$details->height}}</div>
                        <div class="col-2"></div>
                    </div> --}}
                </center>
            </div>

            <div>
                <hr>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <small class="text-muted">{{__('msg.Email')}}</small><h6>{{(!empty($reverify) && $reverify->email) ? $reverify->email : $details->email}}</h6>
                    </div>
                    <div class="col-6">
                        <small class="text-muted p-t-30 db">{{__('msg.Phone')}}</small><h6>{{(!empty($reverify) && $reverify->mobile) ? $reverify->mobile : $details->mobile}}</h6>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <small class="text-muted p-t-30 db">{{__('msg.Address')}}</small><h6>{{(!empty($reverify) && $reverify->location) ? $reverify->location : $details->location}}</h6>
                    </div>
                    <div class="col-6">
                        <small class="text-muted p-t-30 db">{{__('msg.Marital Status')}}</small><h6>{{(!empty($reverify) && $reverify->marital_status) ? $reverify->marital_status : $details->marital_status}}</h6>
                    </div>
                </div>

                <hr />

                <div class="row">
                    <div class="col-4"><small class="text-muted p-t-30 db">{{__('msg.Gender')}}</small><h6>{{(!empty($reverify) && $reverify->gender) ? $reverify->gender : $details->gender}}</h6></div>
                    <div class="col-4"><small class="text-muted p-t-30 db">{{__('msg.Age')}}</small><h6>{{(!empty($reverify) && $reverify->age) ? $reverify->age : $details->age}}</h6></div>
                    <div class="col-4"><small class="text-muted p-t-30 db">{{__('msg.Height')}}</small><h6>{{(!empty($reverify) && $reverify->height) ? $reverify->height : $details->height}}</h6></div>
                </div>

                <hr />

                <div class="row">
                    <div class="col-4"><small class="text-muted p-t-30 db">{{__('msg.Nationality')}}</small><h6>{{(!empty($reverify) && $reverify->nationality) ? $reverify->nationality : $details->nationality}}</h6></div>
                    <div class="col-4"><small class="text-muted p-t-30 db">{{__('msg.Ethnic Origin')}}</small><h6>{{(!empty($reverify) && $reverify->ethnic_origin) ? $reverify->ethnic_origin : $details->ethnic_origin}}</h6></div>
                    <div class="col-4"><small class="text-muted p-t-30 db">{{__('msg.Islamic Sector')}}</small><h6>{{(!empty($reverify) && $reverify->islamic_sect) ? $reverify->islamic_sect : $details->islamic_sect}}</h6></div>
                </div>


                <hr />

                <small class="text-muted p-t-30 db">{{__('msg.Short Intro')}}</small><h6>{{(!empty($reverify) && $reverify->short_intro) ? $reverify->short_intro : $details->short_intro}}</h6>

            </div>
        </div>
    </div>
    <!-- Column -->
    <!-- Column -->
    <div class="col-lg-8 col-xlg-9 col-md-7">
        <div class="card">
            <!-- Tabs -->
            <ul class="nav nav-pills custom-pills" id="pills-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active text-qysmat" id="pills-profile-tab" data-toggle="pill" href="#last-month" role="tab" aria-controls="pills-profile" aria-selected="false">{{__('msg.Verify Profile')}}</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-qysmat" id="pills-timeline-tab" data-toggle="pill" href="#current-month" role="tab" aria-controls="pills-timeline" aria-selected="true">{{ __('msg.Images')}}</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link text-qysmat" id="pills-setting-tab" data-toggle="pill" href="#previous-month" role="tab" aria-controls="pills-setting" aria-selected="false">{{__('msg.Subscription Plan')}}</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-qysmat" id="pills-setting-tab" data-toggle="pill" href="#parent-details" role="tab" aria-controls="pills-setting" aria-selected="false">{{__('msg.Parent Details')}}</a>
                </li>
            </ul>
            <!-- Tabs -->
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="last-month" role="tabpanel" aria-labelledby="pills-profile-tab">
                    <div class="card-body">
                        @if ( !empty($reverify) && $reverify->live_photo)
                            <div class="card">
                                <div class="el-card-item">
                                    <div class="el-card-avatar el-overlay-1">
                                        <a class="image-popup-vertical-fit image-size" href="{{ $reverify->live_photo ? asset($reverify->live_photo) : 'assets/images/users/no-image.png'}}"> <img src="{{ $reverify->live_photo ? asset($reverify->live_photo) : 'assets/images/users/no-image.png'}}" class="image-size" alt="live-photo" /> </a>
                                    </div>
                                </div>
                            </div>
                        @elseif ($details->live_photo)
                            <div class="card">
                                <div class="el-card-item">
                                    <div class="el-card-avatar el-overlay-1">
                                        <a class="image-popup-vertical-fit image-size" href="{{ $details->live_photo ? asset($details->live_photo) : 'assets/images/users/no-image.png'}}"> <img src="{{ $details->live_photo ? asset($details->live_photo) : 'assets/images/users/no-image.png'}}" class="image-size" alt="live-photo" /> </a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (!empty($reverify) && $reverify->id_proof)
                            <div class="row">
                                <div class="col-lg-4 col-md-12 m-b-20"><a href="{{asset($reverify->id_proof ? asset($reverify->id_proof) : 'assets/images/users/no-image.png')}}" class="btn btn-qysmat image-popup-vertical-fit el-link">{{__('msg.View ID Proof')}}</a></div>
                            </div>
                            <div class="row">
                                <form action="{{route('verifySingleton')}}" method="post">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$details->id}}">
                                    <input type="hidden" name="is_verified" value="{{$details->is_verified == 'verified' ? 'rejected' : 'verified' }}">
                                    <button type="submit" data-status="{{$details->is_verified}}" data-id="{{$details->id}}" data-name="{{$details->name}}" class="btn btn-rounded show_confirm btn-{{$details->is_verified == 'verified' ? 'danger' : 'success' }}">{{$details->is_verified == 'verified' ? __('msg.Mark As Rejected') : __('msg.Mark As Verified') }}</button>
                                </form>
                            </div>
                        @elseif ($details->id_proof)
                            <div class="row">
                                <div class="col-lg-4 col-md-12 m-b-20"><a href="{{asset($details->id_proof ? asset($details->id_proof) : 'assets/images/users/no-image.png')}}" class="btn btn-qysmat image-popup-vertical-fit el-link">{{__('msg.View ID Proof')}}</a></div>
                            </div>
                            <div class="row">
                                <form action="{{route('verifySingleton')}}" method="post">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$details->id}}">
                                    <input type="hidden" name="is_verified" value="{{$details->is_verified == 'verified' ? 'rejected' : 'verified' }}">
                                    <button type="submit" data-status="{{$details->is_verified}}" data-id="{{$details->id}}" data-name="{{$details->name}}" class="btn btn-rounded show_confirm btn-{{$details->is_verified == 'verified' ? 'danger' : 'success' }}">{{$details->is_verified == 'verified' ? __('msg.Mark As Rejected') : __('msg.Mark As Verified') }}</button>
                                </form>
                            </div>
                        @endif

                    </div>
                </div>
                <div class="tab-pane fade" id="current-month" role="tabpanel" aria-labelledby="pills-timeline-tab">
                    <div class="card-body">
                        <div class="card-columns el-element-overlay">
                            @if ( !empty($details->photo1) || !empty($reverify->photo1))
                                <div class="card">
                                    <div class="el-card-item">
                                        <div class="el-card-avatar el-overlay-1">
                                            <a class="image-popup-vertical-fit image-size" href="{{ (!empty($reverify) && $reverify->photo1) ? $reverify->photo1 : ($details->photo1 ? asset($details->photo1) : 'assets/images/users/no-image.png') }}"> <img src="{{ (!empty($reverify) && $reverify->photo1) ? $reverify->photo1 : ($details->photo1 ? asset($details->photo1) : 'assets/images/users/no-image.png') }}" class="image-size" alt="image-1" /> </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            @if ( !empty($details->photo2) || !empty($reverify->photo2))
                                <div class="card">
                                    <div class="el-card-item">
                                        <div class="el-card-avatar el-overlay-1">
                                            <a class="image-popup-vertical-fit image-size" href="{{ (!empty($reverify) && $reverify->photo2) ? $reverify->photo2 : ($details->photo2 ? asset($details->photo2) : 'assets/images/users/no-image.png') }}"> <img src="{{ (!empty($reverify) && $reverify->photo2) ? $reverify->photo2 : ($details->photo2 ? asset($details->photo2) : 'assets/images/users/no-image.png') }}" class="image-size" alt="image-2" /> </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if ( !empty($details->photo3) || !empty($reverify->photo3))
                                <div class="card">
                                    <div class="el-card-item">
                                        <div class="el-card-avatar el-overlay-1">
                                            <a class="image-popup-vertical-fit image-size" href="{{ (!empty($reverify) && $reverify->photo3) ? $reverify->photo3 : ($details->photo3 ? asset($details->photo3) : 'assets/images/users/no-image.png') }}"> <img src="{{ (!empty($reverify) && $reverify->photo3) ? $reverify->photo3 : ($details->photo3 ? asset($details->photo3) : 'assets/images/users/no-image.png') }}" class="image-size" alt="image-3" /> </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if ( !empty($details->photo4) || !empty($reverify->photo4))
                                <div class="card">
                                    <div class="el-card-item">
                                        <div class="el-card-avatar el-overlay-1">
                                            <a class="image-popup-vertical-fit image-size" href="{{ (!empty($reverify) && $reverify->photo4) ? $reverify->photo4 : ($details->photo4 ? asset($details->photo4) : 'assets/images/users/no-image.png') }}"> <img src="{{ (!empty($reverify) && $reverify->photo4) ? $reverify->photo4 : ($details->photo4 ? asset($details->photo4) : 'assets/images/users/no-image.png') }}" class="image-size" alt="image-4" /> </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if ( !empty($details->photo5) || !empty($reverify->photo5))
                                <div class="card">
                                    <div class="el-card-item">
                                        <div class="el-card-avatar el-overlay-1">
                                            <a class="image-popup-vertical-fit image-size" href="{{ (!empty($reverify) && $reverify->photo5) ? $reverify->photo5 : ($details->photo5 ? asset($details->photo5) : 'assets/images/users/no-image.png') }}"> <img src="{{ (!empty($reverify) && $reverify->photo5) ? $reverify->photo5 : ($details->photo5 ? asset($details->photo5) : 'assets/images/users/no-image.png') }}" class="image-size" alt="image-5" /> </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="previous-month" role="tabpanel" aria-labelledby="pills-setting-tab">
                    <div class="row justify-content-center mt-4">
                        <div class="col-6">
                            <div class="card" style="border: 1px solid #e7e0d6; border-radius:15px;">
                                <div class="card-header bg-qysmat">
                                    <h4 class="card-title text-uppercase text-center">{{$details->subscription_type}}</h4>
                                    <h6 class="card-price text-white text-center">{{ $details->active_subscription_id !=1 ? $details->currency : ''}}{{$details->price}}
                                        @if ($details->active_subscription_id == 2)
                                            <p style="font-size:14px;">{{ __('msg.per month') }}</p>
                                        @elseif ($details->active_subscription_id == 3)
                                            <p style="font-size:14px;">{{ __('msg.per month per person') }}</p>
                                        @endif
                                        <span class="term"></span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        @foreach ($details->features as $feature)
                                        <li class="list-group-item"><i class="fas fa-check mr-2 btn-sm"></i>{{$feature}}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade show" id="parent-details" role="tabpanel" aria-labelledby="pills-profile-tab">
                    @if (!empty($parent_details))
                        <div class="row">
                            <div class="col-lg-3 col-xlg-3 col-md-5">
                                <div class="card">
                                    <div class="card-body">
                                        <center class="m-t-10">
                                            <div class="el-card-item">
                                                <div class="el-card-avatar el-overlay-1">
                                                    <a class="image-popup-vertical-fit image-size" href="{{ (!empty($parent_details) && $parent_details->profile_pic) ? asset($parent_details->profile_pic) : 'assets/images/users/no-image.png' }}"> <img src="{{ (!empty($parent_details) && $parent_details->profile_pic) ? asset($parent_details->profile_pic) : 'assets/images/users/no-image.png'}}" class="rounded-circle" width="150" height="150" /> </a>
                                                </div>
                                            </div>
                                            <h4 class="card-title m-t-10">{{(!empty($parent_details) && $parent_details->name) ? $parent_details->name.' '.$parent_details->lname : ''}}</h4>
                                            <h6 class="card-subtitle">{{(!empty($parent_details) && $parent_details->email) ? $parent_details->email : $parent_details->email}}</h6>
                                        </center>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-xlg-6 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                            <small class="text-muted">{{__('msg.Email')}}</small><h6>{{(!empty($parent_details) && $parent_details->email) ? $parent_details->email : ''}}</h6>
                                            <small class="text-muted p-t-30 db">{{__('msg.Phone')}}</small><h6>{{(!empty($parent_details) && $parent_details->mobile) ? $parent_details->mobile : ''}}</h6>
                                            <small class="text-muted p-t-30 db">{{__('msg.Address')}}</small><h6>{{(!empty($parent_details) && $parent_details->location) ? $parent_details->location : ''}}</h6>
                                            <hr />
                        
                                        <div class="row">
                                            <div class="col-4"><small class="text-muted p-t-30 db">{{__('msg.Nationality')}}</small><h6>{{(!empty($parent_details) && $parent_details->nationality) ? $parent_details->nationality : ''}}</h6></div>
                                            <div class="col-4"><small class="text-muted p-t-30 db">{{__('msg.Ethnic Origin')}}</small><h6>{{(!empty($parent_details) && $parent_details->ethnic_origin) ? $parent_details->ethnic_origin : ''}}</h6></div>
                                            <div class="col-4"><small class="text-muted p-t-30 db">{{__('msg.Islamic Sector')}}</small><h6>{{(!empty($parent_details) && $parent_details->islamic_sect) ? $parent_details->islamic_sect : ''}}</h6></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card-body">

                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- Column -->
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script type="text/javascript">

     $('.show_confirm').click(function(event) {
        var form =  $(this).closest("form");
        var name = $(this).data("name");
        let id = $(this).data('id');
        let status = $(this).data('status');
        event.preventDefault();
        swal({
            title: "{{__('msg.Are You Sure')}}",
            text: (status == "verified") ? "{{__('msg.You want to Reject ')}}"+name+" ?" : "{{__('msg.You want to Verify ')}}"+name+" ?" ,
            icon: "warning",
            buttons: ["Cancel", "Yes"],
            dangerMode: true,
        })
        .then((willDelete) => {
        if (willDelete) {
            form.submit();
        }
        });
    });
</script>
