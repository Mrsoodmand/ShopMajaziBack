@php
    $Plan = \App\Models\Product::find($OrderItems->product_id);
    $PlanData = json_decode($Plan->detail,true);
    $Country = \App\Models\Category::find($Plan->category_id);
@endphp
@if($Order->status == 'failed' && !is_null($Ip))
    <div class="alert alert-danger d-flex fs-sm mb-4 mt-5" role="alert">
        <div class="alert-icon"><i class="ci-announcement"></i></div>
        <div>این سفارش لغو شده است.</div>
    </div>
@endif
<div class="card border-0 shadow-lg mt-3">
    <div class="card-header">
        <h4>جزئیات سفارش:</h4>
    </div>
    <div class="card-body pb-2">
        <div class="row">
            <div class="col-lg-4 col-6 mb-2">
                <div class="custom-nav completed">
                    <div class="d-flex align-items-center" style="position: relative">
                        <div>
                            <div class="media-tab-subtitle text-muted fs-sm mb-1">نام کشور:</div>
                            <h6 class="media-tab-title fs-md text-nowrap mb-0">{{$Country->name}}</h6>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-6 mb-2">
                <div class="custom-nav completed">
                    <div class="d-flex align-items-center" style="position: relative">
                        <div>
                            <div class="media-tab-subtitle text-muted fs-sm mb-1">پلن:</div>
                            <h6 class="media-tab-title fs-md text-nowrap mb-0">{{$Plan->faName}}
                                با {{$PlanData['volume']}} گیگ حجم </h6>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-6 mb-2">
                <div class="custom-nav completed">
                    <div class="d-flex align-items-center" style="position: relative">
                        <div>
                            <div class="media-tab-subtitle text-muted fs-sm mb-1">تاریخ اتمام اشتراک:</div>
                            <h6 class="media-tab-title fs-md text-nowrap mb-0"> {{!is_null($Ip) ? $jdf->jdate('d F Y',strtotime($Ip->expire_date)): '--'}}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@if(!is_null($Ip))
    @php
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
    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="border-0 rounded-3 shadow-lg mt-3" style="position: sticky;top: 6rem">
                <div class="card-header">
                    <h4>جزئیات آی پی:</h4>
                </div>
                <div class="card-body pb-2">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="mt-1">
                                <div class="media-tab-subtitle text-muted fs-sm mb-1">کد آی پی:</div>
                                {{--                                <textarea class="form-control" disabled id="" cols="30" rows="10">{{$Data['user_code']}}</textarea>--}}
                            </div>
                        </div>
                        <div class="col-lg-12 col-12 mb-3">
                            <button class="btn btn-info w-100" onclick="myFunction('{{$Data['user_code']}}')">کپی لینک
                            </button>
                        </div>
                        <div class="col-12">
                            <div class="mt-3">
                                <div class="media-tab-subtitle text-muted fs-sm mb-1"
                                     style="text-align: left;direction: ltr;"> QrCode:
                                </div>
                                <div class="product-thumb mt-3 mb-3">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?data={{urlencode($Data['user_code'])}}&size=200x20"
                                         style="margin: auto;display: block" alt="Qr Code">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="border-0 shadow-lg rounded-3 mt-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="">اطلاعات حجم مصرف شده:</h4>
                    </div>
                    <div class="card-body pb-2">
                        <div class="row">
                            <div class="col-lg-12 col-12">
                                <div class="bg-secondary rounded-3 p-4 mb-4 text-center">
                                    حجم مصرف شده:
                                    <br>
                                    @if($UsedVolume != 00)
                                        <span class="text-danger mb-2"> {{number_format($UsedVolume)}} مگابایت</span>
                                    @else
                                        <span class="text-danger mb-2"> اطلاعاتی یافت نشد.</span>
                                    @endif
                                    <br>
                                    حجم کل:{{number_format($Ip->volume * 1024)}} مگابایت
                                </div>
                            </div>
                            @if($Order->status != 'failed')
                                <div class="col-lg-12 col-12 mb-3">
                                    <button class="btn btn-success w-100" data-bs-toggle="modal"
                                            data-bs-target="#buy-volume">
                                        خرید حجم اضافه
                                    </button>
                                </div>
                                <div class="col-lg-12 col-12 mb-3">
                                    <button class="btn btn-warning w-100" data-bs-toggle="modal"
                                            data-bs-target="#reNew">
                                        تمدید آی پی
                                    </button>
                                </div>
                            @else
                                <div class="col-lg-12 col-12 mb-3">
                                    <button class="btn btn-success w-100" data-bs-toggle="modal"
                                            data-bs-target="#failedIp">
                                        خرید حجم اضافه
                                    </button>
                                </div>
                                <div class="col-lg-12 col-12 mb-3">
                                    <button class="btn btn-warning w-100" data-bs-toggle="modal"
                                            data-bs-target="#failedIp">
                                        تمدید آی پی
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-0 shadow-lg rounded-3 mt-3">
                <div class="card mt-2">
                    <div class="card-header mt-2">
                        <h4>آموزش اتصال به آی پی:</h4>
                    </div>
                    <div class="card-body pb-2">
                        <div class="row">
                            <div class="col-lg-12 col-12 mb-3">
                                <a href="{{secure_url('/help/ip-sabet/android.pdf')}}" target="_blank"
                                   class="btn btn-info w-100">
                                    آموزش اتصال برای اندروید
                                </a>
                            </div>
                            <div class="col-lg-12 col-12 mb-3">
                                <a href="{{secure_url('/help/ip-sabet/ios.pdf')}}" target="_blank" class="btn btn-info w-100">
                                    آموزش اتصال برای IOS
                                </a>
                            </div>
                            <div class="col-lg-12 col-12 mb-3">
                                <a href="{{secure_url('/help/ip-sabet/windows.pdf')}}" target="_blank"
                                   class="btn btn-info w-100">
                                    آموزش اتصال برای ویندوز
                                </a>
                            </div>
                            <div class="col-lg-12 col-12 mb-3">
                                <a href="{{secure_url('/help/ip-sabet/ios.pdf')}}" target="_blank" class="btn btn-info w-100">
                                    آموزش اتصال برای Mac
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="buy-volume" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-secondary">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item"><label class="nav-link fw-medium active" id="Login-tab-header"><i
                                        class="ci-unlocked me-2 mt-n1"></i>خرید حجم اضافه</label></li>
                    </ul>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{route('user.service-bank-payment')}}"
                      class="needs-validation tab-pane fade show active">
                    <div class="modal-body tab-content py-4">
                        <div class="mb-1">
                            <label class="form-label" for="login-mobile">حجم مورد نظر را انتخاب نمایید:</label>
                            <select name="product_id" class="form-select select-volume" id="volume">
                                @for($i = 1;$i <= 5;$i++)
                                    @php $Price = __('order.Volume-Gb.'.$i) * 1500 @endphp
                                    <option value="{{$i}}" data-price="{{$Price}}">@lang('order.Volume-Name.'.$i)
                                        - {{number_format($Price)}} تومان
                                    </option>
                                @endfor
                            </select>
                            <input type="hidden" name="ip_id" value="{{$Order->id}}">
                            <input type="hidden" name="type" value="7">
                        </div>
                        @csrf
                    </div>
                    <div class="modal-footer justify-content-center">
                        @php $Price = __('order.Volume-Gb.1') * 1500 @endphp
                        @if(CheckWallet() >= $Price)
                            <a id="second-btn" data-type="1" type="button"
                               class="volume-with-wallet btn btn-success btn-shadow d-block pe-3 buy-with-wallet"
                               data-id="1" style="width: 47%">پرداخت با کیف پول</a>
                        @else
                            <a id="second-btn" class="login-btn btn btn-info btn-shadow d-block pe-3"
                               style="width: 47%" type="submit">شارژ کیف پول</a>
                        @endif
                        <button id="btn-area" class="login-btn btn btn-primary btn-shadow d-block pe-3"
                                style="width: 47%" type="submit">پرداخت با درگاه
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="reNew" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-secondary">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item"><label class="nav-link fw-medium active" id="Login-tab-header"><i
                                        class="ci-unlocked me-2 mt-n1"></i>تمدید آی پی</label></li>
                    </ul>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{route('user.service-bank-payment')}}"
                      class="needs-validation tab-pane fade show active">
                    <div class="modal-body tab-content py-4">
                        <div class="mb-1">
                            <label class="form-label" for="login-mobile">پلن مورد نظر خود را انتخاب نمایید:</label>
                            <select name="plan_id" class="form-select select-renew-plan" id="reNew-plan-id">
                                @foreach($Products as $product)
                                    @php
                                        $Detail = json_decode($product->detail,true);
                                   if ($Ip->product_id == $product->id){
                                       $product_price = $Detail['dis'] == 0 ? $product->price : $Detail['dis'];
                                   }
                                    @endphp
                                    <option value="{{$product->id}}"
                                            data-price="{{$Detail['dis'] == 0 ? $product->price : $Detail['dis']}}"> {{$product->faName}}
                                        - حجم {{$Detail['volume']}}
                                        - {{$Detail['dis'] == 0 ? number_format($product->price) : number_format($Detail['dis'])}}
                                        تومان
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="login-mobile">حجم مورد نظر را انتخاب نمایید:</label>
                            <select name="volume_id" class="form-select select-volume-renew" id="reNew-volume-id">
                                <option value="0" data-price="0">هیچکدام
                                </option>
                                @for($i = 1;$i <= 5;$i++)
                                    @php $Price = __('order.Volume-Gb.'.$i) * 1500 @endphp
                                    <option value="{{$i}}" data-price="{{$Price}}">@lang('order.Volume-Name.'.$i)
                                        - {{number_format($Price)}} تومان
                                    </option>
                                @endfor
                            </select>
                            <input type="hidden" name="ip_id" value="{{$Order->id}}">
                            <input type="hidden" name="type" value="8">
                            <input type="hidden" name="product_id" value="0">
                        </div>
                        @csrf
                    </div>
                    <div class="modal-footer justify-content-center">
                        @php $product_price; @endphp
                        @if(CheckWallet() >= $product_price)
                            <a id="second-btn-renew" data-type="1" type="button"
                               class="reNew-with-wallet btn btn-success btn-shadow d-block pe-3 buy-with-wallet"
                               data-id="1" style="width: 47%">پرداخت با کیف پول</a>
                        @else
                            <a id="second-btn-renew" class="login-btn btn btn-info btn-shadow d-block pe-3"
                               style="width: 47%" type="submit">شارژ کیف پول</a>
                        @endif
                        <button id="btn-area-renew" class="login-btn btn btn-primary btn-shadow d-block pe-3"
                                style="width: 47%" type="submit">پرداخت با درگاه
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="failedIp" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-secondary">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item"><label class="nav-link fw-medium active" id="Login-tab-header"><i
                                        class="ci-unlocked me-2 mt-n1"></i>تمدید آی پی</label></li>
                    </ul>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body tab-content py-4">
                    <div class="mb-1">
                        کاربر گرامی،
                        <br>
                        سفارش شما بعلت عدم تمدید لغو شده است.
                        <br>
                        چنانچه درخواست فعال سازی مجدد را دارید، به پشتیبانی اعلام نمایید.
                    </div>
                </div>
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
                });
            }
        </script>
        @else
            @if($Order->status == 'pending')
                <div class="alert alert-info d-flex fs-sm mb-4 mt-5" role="alert">
                    <div class="alert-icon"><i class="ci-announcement"></i></div>
                    <div> با تشکر از خرید شما، به زودی اطلاعات آی پی برای شما ارسال خواهد شد.</div>
                </div>
            @elseif($Order->status == 'failed')
                <div class="alert alert-danger d-flex fs-sm mb-4 mt-5" role="alert">
                    <div class="alert-icon"><i class="ci-announcement"></i></div>
                    <div>این سفارش لغو شده است.</div>
                </div>
@endif
@endif
