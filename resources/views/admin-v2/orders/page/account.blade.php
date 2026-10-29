<div class="card card-flush py-4 flex-row-fluid overflow-hidden">
    <div class="card-header">
        <div class="card-title">
            <h2>شماره سفارش {{$Order->id}}</h2>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="row">
            <div class="col-lg-4">
                <div class="d-flex align-items-center bg-light-warning rounded p-5 mb-7">
                                <span class="svg-icon svg-icon-warning me-5"><i
                                            class="fa-regular fa-typewriter"></i></span>
                    <div class="flex-grow-1 me-2">
                        <label class="fw-bolder text-gray-800 text-hover-primary fs-6">نوع سفارش</label>
                        <span class="text-muted fw-bold d-block">شماره مجازی</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="d-flex align-items-center bg-light-success rounded p-5 mb-7">
                                <span class="svg-icon svg-icon-warning me-5"><i
                                            class="fa-regular fa-typewriter"></i></span>
                    <div class="flex-grow-1 me-2">
                        <label class="fw-bolder text-gray-800 text-hover-primary fs-6">مبلغ سفارش</label>
                        <span class="text-muted fw-bold d-block">{{number_format($Order->price)}} تومان </span>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="d-flex align-items-center bg-light-danger rounded p-5 mb-7">
                                <span class="svg-icon svg-icon-warning me-5"><i
                                            class="fa-regular fa-typewriter"></i></span>
                    <div class="flex-grow-1 me-2">
                        <label class="fw-bolder text-gray-800 text-hover-primary fs-6">وضعیت سفارش</label>
                        <span class="text-muted fw-bold d-block">{{AdminOrderStatus($Order->status)}}</span>
                    </div>
                </div>
            </div>
            <hr>
            @foreach($OrderItems as $items)
                @php $Details = json_decode($items->detail,true);@endphp
                <div class="col-lg-6">
                    <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7"><span
                                class="svg-icon svg-icon-warning me-5"><i class="fa-regular fa-typewriter"></i></span>
                        <div class="flex-grow-1 me-2">
                            <label class="fw-bolder text-gray-800 text-hover-primary fs-6">نام محصول</label>
                            <span class="text-muted fw-bold d-block">{{$Details['plan']['plan_name']}}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                                    <span class="svg-icon svg-icon-warning me-5"><i
                                                class="fa-regular fa-typewriter"></i></span>
                        <div class="flex-grow-1 me-2">
                            <label class="fw-bolder text-gray-800 text-hover-primary fs-6">شناسه محصول:</label>
                            <span class="text-muted fw-bold d-block">{{$Details['plan']['plan_name']}}</span>
                        </div>
                    </div>
                </div>
                @php
                    unset($Details['plan']);
                @endphp
                @foreach($Details as $detail => $item)
                    <div class="col-lg-6">
                        <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                                    <span class="svg-icon svg-icon-warning me-5"><i
                                                class="fa-regular fa-typewriter"></i></span>
                            <div class="flex-grow-1 me-2">
                                <label class="fw-bolder text-gray-800 text-hover-primary fs-6">{{$item['filed_type']}}</label>
                                <span class="text-muted fw-bold d-block">{{$item['filed_value']}}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>
</div>
