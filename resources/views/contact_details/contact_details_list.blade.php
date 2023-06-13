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
        <div class="row">
            <div class="col-10"></div>
            {{-- <div class="col-2"><a href="{{route('addContact')}}" class="btn btn-qysmat mb-2">{{ __('Add Contact Details')}}</a></div> --}}
        </div>
            <div class="table-responsive">
                <table id="zero_config" class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">{{ __('msg.No')}}</th>
                            <th class="text-center">{{ __('msg.Contact Type')}}</th>
                            {{-- <th class="text-center">{{ __('msg.Details')}}</th> --}}
                            <th class="text-center">{{ __('msg.Status')}}</th>
                            <th class="text-center">{{ __('msg.Actions')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!$records->isEmpty())
                            @foreach ($records as $value)
                                <tr>
                                    <td class="text-center">{{$loop->iteration}}</td>
                                    <td class="text-center">{{$value->contact_type}}</td>
                                    {{-- <td class="text-center">{{$value->details}}</td> --}}
                                    <td class="text-center">{{$value->status}}</td>
                                    <td class="text-center bt-switch">

                                        <div class="row justify-content-center">
                                            <div class="col-2"></div>
                                            <div class="col-3 mt-1">
                                                <form action="{{route('changeContactStatus')}}" method="post" class="text-center">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{$value->id}}">
                                                    <input type="hidden" name="status" value="{{$value->status == 'Active' ? 'Inactive' : 'Active' }}">
                                                    <button type="submit" data-status="{{$value->status == 'Active' ? 'Active' : 'Inactive'}}" data-id="{{$value->id}}" data-name="{{$value->contact_type}}" class="btn block_confirm btn-sm"><input type="checkbox" id="switch" {{$value->status == 'Inactive' ? '' : 'checked'}} /><label class="qysmat-lable" for="switch">Toggle</label></button>
                                                </form>
                                            </div>
                                            <div class="col-2">
                                                <form action="{{route('updateContact')}}" method="post">
                                                    @csrf
                                                    <input type="hidden" value="{{$value->id}}" id="id" name="id" />
                                                    <button type="submit" class="btn btn-lg text-qysmat" onclick="this.form.submit()" data-toggle="tooltip" title='Edit'> <i class="fas fa-edit"></i> </button>
                                                </form>
                                            </div>
                                            {{-- <div class="col-2">
                                                <form action="{{route('deleteContact')}}" method="post">
                                                    @csrf
                                                    <input type="hidden" value="{{$value->id}}" id="id" name="id" />
                                                    <button type="submit" class="btn btn-lg text-qysmat show_confirm" data-name="{{$value->contact_type}}" data-id="{{$value->id}}" data-toggle="tooltip" title='Delete'> <i class="fas fa-trash"></i> </button>
                                                </form>
                                            </div> --}}
                                            <div class="col-3"></div>
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
                {{-- {!!$records->withQueryString()->links('pagination::bootstrap-5')!!} --}}
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
            text: (status == 'Inactive') ? "{{__('msg.You want to Activate ')}}"+name+" ?" : "{{__('msg.You want to Inactivate ')}}"+name+" ?",
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

    $(document).ready(function() {
    // Initialize DataTable
    var table = $('#zero_config').DataTable();

    // Set custom placeholder for search input
    var placeholderText = '{{ trans("msg.Search here") }}';

    // Find the search input element and set the new placeholder
    var searchInput = $('div.dataTables_wrapper input[type="search"]');
    searchInput.attr('placeholder', placeholderText);
    });
</script>
