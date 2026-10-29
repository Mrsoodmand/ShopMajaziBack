@php $Ip = \App\Models\Ip::where('order_id',$Order->id)->first(); @endphp
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
                        <span class="text-muted fw-bold d-block">سفارش آی پی ثابت</span>
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
                <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                                <span class="svg-icon svg-icon-warning me-5"><i
                                            class="fa-regular fa-typewriter"></i></span>
                    <div class="flex-grow-1 me-2">
                        <label class="fw-bolder text-gray-800 text-hover-primary fs-6">حجم اضافه</label>
                        <span class="text-muted fw-bold d-block">{{__('order.Volume-Gb.'.$OrderItems->first()->detail)}} گیگ </span>
                    </div>
                </div>
            </div>
            <hr>

            @if(!is_null($Ip))
                @if(!is_null($Ip) && $Order->status == 'failed')
                    <div class="col-lg-12">
                        <div class="notice d-flex bg-light-primary rounded border-danger border border-dashed mb-9 p-6">
                        <span class="svg-icon svg-icon-2tx svg-icon-primary me-4">
														<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                             viewBox="0 0 24 24" fill="none">
															<path opacity="0.3"
                                                                  d="M22 19V17C22 16.4 21.6 16 21 16H8V3C8 2.4 7.6 2 7 2H5C4.4 2 4 2.4 4 3V19C4 19.6 4.4 20 5 20H21C21.6 20 22 19.6 22 19Z"
                                                                  fill="currentColor"></path>
															<path d="M20 5V21C20 21.6 19.6 22 19 22H17C16.4 22 16 21.6 16 21V8H8V4H19C19.6 4 20 4.4 20 5ZM3 8H4V4H3C2.4 4 2 4.4 2 5V7C2 7.6 2.4 8 3 8Z"
                                                                  fill="currentColor"></path>
														</svg>
													</span>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-bold">
                                    <div class="fs-6 text-gray-700">سفارش لغو شده است.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                @php
                    $Data = json_decode($Ip->detail,true);
                    $Country = \App\Models\Category::find($Ip->category_id);
                    $Product = \App\Models\Product::find($Ip->product_id);
                    $ProductDetail = json_decode($Product->detail,true);
                    $Vol = __('order.Volume-Gb.'.$OrderItems->first()->detail);
                    $Vol = $Vol + $ProductDetail['volume'];

                    $Data = json_decode($Ip->detail,true);
                    $Domain = \App\Models\Ip::find($Ip->parent_id);
                    $ServerIp = json_decode($Domain->detail,true);
                    $IpDetail = json_decode($Ip->detail,true);
                    $GetData['ip_address'] = $ServerIp['ip_api_domain'];
                    $GetData['ip_port'] = $ServerIp['ip_port'];
                    $GetData['Session'] = $ServerIp['session'];
                    $GetData['uid'] = $Ip->UID;
                    $GetData['name'] = $IpDetail['user_remark'];
                    $GetData['ip_api_domain'] = $ServerIp['ip_api_domain'];
                    $GetData['Data'] = $ServerIp;
                    $UsedVolume = getClientTraffics($GetData);
                     if ($UsedVolume['success'] == false){
                         $UsedVolume = 00;
                     }else{
                         $UsedVolume = $UsedVolume['hajm'];
                     }
                @endphp
                <div class="col-12">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                                    <span class="svg-icon svg-icon-warning me-5"><i
                                                class="fa-regular fa-typewriter"></i></span>
                                <div class="flex-grow-1 me-2">
                                    <label class="fw-bolder text-gray-800 text-hover-primary fs-6">کشور</label>
                                    <span class="text-muted fw-bold d-block">{{$Country->name}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                                    <span class="svg-icon svg-icon-warning me-5"><i
                                                class="fa-regular fa-typewriter"></i></span>
                                <div class="flex-grow-1 me-2">
                                    <label class="fw-bolder text-gray-800 text-hover-primary fs-6">مجموع حجم</label>
                                    <span class="text-muted fw-bold d-block">{{$Vol}} گیگ </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                                <span class="svg-icon svg-icon-warning me-5"><i
                                            class="fa-regular fa-typewriter"></i></span>
                                <div class="flex-grow-1 me-2">
                                    <label class="fw-bolder text-gray-800 text-hover-primary fs-6">مصرف شده</label>
                                    <span class="text-muted fw-bold d-block">
                                        @if($UsedVolume != 00)
                                            {{number_format($UsedVolume)}}  مگابایت
                                        @else
                                            اطلاعاتی یافت نشد.
                                        @endif
                                        </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                                <span class="svg-icon svg-icon-warning me-5"><i
                                            class="fa-regular fa-typewriter"></i></span>
                                <div class="flex-grow-1 me-2">
                                    <label class="fw-bolder text-gray-800 text-hover-primary fs-6">Remark</label>
                                    <span class="text-muted fw-bold d-block">{{$Data['user_remark']}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                                    <span class="svg-icon svg-icon-warning me-5"><i
                                                class="fa-regular fa-typewriter"></i></span>
                                <div class="flex-grow-1 me-2">
                                    <label class="fw-bolder text-gray-800 text-hover-primary fs-6">Port</label>
                                    <span class="text-muted fw-bold d-block">{{$Data['user_port']}}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                                    <span class="svg-icon svg-icon-warning me-5"><i
                                                class="fa-regular fa-typewriter"></i></span>
                                <div class="flex-grow-1 me-2">
                                    <label class="fw-bolder text-gray-800 text-hover-primary fs-6">کد</label>
                                    <span class="text-muted fw-bold d-block" onclick="myFunction('{{$Data['user_code']}}')" style="cursor: pointer">کپی کردن کد</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                </div>
            @else
                @if($Order->status == 'pending')
                    <div class="col-lg-12">
                        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-9 p-6">
                        <span class="svg-icon svg-icon-2tx svg-icon-primary me-4">
														<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                             viewBox="0 0 24 24" fill="none">
															<path opacity="0.3"
                                                                  d="M22 19V17C22 16.4 21.6 16 21 16H8V3C8 2.4 7.6 2 7 2H5C4.4 2 4 2.4 4 3V19C4 19.6 4.4 20 5 20H21C21.6 20 22 19.6 22 19Z"
                                                                  fill="currentColor"></path>
															<path d="M20 5V21C20 21.6 19.6 22 19 22H17C16.4 22 16 21.6 16 21V8H8V4H19C19.6 4 20 4.4 20 5ZM3 8H4V4H3C2.4 4 2 4.4 2 5V7C2 7.6 2.4 8 3 8Z"
                                                                  fill="currentColor"></path>
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
														<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                             viewBox="0 0 24 24" fill="none">
															<path opacity="0.3"
                                                                  d="M22 19V17C22 16.4 21.6 16 21 16H8V3C8 2.4 7.6 2 7 2H5C4.4 2 4 2.4 4 3V19C4 19.6 4.4 20 5 20H21C21.6 20 22 19.6 22 19Z"
                                                                  fill="currentColor"></path>
															<path d="M20 5V21C20 21.6 19.6 22 19 22H17C16.4 22 16 21.6 16 21V8H8V4H19C19.6 4 20 4.4 20 5ZM3 8H4V4H3C2.4 4 2 4.4 2 5V7C2 7.6 2.4 8 3 8Z"
                                                                  fill="currentColor"></path>
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
														<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                             viewBox="0 0 24 24" fill="none">
															<path opacity="0.3"
                                                                  d="M22 19V17C22 16.4 21.6 16 21 16H8V3C8 2.4 7.6 2 7 2H5C4.4 2 4 2.4 4 3V19C4 19.6 4.4 20 5 20H21C21.6 20 22 19.6 22 19Z"
                                                                  fill="currentColor"></path>
															<path d="M20 5V21C20 21.6 19.6 22 19 22H17C16.4 22 16 21.6 16 21V8H8V4H19C19.6 4 20 4.4 20 5ZM3 8H4V4H3C2.4 4 2 4.4 2 5V7C2 7.6 2.4 8 3 8Z"
                                                                  fill="currentColor"></path>
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

<script>
    function myFunction(id) {
        /* Copy the text inside the text field */
        navigator.clipboard.writeText(id);
        /* Alert the copied text */
        toastr.success("عملیات کپی با موفقیت انجام شد.", "اطلاعیه", {
            timeOut: 3000,
            closeButton: !0,
            debug: !1,
            newestOnTop: !0,
            progressBar: !0,
            positionClass: "toast-top-right",
            preventDuplicates: !0,
            onclick: null,
            showDuration: "300",
            hideDuration: "1000",
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut",
            tapToDismiss: !1
        })
    }
</script>
