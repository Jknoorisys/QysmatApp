<div class="card">
    <div class="card-body">
        <form class="form-horizontal form-material" action="{{route('updatePriceFun')}}" method="post" id="change_price">
            @csrf
            <div class="row">
                <input type="hidden" name="id" value="{{$records->id}}">
                <div class="col-md-6">
                    <label for="subscription_type" class="form-label">{{__('msg.Subscription')}}</label>
                    <div class="input-group">
                        <input type="text" value="{{$records->subscription_type}}" class="form-control smp-input" style="font-weight: 300;font-size: 15px;color: #38424C;" name="subscription_type" id="subscription_type" placeholder="{{ __('msg.Enter Subscription Type')}}">
                    </div>
                    <span class="err_subscription_type text-danger">@error('subscription_type') {{$message}} @enderror</span>
                </div>
                <div class="col-md-4">
                    <label for="price" class="form-label">{{__('msg.Price')}}</label>
                    <div class="input-group">
                        <input type="text" value="{{$records->price}}" class="form-control smp-input" style="font-weight: 300;font-size: 15px;color: #38424C;" name="price" id="price" placeholder="{{ __('msg.Enter Subscription Price')}}">
                    </div>
                    <span class="err_price text-danger">@error('price') {{$message}} @enderror</span>
                </div>
                <div class="col-md-2">
                    <label for="save" class="form-label">&nbsp;</label>
                    <div class="input-group">
                        <button type="submit" id="save" class="btn btn-qysmat text-uppercase">{{ __('Save Changes')}}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="assets/libs/jquery/dist/jquery.min.js"></script>

<script>
    $(document).ready(function() {

        $("#change_price").on('submit', function(e) {
            e.preventDefault();
            let valid = true;
            let form = $(this).get(0);
            let subscription_type = $("#subscription_type").val();
            let err_subscription_type = "{{__('msg.Subscription Type is Required')}}";
            let price = $("#price").val();
            let err_price = "{{__('msg.Price is Required')}}";

                if (subscription_type.length === 0) {
                    $(".err_subscription_type").text(err_subscription_type);
                    $('#subscription_type').addClass('is-invalid');
                    valid = false;
                } else {
                    $(".err_subscription_type").text('');
                    $('#subscription_type').addClass('is-valid');
                    $('#subscription_type').removeClass('is-invalid');
                }
                if (price.length === 0) {
                    $(".err_price").text(err_price);
                    $('#price').addClass('is-invalid');
                    valid = false;
                } else {
                    $(".err_price").text('');
                    $('#price').addClass('is-valid');
                    $('#price').removeClass('is-invalid');
                }
            if (valid) {
                form.submit();
            }
        });

    });
</script>
