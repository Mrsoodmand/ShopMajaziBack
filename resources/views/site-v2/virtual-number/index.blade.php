@extends('site-v2.layout.master',['MetaTitle' => 'خرید شماره مجازی ارزان | +100 کشور و سرویس | تحویل آنی'])
@section('content')
    <!-- Header Content -->
    <section class="position-relative bg-size-cover bg-position-center-x position-relative bg-header">
        <div class="container position-relative zindex-5 py-4 my-3">
            <div class="row justify-content-center">
                @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                    <div class="col-lg-3 col-12">
                        <img class="header-image gallery-item rounded-3"
                             src="https://shopmajazi.com/upload/virtual-number.jpg" width="250" height="250"
                             alt="{{$Page->h1}}">
                    </div>
                @endif
                <div class="col-lg-9 col-12">
                    @if(!is_null($Page))
                        <h1>{{$Page->h1}}</h1>
                        <p class="pb-3 text-white" style="text-align: justify">شماره مجازی" جزء آن دسته واژه هایی است که
                            امروزه در کسب و کارهای آنلاین
                            و به خصوص
                            بین المللی بسیار شنیده می شود هر چند که باید اشاره نمود افراد می توانند با اهداف شخصی نیز از
                            این
                            شماره ها استفاده نمایند. از همین رو می توان بدون صرف هزینه و زمان زیاد این شماره ها را از
                            "وب
                            سایت شماره مجازی" در سریع ترین زمان ممکن خریداری نمایید بدون آنکه دغدغه ارائه مدارک و تایید
                            آنها
                            را داشته باشید.</p>

                    @else
                        <h1>خرید شماره مجازی ارزان و تحویل آنی</h1>
                        <p class="pb-3 text-white" style="text-align: justify">شماره مجازی" جزء آن دسته واژه هایی است که
                            امروزه در کسب و کارهای آنلاین
                            و به خصوص
                            بین المللی بسیار شنیده می شود هر چند که باید اشاره نمود افراد می توانند با اهداف شخصی نیز از
                            این
                            شماره ها استفاده نمایند. از همین رو می توان بدون صرف هزینه و زمان زیاد این شماره ها را از
                            "وب
                            سایت شماره مجازی" در سریع ترین زمان ممکن خریداری نمایید بدون آنکه دغدغه ارائه مدارک و تایید
                            آنها
                            را داشته باشید.</p>
                    @endif

                    <div class="d-table scale-up delay-4 mx-auto mx-lg-0">
                        <button class="btn btn-primary slow-scroll" style="margin-left: 10px;margin-bottom: 10px;"
                                data-target="CountryArea">ثبت سفارش
                        </button>
                        <button class="btn btn-info slow-scroll" style="margin-left: 10px;margin-bottom: 10px;"
                                data-target="FaqArea">سوالات متداول
                        </button>
                        @if(!\Illuminate\Support\Facades\Auth::guard('user')->check())
                            <a class="btn btn-primary" target="_blank" style="margin-left: 10px;margin-bottom: 10px;"
                               href="{{secure_url('user/login')}}">ثبت نام</a>
                        @endif
                    </div>
                </div>
                @if(!\Jenssegers\Agent\Facades\Agent::isPhone())
                    <div class="col-lg-3 col-12">
                        <img class="header-image gallery-item rounded-3"
                             src="https://shopmajazi.com/upload/virtual-number.jpg" alt="Gallery image">
                    </div>
                @endif
            </div>
        </div>
    </section>    <!-- Header Content -->
    <!-- Header Content -->
    <!-- How it Works -->
    <section class="container pt-4 mb-3 text-center">
        <label class="h3 text-center pt-2">چگونه خرید کنم؟</label>
        <div class="row g-0 bg-light rounded-3">
            <div class="col-xl-4 col-lg-12 col-md-4 border-end">
                <div class="py-3">
                    <div class="align-items-center mx-auto py-1 px-1" style="text-align: center;max-width: 362px;">
                        <div class="ps-2"><i class="fa-regular fa-circle-1"></i>
                            <p class="mb-3 pt-1">بر روی سرویس مورد نظر کلیک کنید.</p>
                        </div>
                    </div>
                    <hr class="d-md-none d-lg-block d-xl-none">
                </div>
            </div>
            <div class="col-xl-4 col-lg-12 col-md-4 border-end">
                <div class="py-3">
                    <div class="align-items-center mx-auto py-1 px-1" style="text-align: center;max-width: 362px;">
                        <div class="ps-2"><i class="fa-regular fa-circle-2"></i>
                            <p class="mb-3 pt-1"> کشوری که قصد خرید از آنرا دارید انتخاب کنید.</p>
                        </div>
                    </div>
                    <hr class="d-md-none d-lg-block d-xl-none">
                </div>
            </div>
            <div class="col-xl-4 col-lg-12 col-md-4">
                <div class="py-3">
                    <div class="align-items-center mx-auto py-1 px-1" style="text-align: center;max-width: 362px;">
                        <div class="ps-2"><i class="fa-regular fa-circle-3"></i>
                            <p class="mb-3 pt-1">ثبت سفارش و پرداخت!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- How it Works -->
    @if(\Illuminate\Support\Facades\Auth::guard('user')->check())
        <!-- User Order -->
        <section class="container ps-lg-4 pe-lg-3 pt-4" id="UserNumbers">
            <section class="py-2 px-4 mt-2 shadow-lg mb-3 rounded-3">
                <div class="row px-1 p-3 mb-2 rounded scrollbar-juicy-peach">
                    <div class="col-12 col-lg-8">
                        <section class="py-1 px-1 mt-2">
                            <div class="d-flex align-items-center ps-2 shadow-sm mb-2"
                                 style="border-radius: 10px;height: 77px;">
                                <label class="h4 mb-0 me-2">آخرین شماره های دریافت شده</label>
                            </div>
                            <div class="row px-1 p-3 mb-3 rounded scrollbar-juicy-peach"
                                 style="height: auto;max-height: 500px; overflow:auto">
                                <span id="Number-Area" style="display: none"></span>
                                @if(count($Orders) != 0)
                                    @foreach($Orders as $order)
                                        @php
                                            $Detail = json_decode($order->detail,true);
                                            $now = \Illuminate\Support\Carbon::now();
                                             $expire = \Illuminate\Support\Carbon::parse($order->Order->expire_date);
                                             $dif = $now->diff($expire);
                                             $mins = $dif->i * 60;
                                             $sec = $mins + $dif->s;
                                        @endphp
                                        <div class="col-lg-6 col-md-4 col-sm-6 px-2 mb-grid-gutter @if($order->Order->status == 'pending') check-number check-number-{{$order->Order->id}}@endif"
                                             data-id="{{$order->Order->id}}">
                                            <div class="card product-card-alt">
                                                <div class="card-body bg-white d-block country-item shadow-sm rounded-3">
                                                    <div class="d-flex flex-wrap justify-content-between align-items-start pb-2">
                                                        <div class="product-thumb"><img
                                                                    src="{{secure_url($order->VirtualNumber->countryImage)}}"
                                                                    alt="{{$order->VirtualNumber->countryName}}"></div>
                                                        <div class="text-muted fs-xs me-1 order-service-name">{{$order->VirtualNumber->faName}}</div>
                                                        @if($order->Order->status == 'pending')
                                                            <div class="star-rating"
                                                                 id="order-status-{{$order->Order->id}}"><span
                                                                        class="badge bg-warning m-0">در انتظار دریافت کد</span>
                                                            </div>
                                                        @elseif($order->Order->status == 'complete')
                                                            <div class="star-rating"
                                                                 id="order-status-{{$order->Order->id}}"><span
                                                                        class="badge bg-success m-0">تکمیل شده</span>
                                                            </div>
                                                        @elseif($order->Order->status == 'failed')
                                                            <div class="star-rating"
                                                                 id="order-status-{{$order->Order->id}}"><span
                                                                        class="badge bg-danger m-0">لغو شده</span></div>
                                                        @endif
                                                    </div>
                                                    @if(!is_null($Detail))
                                                        <p class="product-title fs-sm mb-2 order-service-number"> شماره:
                                                            <a class="copy" data-copy="{{$Detail['number']}}"
                                                               href="javascript:;">{{$Detail['number']}} <i
                                                                        class="fa-light fa-copy"></i></a></p>
                                                        @if($order->Order->status == 'pending')
                                                            <p class="product-title fs-sm mb-2 timer-quick timer-{{$order->Order->id}}"
                                                               data-seconds-left="{{$sec}}"></p>
                                                        @endif
                                                        <div class="d-flex flex-wrap justify-content-between align-items-center button-area-{{$order->Order->id}}">
                                                            <div class="fs-sm me-2"><span
                                                                        class="fs-xs ms-1">پیامک:</span>
                                                                @if($order->Order->status == 'pending')
                                                                    <a class="code code-{{$order->Order->id}}"
                                                                       data-copy="" href="javascript:;">در انتظار دریافت
                                                                        کد <i class="fa fa-spin fa-spinner"></i></a>
                                                                @elseif($order->Order->status == 'complete')
                                                                    <a class="copy code" href="javascript:;"
                                                                       data-copy="{{$Detail['sms']}}">{{$Detail['sms']}}
                                                                        <i class="fa-light fa-copy"></i></a>
                                                                @else
                                                                    <a class="code" href="javascript:;">کدی دریافت
                                                                        نشده.</a>
                                                                @endif
                                                            </div>
                                                            @if($order->Order->status == 'pending')
                                                                <button class="order-button order-btn-{{$order->Order->id}} cancel-number btn btn-danger text-accent rounded-1 py-1 px-2"
                                                                        data-id="{{$order->Order->id}}">لغو
                                                                </button>
                                                            @else
                                                                <button class="order-button order-{{$order->id}} btn btn-info text-accent rounded-1 py-1 px-2">
                                                                    درخواست مجدد
                                                                </button>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <p class="product-title fs-sm mb-2 order-service-number"> شماره:<a
                                                                    href="javascript:;">شماره دریافت نشده.</a></p>
                                                        <div class="d-flex flex-wrap justify-content-between align-items-center button-area-{{$order->id}}">
                                                            <div class="fs-sm me-2"><span
                                                                        class="fs-xs ms-1">پیامک:</span>
                                                                @if($order->Order->status == 'pending')
                                                                    <a class="code code-{{$order->id}}" data-copy=""
                                                                       href="javascript:;">در انتظار دریافت کد <i
                                                                                class="fa fa-spin fa-spinner"></i></a>
                                                                @elseif($order->Order->status == 'complete')
                                                                    <a class="copy code" href="javascript:;"
                                                                       data-copy="{{$Detail['sms']}}">{{$Detail['sms']}}
                                                                        <i class="fa-light fa-copy"></i></a>
                                                                @else
                                                                    <a class="code" href="javascript:;">کدی دریافت
                                                                        نشده.</a>
                                                                @endif
                                                            </div>
                                                            @if($order->Order->status == 'pending')
                                                                <button class="order-button order-btn-{{$order->id}} cancel-number btn btn-danger text-accent rounded-1 py-1 px-2"
                                                                        data-id="{{$order->Order->id}}">لغو
                                                                </button>
                                                            @else
                                                                <button class="order-button order-{{$order->id}} btn btn-info text-accent rounded-1 py-1 px-2">
                                                                    درخواست مجدد
                                                                </button>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-lg-12 col-md-4 col-sm-6 px-2 mb-grid-gutter" id="NewOrder">
                                        <div class="card product-card-alt">
                                            <div class="card-body bg-white bg-faded-success d-block country-item shadow-sm rounded-3">
                                                <div class="d-flex flex-wrap justify-content-between align-items-start pb-2">
                                                    <div class="text-muted fs-xs me-1">سفارشی ثبت نشده است.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </section>
                    </div>
                    <div class="col-12 col-lg-4">
                        <section class="py-2 px-4 mt-2">
                            <div class="d-flex align-items-center ps-2 shadow-sm mb-2"
                                 style="border-radius: 10px;height: 77px;">
                                <label class="h4 mb-0 me-2">راهنمای استفاده</label>
                            </div>
                            <div class="card-body py-2 px-2 fs-md">
                                <p>1. ابتدا شماره را وارد اپلیکشین یا سایت مورد نظر می کنید. </p>
                                <p>2. سپس از سایت مورد نظر درخواست ارسال کد تایید می کنید. </p>
                                <p>3. در همین قسمت منتظر دریافت کد می شوید. </p>
                                <p>4. پس از دریافت کد، آنرا وارد اپلیکشین یا سایت مورد نظر می کنید. </p>
                            </div>
                        </section>
                    </div>
                </div>
            </section>
        </section>
        <!-- User Order -->
    @endif

    <!-- Services and countries -->
    <section class="container ps-lg-4 pe-lg-3 pt-4" id="CountryArea">
        <!-- Countries -->
        @if(\Illuminate\Support\Facades\Session::has('error'))
            @foreach(\Illuminate\Support\Facades\Session::get('error') as $value)
                <div class="alert alert-danger">
                    {{$value}}
                </div>
            @endforeach
        @endif
        <section class="py-2 px-4 mt-2 shadow-lg mb-5 rounded-3">
            <div class="d-flex align-items-center ps-2 mb-2" style="border-radius: 10px;height: 77px;">
                <label class="h4 mb-0 me-2 custom-width-10">سرویس ها</label>
                @if(!\Jenssegers\Agent\Facades\Agent::isPhone())
                    <div class="input-group">
                        <i class="ci-search position-absolute top-50 start-0 translate-middle-y fs-md ms-3"></i>
                        <input class="form-control border-0 shadow-none" id="CountrySearch" type="text"
                               placeholder="جستجو بر اساس نام سرویس">
                    </div>
                @endif
                <div class="d-flex align-items-center custom-margin-right-25">
                    <div class="dropdown py-4">
                    </div>
                </div>
            </div>
            @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                <div class="d-flex align-items-center ps-2 shadow-lg mb-2" style="border-radius: 10px">
                    <div class="input-group">
                        <i class="ci-search position-absolute top-50 start-0 translate-middle-y fs-md ms-3"></i>
                        <input class="form-control border-0 shadow-none" id="CountrySearch" type="text"
                               placeholder="جستجو بر اساس نام سرویس">
                    </div>
                </div>
            @endif
            <hr>
            <div class="row px-1 p-3 mb-2 rounded scrollbar-juicy-peach"
                 style=" height: 500px; overflow:auto">
                @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                    @foreach($Services as $service)
                        <div class="col-md-4 col-sm-4 col-12 country-search box-area" style="position: relative">
                            <div
                                    class="bg-white d-block country-item shadow-sm @if($service->id == 50) selected-country @endif rounded-3 py-3 py-sm-4 mb-grid-gutter get-country"
                                    id="country-{{$service->id}}" data-country="{{$service->id}}">
                                <img class="country-image d-block mx-auto"
                                     src="{{secure_url(str_replace('http://localhost:8000/','',$service->image))}}"
                                     alt="{{$service->faName}}">
                                <span class="service-name-2">{{$service->faName}} </span>
                            </div>
                            <button class="btn btn-icon me-2 fav-btn" data-type="country" data-id="{{$service->id}}"
                                    type="button" data-bs-toggle="tooltip" title="علاقه مندی ها"><i
                                        class="fa-regular fa-heart text-primary"></i></button>
                        </div>
                    @endforeach
                @else
                    @foreach($Services as $service)
                        <div class="col-md-4 col-sm-4 col-12 country-search box-area" style="position: relative">
                            <div
                                    class="bg-white d-block country-item shadow-sm @if($service->id == 50) selected-country @endif rounded-3 py-3 py-sm-4 mb-grid-gutter get-country"
                                    id="country-{{$service->id}}" data-country="{{$service->id}}">
                                <img class="d-block mx-auto country-image"
                                     src="{{secure_url(str_replace('http://localhost:8000/','',$service->image))}}"
                                     alt="{{$service->faName}}">
                                <span class="service-name-2">{{$service->faName}} </span>
                            </div>
                            <button class="btn btn-icon me-2 fav-btn" data-type="country" data-id="{{$service->id}}"
                                    type="button" data-bs-toggle="tooltip" title="علاقه مندی ها"><i
                                        class="fa-regular fa-heart text-primary"></i></button>
                        </div>
                    @endforeach
                @endif
            </div>
        </section>
        <!-- End Countries -->
        <!-- Products -->
        <section class="py-2 px-4 mt-2 shadow-lg mb-5 rounded-3">
            <!-- Heading-->
            <div class="d-flex align-items-center ps-2 mb-2" style="border-radius: 10px">
                <!-- Search-->
                <label class="h4 mb-0 me-2 custom-width-10">کشور ها</label>
                @if(!\Jenssegers\Agent\Facades\Agent::isPhone())
                    <div class="input-group">
                        <i class="ci-search position-absolute top-50 start-0 translate-middle-y fs-md ms-3"></i>
                        <input class="form-control border-0 shadow-none" id="ServiceSearch" type="text"
                               placeholder="جستجو بر اساس نام کشور">
                    </div>
                @endif
                <!-- Sort-->
                <div class="d-flex align-items-center custom-margin-right-25">
                    <div class="dropdown py-4">
                        <span class="nav-link-style fs-md fw-medium dropdown-toggle p-4 pointer"
                              data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="d-inline-block py-1"><i
                                        class="fa-regular fa-filter-list opacity-60 mt-n1 me-2"></i>فیلتر ها</span>
                        </span>
                        <ul class="dropdown-menu dropdown-menu-end" style="margin: 0px 42px;">
                            <li>
                                <div data-filter="h-l" class="pointer dropdown-item changeFilter"><i
                                            class="ci-rocket me-2 opacity-60"></i>بیشترین قیمت
                                </div>
                            </li>
                            <li>
                                <div data-filter="l-h" class="pointer dropdown-item changeFilter"><i
                                            class="ci-flag me-2 opacity-60"></i>کمترین قیمت
                                </div>
                            </li>
                            <li>
                                <div data-filter="n" class="pointer dropdown-item changeFilter"><i
                                            class="ci-loading me-2 opacity-60"></i>محبوب ترین
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                <div class="d-flex align-items-center ps-2 shadow-sm mb-2" style="border-radius: 10px">
                    <div class="input-group">
                        <i class="ci-search position-absolute top-50 start-0 translate-middle-y fs-md ms-3"></i>
                        <input class="form-control border-0 shadow-none" id="ServiceSearch" type="text"
                               placeholder="جستجو بر اساس نام کشور">
                    </div>
                </div>
            @endif
            <hr>
            <!-- Grid-->
            <div class="row px-1 p-3 mb-2 rounded scrollbar-juicy-peach"
                 style=" height: 500px; overflow:auto" id="Service-Area">

                @for($i = 0;$i <= 11; $i++)
                    <div class="col-md-4 col-sm-4 col-12 service-search box-area"
                         style="position: relative;margin-bottom: -10px">
                        <div class="bg-white d-block country-item shadow-sm rounded-3 py-3 py-sm-4 mb-grid-gutter buy-service">
                            <div class="skeleton skeleton-service">
                                <div class="skeleton-left flex1">
                                    <div class="square circle-service"></div>
                                </div>
                                <div class="skeleton-right flex2">
                                    <div class="line s-service-v4-title"></div>
                                    <div class="line s-service-v3-title"></div>
                                    <div class="line s-service-v2-title"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endfor

                {{--                @if(\Jenssegers\Agent\Facades\Agent::isPhone())--}}
                {{--                    @foreach($Products as $service)--}}
                {{--                        @php--}}
                {{--                            if ($service->type == 'pva') { $Price = $Dollar * $service->dollar_price; } else { $Price = $Ruble * $service->dollar_price; }--}}
                {{--                        @endphp--}}
                {{--                        <div class="col-md-4 col-sm-4 col-12 service-search box-area"--}}
                {{--                             style="position: relative;margin-bottom: -10px">--}}
                {{--                                            <div--}}
                {{--                                                    class="bg-white d-block country-item shadow-sm rounded-3 py-3 py-sm-4 mb-grid-gutter buy-service"--}}
                {{--                                                    data-id="{{$service->id}}">--}}
                {{--                                <div class="number-text">--}}
                {{--                                    <img class="service-image d-block mx-auto" src="{{secure_url($service->countryImage)}}"--}}
                {{--                                         alt="{{$service->countryName}}">--}}
                {{--                                    <span class="service-name">{{$service->countryName}}</span>--}}
                {{--                                    <div>--}}
                {{--                                        <span class="price-area-2">{{CovertVirtualNumberPrice(number_format($Price))}} تومان </span>--}}
                {{--                                    </div>--}}
                {{--                                </div>--}}
                {{--                                <button class="btn btn-icon me-2 btn-info service-buy-btn"--}}
                {{--                                        data-service="{{$service->id}}" type="button" data-bs-toggle="tooltip"--}}
                {{--                                        title="خرید"><i class="fa-regular fa-cart-shopping"></i>دریافت شماره--}}
                {{--                                </button>--}}
                {{--                            </div>--}}
                {{--                        </div>--}}
                {{--                    @endforeach--}}
                {{--                @else--}}
                {{--                    @foreach($Products as $service)--}}
                {{--                        @php--}}
                {{--                            if ($service->type == 'pva') { $Price = $Dollar * $service->dollar_price; } else { $Price = $Ruble * $service->dollar_price; }--}}
                {{--                        @endphp--}}
                {{--                        <div class="col-md-4 col-sm-4 col-12 service-search box-area"--}}
                {{--                             style="position: relative;margin-bottom: -10px">--}}
                {{--                            <div--}}
                {{--                                    class="bg-white d-block country-item shadow-sm rounded-3 py-3 py-sm-4 mb-grid-gutter buy-service"--}}
                {{--                                    data-id="{{$service->id}}">--}}
                {{--                                <img class="d-block mx-auto service-image" src="{{secure_url($service->countryImage)}}"--}}
                {{--                                     alt="{{$service->countryName}}">--}}
                {{--                                <span class="service-name">{{$service->countryName}}</span>--}}
                {{--                                <button class="btn btn-icon btn-info me-2 service-buy-btn"--}}
                {{--                                        data-service="{{$service->id}}" type="button" data-bs-toggle="tooltip"--}}
                {{--                                        title="خرید"><i class="fa-regular fa-cart-shopping"></i>دریافت شماره--}}
                {{--                                </button>--}}
                {{--                                <label class="btn btn-icon service-fav-btn"><span>{{CovertVirtualNumberPrice(number_format($Price))}} تومان </span></label>--}}
                {{--                            </div>--}}
                {{--                        </div>--}}
                {{--                    @endforeach--}}
                {{--                @endif--}}
            </div>
        </section>
        <!-- End Products -->
        <div class="pb-3"></div>
    </section>
    <!-- Services and countries -->
    <input type="hidden" id="countryId" value="50">

    @include('site-v2.components.why-shopmajazi')

    @if(!\Illuminate\Support\Facades\Auth::guard('user')->check())
        <!-- Start Content -->
        <section class="container ps-lg-4 pe-lg-3 pt-4">
            <section class="mb-3 pb-3">
                <div class="bg-light shadow-lg rounded-3 overflow-hidden">
                    <div class="row content-area">
                        <div class="col-lg-12 pt-2 pt-lg-4 pb-4">
                            <div class="pt-3 px-4 pe-lg-5 ps-lg-5">
                                <div class="post-container">
                                    {{--                                    <div class="gallery row pb-2" lg-uid="lg0">--}}
                                    {{--                                        <div class="col-sm-8 offset-lg-2"><img--}}
                                    {{--                                                    class="gallery-item rounded-3 mb-grid-gutter"--}}
                                    {{--                                                    src="{{secure_url('/site-v2/')}}/img/blog/single/01.jpg"--}}
                                    {{--                                                    alt="Gallery image">--}}
                                    {{--                                        </div>--}}
                                    {{--                                    </div>--}}
                                    <div class="post-content" id="content">
                                        @if(!is_null($Page))
                                            {!! $Page->long_detail !!}
                                        @endif
                                    </div>
                                    <div class="show-more-btn">
                                        <button class="btn btn-primary btn-shadow more-btn"><i
                                                    class="ci-book mt-n1 me-2"></i>مشاهده
                                            بیشتر
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <table>
                            <tbody>
                            <tr>
                                <th>نام سرویس</th>
                                <td>خرید شماره مجازی</td>
                            </tr>
                            <tr>
                                <th>تحویل آنی</th>
                                <td>24 ساعته</td>
                            </tr>
                            <tr>
                                <th>قیمت</th>
                                <td>از 5 هزارتومان</td>
                            </tr>
                            <tr>
                                <th>پشتیبانی</th>
                                <td>24 ساعته</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </section>
        <!-- End Content -->
    @endif

    @php $Faq = [
             [
            'q' => 'شماره مجازی چیست؟',
            'a' => 'شماره مجازی، یک شماره ابری میباشد که سیم کارتی ندارد و علاوه بر آن به دستگاه خاصی متصل نمیباشد و این شماره ها فقط قابلیت دریافت پیامک را دارند.',
            ], [
            'q' => 'چگونه شماره مجازی خریداری کنم؟',
            'a' => 'ابتدا سرویس مورد نظر را انتخاب کرده و سپس از کشور های موجود، کشور مورد نظر را انتخاب میکنید.',
            ],   [
            'q' => 'آیا پس از خرید شماره مجازی، سیم کارت در اختیار من قرار میگیرد؟',
            'a' => 'خیر، پس از خرید شماره مجازی، فقط یک شماره در اختیار شما قرار خواهد گرفت.',
            ],[
            'q' => 'پس از خرید شماره مجازی، چگونه کد تایید را دریافت میکنم؟',
            'a' => 'پس از خرید و استفاده شماره در اپلیکشین یا سایت مورد نظر و پس از دریافت کد توسط اوپراتور های ما، کد در سایت به شما نمایش داده خواهد شد.',
            ],[
            'q' => 'آیا با شماره مجازی میشه تماس گرفت؟',
            'a' => 'خیر، متاسفانه شماره های مجازی فقط قابلیت دریافت پیامک را دارند.',
            ],[
            'q' => 'چرا قیمت شماره های مجازی متغیر است؟',
            'a' => 'قیمت این شماره ها بر اساس کشور، اوپراتور و ارزش ارز آن کشور محاسبه میشود.',
            ],

]@endphp

    @include('site-v2.components.faq',[$Faq])
    @include('site-v2.components.comment',[$Comment,'Type' => 'page' ,'Id' => '1'])
    <div class="modal fade" id="virtual-number" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-secondary">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item"><label class="nav-link fw-medium active" id="Login-tab-header"><i
                                        class="fa-regular fa-cart-shopping"></i>خرید شماره مجازی</label></li>
                    </ul>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="{{route('user.service-bank-payment')}}"
                      class="needs-validation tab-pane fade show active" autocomplete="off" novalidate>
                    <div class="modal-body tab-content py-4">
                        <div class="table-responsive fs-md mb-4">
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>نام سرویس</th>
                                    <th>مبلغ</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(\Illuminate\Support\Facades\Auth::guard('user')->check() && $SelectedProduct != null)
                                    @php  if ($SelectedProduct->type == 'pva') { $Price = $Dollar * $SelectedProduct->dollar_price; } else { $Price = $Ruble * $SelectedProduct->dollar_price; } @endphp
                                    <tr>
                                        <td class="py-3 align-middle">
                                            <div class="d-flex align-items-center">
                                                <img id="ProductImage" src="{{secure_url($SelectedProduct->countryImage)}}"
                                                     width="39" alt="product-image">
                                                <div class="ps-2"><span id="ProductName"
                                                                        class="fw-medium text-heading me-1">{{$SelectedProduct->faName}}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 align-middle"
                                            id="ProductPrice">{{CovertVirtualNumberPrice(number_format($Price))}}</td>
                                        <input type="hidden" value="{{$SelectedProduct->id}}" name="product_id"
                                               id="ProductId">
                                        <input type="hidden" value="1" name="type">
                                        @csrf
                                    </tr>
                                @else
                                    <tr>
                                        <td class="py-3 align-middle">
                                            <div class="d-flex align-items-center">
                                                <img id="ProductImage" src="" width="39" alt="product-image">
                                                <div class="ps-2"><span id="ProductName"
                                                                        class="fw-medium text-heading me-1"></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 align-middle" id="ProductPrice"></td>
                                        <input type="hidden" value="" id="ProductId" name="product_id">
                                        <input type="hidden" value="1" name="type">
                                        @csrf
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        @if(\Illuminate\Support\Facades\Auth::guard('user')->check() && $SelectedProduct != null)
                            @if(CheckWallet() >= $Price)
                                <a id="second-btn" data-type="1" type="button"
                                   class="buy-with-wallet btn btn-success btn-shadow d-block pe-3 buy-with-wallet"
                                   data-id="{{$SelectedProduct->id}}" style="width: 47%">پرداخت با کیف پول</a>
                            @else
                                <a id="second-btn" class="login-btn btn btn-info btn-shadow d-block pe-3"
                                   style="width: 47%" type="submit">شارژ کیف پول</a>
                            @endif
                        @endif
                        <button id="btn-area" class="login-btn btn btn-primary btn-shadow d-block pe-3"
                                style="width: 47%" type="submit">پرداخت با درگاه
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@section('header')
    <link rel="stylesheet" href="{{secure_url('/site-v2/css/virtual-number.css?v=14')}}">
    <link rel="stylesheet" href="{{secure_url('/site-v2/css/selecton.css?v14')}}">
    <style>
        .pointer {
            cursor: pointer;
        }

        /* width */
        #CountryArea ::-webkit-scrollbar {
            width: 5px;
        }

        /* Track */
        #CountryArea ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        /* Handle */
        #CountryArea ::-webkit-scrollbar-thumb {
            background: #fe696a;
            border-radius: 10px;
        }

        /* Handle on hover */
        #CountryArea ::-webkit-scrollbar-thumb:hover {
            background: red;
        }

        /* width */
        #UserNumbers ::-webkit-scrollbar {
            width: 5px;
        }

        /* Track */
        #UserNumbers ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        /* Handle */
        #UserNumbers ::-webkit-scrollbar-thumb {
            background: #fe696a;
            border-radius: 10px;
        }

        /* Handle on hover */
        #UserNumbers ::-webkit-scrollbar-thumb:hover {
            background: red;
        }

    </style>
    <style>
        .box-area {
            position: relative;
            margin-bottom: -10px;
        }

        .product-thumb {
            position: absolute !important;
        }

        .product-thumb img {
            width: 40px;
            height: 40px
        }

        .order-service-name {
            margin-right: 47px;
        }

        .star-rating span {
            position: inherit !important;
        }

        .order-service-number {
            margin-top: 20px;
        }

        .order-service-number a {
            margin-right: 10px
        }

        .timer-quick {
            margin-top: -32px;
            position: absolute;
            left: 10px;
            display: flex;
            direction: ltr;
        }

        .code {
            font-size: 12px
        }

        .code i {
            margin-right: 10px
        }

        .order-button {
            font-size: 12px
        }

        .selected-country {
            background-color: beige !important;
        }

        .custom-margin-right-25 {
            margin-right: 25%;
        }

        #ProductImage {
            border-radius: 10px;
        }

        @media screen and (min-width: 1440px) {
            .custom-width-10 {
                width: 20%;
            }
        }
    </style>
