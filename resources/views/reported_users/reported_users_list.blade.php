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
                    <thead>
                        <tr>
                            <th class="text-center">{{ __('msg.Name')}}</th>
                            <th class="text-center">{{ __('msg.User Type')}}</th>
                            <th class="text-center">{{ __('msg.Reason')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!$records->isEmpty())
                            @foreach ($records as $value)
                                <tr>
                                    <td class="text-center">{{$value->reported_user_name}}</td>
                                    <td class="text-center">{{$value->reported_user_type}}</td>
                                    <td class="text-center">{{$value->reason}}</td>
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

<script>
    $(document).ready(function() {
    // Initialize DataTable
    var table = $('#zero_config').DataTable();

    // Set custom placeholder for search input
    var placeholderText = '{{ trans("msg.Search by Name") }}';

    // Find the search input element and set the new placeholder
    var searchInput = $('div.dataTables_wrapper input[type="search"]');
    searchInput.attr('placeholder', placeholderText);
    });
</script>