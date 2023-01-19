<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

<style>
    input[type=checkbox]{
    height: 0;
    width: 0;
    visibility: hidden;
    }

    label {
    cursor: pointer;
    text-indent: -9999px;
    width: 40px;
    height: 17px;
    background: rgb(145, 143, 143);
    /* display: block; */
    border-radius: 100px;
    position: relative;
    }

    label:after {
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

    label:active:after {
    width: 100px;
    }

    .card .card-header {
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }

    .card-price {
        font-size: 2.7rem;
    }

</style>

<div class="row justify-content-center">
    @foreach ($records as $value)
        <div class="col-4">
            <div class="card">
                <div class="card-header bg-qysmat">
                    <h4 class="card-title text-uppercase text-center">{{$value->subscription_type}}</h4>
                    <h6 class="card-price text-white text-center">{{ $value->id !=1 ? $value->currency : ''}}{{$value->price}}
                        @if ($value->id == 2)
                            <p style="font-size:14px;">per month</p>
                        @elseif ($value->id == 3)
                            <p style="font-size:14px;">per month per person</p>
                        @endif
                        <span class="term"></span>
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @foreach ($value->features as $feature)
                        <li class="list-group-item"><i class="fas fa-check mr-2 btn-sm"></i>{{$feature}}</li>
                        @endforeach
                        <li class="list-group-item"><i class="fas fa-check mr-2 btn-sm"></i><span>{{__('msg.Status')}} : {{$value->status}}</span>

                                <form action="{{route('changeSubscriptionStatus')}}" method="post" class="text-center">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$value->id}}">
                                    <input type="hidden" name="status" value="{{$value->status == 'Active' ? 'Inactive' : 'Active' }}">
                                    <button type="submit" data-status="{{$value->status == 'Active' ? 'Active' : 'Inactive'}}" data-id="{{$value->id}}" data-name="{{$value->subscription_type}}" class="btn block_confirm btn-sm"><input type="checkbox" id="switch" {{$value->status == 'Inactive' ? '' : 'checked'}} /><label for="switch">Toggle</label></button>
                                </form>

                        </li>
                    </ul>
                    <div class="d-grid mt-3">
                        <form action="{{route('updatePrice')}}" method="post" class="text-center">
                            @csrf
                            <input type="hidden" name="id" value="{{$value->id}}">
                            <button type="button" class="btn btn-qysmat my-2 radius-30" onclick="this.form.submit()">{{__('msg.Update Price')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
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

</script>
