<div class="col-12">
    <div class="card">
       <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <h4 class="text-muted">{{__('msg.Name')}}</h4><h5>{{$details->user_name}}</h5>
                    <h4 class="text-muted p-t-30 db">{{__('msg.User Type')}}</h4><h5>{{$details->user_type}}</h5>
                    <h4 class="text-muted p-t-30 db">{{__('msg.Paid By')}}</h4><h5>{{$details->paid_by}}</h5>
                    <h4 class="text-muted p-t-30 db">{{__('msg.Status')}}</h4><h5>{{$details->payment_status}}</h5>
                </div>
                <div class="col-6">
                    <h4 class="text-muted">{{__('msg.Paid Amount')}}</h4><h5>{{$details->paid_amount}}</h5>
                    <h4 class="text-muted p-t-30 db">{{__('msg.Curency')}}</h4><h5>{{$details->currency_code}}</h5>
                    <h4 class="text-muted p-t-30 db">{{__('msg.Subscription Type')}}</h4><h5>{{$details->subscription_type}}</h5>
                    <h4 class="text-muted p-t-30 db">{{__('msg.Date and Time')}}</h4><h5>{{date('d-m-Y',strtotime($details->transaction_datetime))}}<br>{{date('h:i:s A', strtotime($details->transaction_datetime))}}</h5>
                </div>
            </div>
       </div>
    </div>
</div>
