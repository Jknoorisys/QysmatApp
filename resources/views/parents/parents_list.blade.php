<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

<style>
    input[type=checkbox]{
    height: 0;
    width: 0;
    visibility: hidden;
    }

    .qysmat-lable {
    cursor: pointer;
    text-indent: -9999px;
    width: 40px;
    height: 17px;
    background: rgb(145, 143, 143);
    /* display: block; */
    border-radius: 100px;
    position: relative;
    }

    .qysmat-lable:after {
    content: '';
    position: absolute;
    top: 2.2px;
    left: 4px;
    width: 12px;
    height: 12px;
    background: #fff;
    border-radius: 100px;
    transition: 0.2s;
    }

    input:checked + label {
        background: linear-gradient(180deg, #AF9A7F 0%, #A28D69 50%, #8F7C5C 100%);
    }

    input:checked + label:after {
    left: calc(100% - 5px);
    transform: translateX(-100%);
    }

    .qysmat-lable:active:after {
    width: 100px;
    }

    /* div.dataTables_wrapper div.dataTables_filter label{
        display: none
    } */
</style>

<div class="col-12">
    <div class="card">
       <div class="card-body">
            <div class="table-responsive">
                <table id="zero_config" class="table table-sm table-hover">
                    {{-- <form class="m-t-30" action="{{route('parents')}}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-8">

                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <input type="search" name="search" class="form-control" value="{{$search}}" placeholder="{{__('Search by Name or Email')}}">
                                </div>
                            </div>
                        </div>
                    </form> --}}
                    <thead>
                        <tr>
                            <th class="text-center">{{ __('msg.Name')}}</th>
                            <th class="text-center">{{ __('msg.Email')}}</th>
                            <th class="text-center">{{ __('msg.Status')}}</th>
                            <th class="text-center">{{ __('msg.Verified')}}</th>
                            <th class="text-center">{{ __('msg.Actions')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!$records->isEmpty())
                            @foreach ($records as $value)
                                <tr>
                                    <td class="text-center">{{$value->name.' '.$value->lname}}</td>
                                    <td class="text-center">{{$value->email}}</td>
                                    <td class="text-center">{{$value->status}}</td>
                                    <td class="text-center"><span class="label label-{{ $value->is_verified == 'verified' ? 'success': 'danger'}}">{{ $value->is_verified == 'verified' ? 'Verified': 'Not Verified'}}</span></td>
                                    <td class="text-center">

                                        <div class="row justify-content-center">
                                            {{-- <div class="col-3"></div> --}}
                                            <div class="col-3 mt-1">
                                                <form action="{{route('changeParentStatus')}}" method="post">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{$value->id}}">
                                                    <input type="hidden" name="status" value="{{$value->status == 'Unblocked' ? 'Blocked' : 'Unblocked' }}">
                                                    <button type="submit" data-status="{{$value->status == 'Unblocked' ? 'Unblocked' : 'Blocked'}}" data-id="{{$value->id}}" data-name="{{$value->name}}" class="btn block_confirm btn-sm"><input type="checkbox" id="switch" {{$value->status == 'Unblocked' ? 'checked' : ''}} /><label class="qysmat-lable" for="switch">Toggle</label></button>
                                                </form>
                                            </div>
                                            <div class="col-3">
                                                <form action="{{route('viewParent')}}" method="post">
                                                    @csrf
                                                    <input type="hidden" value="{{$value->id}}" id="id" name="id" />
                                                    <button type="submit" class="btn btn-lg text-qysmat" onclick="this.form.submit()"> <i class="fas fa-eye"></i> </button>
                                                </form>
                                            </div>
                                            <div class="col-3">
                                                <form action="{{route('deleteParent')}}" method="post">
                                                    @csrf
                                                    <input type="hidden" value="{{$value->id}}" id="id" name="id" />
                                                    <button type="submit" class="btn btn-lg text-qysmat show_confirm" data-name="{{$value->name}}" data-id="{{$value->id}}" data-toggle="tooltip" title='Delete'> <i class="fas fa-trash"></i> </button>
                                                </form>
                                            </div>
                                            {{-- <div class="col-3"></div> --}}
                                        </div>
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
