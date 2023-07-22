<div class="card">
    <div class="card-body">
        <form class="form-horizontal form-material" action="{{route('addWebPageFun')}}" method="post" id="add_contact">
            @csrf
            {{-- <div class="row"> --}}
                <div class="col-md-12">
                    <label for="page_name" class="form-label">{{__('msg.Web Page Name')}}</label>
                    <div class="input-group">
                        <input type="text" class="form-control smp-input" style="font-weight: 300;font-size: 15px;color: #38424C;" name="page_name" id="page_name" placeholder="{{ __('msg.Enter Web Page Name')}}">
                    </div>
                    <span class="err_page_name text-danger">@error('page_name') {{$message}} @enderror</span>
                </div>
                <div class="col-md-12 mt-4">
                    <label for="page_title" class="form-label">{{__('msg.Web Page Title')}}</label>
                    <div class="input-group">
                        <input type="text" class="form-control smp-input" style="font-weight: 300;font-size: 15px;color: #38424C;" name="page_title" id="page_title" placeholder="{{ __('msg.Enter Web Page Title')}}">
                    </div>
                    <span class="err_page_title text-danger">@error('page_title') {{$message}} @enderror</span>
                </div>
                <div class="col-md-12 mt-4">
                    <label for="description" class="form-label">{{__('msg.Description')}}</label>
                    <div class="input-group">
                        <textarea id="description" name="description" rows="10" placeholder="{{ __('msg.Enter Web Page Description')}}" data-sample="2" data-sample-short></textarea>
                    </div>
                    <span class="err_description text-danger">@error('description') {{$message}} @enderror</span>
                </div>
                <div class="col-md-12">
                    <label for="save" class="form-label">&nbsp;</label>
                    <div class="input-group">
                        <a href="{{route('web_pages')}}" id="save" class="btn btn-qysmat-light text-uppercase mr-2">{{ __('Cancel')}}</a>
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
            let page_name = $("#page_name").val();
            let err_page_name = "{{__('msg.Page Name is Required')}}";
            let page_title = $("#page_title").val();
            let err_page_title = "{{__('msg.Page Title is Required')}}";
            let description = CKEDITOR.instances['description'].getData();
            let err_description = "{{__('msg.Page Description is Required')}}";

                if (page_name.length === 0) {
                    $(".err_page_name").text(err_page_name);
                    $('#page_name').addClass('is-invalid');
                    valid = false;
                } else {
                    $(".err_page_name").text('');
                    $('#page_name').addClass('is-valid');
                    $('#page_name').removeClass('is-invalid');
                }

                if (page_title.length === 0) {
                    $(".err_page_title").text(err_page_title);
                    $('#page_title').addClass('is-invalid');
                    valid = false;
                } else {
                    $(".err_page_title").text('');
                    $('#page_title').addClass('is-valid');
                    $('#page_title').removeClass('is-invalid');
                }

                if (description.length === 0) {
                    $(".err_description").text(err_description);
                    $('#description').addClass('is-invalid');
                    valid = false;
                } else {
                    $(".err_description").text('');
                    $('#description').addClass('is-valid');
                    $('#description').removeClass('is-invalid');
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
    CKEDITOR.replace('description', {
        height: 200,
        width:1000,
    });
</script>
