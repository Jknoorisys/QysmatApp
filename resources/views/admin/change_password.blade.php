<div class="row">
    <!-- Column -->
    <div class="col-lg-4 col-xlg-3 col-md-5">
        <div class="card">
            <div class="card-body">
                <center class="m-t-30">
                    <img src="{{ $admin->profile ? asset('uploads/admin-profile/'.$admin->profile) : 'assets/images/users/5.jpg'}}" class="rounded-circle" width="150" />
                    <h4 class="card-title m-t-10">{{$admin->name}}</h4>
                    <h6 class="card-subtitle">{{$admin->email}}</h6>
                </center>
            </div>
        </div>
    </div>
    <!-- Column -->
    <!-- Column -->
    <div class="col-lg-8 col-xlg-9 col-md-7">
        <div class="card">
            <div class="card-body">
                <form class="form-horizontal form-material" action="{{route('changePasswordFun')}}" method="post" id="change_password">
                    @csrf
                    <div class="col-md-12">
                        <label for="old_password" class="form-label">{{__('msg.Password')}}</label>
                        <div class="input-group" id="show_hide_new_password">
                            <input type="password" class="form-control smp-input border-end-0" style="font-weight: 300;font-size: 15px;color: #38424C;" name="old_password" id="old_password" placeholder="{{ __('msg.Enter Old Password')}}"> <a href="javascript:;" style="line-height: 38px;align-items: center;color: #9197B3;" class="input-group-text bg-transparent"><i class='fas fa-eye-slash'></i></a>
                        </div>
                        <span class="err_old_password text-danger">@error('old_password') {{$message}} @enderror</span>
                    </div>
                    <div class="col-md-12 mt-4">
                        <label for="new_password" class="form-label">{{__('msg.New Password')}}</label>
                        <div class="input-group" id="show_hide_old_password">
                            <input type="password" class="form-control smp-input border-end-0" style="font-weight: 300;font-size: 15px;color: #38424C;" name="new_password" id="new_password" placeholder="{{ __('msg.Enter New Password')}}"> <a href="javascript:;" style="line-height: 38px;align-items: center;color: #9197B3;" class="input-group-text bg-transparent"><i class='fas fa-eye-slash'></i></a>
                        </div>
                        <span class="err_new_password text-danger">@error('new_password') {{$message}} @enderror</span>
                    </div>
                    <div class="col-md-12 mt-4">
                        <label for="password" class="form-label">{{__('msg.Confirm Password')}}</label>
                        <div class="input-group" id="show_hide_password">
                            <input type="password" class="form-control smp-input border-end-0" style="font-weight: 300;font-size: 15px;color: #38424C;" name="cnfm_password" id="password" placeholder="{{ __('msg.Confirm Password')}}"> <a href="javascript:;" style="line-height: 38px;align-items: center;color: #9197B3;" class="input-group-text bg-transparent"><i class='fas fa-eye-slash'></i></a>
                        </div>
                        <span class="err_password text-danger">@error('cnfm_password') {{$message}} @enderror</span>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12 mt-4">
                            <button type="submit" class="btn btn-qysmat text-uppercase">{{ __('Save Changes')}}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="assets/libs/jquery/dist/jquery.min.js"></script>

<script>
    $(document).ready(function() {

        // Match Password
        var new_password = document.getElementById("new_password")
        , password = document.getElementById("password")
        , old_password = document.getElementById("old_password");

        function validatePassword(){
            if(password.value != new_password.value) {
                password.setCustomValidity("Passwords Don't Match");
            } else {
                password.setCustomValidity('');
            }
        }

        function validateNewPassword(){
            if(old_password.value == new_password.value) {
                new_password.setCustomValidity("New Password Should'nt be Same as Old Password");
            } else {
                new_password.setCustomValidity('');
            }
        }

        new_password.onchange = validatePassword;
        password.onkeyup = validatePassword;
        new_password.onkeyup = validateNewPassword;
            
        $("#change_password").on('input', function(e) {
            e.preventDefault();
            let valid = true;
            let form = $(this).get(0);
            let old_password = $("#old_password").val();
            let err_old_password = "{{__('msg.Enter Old Password')}}";
            let new_password = $("#new_password").val();
            let err_new_password = "{{__('msg.Enter New Password')}}";
            let password = $("#password").val();
            let err_password = "{{__('msg.Confirm Password')}}";

                if (old_password.length === 0) {
                    $(".err_old_password").text(err_old_password);
                    $('#old_password').addClass('is-invalid');
                    valid = false;
                } else {
                    $(".err_old_password").text('');
                    $('#old_password').addClass('is-valid');
                    $('#old_password').removeClass('is-invalid');
                }
                if (new_password.length === 0) {
                    $(".err_new_password").text(err_new_password);
                    $('#new_password').addClass('is-invalid');
                    valid = false;
                } else {
                    $(".err_new_password").text('');
                    $('#new_password').addClass('is-valid');
                    $('#new_password').removeClass('is-invalid');
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
            // if (valid) {
            //     form.submit();
            // }
        });

        $("#show_hide_old_password a").on('click', function(event) {
                event.preventDefault();
                if ($('#show_hide_old_password input').attr("type") == "text") {
                    $('#show_hide_old_password input').attr('type', 'password');
                    $('#show_hide_old_password i').addClass("fa-eye-slash");
                    $('#show_hide_old_password i').removeClass("fa-eye");
                } else if ($('#show_hide_old_password input').attr("type") == "password") {
                    $('#show_hide_old_password input').attr('type', 'text');
                    $('#show_hide_old_password i').removeClass("fa-eye-slash");
                    $('#show_hide_old_password i').addClass("fa-eye");
                }
            });

        $("#show_hide_new_password a").on('click', function(event) {
                event.preventDefault();
                if ($('#show_hide_new_password input').attr("type") == "text") {
                    $('#show_hide_new_password input').attr('type', 'password');
                    $('#show_hide_new_password i').addClass("fa-eye-slash");
                    $('#show_hide_new_password i').removeClass("fa-eye");
                } else if ($('#show_hide_new_password input').attr("type") == "password") {
                    $('#show_hide_new_password input').attr('type', 'text');
                    $('#show_hide_new_password i').removeClass("fa-eye-slash");
                    $('#show_hide_new_password i').addClass("fa-eye");
                }
            });

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
