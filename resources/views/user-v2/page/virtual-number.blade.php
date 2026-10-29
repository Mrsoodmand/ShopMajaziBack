<div class="card border-0 shadow-lg mt-3">
    @foreach($OrderItems as $items)
        @php
            $Details = json_decode($items->detail,true);
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
                                <h6 class="media-tab-title fs-md text-nowrap mb-0">{{$items->VirtualNumber->faName}}</h6>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item">
                    <div class="nav-link completed">
                        <div class="d-flex align-items-center">
                            <div class="ps-3">
                                <div class="media-tab-subtitle text-muted fs-sm mb-1">نام کشور</div>
                                <h6 class="media-tab-title fs-md text-nowrap mb-0">{{$items->VirtualNumber->countryName}}</h6>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
            <ul class="nav nav-tabs media-tabs nav-justified">
                <li class="nav-item">
                    <div class="nav-link completed">
                        <div class="d-flex align-items-center">
                            <div class="ps-3">
                                <div class="media-tab-subtitle text-muted fs-sm mb-1">شماره دریافت شده</div>
                                <h6 class="media-tab-title fs-md text-nowrap mb-0">{{!is_null($Details) && $Details['number'] != null ? $Details['number'] : 'شماره دریافت نشده است.'}}</h6>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item">
                    <div class="nav-link completed">
                        <div class="d-flex align-items-center">
                            <div class="ps-3">
                                <div class="media-tab-subtitle text-muted fs-sm mb-1">پیامک</div>
                                <h6 class="media-tab-title fs-md text-nowrap mb-0">{{!is_null($Details) && $Details['sms'] != null ? $Details['sms'] : 'پیامک دریافت نشده است.'}}</h6>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    @endforeach
</div>
