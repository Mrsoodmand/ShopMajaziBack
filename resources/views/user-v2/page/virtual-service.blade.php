 <div class="card border-0 shadow-lg mt-3">
    @php
        $Details = json_decode($OrderItems->detail,true);
    @endphp
    <div class="card-header">
        <h4>جزئیات سفارش:</h4>
    </div>
    <div class="card-body pb-2">
        <ul class="nav nav-tabs media-tabs nav-justified">
            <li class="nav-item">
                <div class="nav-link completed">
                    <div class="d-flex align-items-center">
                        <div class="ps-3">
                            <div class="media-tab-subtitle text-muted fs-sm mb-1">نام سرویس:</div>
                            <h6 class="media-tab-title fs-md text-nowrap mb-0">{{$Product->faName}}</h6>
                        </div>
                    </div>
                </div>
            </li>
            <li class="nav-item">
                <div class="nav-link completed">
                    <div class="d-flex align-items-center">
                        <div class="ps-3">
                            <div class="media-tab-subtitle text-muted fs-sm mb-1">نوع پلن</div>
                            <h6 class="media-tab-title fs-md text-nowrap mb-0">{{$Details['plan']['plan_name']}}</h6>
                        </div>
                    </div>
                </div>
            </li>
            <li class="nav-item">
                <div class="nav-link completed">
                    <div class="d-flex align-items-center">
                        <div class="ps-3">
                            <div class="media-tab-subtitle text-muted fs-sm mb-1">مبلغ سفارش</div>
                            <h6 class="media-tab-title fs-md text-nowrap mb-0">{{number_format($Order->price)}}
                                تومان </h6>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    <div class="card-body pb-2">
        <ul class="nav nav-tabs media-tabs nav-justified">
            @php
                unset($Details['plan']);
            @endphp
            @foreach($Details as $key => $item)
                <li class="nav-item">
                    <div class="nav-link completed">
                        <div class="d-flex align-items-center">
                            <div class="ps-3">
                                <div class="media-tab-subtitle text-muted fs-sm mb-1">{{$item['filed_type']}}</div>
                                <h6 class="media-tab-title fs-md text-nowrap mb-0">{{$item['filed_value']}}</h6>
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
