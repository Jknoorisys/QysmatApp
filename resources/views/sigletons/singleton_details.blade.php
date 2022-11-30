<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<style>
    .card .card-header {
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }

    .card-price {
        font-size: 2.7rem;
    }
</style>
<div class="row">
    <!-- Column -->
    <div class="col-lg-4 col-xlg-3 col-md-5">
        <div class="card">
            <div class="card-body">
                <center class="m-t-30"> <img src="{{ $details->photo1 ? asset($details->photo1) : 'assets/images/users/5.jpg'}}" class="rounded-circle" width="150" height="150" />
                    <h4 class="card-title m-t-10">{{$details->name}}</h4>
                    <h6 class="card-subtitle">{{$details->profession}}</h6>
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
                        <small class="text-muted">{{__('msg.Email')}}</small><h6>{{$details->email}}</h6>
                    </div>
                    <div class="col-6">
                        <small class="text-muted p-t-30 db">{{__('msg.Phone')}}</small><h6>{{$details->mobile}}</h6>
                    </div>
                </div>


                <small class="text-muted p-t-30 db">{{__('msg.Address')}}</small><h6>{{$details->location}}</h6>

                <hr />

                <div class="row">
                    <div class="col-4"><small class="text-muted p-t-30 db">{{__('msg.Gender')}}</small><h6>{{$details->gender}}</h6></div>
                    <div class="col-4"><small class="text-muted p-t-30 db">{{__('msg.Age')}}</small><h6>{{$details->age}}</h6></div>
                    <div class="col-4"><small class="text-muted p-t-30 db">{{__('msg.Height')}}</small><h6>{{$details->height}}</h6></div>
                </div>

                <hr />

                <div class="row">
                    <div class="col-4"><small class="text-muted p-t-30 db">{{__('msg.Nationality')}}</small><h6>{{$details->nationality}}</h6></div>
                    <div class="col-4"><small class="text-muted p-t-30 db">{{__('msg.Ethnic Origin')}}</small><h6>{{$details->ethnic_origin}}</h6></div>
                    <div class="col-4"><small class="text-muted p-t-30 db">{{__('msg.Islamic Sector')}}</small><h6>{{$details->islamic_sect}}</h6></div>
                </div>


                <hr />

                <small class="text-muted p-t-30 db">{{__('msg.Short Intro')}}</small><h6>{{$details->short_intro}}</h6>

                {{-- <div>
                    <hr>
                </div>

                <div class="map-box">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d470029.1604841957!2d72.29955005258641!3d23.019996818380896!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x395e848aba5bd449%3A0x4fcedd11614f6516!2sAhmedabad%2C+Gujarat!5e0!3m2!1sen!2sin!4v1493204785508" width="100%" height="150" frameborder="0" style="border:0" allowfullscreen></iframe>
                </div> --}}
                {{-- <small class="text-muted p-t-30 db">Social Profile</small>
                <br/>
                <button class="btn btn-circle btn-secondary"><i class="fab fa-facebook-f"></i></button>
                <button class="btn btn-circle btn-secondary"><i class="fab fa-twitter"></i></button>
                <button class="btn btn-circle btn-secondary"><i class="fab fa-youtube"></i></button> --}}
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
                    <a class="nav-link active" id="pills-timeline-tab" data-toggle="pill" href="#current-month" role="tab" aria-controls="pills-timeline" aria-selected="true">{{ __('msg.Images')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pills-profile-tab" data-toggle="pill" href="#last-month" role="tab" aria-controls="pills-profile" aria-selected="false">{{__('msg.Verify Profile')}}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pills-setting-tab" data-toggle="pill" href="#previous-month" role="tab" aria-controls="pills-setting" aria-selected="false">{{__('msg.Subscription Plan')}}</a>
                </li>
            </ul>
            <!-- Tabs -->
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="current-month" role="tabpanel" aria-labelledby="pills-timeline-tab">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 col-md-12 m-b-20"><img src="{{ $details->photo1 ? asset($details->photo1) : 'assets/images/big/img1.jpg'}}" class="img-fluid rounded" width="200" height="200" /></div>
                            <div class="col-lg-4 col-md-12 m-b-20"><img src="{{ $details->photo2 ? asset($details->photo2) : 'assets/images/big/img2.jpg'}}" class="img-fluid rounded" width="200" height="200" /></div>
                            <div class="col-lg-4 col-md-12 m-b-20"><img src="{{ $details->photo3 ? asset($details->photo3) : 'assets/images/big/img3.jpg'}}" class="img-fluid rounded" width="200" height="200" /></div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 col-md-12 m-b-20"><img src="{{ $details->photo4 ? asset($details->photo4) : 'assets/images/big/img4.jpg'}}" class="img-fluid rounded" width="200" height="200" /></div>
                            <div class="col-lg-4 col-md-12 m-b-20"><img src="{{ $details->photo5 ? asset($details->photo5) : 'assets/images/big/img5.jpg'}}" class="img-fluid rounded" width="200" height="200" /></div>
                            {{-- <div class="col-lg-4 col-md-12 m-b-20"><img src="{{ $details->photo1 ? $details->photo1) : 'assets/images/big/img6.jpg'}}" class="img-fluid rounded" width="200" height="200" /></div> --}}
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="last-month" role="tabpanel" aria-labelledby="pills-profile-tab">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 m-b-20"><img src="{{ $details->live_photo ? asset($details->live_photo) : 'assets/images/big/img1.jpg'}}" class="img-fluid rounded" width="300" height="200" /></div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 col-md-12 m-b-20"><a href="{{asset($details->id_proof)}}" class="btn btn-qysmat image-popup-vertical-fit el-link">{{__('msg.View ID Proof')}}</a></div>
                        </div>
                        <div class="row">
                            <form action="{{route('verifySingleton')}}" method="post">
                                @csrf
                                <input type="hidden" name="id" value="{{$details->id}}">
                                <input type="hidden" name="is_verified" value="{{$details->is_verified == 'verified' ? 'rejected' : 'verified' }}">
                                <button type="submit" data-status="{{$details->is_verified}}" data-id="{{$details->id}}" data-name="{{$details->name}}" class="btn btn-rounded show_confirm btn-{{$details->is_verified == 'verified' ? 'danger' : 'success' }}">{{$details->is_verified == 'verified' ? __('msg.Mark As Rejected') : __('msg.Mark As Verified') }}</button>
                            </form>
                        </div>

                    </div>
                </div>
                <div class="tab-pane fade" id="previous-month" role="tabpanel" aria-labelledby="pills-setting-tab">
                    <div class="row justify-content-center mt-4">
                        <div class="col-6">
                            <div class="card" style="border: 1px solid #e7e0d6; border-radius:15px;">
                                <div class="card-header bg-qysmat">
                                    <h5 class="card-title text-uppercase text-center">{{$details->subscription_type}}</h5>
                                    <h6 class="card-price text-white text-center">{{$details->price}} {{$details->currency}}<span class="term"></span></h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item"><i class="fas fa-check mr-2 btn-sm"></i>Single User</li>
                                        <li class="list-group-item"><i class="fas fa-check mr-2 btn-sm"></i>Unlimited Test</li>
                                        <li class="list-group-item"><i class="fas fa-check mr-2 btn-sm"></i>Community Access</li>
                                        <li class="list-group-item"><i class="fas fa-check mr-2 btn-sm"></i>Easy Download</li>
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
