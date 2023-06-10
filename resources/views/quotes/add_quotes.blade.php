<div class="card">
    <div class="card-body">
        <form class="form-horizontal form-material" action="{{route('addQuoteFun')}}" method="post" id="add_contact" enctype="multipart/form-data">
            @csrf
                <div class="col-md-12 mt-4">
                    <label for="quote" class="form-label">{{__('msg.Quote')}}</label>
                    <div class="input-group">
                        <textarea id="quote" name="quote" rows="5" cols="150" placeholder=" {{ __('msg.Enter Islamic Quote')}}"></textarea>
                    </div>
                    <span class="err_quote text-danger">@error('quote') {{$message}} @enderror</span>
                </div>
                <div class="col-md-12 mt-4">
                    <label for="quote" class="form-label">{{__('msg.Upload Image')}}</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Upload</span>
                        </div>
                        <div class="custom-file">
                            <input type="file" name="image" id="image" class="custom-file-input" id="inputGroupFile01">
                            <label class="custom-file-label" for="inputGroupFile01">Choose file</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <label for="save" class="form-label">&nbsp;</label>
                    <div class="input-group">
                        <a href="{{route('quotes')}}" id="save" class="btn btn-qysmat-light text-uppercase mr-2">{{ __('Cancel')}}</a>
                        <button type="submit" id="save" class="btn btn-qysmat text-uppercase">{{ __('Save')}}</button>
                    </div>
                </div>
        </form>
    </div>
</div>

<script src="assets/libs/jquery/dist/jquery.min.js"></script>

<script>
    $(document).ready(function() {

        $("#add_contact").on('input', function(e) {
            e.preventDefault();
            let valid = true;
            let form = $(this).get(0);
            let quote = $("#quote").val();
            let image = $("#image").val();
            let err_quote = "{{__('msg.Quote is Required')}}";

            if (quote.length === 0) {
                $(".err_quote").text(err_quote);
                $('#quote').addClass('is-invalid');
                valid = false;
            } else {
                $(".err_quote").text('');
                $('#quote').addClass('is-valid');
                $('#quote').removeClass('is-invalid');
            }

            // if (valid) {
            //     form.submit();
            // }
        });

    });
</script>
