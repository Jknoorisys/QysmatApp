<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

<div class="col-12">
    <div class="card">
       <div class="card-body">
        <!-- <div class="row">
            <div class="col-10"></div>
            <div class="col-2"><button type="button" data-toggle="modal" data-target="#verticalcenter" class="btn btn-qysmat mb-2">{{ __('msg.Send Notification')}}</button></div>
        </div> -->
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">{{ __('msg.Name')}}</th>
                            <th class="text-center">{{ __('msg.User Type')}}</th>
                            <th class="text-center">{{ __('msg.Email')}}</th>
                            <th class="text-center">{{ __('msg.Title')}}</th>
                            <th class="text-center">{{ __('msg.Message')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!$records->isEmpty())
                            @foreach ($records as $value)
                                <tr>
                                    <td class="text-center">{{$value->data['name']}}</td>
                                    <td class="text-center">{{$value->data['user_type']}}</td>
                                    <td class="text-center">{{$value->data['email']}}</td>
                                    <td class="text-center">{{$value->data['title']}}</td>
                                    <td class="text-center">{{$value->data['msg']}}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr class="text-center">
                                <td colspan="9"><h4>{{ __('msg.No Data Found')}}</h4></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                {!!$records->withQueryString()->links('pagination::bootstrap-5')!!}
            </div>
       </div>
    </div>
</div>

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabel1">{{ __('msg.Send Notification')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('send-notification') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="message-text" class="control-label">{{ __('msg.Message') }}:</label>
                        <textarea class="form-control" name="message" id="message-text1"></textarea>
                    </div>
               
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">{{ __('msg.Send') }}</button> 
            </form>
            </div>
        </div>
    </div>
</div>

<div id="verticalcenter" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="vcenter" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="vcenter">{{ __('msg.Send Notification')}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
            <form action="{{ route('send-notification') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="message-text" class="control-label">{{ __('msg.Message') }}:</label>
                        <textarea class="form-control" rows="5" name="message" id="message-text1"></textarea>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-qysmat-light" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-qysmat">{{ __('msg.Send') }}</button> 
            </form>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

