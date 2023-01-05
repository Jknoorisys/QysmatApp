<div class="card">
    <div class="card-body">
        <form class="form-horizontal form-material" action="{{route('updateFAQFun')}}" method="post" id="add_faq">
            @csrf
            <input type="hidden" name="id" value="{{$records->id}}">
            {{-- <div class="row"> --}}
                <div class="col-md-12 mt-4">
                    <label for="question" class="form-label">{{__('msg.Question')}}</label>
                    <div class="input-group">
                        <textarea rows="4" class="form-control smp-input" style="font-weight: 300;font-size: 15px;color: #38424C;" name="question" id="question" placeholder="{{ __('msg.Enter Question')}}">{{$records->question}}</textarea>
                    </div>
                    <span class="err_question text-danger">@error('question') {{$message}} @enderror</span>
                </div>
                <div class="col-md-12 mt-4">
                    <label for="answer" class="form-label">{{__('msg.Answer')}}</label>
                    <div class="input-group">
                        <textarea id="answer" name="answer" rows="10" placeholder="{{ __('msg.Enter Answer')}}" data-sample="2" data-sample-short>{{$records->answer}}</textarea>
                    </div>
                    <span class="err_answer text-danger">@error('answer') {{$message}} @enderror</span>
                </div>
                <div class="col-md-12">
                    <label for="save" class="form-label">&nbsp;</label>
                    <div class="input-group">
                        <a href="{{route('faqs')}}" id="save" class="btn btn-qysmat-light text-uppercase mr-2">{{ __('Cancel')}}</a>
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

        $("#add_faq").on('submit', function(e) {
            e.preventDefault();
            let valid = true;
            let form = $(this).get(0);
            let question = $("#question").val();
            let err_question = "{{__('msg.Question is Required')}}";
            let answer = $("#answer").val();
            let err_answer = "{{__('msg.Answer is Required')}}";

                if (question.length === 0) {
                    $(".err_question").text(err_question);
                    $('#question').addClass('is-invalid');
                    valid = false;
                } else {
                    $(".err_question").text('');
                    $('#question').addClass('is-valid');
                    $('#question').removeClass('is-invalid');
                }

                if (answer.length === 0) {
                    $(".err_answer").text(err_answer);
                    $('#answer').addClass('is-invalid');
                    valid = false;
                } else {
                    $(".err_answer").text('');
                    $('#answer').addClass('is-valid');
                    $('#answer').removeClass('is-invalid');
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
    CKEDITOR.replace('answer', {
        height: 200,
        width:1000,
    });
</script>
