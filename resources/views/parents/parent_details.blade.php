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
                <center class="m-t-30"> <img src="{{ (!empty($reverify) && $reverify->profile_pic) ? $reverify->profile_pic : ($details->profile_pic ? asset($details->profile_pic) : 'assets/images/users/no-image.png')}}" class="rounded-circle" width="150" height="150" />
                    <h4 class="card-title m-t-10">{{(!empty($reverify) && $reverify->name) ? $reverify->name.' '.$reverify->lname : $details->name.' '.$details->lname}}</h4>
                    <h6 class="card-subtitle">{{(!empty($reverify) && $reverify->email) ? $reverify->email : $details->email}}</h6>
                </center>
            </div>

                <hr>

            <div class="card-body">
                <small class="text-muted">{{__('msg.Email')}}</small><h6>{{(!empty($reverify) && $reverify->email) ? $reverify->email : $details->email}}</h6>
                <small class="text-muted p-t-30 db">{{__('msg.Phone')}}</small><h6>{{(!empty($reverify) && $reverify->mobile) ? $reverify->mobile : $details->mobile}}</h6>
                <small class="text-muted p-t-30 db">{{__('msg.Address')}}</small><h6>{{(!empty($reverify) && $reverify->location) ? $reverify->location : $details->location}}</h6>
                <small class="text-muted p-t-30 db">{{__('msg.Relation with Singleton')}}</small><h6>{{(!empty($reverify) && $reverify->relation_with_singleton) ? $reverify->relation_with_singleton : $details->relation_with_singleton}}</h6>
                <hr />

                <div class="row">
                    <div class="col-4"><small class="text-muted p-t-30 db">{{__('msg.Nationality')}}</small><h6>{{(!empty($reverify) && $reverify->nationality) ? $reverify->nationality : $details->nationality}}</h6></div>
                    <div class="col-4"><small class="text-muted p-t-30 db">{{__('msg.Ethnic Origin')}}</small><h6>{{(!empty($reverify) && $reverify->ethnic_origin) ? $reverify->ethnic_origin : $details->ethnic_origin}}</h6></div>
                    <div class="col-4"><small class="text-muted p-t-30 db">{{__('msg.Islamic Sector')}}</small><h6>{{(!empty($reverify) && $reverify->islamic_sect) ? $reverify->islamic_sect : $details->islamic_sect}}</h6></div>
                </div>
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
                    <a class="nav-link text-qysmat" id="pills-setting-tab" data-toggle="pill" href="#previous-month" role="tab" aria-controls="pills-setting" aria-selected="false">{{__('msg.Subscription Plan')}}</a>
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
                                <form action="{{route('verifyParent')}}" method="post">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$details->id}}">
                                    <input type="hidden" name="is_verified" value="{{$details->is_verified == 'verified' ? 'rejected' : 'verified' }}">
                                    <button type="submit" data-status="{{$details->is_verified}}" data-id="{{$details->id}}" data-name="{{$details->name}}" class="btn btn-rounded show_confirm btn-{{$details->is_verified == 'verified' ? 'danger' : 'success' }}">{{$details->is_verified == 'verified' ? __('msg.Mark As Rejected') : __('msg.Mark As Verified') }}</button>
                                </form>
                            </div>
                        @elseif ($details->id_proof)
                            <div class="row">
                                <div class="col-lg-4 col-md-12 m-b-20"><a href="{{asset($details->id_proof ? asset($details->id_proof) : 'assets/images/big/img4.jpg')}}" class="btn btn-qysmat image-popup-vertical-fit el-link">{{__('msg.View ID Proof')}}</a></div>
                            </div>
                            <div class="row">
                                <form action="{{route('verifyParent')}}" method="post">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$details->id}}">
                                    <input type="hidden" name="is_verified" value="{{$details->is_verified == 'verified' ? 'rejected' : 'verified' }}">
                                    <button type="submit" data-status="{{$details->is_verified}}" data-id="{{$details->id}}" data-name="{{$details->name}}" class="btn btn-rounded show_confirm btn-{{$details->is_verified == 'verified' ? 'danger' : 'success' }}">{{$details->is_verified == 'verified' ? __('msg.Mark As Rejected') : __('msg.Mark As Verified') }}</button>
                                </form>
                            </div>
                        @endif
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
