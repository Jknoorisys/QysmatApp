<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

<div class="col-12">
    <div class="card">
       <div class="card-body">
            <form class="" action="{{route('transactions')}}" method="post">
                @csrf
                <div class="row">
                    <div class="col-4">
                        <label for="from_date" class="form-label">{{__('msg.From Date')}}</label>
                        <div class="form-group">
                            <input type="date" name="from_date" id="from_date" class="form-control" value="{{$from_date}}">
                        </div>
                    </div>
                    <div class="col-4">
                        <label for="to_date" class="form-label">{{__('msg.To Date')}}</label>
                        <div class="form-group">
                            <input type="date" name="to_date" id="to_date" class="form-control" value="{{$to_date}}">
                        </div>
                    </div>
                    <div class="col-2">
                        <label for="search" class="form-label">&nbsp;</label>
                        <div class="form-group">
                            <button type="submit" id="search" class="btn btn-qysmat btn-block">{{__('msg.Search by Date')}}</button>
                        </div>
                    </div>
                    <div class="col-2">
                        <label for="reset" class="form-label">&nbsp;</label>
                        <div class="form-group">
                            <a href="{{route('transactions')}}" id="reset" class="btn btn-qysmat-light btn-block">{{__('msg.Reset')}}</a>
                        </div>
                    </div>
                </div>
            </form>
            <hr />
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">{{ __('msg.Name')}}</th>
                            <th class="text-center">{{ __('msg.User Type')}}</th>
                            <th class="text-center">{{ __('msg.Status')}}</th>
                            <th class="text-center">{{ __('msg.Date')}}</th>
                            <th class="text-center">{{ __('msg.Actions')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!$records->isEmpty())
                            @foreach ($records as $value)
                                <tr>
                                    <td class="text-center">{{$value->user_name}}</td>
                                    <td class="text-center">{{$value->user_type}}</td>
                                    <td class="text-center">{{$value->payment_status}}</td>
                                    <td class="text-center">{{date('d-m-Y',strtotime($value->transaction_datetime))}}<br>{{date('h:i:s A', strtotime($value->transaction_datetime))}}</td>
                                    <td class="text-center bt-switch">
                                        <form action="{{route('viewTransaction')}}" method="post">
                                            @csrf
                                            <input type="hidden" value="{{$value->id}}" id="id" name="id" />
                                            <button type="submit" class="btn btn-lg text-qysmat" onclick="this.form.submit()"> <i class="fas fa-eye"></i> </button>
                                        </form>
                                    </td>
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


<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
<script type="text/javascript">

     $('.show_confirm').click(function(event) {
        var form =  $(this).closest("form");
        var name = $(this).data("name");
        let id = $(this).data('id');
        event.preventDefault();
        swal({
            title: "{{__('msg.Are You Sure')}}",
            text: "{{__('msg.You want to Delete ')}}"+name+" ?",
            icon: "warning",
            buttons: ["{{__('msg.Cancel')}}", "{{__('msg.Yes')}}"],
            dangerMode: true,
        })
        .then((willDelete) => {
        if (willDelete) {
            form.submit();
        }
        });
    });

    $('.block_confirm').click(function(event) {
        var form =  $(this).closest("form");
        var name = $(this).data("name");
        let status = $(this).data('status');
        let id = $(this).data('id');
        event.preventDefault();
        swal({
            title: "{{__('msg.Are You Sure')}}",
            text: (status == 'Unblocked') ? "{{__('msg.You want to Block ')}}"+name+" ?" : "{{__('msg.You want to Unblock ')}}"+name+" ?",
            icon: "warning",
            buttons: ["{{__('msg.Cancel')}}", "{{__('msg.Yes')}}"],
            dangerMode: true,
        })
        .then((willDelete) => {
        if (willDelete) {
            form.submit();
        }
        });
    });

</script>
