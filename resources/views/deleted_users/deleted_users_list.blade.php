<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

<div class="col-12">
    <div class="card">
       <div class="card-body">
            <div class="table-responsive">
                <table id="zero_config" class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">{{ __('msg.No')}}</th>
                            <th class="text-center">{{ __('msg.Name')}}</th>
                            <th class="text-center">{{ __('msg.User Type')}}</th>
                            <th class="text-center">{{ __('msg.Reason')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!$records->isEmpty())
                            @foreach ($records as $value)
                                <tr>
                                    <td class="text-center">{{$loop->iteration}}</td>
                                    <td class="text-center">{{$value->user_name}}</td>
                                    <td class="text-center">{{$value->user_type}}</td>
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

<script>
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