@endsection
@section('footer')
    <script src="{{URL::asset('plugins/timer/jquery.simple.timer.js?v=2')}}"></script>

    <script>
        var btn = 0;
        $('.more-btn').click(function () {
            if (btn == 0) {
                btn = 1;
                $(this).empty().text('مشاهده کمتر')
            } else {
                btn = 0;
                $(this).empty().text('مشاهده بیشتر');
            }
            $('#content').toggleClass('height-auto');
        });
        $("#CountrySearch").keyup(function () {
            // Retrieve the input field text and reset the count to zero
            var filter = $(this).val();
            console.log('asd')
            count = 0;
            // Loop through the comment list
            $('.country-search').each(function () {
                // If the list item does not contain the text phrase fade it out
                if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                    $(this).hide();  // MY CHANGE
                    // Show the list item if the phrase matches and increase the count by 1
                } else {
                    $(this).show(); // MY CHANGE
                    count++;
                }
            });
        });
        $("#ServiceSearch").keyup(function () {

            // Retrieve the input field text and reset the count to zero
            var filter = $(this).val();
            console.log('asd')
            count = 0;
            // Loop through the comment list
            $('.service-search').each(function () {
                // If the list item does not contain the text phrase fade it out
                if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                    $(this).hide();  // MY CHANGE
                    // Show the list item if the phrase matches and increase the count by 1
                } else {
                    $(this).show(); // MY CHANGE
                    count++;
                }
            });
        });
        setTimeout(function () {
            $.ajax({
                url: '{{route('get-virtual-service')}}',
                dataType: 'json',
                method: "GET",
                data: {id: 50},
                success: function (data) {
                    var Services = data.success;
                    $('#Service-Area').empty();
                    $.each(Services, function (key, value) {
                        @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                        $('#Service-Area').append('',
                            '<div class="col-md-4 col-sm-4 col-12 service-search buy-service box-area" data-id="' + value.id + '" style="position: relative">' +
                            '<div class="bg-white d-block country-item shadow-sm rounded-3 py-3 py-sm-4 mb-grid-gutter">' +
                            '<div class="number-text">' +
                            '<img class="service-image d-block mx-auto" src="' + value.image + '" alt="' + value.name + '">' +
                            '<span class="service-name">' + value.name + ' </span>' +
                            '<div>' +
                            '<span class="price-area-2">' + value.price + ' تومان </span>' +
                            '</div>' +
                            '</div>' +
                            '<button class="btn btn-icon me-2 btn-info service-buy-btn" data-service="' + value.id + '" type="button" data-bs-toggle="tooltip" title="خرید"><i class="fa-regular fa-cart-shopping"></i>دریافت شماره</button>' +
                            '</div>' +
                            '</div>')
                        @else
                        $('#Service-Area').append('',
                            '<div class="col-md-4 col-sm-4 col-12 service-search buy-service" data-id="' + value.id + ' box-area" style="position: relative">' +
                            '<div class="bg-white d-block country-item shadow-sm rounded-3 py-3 py-sm-4 mb-grid-gutter">' +
                            '<img class="service-image d-block mx-auto" src="' + value.image + '" alt="' + value.name + '">' +
                            '<span class="service-name">' + value.name + ' </span>' +
                            '<button class="btn btn-icon me-2 btn-info service-buy-btn" data-service="' + value.id + '" type="button" data-bs-toggle="tooltip" title="خرید"><i class="fa-regular fa-cart-shopping"></i>دریافت شماره</button>' +
                            ' <label class="btn btn-icon service-fav-btn"><span>' + value.price + ' تومان </span></label>' +
                            '</div>' +
                            '</div>');
                        @endif
                    });
                },
                error: function () {

                }
            });
        }, 5000);
        $(document).on('click', '.get-country', function () {
            var country_id = $(this).data('country');
            $('#countryId').val(country_id);
            $('.country-item').removeClass('selected-country');
            $(this).addClass('selected-country');
            $.ajax({
                url: '{{route('get-virtual-service')}}',
                dataType: 'json',
                method: "GET",
                data: {id: country_id},
                success: function (data) {
                    var Services = data.success;
                    $('#Service-Area').empty();
                    $.each(Services, function (key, value) {
                        @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                        $('#Service-Area').append('',
                            '<div class="col-md-4 col-sm-4 col-12 service-search buy-service box-area" data-id="' + value.id + '" style="position: relative">' +
                            '<div class="bg-white d-block country-item shadow-sm rounded-3 py-3 py-sm-4 mb-grid-gutter">' +
                            '<div class="number-text">' +
                            '<img class="service-image d-block mx-auto" src="' + value.image + '" alt="' + value.name + '">' +
                            '<span class="service-name">' + value.name + ' </span>' +
                            '<div>' +
                            '<span class="price-area-2">' + value.price + ' تومان </span>' +
                            '</div>' +
                            '</div>' +
                            '<button class="btn btn-icon me-2 btn-info service-buy-btn" data-service="' + value.id + '" type="button" data-bs-toggle="tooltip" title="خرید"><i class="fa-regular fa-cart-shopping"></i>دریافت شماره</button>' +
                            '</div>' +
                            '</div>')
                        @else
                        $('#Service-Area').append('',
                            '<div class="col-md-4 col-sm-4 col-12 service-search buy-service" data-id="' + value.id + ' box-area" style="position: relative">' +
                            '<div class="bg-white d-block country-item shadow-sm rounded-3 py-3 py-sm-4 mb-grid-gutter">' +
                            '<img class="service-image d-block mx-auto" src="' + value.image + '" alt="' + value.name + '">' +
                            '<span class="service-name">' + value.name + ' </span>' +
                            '<button class="btn btn-icon me-2 btn-info service-buy-btn" data-service="' + value.id + '" type="button" data-bs-toggle="tooltip" title="خرید"><i class="fa-regular fa-cart-shopping"></i>دریافت شماره</button>' +
                            ' <label class="btn btn-icon service-fav-btn"><span>' + value.price + ' تومان </span></label>' +
                            '</div>' +
                            '</div>');
                        @endif
                    });
                    $([document.documentElement, document.body]).animate({scrollTop: $("#Service-Area").offset().top - 250}, 2000);
                },
                error: function () {
                }
            });
        });
        $(document).on('click', '.buy-service', function () {
            var id = $(this).data('id');
            $.ajax({
                url: '{{route('get-single-virtual-number')}}',
                method: "GET",
                dataType: 'JSON',
                data: {
                    p_id: id
                },
                success: function (data) {
                    var info = data.data
                    $('#ProductImage').attr('src', info.image);
                    $('#ProductId').val(info.id);
                    $('#ProductName').empty().text(info.serviceName);
                    $('#ProductPrice').empty().text(info.price + ' تومان ');
                    $('#second-btn').remove();
                    $('#btn-area').before(info.btn);
                    $('#virtual-number').modal('toggle');
                },
                error: function (err) {
                    var errors = err.responseJSON;
                    if (errors.status == 1) {
                        err.responseJSON.error.forEach(function (value, key) {
                            error(value)
                        });
                        $('.product-id').val(id);
                        $('.type').val('virtual-number');
                        $('#signin-modal').modal('toggle');
                    } else if (errors.status == 2) {
                        for (const [key, value] of Object.entries(err.responseJSON.error)) {
                            error(value)
                        }
                    }
                }
            });
        });
        $('.submit-comment').click(function () {
            var BtnId = $(this).attr('id');
            var text = $(this).data('text');
            var form = $(this).data('form');
            $('#' + BtnId).html('لطفا صبر کنید...');
            $('#' + BtnId).attr('disabled', true);
            form = document.querySelector('#' + form);
            $(form).ajaxSubmit({
                dataType: 'json',
                beforeSend: function () {
                    $('#' + BtnId).html('لطفا صبر کنید');
                },
                success: function (data) {
                    success(data.success)
                    $('#' + BtnId).html(text);
                    $('#' + BtnId).attr('disabled', false);
                    setTimeout(function () {
                        location.reload();
                    }, 2000);
                },
                error: function (data) {
                    console.log('error')
                    if (data.responseJSON.message) {
                        error('');
                    } else {
                        data.responseJSON.error.forEach(function (value) {
                            error(value);
                        });
                    }
                    $('#' + BtnId).html(text);
                    $('#' + BtnId).attr('disabled', false);
                }
            });
            return false;
        });
        $(document).on('click', '.copy', function () {
            $(this).data('copy');
            navigator.clipboard.writeText($(this).data('copy'));
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
        });
        $(document).on('click', '.changeFilter', function () {
            var serviceId = $('#countryId').val();
            var filter = $(this).data('filter');
            $.ajax({
                url: '{{route('filter-numbers')}}',
                dataType: 'json',
                method: "GET",
                data: {
                    id: serviceId,
                    filter: filter
                },
                beforeSend: function () {
                    $('#Service-Area').empty()
                    for(i = 0; i < 12; i++){
                        $('#Service-Area').append(' <div class="col-md-4 col-sm-4 col-12 service-search box-area" style="position: relative;margin-bottom: -10px"> <div class="bg-white d-block country-item shadow-sm rounded-3 py-3 py-sm-4 mb-grid-gutter buy-service"> <div class="skeleton skeleton-service"> <div class="skeleton-left flex1"> <div class="square circle-service"></div></div><div class="skeleton-right flex2"> <div class="line s-service-v4-title"></div><div class="line s-service-v3-title"></div><div class="line s-service-v2-title"></div></div></div></div></div>')
                    }
                },
                success: function (data) {
                    var Services = data.success;
                    $('#Service-Area').empty();
                    $.each(Services, function (key, value) {
                        @if(\Jenssegers\Agent\Facades\Agent::isPhone())
                        $('#Service-Area').append('',
                            '<div class="col-md-4 col-sm-4 col-12 service-search buy-service box-area" data-id="' + value.id + '" style="position: relative">' +
                            '<div class="bg-white d-block country-item shadow-sm rounded-3 py-3 py-sm-4 mb-grid-gutter">' +
                            '<div class="number-text">' +
                            '<img class="service-image d-block mx-auto" src="' + value.image + '" alt="' + value.name + '">' +
                            '<span class="service-name">' + value.name + ' </span>' +
                            '<div>' +
                            '<span class="price-area-2">' + value.price + ' تومان </span>' +
                            '</div>' +
                            '</div>' +
                            '<button class="btn btn-icon me-2 btn-info service-buy-btn" data-service="' + value.id + '" type="button" data-bs-toggle="tooltip" title="خرید"><i class="fa-regular fa-cart-shopping"></i>دریافت شماره</button>' +
                            '</div>' +
                            '</div>')
                        @else
                        $('#Service-Area').append('',
                            '<div class="col-md-4 col-sm-4 col-12 service-search buy-service" data-id="' + value.id + ' box-area" style="position: relative">' +
                            '<div class="bg-white d-block country-item shadow-sm rounded-3 py-3 py-sm-4 mb-grid-gutter">' +
                            '<img class="service-image d-block mx-auto" src="' + value.image + '" alt="' + value.name + '">' +
                            '<span class="service-name">' + value.name + ' </span>' +
                            '<button class="btn btn-icon me-2 btn-info service-buy-btn" data-service="' + value.id + '" type="button" data-bs-toggle="tooltip" title="خرید"><i class="fa-regular fa-cart-shopping"></i>دریافت شماره</button>' +
                            ' <label class="btn btn-icon service-fav-btn"><span>' + value.price + ' تومان </span></label>' +
                            '</div>' +
                            '</div>');
                        @endif
                    });
                    $([document.documentElement, document.body]).animate({scrollTop: $("#Service-Area").offset().top - 250}, 2000);
                },
                error: function () {
                }
            });
        })
        @if(\Illuminate\Support\Facades\Auth::guard('user')->check())
        <!-- خرید شماره با کیف پول -->
        $(document).on('click', '.buy-with-wallet', function () {
            var id = $(this).data('id');
            $.ajax({
                url: '{{secure_url('user/payment/wallet')}}/1/' + id + '',
                dataType: 'JSON',
                method: 'GET',
                beforeSend: function () {
                    $('.buy-with-wallet').prop('disabled', true);
                    $('.buy-with-wallet').empty().text('لطفا صبر کنید...');
                },
                success: function (data) {
                    $('#NewOrder').remove();
                    var Data = data.data;
                    console.log(Data);
                    $('#Number-Area').after('' +
                        '<div class="col-lg-6 col-md-4 col-sm-6 px-2 mb-grid-gutter check-number check-number-' + Data.id + '" data-id="' + Data.id + '">' +
                        '<div class="card product-card-alt">' +
                        '<div class="card-body bg-white d-block country-item shadow-sm rounded-3">' +
                        '<div class="d-flex flex-wrap justify-content-between align-items-start pb-2">' +
                        '<div class="product-thumb" style="position: absolute"><img src="{{secure_url('/')}}/' + Data.countryImage + '" alt="' + Data.serviceName + '" style="width: 40px;height: 40px"></div>' +
                        '<div class="text-muted fs-xs me-1 order-service-name">' + Data.serviceName + '</div>' +
                        '<div class="star-rating" id="order-status-' + Data.id + '"><span class="badge bg-warning m-0">در انتظار دریافت کد</span></div>' +
                        '</div>' +
                        '<p class="product-title fs-sm mb-2 order-service-number">شماره:<a class="copy" data-copy="' + Data.number + '"  href="javascript:;">' + Data.number + ' <i class="fa-light fa-copy"></i></a></p>' +
                        '<p class="product-title fs-sm mb-2 timer-quick timer-' + Data.id + '" data-seconds-left="600" style="margin-top: -29px;position: absolute;left: 10px;display: flex;direction: ltr;"></p>' +
                        '<div class="d-flex flex-wrap justify-content-between align-items-center button-area-' + Data.id + '">' +
                        '<div class="fs-sm me-2"><span class="fs-xs ms-1">پیامک:</span><a class="copy code code-' + Data.id + '" data-copy="" href="javascript:;">در انتظار دریافت کد <i class="fa fa-spin fa-spinner"></i></a></div>' +
                        '<button data-id="' + Data.id + '" class="order-button cancel-number order-btn-' + Data.id + ' btn btn-danger text-accent rounded-1 py-1 px-2">لغو</button>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>');
                    $('#virtual-number').modal('toggle');
                    success('شماره با موفقیت دریافت شد.');
                    $([document.documentElement, document.body]).animate({scrollTop: $("#UserNumbers").offset().top - 250}, 2000);
                    $('.timer-' + Data.id).startTimer();
                    $('.buy-with-wallet').prop('disabled', false);
                    $('.buy-with-wallet').empty().text('خرید با کیف پول');
                },
                error: function (err) {
                    var Error = err.responseJSON.error;
                    Error.forEach(function (value) {
                        error(value);
                    });
                    $('.buy-with-wallet').prop('disabled', false);
                    $('.buy-with-wallet').empty().text('خرید با کیف پول');
                }
            });
        });
        <!-- لغو شماره -->
        $(document).on('click', '.cancel-number', function () {
            var id = $(this).data('id');
            $.ajax({
                url: '{{route('cancel-virtual-number')}}',
                dataType: 'JSON',
                method: 'GET',
                data: {
                    id: id,
                },
                success: function (data) {
                    success('شماره با موفقیت لغو و مبلغ به کیف پول شما عودت داده شد.');
                    $('#order-status-' + id).empty().append('<div class="star-rating" id="order-status-' + id + '"><span class="badge bg-danger m-0" style="position: inherit!important;">لغو شده</span></div>')
                    $('.order-btn-' + id).remove();
                    $('.timer-' + id).remove();
                    $('.button-area-' + id).append(' <button class="order-button order-' + id + ' btn btn-info text-accent rounded-1 py-1 px-2">درخواست مجدد </button>');
                    $('.code-' + id).empty().append('کدی دریافت نشده.');
                    $('.check-number-' + id).removeClass('check-number');
                },
                error: function (err) {
                    var Error = err.responseJSON.error;
                    Error.forEach(function (value) {
                        error(value);
                    });
                }
            });
        });

        <!-- دریافت کد -->
        function fetchdata() {
            $('.check-number').each(function () {
                var id = $(this).data('id');
                $.ajax({
                    url: '{{secure_url('virtual-number/get-sms')}}',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        id: id
                    },
                    success: function (data) {
                        console.log(data);
                        $('.code-' + id).empty().append('' + data.code + '<i class="fa-light fa-copy"></i>');
                        $('.code-' + id).attr('data-copy', data.code);
                        $('.timer-' + id).remove();
                        $('.check-number-' + id).removeClass('check-number');
                        $('.order-btn-' + id).remove();
                        $('.button-area-' + id).append('<button class="order-button order-' + id + ' btn btn-info text-accent rounded-1 py-1 px-2">درخواست مجدد</button>');
                        $('#order-status-' + id).empty().append('<div class="star-rating" id="order-status-' + id + '"><span class="badge bg-success m-0">تکمیل شده</span></div>');
                        var audio = new Audio('{{secure_url('/site-v2/sound/SmsSound.wav')}}');
                        audio.play();
                    }, error: function (data) {
                        var data = data.responseJSON;
                        if (data.status == 2) {


                            $('.code-' + id).empty().append('' + data.code + '<i class="fa-light fa-copy"></i>');
                            $('.code-' + id).attr('data-copy', data.code);
                            $('.timer-' + id).remove();
                            $('.check-number-' + id).removeClass('check-number');
                            $('.order-btn-' + id).remove();
                            $('.button-area-' + id).append('<button class="order-button order-' + id + ' btn btn-info text-accent rounded-1 py-1 px-2">درخواست مجدد</button>');
                            $('#order-status-' + id).empty().append('<div class="star-rating" id="order-status-' + id + '"><span class="badge bg-success m-0">تکمیل شده</span></div>');

                            var audio = new Audio('{{secure_url('/site-v2/sound/SmsSound.wav')}}');
                            audio.play();

                        } else if (data.status == 3) {
                            success('مدت زمان دریافت کد برای این شماره به اتمام رسید، وجه به کیف پول شما مجدد شارژ گردید.');
                            $('#order-status-' + id).empty().append('<div class="star-rating" id="order-status-' + id + '"><span class="badge bg-danger m-0" style="position: inherit!important;">لغو شده</span></div>')
                            $('.code-' + id).empty().append('کدی دریافت نشده.');
                            $('.check-number-' + id).removeClass('check-number');
                        }
                    }
                });
            });
        }

        $(document).ready(function () {
            $('.timer-quick').startTimer();
            setInterval(fetchdata, 5000);

            @if(\Illuminate\Support\Facades\Session::has('VirtualNumberSuccess'))
            $([document.documentElement, document.body]).animate({scrollTop: $("#UserNumbers").offset().top - 250}, 2000);
            @endif
        });
        @if($SelectedProduct != null)
        $(document).ready(function () {
            $('#virtual-number').modal('toggle');
        });
        @endif
        @endif

    </script>
@endsection
@section('seo')
    @php
        $seo['meta_title'] = 'خرید شماره مجازی ارزان | +100 کشور و سرویس | تحویل آنی';
        $seo['meta_desc'] = 'خرید شماره مجازی ارزان برای تمامی سایت ها و شبکه های اجتماعی. خرید شماره مجازی تلگرام، خرید شماره مجازی واتساپ، خرید شماره مجازی اپل آیدی | شاپ مجازی';
        $seo['image'] = 'https://shopmajazi.com/upload/virtual-number.jpg';
    @endphp
    @include('site-v2.layout.seo')
@endsection
