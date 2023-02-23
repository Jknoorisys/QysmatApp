<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

<div class="col-12">
    <div class="card">
       <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th class="text-center">{{ __('msg.Name')}}</th>
                            <th class="text-center">{{ __('msg.User Type')}}</th>
                            <th class="text-center">{{ __('msg.Title')}}</th>
                            <th class="text-center">{{ __('msg.Description')}}</th>
                            <th class="text-center">{{ __('msg.Date')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!$records->isEmpty())
                            @foreach ($records as $value)
                                <tr>
                                    <td class="text-center">{{$value->user_name}}</td>
                                    <td class="text-center">{{$value->user_type}}</td>
                                    <td class="text-center">{{$value->title}}</td>
                                    <td class="text-center">{{$value->description}}</td>
                                    <td class="text-center">{{date('d M Y',strtotime($value->created_at))}}</td>
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
