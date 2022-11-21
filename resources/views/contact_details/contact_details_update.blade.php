<div class="card">
    <div class="card-body">
        <form class="form-horizontal form-material" action="{{route('updateContactFun')}}" method="post" id="add_contact">
            @csrf
            <input type="hidden" value="{{$records->id}}" id="id" name="id" />
                <div class="col-md-12">
                    <label for="contact_type" class="form-label">{{__('msg.Contact Type')}}</label>
                    <div class="input-group">
                        <input type="text" class="form-control smp-input" value="{{$records->contact_type}}" style="font-weight: 300;font-size: 15px;color: #38424C;" name="contact_type" id="contact_type" placeholder="{{ __('msg.Enter Contact Type')}}">
                    </div>
                    <span class="err_contact_type text-danger">@error('contact_type') {{$message}} @enderror</span>
                </div>
                <div class="col-md-12 mt-4">
                    <label for="details" class="form-label">{{__('msg.Details')}}</label>
                    <div class="input-group">
                        {{-- <textarea rows="4" class="form-control smp-input" style="font-weight: 300;font-size: 15px;color: #38424C;" name="details" id="details" placeholder="{{ __('msg.Enter Contact Details')}}">{{$records->details}}</textarea> --}}
                        <textarea id="details" name="details" rows="10" placeholder="{{ __('msg.Enter Contact Details')}}" data-sample="2" data-sample-short>{{$records->details}}</textarea>
                    </div>
                    <span class="err_details text-danger">@error('details') {{$message}} @enderror</span>
                </div>
                <div class="col-md-12">
                    <label for="save" class="form-label">&nbsp;</label>
                    <div class="input-group">
                        <a href="{{route('contact_details')}}" id="save" class="btn btn-qysmat-light text-uppercase mr-2">{{ __('Cancel')}}</a>
                        <button type="submit" id="save" class="btn btn-qysmat text-uppercase">{{ __('Save')}}</button>
                    </div>
                </div>
            {{-- </div> --}}
        </form>
    </div>
</div>

<script src="assets/libs/jquery/dist/jquery.min.js"></script>

<script>
    $(document).ready(function() {

        $("#add_contact").on('submit', function(e) {
            e.preventDefault();
            let valid = true;
            let form = $(this).get(0);
            let contact_type = $("#contact_type").val();
            let err_contact_type = "{{__('msg.Contact Type is Required')}}";
            let details = $("#details").val();
            let err_details = "{{__('msg.Contact Detail is Required')}}";

                if (contact_type.length === 0) {
                    $(".err_contact_type").text(err_contact_type);
                    $('#contact_type').addClass('is-invalid');
                    valid = false;
                } else {
                    $(".err_contact_type").text('');
                    $('#contact_type').addClass('is-valid');
                    $('#contact_type').removeClass('is-invalid');
                }
                if (details.length === 0) {
                    $(".err_details").text(err_details);
                    $('#details').addClass('is-invalid');
                    valid = false;
                } else {
                    $(".err_details").text('');
                    $('#details').addClass('is-valid');
                    $('#details').removeClass('is-invalid');
                }
            if (valid) {
                form.submit();
            }
        });

    });
</script>

<script src="assets/libs/ckeditor/ckeditor.js"></script>
<script src=" assets/libs/ckeditor/samples/js/sample.js"></script>


<script data-sample="2">
    CKEDITOR.replace('details', {
        height: 200,
        width:1000,
    });
</script>
