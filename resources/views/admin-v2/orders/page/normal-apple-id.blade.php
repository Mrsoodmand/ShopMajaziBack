@php $AppleIds = \App\Models\AppleId::where('order_id',$Order->id)->where('status',1)->get(); $Decrypt = new \App\lib\PhpEncrypt(); @endphp

<div class="card card-flush py-4 flex-row-fluid overflow-hidden">
    <div class="card-header">
        <div class="card-title">
            <h2>شماره سفارش {{$Order->id}}</h2>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="d-flex align-items-center bg-light-warning rounded p-5 mb-7">
                                <span class="svg-icon svg-icon-warning me-5"><i
                                        class="fa-regular fa-typewriter"></i></span>
                    <div class="flex-grow-1 me-2">
                        <label class="fw-bolder text-gray-800 text-hover-primary fs-6">نوع سفارش</label>
                        <span class="text-muted fw-bold d-block">سفارش اپل آیدی</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="d-flex align-items-center bg-light-success rounded p-5 mb-7">
                                <span class="svg-icon svg-icon-warning me-5"><i
                                        class="fa-regular fa-typewriter"></i></span>
                    <div class="flex-grow-1 me-2">
                        <label class="fw-bolder text-gray-800 text-hover-primary fs-6">مبلغ سفارش</label>
                        <span class="text-muted fw-bold d-block">{{number_format($Order->price)}} تومان </span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="d-flex align-items-center bg-light-danger rounded p-5 mb-7">
                                <span class="svg-icon svg-icon-warning me-5"><i
                                        class="fa-regular fa-typewriter"></i></span>
                    <div class="flex-grow-1 me-2">
                        <label class="fw-bolder text-gray-800 text-hover-primary fs-6">وضعیت سفارش</label>
                        <span class="text-muted fw-bold d-block">{{AdminOrderStatus($Order->status)}}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="d-flex align-items-center bg-light-primary rounded p-5 mb-7">
                                <span class="svg-icon svg-icon-warning me-5"><i
                                        class="fa-regular fa-typewriter"></i></span>
                    <div class="flex-grow-1 me-2">
                        <label class="fw-bolder text-gray-800 text-hover-primary fs-6">تعداد</label>
                        <span class="text-muted fw-bold d-block">{{$OrderItems->first()->detail}} عدد </span>
                    </div>
                </div>
            </div>
            <hr>
            @if(count($AppleIds) != 0)
            @foreach($AppleIds as $items)
                @php $Details = json_decode($Decrypt->decrypt($items->serial,$items->code),true); @endphp
                <div class="col-12">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                                    <span class="svg-icon svg-icon-warning me-5"><i
                                            class="fa-regular fa-typewriter"></i></span>
                                <div class="flex-grow-1 me-2">
                                    <label class="fw-bolder text-gray-800 text-hover-primary fs-6">اپل آیدی</label>
                                    <span class="text-muted fw-bold d-block">{{$Details['appleid']}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                                    <span class="svg-icon svg-icon-warning me-5"><i
                                            class="fa-regular fa-typewriter"></i></span>
                                <div class="flex-grow-1 me-2">
                                    <label class="fw-bolder text-gray-800 text-hover-primary fs-6">تاریخ تولد</label>
                                    <span class="text-muted fw-bold d-block">{{$Details['brithday']}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                                <span class="svg-icon svg-icon-warning me-5"><i class="fa-regular fa-typewriter"></i></span>
                                <div class="flex-grow-1 me-2">
                                    <label class="fw-bolder text-gray-800 text-hover-primary fs-6">کلمه عبور</label>
                                    <span class="text-muted fw-bold d-block">{{$Details['password']}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                                <span class="svg-icon svg-icon-warning me-5"><i class="fa-regular fa-typewriter"></i></span>
                                <div class="flex-grow-1 me-2">
                                    <label class="fw-bolder text-gray-800 text-hover-primary fs-6">سوال امنیتی 1:</label>
                                    <span class="text-muted fw-bold d-block">{{$Details['q1']}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                                    <span class="svg-icon svg-icon-warning me-5"><i
                                            class="fa-regular fa-typewriter"></i></span>
                                <div class="flex-grow-1 me-2">
                                    <label class="fw-bolder text-gray-800 text-hover-primary fs-6">سوال امنیتی 2:</label>
                                    <span class="text-muted fw-bold d-block">{{$Details['q2']}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                                    <span class="svg-icon svg-icon-warning me-5"><i
                                            class="fa-regular fa-typewriter"></i></span>
                                <div class="flex-grow-1 me-2">
                                    <label class="fw-bolder text-gray-800 text-hover-primary fs-6">سوال امنیتی 3:</label>
                                    <span class="text-muted fw-bold d-block">{{$Details['q3']}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                </div>
            @endforeach
            @else
                @if($Order->status == 'pending')
                    <div class="col-lg-12">
                        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-9 p-6">
                        <span class="svg-icon svg-icon-2tx svg-icon-primary me-4">
														<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
															<path opacity="0.3" d="M22 19V17C22 16.4 21.6 16 21 16H8V3C8 2.4 7.6 2 7 2H5C4.4 2 4 2.4 4 3V19C4 19.6 4.4 20 5 20H21C21.6 20 22 19.6 22 19Z" fill="currentColor"></path>
															<path d="M20 5V21C20 21.6 19.6 22 19 22H17C16.4 22 16 21.6 16 21V8H8V4H19C19.6 4 20 4.4 20 5ZM3 8H4V4H3C2.4 4 2 4.4 2 5V7C2 7.6 2.4 8 3 8Z" fill="currentColor"></path>
														</svg>
													</span>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-bold">
                                    <div class="fs-6 text-gray-700">در انتظار دریافت آی پی</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($Order->stauts == 'failed')
                    <div class="col-lg-12">
                        <div class="notice d-flex bg-light-danger rounded border-danger border border-dashed mb-9 p-6">
                        <span class="svg-icon svg-icon-2tx svg-icon-danger me-4">
														<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
															<path opacity="0.3" d="M22 19V17C22 16.4 21.6 16 21 16H8V3C8 2.4 7.6 2 7 2H5C4.4 2 4 2.4 4 3V19C4 19.6 4.4 20 5 20H21C21.6 20 22 19.6 22 19Z" fill="currentColor"></path>
															<path d="M20 5V21C20 21.6 19.6 22 19 22H17C16.4 22 16 21.6 16 21V8H8V4H19C19.6 4 20 4.4 20 5ZM3 8H4V4H3C2.4 4 2 4.4 2 5V7C2 7.6 2.4 8 3 8Z" fill="currentColor"></path>
														</svg>
													</span>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-bold">
                                    <div class="fs-6 text-gray-700">سفارش لغو شده است.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @elseif($Order->status == 'waiting to pay')
                    <div class="col-lg-12">
                        <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed mb-9 p-6">
                        <span class="svg-icon svg-icon-2tx svg-icon-warning me-4">
														<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
															<path opacity="0.3" d="M22 19V17C22 16.4 21.6 16 21 16H8V3C8 2.4 7.6 2 7 2H5C4.4 2 4 2.4 4 3V19C4 19.6 4.4 20 5 20H21C21.6 20 22 19.6 22 19Z" fill="currentColor"></path>
															<path d="M20 5V21C20 21.6 19.6 22 19 22H17C16.4 22 16 21.6 16 21V8H8V4H19C19.6 4 20 4.4 20 5ZM3 8H4V4H3C2.4 4 2 4.4 2 5V7C2 7.6 2.4 8 3 8Z" fill="currentColor"></path>
														</svg>
													</span>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-bold">
                                    <div class="fs-6 text-gray-700">مراحل پرداخت این سفارش تکمیل نشده است.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

