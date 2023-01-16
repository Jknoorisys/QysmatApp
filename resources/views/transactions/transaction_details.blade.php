<div class="col-12">
    <div class="card">
       <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <h4 class="text-muted">{{__('msg.Name')}}</h4><h5>{{$details->user_name}}</h5>
                    <h4 class="text-muted p-t-30 db">{{__('msg.User Type')}}</h4><h5>{{$details->user_type}}</h5>
                    <h4 class="text-muted p-t-30 db">{{__('msg.Subscription Started at')}}</h4><h5>{{date('d M Y',strtotime($details->plan_period_start))}}<br>{{date('h:i:s A', strtotime($details->plan_period_start))}}</h5>
                    <h4 class="text-muted p-t-30 db">{{__('msg.Subscription End at')}}</h4><h5>{{date('d M Y',strtotime($details->plan_period_end))}}<br>{{date('h:i:s A', strtotime($details->plan_period_end))}}</h5>
                </div>
                <div class="col-6">
                    <h4 class="text-muted">{{__('msg.Paid Amount')}}</h4><h5>{{($details->plan_amount)/100}}</h5>
                    <h4 class="text-muted p-t-30 db">{{__('msg.Curency')}}</h4><h5>{{strtoupper($details->plan_amount_currency)}}</h5>
                    <h4 class="text-muted p-t-30 db">{{__('msg.Subscription Type')}}</h4><h5>{{$details->subscription_type}}</h5>
                    <h4 class="text-muted p-t-30 db">{{__('msg.Status')}}</h4><h5>{{$details->status}}</h5>
                </div>
            </div>
       </div>
    </div>
</div>
